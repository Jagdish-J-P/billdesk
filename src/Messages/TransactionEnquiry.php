<?php

namespace JagdishJP\Billdesk\Messages;

use Exception;
use GuzzleHttp\Client;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Validator;
use JagdishJP\Billdesk\Contracts\Message as Contract;
use JagdishJP\Billdesk\Models\Transaction;
use JagdishJP\Billdesk\Traits\Encryption;

class TransactionEnquiry extends Message implements Contract
{
    use Encryption;

    public const REQUEST_TYPE = '0122';

    public const RESPONSE_TYPE = '0130';

    public const STATUS_SUCCESS = 'succeeded';

    public const STATUS_FAILED = 'failed';

    public const STATUS_PENDING = 'Pending';

    public const STATUS_SUCCESS_CODE = '0300';

    public const STATUS_PENDING_CODE = '0002';

    /** Message Url */
    public $url;

    public function __construct()
    {
        parent::__construct();

        $this->url = App::environment('production')
            ? Config::get('billdesk.urls.production.transaction_enquiry')
            : Config::get('billdesk.urls.uat.transaction_enquiry');

        $this->typeField1 = self::REQUEST_TYPE;
    }

    /**
     * handle a message.
     *
     * @param array $options
     *
     * @return mixed
     */
    public function handle($options)
    {
        $data = Validator::make($options, [
            'reference_id'    => 'required',
            'response_format' => 'nullable',
        ])->validate();

        $tranction = Transaction::where('reference_id', $data['reference_id'])->firstOrFail();

        $data = json_decode($tranction->request_payload, true);

        $this->reference      = $data['uniqueTxnID'];
        $this->checksum       = $this->encrypt($this->format());
        $this->responseFormat = $data['response_format'] ?? 'HTML';

        return $this;
    }

    /**
     * connect and excute the request to FPX server.
     *
     * @param array $dataList
     */
    public function connect(array $dataList)
    {
        $client   = new Client();
        $response = $client->request('POST', $this->url, [
            'form_params' => $dataList,
        ]);

        return $response->getBody();
    }

    /**
     * get request data from.
     */
    public function getData()
    {
        $data             = $this->list();
        $data['checksum'] = $this->encrypt($this->format());

        return ['msg' => $data->join('|')];
    }

    /**
     * returns collection of all fields.
     *
     * @return collection
     */
    public function list()
    {
        return collect([
            'RequestType'     => $this->typeField1 ?? '',
            'MerchantID'      => $this->merchantId ?? '',
            'UniqueTxnID'     => $this->reference  ?? '',
            'TransactionTime' => $this->timestamp  ?? now()->format('YmdHis'),
        ]);
    }

    /**
     * Parse the status response.
     *
     * @param mixed $response
     */
    public function parseResponse($response)
    {
        if ($response == 'ERROR' || ! $response) {
            return false;
        }

        $this->response       = $response;
        $this->responseValues = $this->responseList();

        $this->checksum = $this->responseValues['CheckSum'];

        $this->verifyResponse();

        $this->transactionStatus = $this->responseValues['AuthStatus'];
        $this->reference         = $this->responseValues['UniqueTxnID'];
        $this->transaction_id    = $this->responseValues['TxnReferenceNo'];
        $this->id                = $this->responseValues['AdditionalInfo4'];

        $this->responseFormat = $this->saveTransaction();

        if ($this->transactionStatus == self::STATUS_SUCCESS_CODE) {
            return [
                'status'          => self::STATUS_SUCCESS,
                'message'         => 'Payment is successfull',
                'transaction_id'  => $this->transaction_id,
                'reference_id'    => $this->reference,
                'response_format' => $this->responseFormat,
            ];
        }

        if ($this->transactionStatus == self::STATUS_PENDING_CODE) {
            return [
                'status'          => self::STATUS_PENDING,
                'message'         => 'Payment Transaction Pending',
                'transaction_id'  => $this->transaction_id,
                'reference_id'    => $this->reference,
                'response_format' => $this->responseFormat,
            ];
        }

        return [
            'status'          => self::STATUS_FAILED,
            'message'         => $this->responseValues['ErrorDescription'] ?? 'Payment Request Failed',
            'transaction_id'  => $this->transaction_id,
            'reference_id'    => $this->reference,
            'response_format' => $this->responseFormat,
        ];
    }

    /**
     * Format data for checksum.
     *
     * @return string
     */
    public function format()
    {
        return $this->list()->join('|');
    }

    /**
     * returns string in required response format.
     *
     * @return string
     */
    /**
     * Format data for checksum.
     *
     * @return string
     */
    public function responseFormat()
    {
        return $this->responseList()->except('CheckSum')->join('|');
    }

    /**
     * returns collection of all fields.
     *
     * @return collection
     */
    public function responseList()
    {
        $responseValues = explode('|', $this->response);

        return collect(array_combine($this->queryResponseKeys, $responseValues));
    }

    // To validate if response is received from Billdesk or not.
    protected function verifyResponse()
    {
        $result = false;

        if ($this->responseFormat()) {
            if ($this->checksum != strtoupper(hash_hmac('sha256', $this->responseFormat(), $this->checksumKey, false))) {
                throw new Exception('Failed to verify request origin.');
            }
        }

        return $result;
    }

    /**
     * Save response to transaction.
     *
     * @return string initiated from
     */
    public function saveTransaction()
    {
        $transaction = Transaction::where(['reference_id' => $this->reference])->first();

        $transaction->transaction_id     = $this->transaction_id;
        $transaction->transaction_status = $this->transactionStatus;
        $transaction->response_payload   = $this->responseList()->toJson();
        $transaction->save();

        return $transaction->response_format;
    }
}
