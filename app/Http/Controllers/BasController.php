<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class BasController extends Controller
{
    public function sendResponse($result, $message = null, $code = 200)
    {
        $response = [
            'success' => true,
            'message' => $message,
            'code' => $code,
            'data' => $result,
        ];

        return response()->json($response, $code);
    }



    public function sendError($error, $errorMessages = [], $code = 400)
    {
        $response = [
            'success' => false,
            'message' => $error,
            'code' => $code

        ];

        if (!empty($errorMessages)) {
            $response['data'] = $errorMessages;
        }

        return response()->json($response, $code);
    }

}
