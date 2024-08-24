<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\BasController;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class AuthController extends BasController
{
    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email|unique:users,email',
            'password' => 'required',
            'c_password' => 'required|same:password',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $input = $request->all();
        $input['password'] = bcrypt($input['password']);
        $user = User::create($input);
        $success['name'] = $user->name;

        return $this->sendResponse($success, 'User register successfully.');
    }

    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        if (Auth::attempt(["email" => $request->email, "password" => $request->password])) {
            $user = Auth::user();

            $response = [

                'id' => Auth::user()->getAuthIdentifier(),
                'email' => Auth::user()->email,
                'name' => Auth::user()->name,
                'roles_names' => $user->getRoleNames(),
                'permissions' => $user->getPermissionsViaRoles()->pluck("name"),
                'token_type' => 'Bearer',
                'token' => $user->createToken('MyApp')->accessToken,
            ];

            return $this->sendResponse($response, 'Login successfully.');

        }
        else {
            $user = User::where('email', $request->email)->first();
            if (isset($user)) {
                $error['message'] = "The password you have entered is incorrect.";
                $error['code'] = "AUTHENTICATION_ERROR";
                return $this->sendError('Logical Error.', $error);
            }
            $error['message'] = "The email you have entered is incorrect.";
            $error['code'] = "AUTHENTICATION_ERROR";
            return $this->sendError('Logical Error.', $error);
        }
    }
}
