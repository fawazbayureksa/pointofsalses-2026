<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Outlet;
use App\Models\Product;
use App\Models\Setting;
use Illuminate\Support\Facades\Auth;

class PosController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $outlets = Outlet::where('is_active', true)->orderBy('name')->get();
        $categories = Category::where('is_active', true)->orderBy('name')->get();
        $products = Product::where('is_active', true)
            ->with(['category', 'outlets'])
            ->orderBy('name')
            ->get();

        $defaultOutletId = old('outlet_id')
            ?? $user?->outlets()->where('outlets.is_active', true)->wherePivot('is_default', true)->value('outlets.id')
            ?? $user?->outlets()->where('outlets.is_active', true)->value('outlets.id')
            ?? $outlets->first()?->id;

        $taxSettings = Setting::whereIn('key', ['tax_enabled', 'tax_rate', 'tax_label', 'tax_inclusion'])
            ->get()
            ->keyBy('key');

        $taxEnabled = filter_var($taxSettings->get('tax_enabled')?->value ?? false, FILTER_VALIDATE_BOOLEAN);
        $taxConfig = [
            'enabled' => $taxEnabled,
            'rate' => $taxEnabled ? (float) ($taxSettings->get('tax_rate')?->value ?? 0) : 0.0,
            'label' => $taxSettings->get('tax_label')?->value ?: 'Tax',
            'inclusion' => $taxSettings->get('tax_inclusion')?->value ?: 'exclusive',
        ];

        $edcConfig = edc_config();

        $customersJson = Customer::where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(fn ($c) => [
                'id' => $c->id,
                'name' => $c->name,
                'phone' => $c->phone,
                'is_member' => $c->is_member,
                'membership_tier' => $c->membership_tier ?? 'regular',
                'loyalty_points' => (int) $c->loyalty_points,
            ])
            ->values();

        return view('admin.pos.index', compact(
            'outlets',
            'defaultOutletId',
            'taxConfig',
            'edcConfig',
            'customersJson',
            'categories',
            'products',
        ));
    }

    public function customerDisplay()
    {
        $user = Auth::user();
        $storeName = setting('app_name') ?: ($user?->tenant?->name ?? config('app.name', 'POS 2026'));
        $receiptFooter = setting('receipt_footer', 'Terima kasih telah berbelanja!');

        return view('admin.pos.customer-display', compact('storeName', 'receiptFooter'));
    }
}
