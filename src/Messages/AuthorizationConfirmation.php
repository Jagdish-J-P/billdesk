<?php

namespace JagdishJP\Billdesk\Messages;

use Exception;
use JagdishJP\Billdesk\Constant\Response;
use JagdishJP\Billdesk\Contracts\Message as Contract;
use JagdishJP\Billdesk\Models\Transaction;
use JagdishJP\Billdesk\Traits\Encryption;

class AuthorizationConfirmation extends Message implements Contract
{
    use Encryption;

    public const STATUS_SUCCESS = 'succeeded';

    public const STATUS_FAILED = 'failed';

    public const STATUS_PENDING = 'pending';

    public const STATUS_SUCCESS_CODE = '0300';

    public const STATUS_PENDING_CODE = '0002';

    /**
     * handle a message.
     *
     * @param array $options
     *
     * @return mixed
     */
    public function handle($options)
    {
        $this->response = @$options['msg'];
        $response       = $this->list();
        $this->checksum = $response['CheckSum'];

        try {
            $this->verifyResponse();

            $this->reference         = $response['UniqueTxnID'];
            $this->transaction_id    = $response['TxnReferenceNo'];
            $this->id                = $response['AdditionalInfo4'];
            $this->transactionStatus = $response['AuthStatus'];

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
                'message'         => @Response::STATUS[$this->transactionStatus] ?? 'Payment Request Failed',
                'transaction_id'  => $this->transaction_id,
                'reference_id'    => $this->reference,
                'response_format' => $this->responseFormat,
            ];
        }
        catch (Exception $e) {
            return [
                'status'          => self::STATUS_FAILED,
                'message'         => $e->getMessage(),
                'transaction_id'  => $this->transaction_id,
                'reference_id'    => $this->reference,
                'response_format' => $this->responseFormat,
            ];
        }
    }

    /**
     * Format data for checksum.
     *
     * @return string
     */
    public function format()
    {
        return $this->list()->except('CheckSum')->join('|');
    }

    /**
     * returns collection of all fields.
     *
     * @return collection
     */
    public function list()
    {
        $this->responseValues = explode('|', $this->response);

        return collect(array_combine($this->responseKeys, $this->responseValues));
    }

    // To validate if response is received from Billdesk or not.
    protected function verifyResponse()
    {
        $result = false;

        if ($this->format()) {
            if ($this->checksum != strtoupper(hash_hmac('sha256', $this->format(), $this->checksumKey, false))) {
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
        $transaction = Transaction::where(['unique_id' => $this->id])->firstOrNew();

        $transaction->reference_id = $this->reference;
        $transaction->request_payload ??= '';
        $transaction->response_format ??= '';
        $transaction->unique_id          = $this->id;
        $transaction->transaction_id     = $this->transaction_id;
        $transaction->transaction_status = $this->transactionStatus;
        $transaction->response_payload   = $this->list()->toJson();
        $transaction->save();

        return $transaction->response_format;
    }
}
