<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

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
            'password' => ['required', Password::defaults(), 'confirmed'],
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
}
