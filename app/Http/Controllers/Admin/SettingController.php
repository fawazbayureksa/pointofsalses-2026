<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class SettingController extends Controller
{
    public function index()
    {
        $settings = Setting::all()->keyBy('key');

        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        foreach ($request->except(['_token', '_method']) as $key => $value) {
            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }

        return redirect()->route('admin.settings.index')->with('success', 'Settings updated successfully.');
    }

    public function updateEmail(Request $request)
    {
        $request->validate([
            'mail_driver' => 'required|string',
            'mail_host' => 'nullable|string',
            'mail_port' => 'nullable|integer',
            'mail_username' => 'nullable|string',
            'mail_password' => 'nullable|string',
            'mail_encryption' => 'nullable|string',
            'mail_from' => 'nullable|email',
        ]);

        foreach ($request->except(['_token', '_method']) as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        }

        return redirect()->route('admin.settings.index')->with('success', 'Email settings updated.');
    }

    public function testEmail(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        try {
            Mail::raw('This is a test email from your POS system.', function ($message) use ($request) {
                $message->to($request->email)->subject('Test Email');
            });

            return redirect()->back()->with('success', 'Test email sent successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to send test email: '.$e->getMessage());
        }
    }

    public function updateBackup(Request $request)
    {
        foreach ($request->except(['_token', '_method']) as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        }

        return redirect()->route('admin.settings.index')->with('success', 'Backup settings updated.');
    }

    public function createBackup(Request $request)
    {
        return redirect()->back()->with('success', 'Backup created successfully.');
    }

    public function downloadBackup($file)
    {
        $path = storage_path('app/backups/'.$file);
        if (! file_exists($path)) {
            abort(404);
        }

        return response()->download($path);
    }

    public function deleteBackup($file)
    {
        $path = storage_path('app/backups/'.$file);
        if (file_exists($path)) {
            unlink($path);
        }

        return redirect()->back()->with('success', 'Backup deleted.');
    }

    public function tax()
    {
        $settings = Setting::all()->keyBy('key');

        return view('admin.settings.tax', compact('settings'));
    }

    public function updateTax(Request $request)
    {
        $request->validate([
            'tax_rate' => 'required|numeric|min:0|max:100',
            'tax_enabled' => 'nullable|boolean',
        ]);

        foreach ($request->except(['_token', '_method']) as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        }

        return redirect()->back()->with('success', 'Tax settings updated.');
    }

    public function currency()
    {
        $settings = Setting::all()->keyBy('key');

        return view('admin.settings.currency', compact('settings'));
    }

    public function updateCurrency(Request $request)
    {
        $request->validate([
            'currency_code' => 'required|string|max:3',
            'currency_symbol' => 'required|string|max:5',
        ]);

        foreach ($request->except(['_token', '_method']) as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        }

        return redirect()->back()->with('success', 'Currency settings updated.');
    }

    public function updateModules(Request $request)
    {
        $request->validate([
            'supplier_management_enabled' => 'nullable|boolean',
            'po_approval_threshold' => 'nullable|numeric|min:0',
        ]);

        $tenant = $request->user()?->tenant;
        if ($tenant && $tenant->supplierManagementOverride() !== 'auto') {
            return redirect()->route('admin.settings.index')
                ->with('error', 'Pengaturan modul Supplier Management saat ini dikunci oleh Super Admin.');
        }

        $enabled = $request->boolean('supplier_management_enabled') ? 'true' : 'false';

        Setting::updateOrCreate(
            ['key' => 'supplier_management_enabled'],
            ['value' => $enabled, 'type' => 'boolean', 'group' => 'modules']
        );

        if ($request->filled('po_approval_threshold')) {
            Setting::updateOrCreate(
                ['key' => 'po_approval_threshold'],
                ['value' => (string) $request->input('po_approval_threshold'), 'type' => 'float', 'group' => 'modules']
            );
        }

        if ($tenant) {
            $tenant->setSetting('supplier_management_enabled', $request->boolean('supplier_management_enabled'));
        }

        return redirect()->route('admin.settings.index')
            ->with('success', 'Pengaturan modul opsional (Supplier Management) berhasil diperbarui.');
    }
}
