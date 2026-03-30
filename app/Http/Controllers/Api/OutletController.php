<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Outlet;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OutletController extends Controller
{
    /**
     * GET /api/outlets
     */
    public function index(Request $request): JsonResponse
    {
        $query = Outlet::active()
            ->when($request->search, fn($q, $s) => $q->where('name', 'like', "%{$s}%")
                ->orWhere('code', 'like', "%{$s}%"))
            ->latest();

        if ($request->boolean('all')) {
            return response()->json($query->get());
        }

        return response()->json($query->paginate($request->per_page ?? 20));
    }

    /**
     * GET /api/outlets/{outlet}
     */
    public function show(Outlet $outlet): JsonResponse
    {
        return response()->json($outlet->load('users'));
    }
}
