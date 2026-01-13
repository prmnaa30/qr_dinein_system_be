<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
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
