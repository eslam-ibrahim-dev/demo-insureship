<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Models\Admin;
use App\Models\AdminPermission;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;  



class AuthController extends Controller
{

    /**
     * Register a User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'email' => 'required|email|unique:osis_admin,email',
            'username' => 'required|string|unique:osis_admin,username',
            'level' => 'required|string',
            'password' => 'required|confirmed',
            'dashboard' => 'nullable|string',
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'permissions' => 'required|array',
            'permissions.*' => 'string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }
        DB::beginTransaction();

        try {
            $image = $request->file('profile_picture')
                ? $request->file('profile_picture')->store('images', 'public')
                : null;

            $admin = Admin::create([
                'name' => $request->name,
                'email' => $request->email,
                'username' => $request->username,
                'level' => $request->level,
                'dashboard' => $request->dashboard,
                'profile_picture' => $image,
                'password' => bcrypt($request->password),
            ]);

            foreach ($request->permissions as $moduleName) {
                AdminPermission::create([
                    'admin_id' => $admin->id,
                    'module' => $moduleName
                ]);
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Admin registered successfully',
                'admin' => $admin->load('permissions'),
            ], 201);
        } catch (\Exception $e) {
            DB::rollback();

            return response()->json([
                'status' => 'error',
                'message' => 'Admin registration failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'email' => 'required|email|unique:osis_admin,email,' . $id,
            'username' => 'required|string|unique:osis_admin,username,' . $id,
            'level' => 'required|string',
            'password' => 'nullable|confirmed',
            'dashboard' => 'nullable|string',
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'permissions' => 'required|array',
            'permissions.*' => 'string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        DB::beginTransaction();

        try {
            // Find the admin by ID
            $admin = Admin::findOrFail($id);

            // Update admin information
            $admin->name = $request->name;
            $admin->email = $request->email;
            $admin->username = $request->username;
            $admin->level = $request->level;
            $admin->dashboard = $request->dashboard;

            // If password is provided, update it
            if ($request->has('password')) {
                $admin->password = bcrypt($request->password);
            }

            // If a profile picture is uploaded, store it
            if ($request->hasFile('profile_picture')) {
                $image = $request->file('profile_picture')->store('images', 'public');
                $admin->profile_picture = $image;
            }

            $admin->save();


            $admin->permissions()->delete();

            foreach ($request->permissions as $moduleName) {
                AdminPermission::create([
                    'admin_id' => $admin->id,
                    'module' => $moduleName
                ]);
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Admin updated successfully',
                'admin' => $admin->load('permissions'),
            ], 200);
        } catch (\Exception $e) {
            DB::rollback();

            return response()->json([
                'status' => 'error',
                'message' => 'Admin update failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request): JsonResponse
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
        // dd($credentials);
        try {
            if (!$token = auth('admin')->attempt($credentials)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized: Invalid username or password.',
                ], 401);
            }

            $user = auth('admin')->user()->load('permissions');;

            return response()->json([
                'status' => 'success',
                'message' => 'Login successful',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'username' => $user->username,
                        'email' => $user->email,
                        'level' => $user->level,
                        'dashboard' => $user->dashboard,
                        'profile_picture' => $user->profile_picture,
                        'status' => $user->status,
                        'permissions' => $user->permissions,
                    ],
                    'token' => $token,
                    'token_type' => 'bearer',
                    'expires_in' => auth('admin')->factory()->getTTL() * 60, // Fixed this line
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

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {

        try {
            $token = JWTAuth::parseToken()->getToken();
            $decoded = JWTAuth::decode($token);

            if (!isset($decoded['sub'])) {
                return response()->json(['error' => 'Token is invalid'], 401);
            }

            $userId = $decoded['sub'];
            $user = Admin::with(['permissions' => function ($query) {
                $query->select('admin_id', 'module');
            }])->find($userId); 

            if (!$user) {
                return response()->json(['error' => 'User not found in the database'], 404);
            }

            return response()->json(['user' => $user]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken(JWTAuth::refresh());
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *  
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'status' => 'success',
            'data' => [
                'access_token' => $token,
                'token_type' => 'bearer',
                'expires_in' => JWTAuth::factory()->getTTL() * 60,  // Token expiration time
                'user' => JWTAuth::parseToken()->authenticate()  // Get the authenticated user
            ]
        ]);
    }
}
