<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;
use Knuckles\Scribe\Attributes\BodyParam;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Response;

class StoreUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    #[Endpoint('User (Cashier and Kitchen Account) Store Request', 'Menangani permintaan pembuatan pengguna baru dengan validasi data.')]
    #[BodyParam('name', 'string', 'Nama lengkap pengguna.', example: 'Budi Santoso')]
    #[BodyParam('email', 'string', 'Alamat email valid.', example: 'budi@example.com')]
    #[BodyParam('username', 'string', 'Username untuk login. Harus alfanumerik dengan tanda hubung dan garis bawah saja.', example: 'budisantoso')]
    #[BodyParam('password', 'string', 'Password dengan minimal 8 karakter.', example: 'password123')]
    #[BodyParam('role', 'string', 'Peran pengguna. Harus salah satu dari: admin, cashier, kitchen.', example: 'cashier')]
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'unique:users,email'],
            'username' => ['required', 'string', 'unique:users,username'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'role' => ['required', 'in:admin,cashier,kitchen']
        ];
    }
}
