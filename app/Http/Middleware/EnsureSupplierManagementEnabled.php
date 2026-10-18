<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSupplierManagementEnabled
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! supplier_management_enabled()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Supplier Management module is disabled for this tenant.',
                ], 403);
            }

            if ($request->user()?->can('manage settings')) {
                return redirect()->route('admin.settings.index')
                    ->with('error', 'Modul Supplier Management saat ini dinonaktifkan untuk bisnis Anda. Silakan aktifkan terlebih dahulu di pengaturan sistem jika dibutuhkan.');
            }

            return redirect()->route('admin.dashboard')
                ->with('error', 'Modul Supplier Management tidak aktif untuk tenant ini.');
        }

        return $next($request);
    }
}
