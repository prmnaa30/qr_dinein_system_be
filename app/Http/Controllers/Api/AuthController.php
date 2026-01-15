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
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\BodyParam;
use Knuckles\Scribe\Attributes\Response;
use Knuckles\Scribe\Attributes\Authenticated;

#[Group('Authentication Management', 'APIs for user authentication including registration, login, logout, and user profile retrieval.')]
class AuthController extends Controller
{
    #[Endpoint('Pendaftaran Pengguna', 'Mendaftarkan akun pengguna baru dengan kredensial yang diberikan. Pengguna akan diberi peran kasir secara default jika tidak ada peran yang ditentukan.')]
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

    #[Endpoint('Login Pengguna', 'Otentikasi kredensial pengguna dan mengembalikan informasi pengguna dengan token otentikasi.')]
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

    #[Endpoint('Logout Pengguna', 'Menghapus token otentikasi pengguna saat ini dan menghapus cookie otentikasi.')]
    #[Authenticated]
    #[Response(content: '{"message": "Logout berhasil!"}', status: 200)]
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        $cookie = cookie()->forget('auth_token');

        return response()->json([
            'message' => 'Logout berhasil!'
        ])->withCookie($cookie);
    }

    #[Endpoint('Dapatkan Data Pengguna Terautentikasi', 'Mengambil informasi pengguna yang saat ini terotentikasi.')]
    #[Authenticated]
    #[Response(content: '{"data": {"id": 1,"name": "John Doe","email": "john@example.com","username": "johndoe","role": "cashier","created_at": "2023-01-01T00:00:00.000000Z","updated_at": "2023-01-01T00:00.00000Z"}}', status: 200)]
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
            name: 'auth_token',
            value: $token,
            minutes: 60 * 24,
            path: '/',
            domain: env('SESSION_DOMAIN'),
            secure: env('SESSION_SECURE_COOKIE'),
            httpOnly: true,
            raw: false,
            sameSite: env('SESSION_SAME_SITE')
        );
    }
}
