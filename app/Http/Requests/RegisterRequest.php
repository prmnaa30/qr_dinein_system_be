<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;
use Knuckles\Scribe\Attributes\BodyParam;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Response;

class RegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    #[Endpoint('Register Request', 'Menangani permintaan register pengguna baru dengan validasi data.')]
    #[BodyParam('name', 'string', 'Nama lengkap pengguna.', example: 'Budi Santoso')]
    #[BodyParam('email', 'string', 'Alamat email valid.', example: 'budi@example.com')]
    #[BodyParam('username', 'string', 'Username untuk login. Harus alfanumerik dengan tanda hubung dan garis bawah saja.', example: 'budisantoso')]
    #[BodyParam('password', 'string', 'Password dengan minimal 8 karakter.', example: 'password123')]
    #[BodyParam('role', 'string', 'Peran pengguna. Opsional, default ke "cashier".', example: 'cashier')]
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email:dns', 'max:255', 'unique:users,email'],
            'username' => ['required', 'string', 'alpha_dash', 'max:30', 'unique:users,username'],
            'password' => ['required', 'confirmed', Password::defaults()]
        ];
    }

    /**
     * Prepare the data for validation.
     *
     * This method normalizes the username and email field by converting it
     * to lowercase and trimming any whitespace before validation occurs.
     */
    public function prepareForValidation()
    {
        $this->merge([
            'username' => strtolower(trim($this->input('username', ''))),
            'email' => strtolower(trim($this->input('email', '')))
        ]);
    }
}
