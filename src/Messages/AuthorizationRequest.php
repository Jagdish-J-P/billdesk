<?php

namespace JagdishJP\Billdesk\Messages;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Validator;
use JagdishJP\Billdesk\Contracts\Message as Contract;
use JagdishJP\Billdesk\Models\Transaction;
use JagdishJP\Billdesk\Traits\Encryption;

class AuthorizationRequest extends Message implements Contract
{
    use Encryption;

    /** Message Url */
    public $url;

    public function __construct()
    {
        parent::__construct();

        $this->url = App::environment('production')
            ? Config::get('billdesk.urls.production.payment_request')
            : Config::get('billdesk.urls.uat.payment_request');
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
            'remark'          => 'nullable',
            'amount'          => 'required',
            'customer_name'   => 'required',
            'customer_email'  => 'required',
        ])->validate();

        $this->responseFormat = $data['response_format'] ?? 'HTML';

        $this->reference       = $data['reference_id'];
        $this->timestamp       = date('YmdHis');
        $this->amount          = $data['amount'];
        $this->additionalInfo1 = $data['customer_name']  ?? 'NA';
        $this->additionalInfo2 = $data['customer_email'] ?? 'NA';
        $this->additionalInfo3 = $data['remark']         ?? 'NA';
        $this->additionalInfo4 = $this->id;
        $this->additionalInfo5 = $this->responseFormat;
        $this->msg             = $this->format();
        $this->checksum        = $this->encrypt($this->msg);

        $this->msg .= "|{$this->checksum}";

        $this->saveTransaction();

        return $this;
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
     * returns collection of all fields.
     *
     * @return collection
     */
    public function list()
    {
        return collect([
            'merchantID'      => $this->merchantId,
            'uniqueTxnID'     => $this->uatPrefix . ($this->reference ?? uniqid()),
            'naField1'        => $this->naData1,
            'txnAmount'       => $this->amount,
            'naField2'        => $this->naData2,
            'naField3'        => $this->naData3,
            'naField4'        => $this->naData4,
            'currencyType'    => $this->currencyType,
            'naField5'        => $this->naData5,
            'typeField1'      => $this->typeField1,
            'securityId'      => $this->securityId,
            'naField7'        => $this->naData7,
            'naField8'        => $this->naData8,
            'typeField2'      => $this->typeField2,
            'additionalInfo1' => $this->additionalInfo1,
            'additionalInfo2' => $this->additionalInfo2,
            'additionalInfo3' => $this->additionalInfo3,
            'additionalInfo4' => $this->additionalInfo4,
            'additionalInfo5' => $this->additionalInfo5,
            'additionalInfo6' => $this->additionalInfo6,
            'additionalInfo7' => $this->additionalInfo7,
            'RU'              => $this->ResponseUrl,
        ]);
    }

    /**
     * Save request to transaction.
     */
    public function saveTransaction()
    {
        $transaction                  = new Transaction();
        $transaction->unique_id       = $this->id;
        $transaction->reference_id    = $this->reference;
        $transaction->response_format = $this->responseFormat;
        $transaction->request_payload = $this->list()->toJson();
        $transaction->save();
    }
}
