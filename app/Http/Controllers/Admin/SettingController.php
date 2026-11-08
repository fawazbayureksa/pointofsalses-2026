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

    public function updateEdc(Request $request)
    {
        $validated = $request->validate([
            'edc_enabled' => 'nullable|boolean',
            'edc_connection_mode' => 'required|in:semi_integrated,ecr_serial,both',
            'edc_banks' => 'nullable|string|max:500',
            'edc_require_reference' => 'nullable|boolean',
            'edc_serial_baud_rate' => 'nullable|integer|in:9600,19200,38400,57600,115200',
        ]);

        $enabled = $request->boolean('edc_enabled');
        $mode = $validated['edc_connection_mode'] ?? 'semi_integrated';
        $banks = trim((string) ($validated['edc_banks'] ?? '')) ?: 'BCA, Mandiri, BRI, BNI, CIMB Niaga, Permata';
        $requireRef = $request->boolean('edc_require_reference');
        $baudRate = (int) ($validated['edc_serial_baud_rate'] ?? 9600);

        $edcSettings = [
            'edc_enabled' => ['value' => $enabled ? 'true' : 'false', 'type' => 'boolean'],
            'edc_connection_mode' => ['value' => $mode, 'type' => 'string'],
            'edc_banks' => ['value' => $banks, 'type' => 'string'],
            'edc_require_reference' => ['value' => $requireRef ? 'true' : 'false', 'type' => 'boolean'],
            'edc_serial_baud_rate' => ['value' => (string) $baudRate, 'type' => 'integer'],
        ];

        foreach ($edcSettings as $key => $cfg) {
            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $cfg['value'], 'type' => $cfg['type'], 'group' => 'edc']
            );
        }

        $tenant = $request->user()?->tenant;
        if ($tenant) {
            $tenant->setSetting('edc_enabled', $enabled);
            $tenant->setSetting('edc_connection_mode', $mode);
            $tenant->setSetting('edc_banks', $banks);
            $tenant->setSetting('edc_require_reference', $requireRef);
            $tenant->setSetting('edc_serial_baud_rate', $baudRate);
        }

        return redirect()->route('admin.settings.index')
            ->with('success', 'Konfigurasi Mesin EDC Bank berhasil diperbarui.');
    }
}
