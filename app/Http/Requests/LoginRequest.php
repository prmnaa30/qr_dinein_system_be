<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Knuckles\Scribe\Attributes\BodyParam;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Response;

/**
 * LoginRequest
 *
 * Handles validation and authentication for user login requests.
 * This request class provides validation rules, authentication logic,
 * rate limiting, and input preparation for login attempts.
 */
class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool True if the user is authorized, false otherwise
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Attempt to authenticate the user with the provided credentials.
     *
     * This method first ensures the user is not rate limited, then attempts
     * to authenticate using the username and password. If authentication
     * fails, it records the attempt for rate limiting purposes and throws
     * a ValidationException with an appropriate error message.
     *
     * @return void
     *
     * @throws ValidationException If authentication fails
     */
    public function authenticate(): User
    {
        $this->ensureIsNotRateLimited();

        $user = User::where('username', $this->input('username'))->first();

        if (!$user || !Hash::check($this->input('password'), $user->password)) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'username' => trans('auth.failed')
            ]);
        }

        RateLimiter::clear($this->throttleKey());

        return $user;
    }

    #[Endpoint('Login Request', 'Menangani request login pengguna dengan validasi credentials.')]
    #[BodyParam('username', 'string', 'Username untuk login.', example: 'johndoe')]
    #[BodyParam('password', 'string', 'Password pengguna.', example: 'password123')]
    public function rules(): array
    {
        return [
            'username' => ['required', 'string'],
            'password' => ['required', 'string']
        ];
    }

    /**
     * Prepare the data for validation.
     *
     * This method normalizes the username field by converting it to lowercase
     * and trimming any whitespace before validation occurs.
     *
     * @return void
     */
    public function prepareForValidation()
    {
        $this->merge([
            'username' => strtolower(trim($this->input('username', ''))),
        ]);
    }

    /**
     * Check if user login too often
     *
     * Verifies if the user has exceeded the allowed number of login attempts
     * within the rate limiting window. If so, it triggers a lockout event
     * and throws a ValidationException with a throttling message.
     *
     * @return void
     *
     * @throws ValidationException If rate limit is exceeded
     */
    public function ensureIsNotRateLimited()
    {
        if (!RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'username' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting key for the request.
     *
     * Creates a unique key based on the username and IP address to track
     * login attempts for rate limiting purposes.
     *
     * @return string The throttle key
     */
    private function throttleKey()
    {
        return Str::transliterate(Str::lower($this->input('username')) . '|' . $this->ip());
    }
}
