<?php

namespace App\Http\Controllers\Billdesk;

use App\Http\Controllers\Controller as BaseController;
use JagdishJP\Billdesk\Http\Requests\AuthorizationConfirmation as Request;

class Controller extends BaseController
{
    /**
     * @param Request $request
     *
     * @return Response
     */
    public function callback(Request $request)
    {
        $response = $request->handle();

        if ($response['response_format'] == 'JSON') {
            return response()->json(['response' => $response, 'billdesk_response' => $request->all()]);
        }

        // Update your order status
    }

    /**
     * @param Request $request
     *
     * @return string
     */
    public function webhook(Request $request)
    {
        $response = $request->handle();

        // Update your order status

        return 'OK';
    }
}
