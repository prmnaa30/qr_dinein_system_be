<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;
use Knuckles\Scribe\Attributes\BodyParam;
use Knuckles\Scribe\Attributes\Endpoint;

class UpdateUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    #[Endpoint('Update User (Cashier, Kitchen) Request', 'Menangani permintaan pembaruan pengguna dengan validasi data.')]
    #[BodyParam('name', 'string', 'Nama lengkap pengguna.', example: 'Budi Santoso')]
    #[BodyParam('email', 'string', 'Alamat email valid.', example: 'budi@example.com')]
    #[BodyParam('username', 'string', 'Username untuk login. Harus alfanumerik dengan tanda hubung dan garis bawah saja.', example: 'budisantoso')]
    #[BodyParam('password', 'string', 'Password dengan minimal 8 karakter (opsional).', example: 'password123')]
    #[BodyParam('role', 'string', 'Peran pengguna. Harus salah satu dari: admin, cashier, kitchen.', example: 'cashier')]
    public function rules(): array
    {
        $userId = $this->route('user') ? $this->route('user')->id : $this->route('id');

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'unique:users,email,' . $userId],
            'username' => ['required', 'string', 'alpha_dash', 'unique:users,username,' . $userId],
            'password' => ['nullable', 'confirmed', Password::defaults()],
            'role' => ['required', 'in:admin,cashier,kitchen']
        ];
    }
}
