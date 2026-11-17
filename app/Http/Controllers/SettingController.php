<?php

namespace App\Http\Controllers;

use App\Models\RentalSetting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        $settings = RentalSetting::getActive();

        return view('settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'rent_amount' => ['required', 'numeric', 'min:0'],
            'electricity_rate' => ['required', 'numeric', 'min:0'],
            'water_share_percentage' => ['required', 'numeric', 'between:0,100'],
            'currency' => ['required', 'string', 'max:10'],
            'rental_start_date' => ['nullable', 'date'],
            'owner_name' => ['nullable', 'string', 'max:255'],
            'owner_phone' => ['nullable', 'string', 'max:50'],
            'owner_email' => ['nullable', 'email', 'max:255'],
            'property_address' => ['nullable', 'string', 'max:255'],
            'payment_reminder_days' => ['required', 'integer', 'between:1,31'],
            'notes' => ['nullable', 'string'],
        ]);

        $settings = RentalSetting::getActive();
        $settings->update($validated);

        return redirect()->route('settings.index')
            ->with('success', 'Rental settings updated successfully. Note: Existing monthly records preserve their original contractual rates.');
    }
}
