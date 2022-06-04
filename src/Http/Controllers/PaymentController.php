<?php

namespace JagdishJP\Billdesk\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use JagdishJP\Billdesk\Messages\AuthorizationRequest;

class PaymentController extends Controller
{
    /**
     * Initiate the request authorization message to FPX.
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function handle(Request $request)
    {
        return view('billdesk::redirect_to_bank', [
            'request' => (new AuthorizationRequest())->handle($request->all()),
        ]);
    }
}
