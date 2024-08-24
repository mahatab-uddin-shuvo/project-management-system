<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BasController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends BasController
{
    public function logout(): JsonResponse
    {
        Auth::user()->token()->revoke();
        return $this->sendResponse('', 'Logged out.');
    }

}
