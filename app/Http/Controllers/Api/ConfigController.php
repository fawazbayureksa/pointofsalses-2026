<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ConfigService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ConfigController extends Controller
{
    public function __construct(
        private readonly ConfigService $configService,
    ) {}

    /**
     * GET /api/config
     */
    public function index(): JsonResponse
    {
        return response()->json($this->configService->all());
    }

    /**
     * GET /api/config/{key}
     */
    public function show(string $key): JsonResponse
    {
        return response()->json([
            'key'   => $key,
            'value' => $this->configService->get($key),
        ]);
    }

    /**
     * PUT /api/config/{key}
     */
    public function update(Request $request, string $key): JsonResponse
    {
        $this->authorize('manage_settings');

        $data = $request->validate([
            'value' => ['required'],
            'type'  => ['sometimes', 'in:string,integer,boolean,json'],
        ]);

        $setting = $this->configService->set($key, $data['value'], $data['type'] ?? 'string');

        return response()->json([
            'key'   => $setting->key,
            'value' => $setting->getTypedValue(),
        ]);
    }
}
