<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\Auth\Events\Verified;
use Illuminate\Auth\Events\PasswordReset;
use Carbon\Carbon;

class AuthController extends Controller
{
    /**
     * Show the registration form.
     */
    public function register(Request $request)
    {
        return view('auth.register');
    }

    /**
     * Handle registration request.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', PasswordRule::defaults(), 'confirmed'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        // Create default rental settings for the user
        $user->rentalSetting()->create([
            'rent_amount' => 16000.00,
            'electricity_rate' => 10.00,
            'water_share_percentage' => 50.00,
            'currency' => 'NPR',
            'rental_start_date' => now()->format('Y-m-d'),
            'owner_name' => 'House Owner',
            'property_address' => 'Kathmandu, Nepal',
            'payment_reminder_days' => 5,
        ]);

        Auth::login($user);

        return redirect()->route('dashboard')
            ->with('success', 'Welcome to RentMate! Your account has been created.');
    }

    /**
     * Show the login form.
     */
    public function login(Request $request)
    {
        return view('auth.login');
    }

    /**
     * Handle login request.
     */
    public function authenticate(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (!Auth::attempt($validated)) {
            return back()->withErrors([
                'email' => 'The provided credentials do not match our records.',
            ])->onlyInput('email');
        }

        $request->session()->regenerate();

        return redirect()->route('dashboard')
            ->with('success', 'Welcome back, ' . Auth::user()->name . '!');
    }

    /**
     * Log the user out.
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('success', 'You have been logged out.');
    }

    /**
     * Show the forgot password form.
     */
    public function forgotPassword(Request $request)
    {
        return view('auth.forgot-password');
    }

    /**
     * Send password reset link.
     */
    public function sendResetLink(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($status === Password::RESET_LINK_SENT) {
            return back()->with('success', 'We have sent you a password reset link. Please check your email.');
        }

        return back()->withErrors([
            'email' => 'We could not find a user with that email address.',
        ])->onlyInput('email');
    }

    /**
     * Show the reset password form.
     */
    public function resetPassword(Request $request, $token)
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->email,
        ]);
    }

    /**
     * Update the user's password.
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', PasswordRule::defaults(), 'confirmed'],
            'token' => ['required'],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                ])->save();

                $user->setRememberToken(Str::random(60));

                event(new PasswordReset($user));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return redirect()->route('login')
                ->with('success', 'Your password has been reset. Please log in with your new password.');
        }

        return back()->withErrors([
            'email' => 'There was a problem resetting your password.',
        ])->onlyInput('email');
    }

    /**
     * Show the email verification notice.
     */
    public function verifyEmail(Request $request)
    {
        return view('auth.verify-email');
    }

    /**
     * Verify the user's email address.
     */
    public function verify(Request $request, $id, $hash)
    {
        $user = User::findOrFail($id);

        if (!hash_equals((string) $hash, sha1((string) $id))) {
            return back()->withErrors([
                'email' => 'Invalid verification link.',
            ]);
        }

        if (!$user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
            event(new Verified($user));
        }

        return redirect()->route('dashboard')
            ->with('success', 'Your email has been verified successfully.');
    }

    /**
     * Resend verification email.
     */
    public function resendVerification(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return back()->withErrors([
                'email' => 'No user found with that email address.',
            ])->onlyInput('email');
        }

        if ($user->hasVerifiedEmail()) {
            return back()->with('success', 'Your email is already verified.');
        }

        $user->sendEmailVerificationNotification();

        return back()->with('success', 'Verification link sent! Please check your email.');
    }
}
