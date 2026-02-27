<?php

namespace App\Http\Requests\Auth;

use App\Enums\MembershipRole;
use App\Enums\StaffRole;
use App\Models\User;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
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
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();
        $this->ensurePrivilegedDemoLoginIsAllowed();

        if (! Auth::attempt($this->only('email', 'password'), $this->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Prevent public demo users from logging in with privileged accounts unless
     * full demo access has been unlocked for the session.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    private function ensurePrivilegedDemoLoginIsAllowed(): void
    {
        if (! config('demo.public_mode')) {
            return;
        }

        $flag = (string) config('demo.session_flag', 'demo.full_access_granted');
        if ($this->session()->get($flag) === true) {
            return;
        }

        $email = Str::lower(trim((string) $this->input('email', '')));
        if ($email === '') {
            return;
        }

        $readOnlyEmails = config('demo.read_only_emails', []);
        if (is_array($readOnlyEmails) && in_array($email, $readOnlyEmails, true)) {
            return;
        }

        $configured = config('demo.privileged_emails', []);
        if (is_array($configured) && in_array($email, $configured, true)) {
            throw ValidationException::withMessages([
                'email' => 'This demo account is locked. Open /demo/full-access first.',
            ]);
        }

        $user = User::query()
            ->where('email', $email)
            ->first();

        if (! $user) {
            return;
        }

        if ($user->is_super_admin) {
            throw ValidationException::withMessages([
                'email' => 'This demo account is locked. Open /demo/full-access first.',
            ]);
        }

        $hasPrivilegedMembership = $user->memberships()
            ->where(function ($query): void {
                $query
                    ->where('role', MembershipRole::RESTAURANT_ADMIN->value)
                    ->orWhere(function ($inner): void {
                        $inner
                            ->where('role', MembershipRole::STAFF->value)
                            ->where('staff_role', StaffRole::MANAGER->value);
                    });
            })
            ->exists();

        if ($hasPrivilegedMembership) {
            throw ValidationException::withMessages([
                'email' => 'This demo account is locked. Open /demo/full-access first.',
            ]);
        }
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('email')).'|'.$this->ip());
    }
}
