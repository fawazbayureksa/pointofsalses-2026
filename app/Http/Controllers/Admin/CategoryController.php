<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::withCount(['children', 'products'])->with('parent')->latest()->paginate(15);
        $parentCategories = Category::whereNull('parent_id')->orderBy('name')->get();

        return view('admin.categories.index', compact('categories', 'parentCategories'));
    }

    public function create()
    {
        return redirect()->route('admin.categories.index');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'parent_id' => 'nullable|exists:categories,id',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        Category::create($validated);

        return redirect()->route('admin.categories.index')->with('success', 'Category created successfully.');
    }

    public function show(Category $category)
    {
        return view('admin.categories.index', compact('category'));
    }

    public function edit(Category $category)
    {
        return view('admin.categories.index', compact('category'));
    }

    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'parent_id' => 'nullable|exists:categories,id',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        $category->update($validated);

        return redirect()->route('admin.categories.index')->with('success', 'Category updated successfully.');
    }

    public function destroy(Category $category)
    {
        $category->delete();

        return redirect()->route('admin.categories.index')->with('success', 'Category deleted successfully.');
    }

    public function downloadTemplate(): StreamedResponse
    {
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="categories-import-template.csv"',
        ];

        $columns = ['name', 'slug', 'parent_category', 'sort_order', 'is_active'];

        $sampleRows = [
            ['Beverages', 'beverages', '', '1', '1'],
            ['Coffee', 'coffee', 'Beverages', '2', '1'],
            ['Food', 'food', '', '3', '1'],
        ];

        return response()->stream(function () use ($columns, $sampleRows) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $columns);
            foreach ($sampleRows as $row) {
                fputcsv($handle, $row);
            }
            fclose($handle);
        }, 200, $headers);
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:5120',
        ]);

        $path = $request->file('file')->getRealPath();
        $handle = fopen($path, 'r');

        if ($handle === false) {
            return redirect()->route('admin.categories.index')
                ->withErrors(['file' => 'Unable to read the uploaded CSV file.']);
        }

        $rawHeaders = fgetcsv($handle);
        if (! $rawHeaders) {
            fclose($handle);

            return redirect()->route('admin.categories.index')
                ->withErrors(['file' => 'The uploaded CSV file is empty.']);
        }

        $headers = array_map(function ($header) {
            $clean = preg_replace('/^\xEF\xBB\xBF/', '', (string) $header);

            return Str::snake(strtolower(trim($clean)));
        }, $rawHeaders);

        if (! in_array('name', $headers, true)) {
            fclose($handle);

            return redirect()->route('admin.categories.index')
                ->withErrors(['file' => 'Invalid CSV template. Required column "name" is missing.']);
        }

        $rows = [];
        $lineNumber = 1;
        $errors = [];

        while (($row = fgetcsv($handle)) !== false) {
            $lineNumber++;

            if ($row === [null] || count(array_filter($row, fn ($v) => trim((string) $v) !== '')) === 0) {
                continue;
            }

            $data = [];
            foreach ($headers as $index => $key) {
                if ($key !== '') {
                    $data[$key] = isset($row[$index]) ? trim((string) $row[$index]) : '';
                }
            }

            if (($data['name'] ?? '') === '') {
                $errors[] = "Row {$lineNumber}: Category name is required.";

                continue;
            }

            $rows[] = [
                'line' => $lineNumber,
                'name' => $data['name'],
                'slug' => ($data['slug'] ?? '') !== '' ? Str::slug($data['slug']) : Str::slug($data['name']),
                'parent_category' => $data['parent_category'] ?? ($data['parent'] ?? ''),
                'sort_order' => is_numeric($data['sort_order'] ?? null) ? max(0, (int) $data['sort_order']) : 0,
                'is_active' => $this->parseBoolean($data['is_active'] ?? '1', true),
            ];
        }

        fclose($handle);

        if (! empty($errors)) {
            return redirect()->route('admin.categories.index')->withErrors($errors);
        }

        if (empty($rows)) {
            return redirect()->route('admin.categories.index')
                ->withErrors(['file' => 'No valid category rows found in the CSV file.']);
        }

        $importedCount = 0;

        DB::transaction(function () use ($rows, &$importedCount) {
            foreach ($rows as $item) {
                $parentId = null;

                if ($item['parent_category'] !== '') {
                    $parentSlug = Str::slug($item['parent_category']);
                    $parent = Category::where(function ($query) use ($item, $parentSlug) {
                        $query->where('name', $item['parent_category'])
                            ->orWhere('slug', $parentSlug);
                    })->first();

                    if (! $parent) {
                        $parent = Category::create([
                            'name' => $item['parent_category'],
                            'slug' => $parentSlug,
                            'is_active' => true,
                            'sort_order' => 0,
                        ]);
                    }

                    $parentId = $parent->id;
                }

                $category = Category::withTrashed()->where('slug', $item['slug'])->first();

                if ($category) {
                    if ($category->trashed()) {
                        $category->restore();
                    }
                    $category->update([
                        'name' => $item['name'],
                        'parent_id' => $parentId !== $category->id ? $parentId : null,
                        'sort_order' => $item['sort_order'],
                        'is_active' => $item['is_active'],
                    ]);
                } else {
                    Category::create([
                        'name' => $item['name'],
                        'slug' => $item['slug'],
                        'parent_id' => $parentId,
                        'sort_order' => $item['sort_order'],
                        'is_active' => $item['is_active'],
                    ]);
                }

                $importedCount++;
            }
        });

        return redirect()->route('admin.categories.index')
            ->with('success', "Successfully imported {$importedCount} categories.");
    }

    private function parseBoolean(string $value, bool $default = true): bool
    {
        $normalized = strtolower(trim($value));

        if ($normalized === '') {
            return $default;
        }

        return in_array($normalized, ['1', 'true', 'yes', 'active', 'y'], true);
    }
}
