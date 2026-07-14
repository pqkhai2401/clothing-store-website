<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    public function edit(): View
    {
        $setting = Setting::current();

        return view('admin.settings.edit', compact('setting'));
    }

    public function update(Request $request): RedirectResponse
    {
        $setting = Setting::current();

        $request->validate([
            'site_name' => 'required|string|max:255',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'address' => 'nullable|string|max:255',
            'hotline' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:255',
            'facebook_url' => 'nullable|url|max:255',
            'facebook_enabled' => 'nullable|boolean',
            'instagram_url' => 'nullable|url|max:255',
            'instagram_enabled' => 'nullable|boolean',
            'zalo_url' => 'nullable|url|max:255',
            'zalo_enabled' => 'nullable|boolean',
        ]);

        $logoPath = $setting->logo_path;
        if ($request->hasFile('logo')) {
            if ($setting->logo_path) {
                $oldPath = str_replace('storage/', '', $setting->logo_path);
                Storage::disk('public')->delete($oldPath);
            }
            $path = $request->file('logo')->store('settings', 'public');
            $logoPath = 'storage/' . $path;
        }

        $setting->update([
            'site_name' => $request->input('site_name'),
            'logo_path' => $logoPath,
            'address' => $request->input('address'),
            'hotline' => $request->input('hotline'),
            'email' => $request->input('email'),
            'facebook_url' => $request->input('facebook_url'),
            'facebook_enabled' => $request->boolean('facebook_enabled'),
            'instagram_url' => $request->input('instagram_url'),
            'instagram_enabled' => $request->boolean('instagram_enabled'),
            'zalo_url' => $request->input('zalo_url'),
            'zalo_enabled' => $request->boolean('zalo_enabled'),
        ]);

        return redirect()->route('admin.settings.edit')->with('success', 'Cập nhật cài đặt website thành công.');
    }
}
