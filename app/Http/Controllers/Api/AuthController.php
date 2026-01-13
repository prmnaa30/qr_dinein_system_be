<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Hash;

/**
 * @group Authentication Management
 *
 * APIs for user authentication including registration, login, logout, and user profile retrieval.
 */
class AuthController extends Controller
{
    /**
     * User Registration
     *
     * Register a new user account with the provided credentials. The user will be assigned a cashier role by default if no role is specified.
     *
     * @bodyParam name string required Full name of the user. Example: John Doe
     * @bodyParam email string required Valid email address. Example: john@example.com
     * @bodyParam username string required Username for login. Must be alphanumeric with dashes and underscores only. Example: johndoe
     * @bodyParam password string required Password with minimum 8 characters. Example: password123
     * @bodyParam role string Role of the user. Optional, defaults to 'cashier'. Example: cashier
     * @response {
     *   "message": "Registrasi berhasil!",
     *   "data": {
     *     "id": 1,
     *     "name": "John Doe",
     *     "email": "john@example.com",
     *     "username": "johndoe",
     *     "role": "cashier",
     *     "created_at": "2023-01-01T00:00.00000Z",
     *     "updated_at": "2023-01-01T00:00:00.000000Z"
     *   }
     * }
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $user = User::create([
            'name'=>$request->input('name'),
            'email'=>$request->input('email'),
            'username'=>$request->input('username'),
            'password'=>Hash::make($request->input('password')),
            'role'=>$request->input('role') ?? 'cashier'
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Registrasi berhasil!',
            'data' => $user,
        ])->withCookie($this->setCookie($token));
    }

    /**
     * User Login
     *
     * Authenticate user credentials and return user information with authentication token.
     *
     * @bodyParam username string required Username for login. Example: johndoe
     * @bodyParam password string required Password. Example: password123
     * @response {
     *   "message": "Login berhasil!",
     *   "data": {
     *     "id": 1,
     *     "name": "John Doe",
     *     "email": "john@example.com",
     *     "username": "johndoe",
     *     "role": "cashier",
     *     "created_at": "2023-01-01T00:00:00.000000Z",
     *     "updated_at": "2023-01-01T00:00:00.000000Z"
     *   }
     * }
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $user = $request->authenticate();

        $user->tokens()->delete();

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login berhasil!',
            'data' => $user,
        ])->withCookie($this->setCookie($token));
    }

    /**
     * User Logout
     *
     * Invalidate the current user's authentication token and remove the authentication cookie.
     *
     * @authenticated
     * @response {
     *   "message": "Logout berhasil!"
     * }
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        $cookie = cookie()->forget('auth_token');

        return response()->json([
            'message' => 'Logout berhasil!'
        ])->withCookie($cookie);
    }

    /**
     * Get Authenticated User
     *
     * Retrieve the currently authenticated user's information.
     *
     * @authenticated
     * @response {
     *   "data": {
     *     "id": 1,
     *     "name": "John Doe",
     *     "email": "john@example.com",
     *     "username": "johndoe",
     *     "role": "cashier",
     *     "created_at": "2023-01-01T00:00:00.000000Z",
     *     "updated_at": "2023-01-01T00:00:00.000000Z"
     *   }
     * }
     */
    public function user(Request $request): JsonResponse
    {
        return response()->json([
            'data' => $request->user()
        ]);
    }

    /**
     * Helper function to set auth cookie
     * @param mixed $token
     * @return \Symfony\Component\HttpFoundation\Cookie
     */
    private function setCookie($token)
    {
        return cookie(
            'auth_token',
            $token,
            60 * 24,
            '/',
            null,
            true,
            true
        );
    }
}
