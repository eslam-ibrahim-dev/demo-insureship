<?php

namespace App\Http\Controllers\Client\Auth;

use App\Http\Controllers\Controller;
use App\Models\ClientLogin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;


class AuthController extends Controller
{
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $credentials = $request->only(['username', 'password']);

        try {
            if (!$token = auth('client')->attempt($credentials)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized: Invalid username or password.',
                ], 401);
            }
            $user = auth('client')->user()->load('permissions', 'client');

            return response()->json([
                'status' => 'success',
                'message' => 'Login successful',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'username' => $user->username,
                        'email' => $user->email,
                        'superclient_id' => $user->client->superclient_id,
                        'test' => $user->client->is_test_account,
                        'status' => $user->client->status,
                        'domain' => $user->client->domain,
                        'referral_id' => $user->client->referral_id,
                        'apikey' => $user->client->apikey,
                        'permissions' => $user->permissions->pluck('module'),
                    ],
                    'token' => $token,
                    'token_type' => 'bearer',
                    'expires_in' => auth('client')->factory()->getTTL() * 60, // Fixed this line
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([

                'status' => 'error',
                'message' => 'An error occurred during login',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function me()
    {
        try {
            auth()->shouldUse('client');
            $user = JWTAuth::parseToken()->authenticate();

            if (!$user) {
                return response()->json(['error' => 'Unauthenticated'], 401);
            }

            return response()->json([
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'permissions' => $user->permissions->pluck('module')
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Invalid token'], 401);
        }
    }
}
