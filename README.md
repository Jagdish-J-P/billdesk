# BillDesk Payment Gateway Integration in Laravel

 Repository has been developed to integrate BillDesk Payment Gateway in Laravel.
 
 This repository is currently in development. There might be some exception.
 
## Installation

You can install the package via composer:

```bash
composer require jagdish-j-p/billdesk
```

Then run the publish command to publish the config files and support controller and view

```bash
php artisan billdesk:publish
```

This will generate the following files

- The config file with default setup for you to override `billdesk.php`
- The controller that will receive payment response and any host-to-host events `Http/Controllers/Billdesk/Controller.php`
- The assets in public directory.
- The view file with default html for you to override `payment.blade.php`. Note do not change form action URL `billdesk.payment.auth.request`.

## Setups

1. Add your response urls and your Merchant Id, Security Id and Cheksum Key to the `.env` file.

```php
BILLDESK_RESPONSE_URL=https://app.test/billdesk/payments/callback
BILLDESK_RESPONSE_PATH=billdesk/payments/callback
BILLDESK_WEBHOOK_URL=https://app.test/billdesk/payments/webhook
BILLDESK_WEBHOOK_PATH=billdesk/payments/webhook

BILLDESK_MERCHANT_ID=
BILLDESK_SECURITY_ID=
BILLDESK_CHECKSUM_KEY=
BILLDESK_UAT_PREFIX="test-prefix"
```

2. Run migration to add the transactions table

```bash
php artisan migrate
```

## Usage

1. You can visit <a href='http://app.test/billdesk/initiate/payment'>http://app.test/billdesk/initiate/payment</a> for the payment flow demo of web integration.

2. Handle the payment response in `Http/Controllers/Billdesk/Controller.php`

```php
    /**
     * This will be called after the user approve the payment
     * on the bank side
     *
     * @param Request $request
     * @return Response
     */
    public function callback(Request $request)
    {
        $response = $request->handle();

        if ($response['response_format'] == 'JSON')
            return response()->json(['response' => $response, 'billdesk_response' => $request->all()]);
        dd($response, $request); // Remove this line and modify as per your needs.
        // Update your order status
    }

    /**
     * This will handle any direct call from BillDesk
     *
     * @param Request $request
     * @return string
     */
    public function webhook(Request $request)
    {
        $response = $request->handle();

        // Update your order status

        return 'OK';
    }

	
```

3. Check Status of all pending transactions using command

```bash
php artisan billdesk:payment-status
```

4. Check Status of specific transaction using command pass comma saperated order reference ids.

```bash
php artisan billdesk:payment-status reference_id1,reference_id2,reference_id3
```

5. Check transaction status from Controller

```php

use JagdishJP/Billdesk/Billdesk;

/**
 * Returns status of transaction
 * 
 * @param string $reference_id reference order id
 * @return array
 */
$status = Billdesk::getTransactionStatus($reference_id);
```

You can also override `payment.blade.php` with your custom design to integrate with your layout. but do not change `name` attribute of html controls and `action` URL of form.

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing

Contrubuters are welcome and will be given full credit.

### Security

If you discover any security related issues, please email jagdish.j.ptl@gmail.com instead of using the issue tracker.

## Credits

- [Jagdish-J-P](https://github.com/jagdish-j-p)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## Packagify

This package was generated using the [Packagify](https://github.com/jagdish-j-p/packagify).
