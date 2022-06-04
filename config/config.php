<?php

// You can place your custom package configuration in here.
return [
    /*
     * The Merchant ID
     *
     * You need to contact BillDesk to request your id.
     */
    'merchant_id' => env('BILLDESK_MERCHANT_ID'),

    // The Merchant Security Key
    'security_id' => env('BILLDESK_SECURITY_ID'),

    // The merchant Checksum Key
    'checksum_key' => env('BILLDESK_CHECKSUM_KEY'),

    // The UAT Prefix provided by Billdesk
    'uat_prefix' => env('BILLDESK_UAT_PREFIX', ''),

    /*
     * Response URL used by BillDesk to direct the user back to your platform after a transaction is completed
     *
     * Example: https://localhost.test/billdesk/payment/callback
     */
    'response_url' => env('BILLDESK_RESPONSE_URL'),

    /*
     * The response url path without the domain and scheme
     *
     * Example: billdesk/payment/callback
     */
    'response_path' => env('BILLDESK_RESPONSE_PATH'),

    /*
     * host-to-host url used by BILLDESK to send direct messages to your app without the need for users actions
     *
     * Example: https://localhost.test/BillDesk/payment/webhook
     */
    'webhook_url' => env('BILLDESK_WEBHOOK_URL'),

    /*
     * The indirect url path without the domain and scheme
     *
     * Example: BillDesk/payment/webhook
     */
    'webhook_path' => env('BILLDESK_WEBHOOK_PATH'),

    // Middleware
    'middleware' => ['web'],

    /*
     * The Default Currency
     *
     * set the default currency code used for transaction. You can reach out to BILLDESK to
     * find out what other currency are supported
     */
    'currency' => env('BILLDESK_CURRENCY', 'INR'),

    /*
     * Urls List
     *
     * the list of urls for uat and production
     *
     * each url is used for a specific request, please refer to documentation to learn more about when to use
     * each url.
     *
     */
    'urls' => [
        'uat' => [
            'payment_request'     => 'https://uat.billdesk.com/pgidsk/PGIMerchantPayment',
            'transaction_enquiry' => 'https://uat.billdesk.com/pgidsk/PGIQueryController',
        ],
        'production' => [
            'payment_request'     => 'https://pgi.billdesk.com/pgidsk/PGIMerchantPayment',
            'transaction_enquiry' => 'https://www.billdesk.com/pgidsk/PGIQueryController',
        ],
    ],
];
