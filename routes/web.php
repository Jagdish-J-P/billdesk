<?php

use App\Http\Controllers\Billdesk\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;
use JagdishJP\Billdesk\Http\Controllers\PaymentController;

$webhookPath  = Config::get('billdesk.webhook_path');
$responsePath = Config::get('billdesk.response_path');

Route::get(
    'billdesk/initiate/payment/{initiated_from?}/{test?}',
    function (Request $request, $initiated_from = 'HTML', $test = '') {
        $response_format = $initiated_from == 'app' ? 'JSON' : 'HTML';

        return view('billdesk::payment', compact('test', 'response_format', 'request'));
    }
)->name('billdesk.initiate.payment');

Route::post('billdesk/payment/request', [PaymentController::class, 'handle'])->name('billdesk.payment.auth.request');

Route::post($webhookPath, [Controller::class, 'webhook'])/* ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]) */ ->name('billdesk.payment.webhook');
Route::post($responsePath, [Controller::class, 'callback'])/* ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]) */ ->name('billdesk.payment.response.url');
