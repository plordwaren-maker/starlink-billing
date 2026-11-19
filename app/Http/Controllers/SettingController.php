<?php

namespace App\Http\Controllers;

use App\Models\AppSetting;
use App\Models\Bill;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function edit() { Bill::generateDueBills(); $settings = AppSetting::current(); return view('settings.edit', compact('settings')); }

    public function update(Request $request) {
        Bill::generateDueBills();
        $data = $request->validate([
            'receiver_name' => 'required|string|max:255',
            'gcash_number' => 'required|string|max:40',
            'default_share' => 'required|numeric|min:0',
            'reminder_days' => 'required|integer|min:0|max:30',
            'increase_reason' => 'nullable|string',
        ]);
        AppSetting::current()->update($data);
        return back()->with('success', 'Setup saved.');
    }
}
