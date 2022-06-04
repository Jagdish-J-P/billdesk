<?php

namespace JagdishJP\Billdesk;

use JagdishJP\Billdesk\Messages\TransactionEnquiry;

class Billdesk
{
    /**
     * Returns status of transaction.
     *
     * @param string $reference_id reference order id
     *
     * @return array
     */
    public static function getTransactionStatus(string $reference_id)
    {
        $transactionEnquiry = new TransactionEnquiry();
        $transactionEnquiry->handle(compact('reference_id'));

        $dataList = $transactionEnquiry->getData();

        $response = $transactionEnquiry->connect($dataList);

        $responseData = $transactionEnquiry->parseResponse($response);

        if ($responseData === false) {
            return [
                'status'         => 'failed',
                'message'        => 'We could not find any data',
                'transaction_id' => null,
                'reference_id'   => $reference_id,
            ];
        }

        return $responseData;
    }
}
