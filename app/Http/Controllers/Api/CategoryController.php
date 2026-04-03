<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    /**
     * GET /api/categories
     */
    public function index(Request $request): JsonResponse
    {
        $query = Category::active()
            ->withCount('products')
            ->when($request->search, fn($q, $s) => $q->where('name', 'like', "%{$s}%"))
            ->when($request->boolean('roots_only'), fn($q) => $q->roots())
            ->with('children')
            ->orderBy('sort_order')
            ->orderBy('name');

        if ($request->boolean('all')) {
            return response()->json($query->get());
        }

        return response()->json($query->paginate($request->per_page ?? 20));
    }

    /**
     * GET /api/categories/{category}
     */
    public function show(Category $category): JsonResponse
    {
        return response()->json($category->load(['parent', 'children']));
    }

    /**
     * POST /api/categories
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'       => ['required', 'string', 'max:255'],
            'slug'       => ['nullable', 'string', 'max:255', 'unique:categories'],
            'parent_id'  => ['nullable', 'exists:categories,id'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active'  => ['boolean'],
        ]);

        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        $category = Category::create($data);

        return response()->json($category, 201);
    }

    /**
     * PUT /api/categories/{category}
     */
    public function update(Request $request, Category $category): JsonResponse
    {
        $data = $request->validate([
            'name'       => ['sometimes', 'string', 'max:255'],
            'slug'       => ['sometimes', 'nullable', 'string', 'max:255', 'unique:categories,slug,' . $category->id],
            'parent_id'  => ['sometimes', 'nullable', 'exists:categories,id'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
            'is_active'  => ['sometimes', 'boolean'],
        ]);

        $category->update($data);

        return response()->json($category->refresh());
    }

    /**
     * DELETE /api/categories/{category}
     */
    public function destroy(Category $category): JsonResponse
    {
        $category->delete();

        return response()->json(null, 204);
    }
}
