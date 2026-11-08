<div x-data="{ open: false }" @open-import-category-modal.window="open = true">
    <x-admin-modal id="import-category-modal" title="Import Categories from CSV" size="lg">
        <form method="POST" action="{{ route('admin.categories.import') }}" enctype="multipart/form-data">
            @csrf

            <div class="mb-4 p-4 bg-blue-50 border border-blue-200 rounded-lg text-sm text-blue-800 space-y-2">
                <div class="flex items-center justify-between">
                    <span class="font-semibold"><i class="fa-solid fa-circle-info mr-1.5"></i>CSV Format Guide</span>
                    <a href="{{ route('admin.categories.import-template') }}"
                        class="inline-flex items-center px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-medium rounded-lg transition-colors">
                        <i class="fa-solid fa-download mr-1.5"></i>Download Template
                    </a>
                </div>
                <p class="text-xs text-blue-700">
                    Upload a CSV file with the following headers:
                    <code class="font-mono bg-blue-100 px-1.5 py-0.5 rounded">name, slug, parent_category, sort_order, is_active</code>
                </p>
                <ul class="list-disc list-inside text-xs text-blue-700 space-y-0.5">
                    <li><strong>name</strong> (required): Category display name.</li>
                    <li><strong>slug</strong> (optional): Auto-generated from name if left blank. Existing slugs will be updated.</li>
                    <li><strong>parent_category</strong> (optional): Parent category name or slug (auto-created if not found).</li>
                    <li><strong>sort_order</strong> (optional): Numeric sort order (default: 0).</li>
                    <li><strong>is_active</strong> (optional): <code class="font-mono">1</code> for Active, <code class="font-mono">0</code> for Inactive (default: 1).</li>
                </ul>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    CSV File <span class="text-red-500">*</span>
                </label>
                <input type="file" name="file" accept=".csv,.txt" required
                    class="block w-full text-sm text-gray-600 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 border border-gray-300 rounded-lg cursor-pointer">
                <p class="text-xs text-gray-400 mt-1">Accepted formats: .csv, .txt (Max: 5MB)</p>
            </div>

            <div class="flex items-center justify-end space-x-3 mt-6">
                <button type="button" @click="open = false"
                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200">
                    Cancel
                </button>
                <x-admin-button type="submit" variant="primary" icon="fa-solid fa-file-import">
                    Import Categories
                </x-admin-button>
            </div>
        </form>
    </x-admin-modal>
</div>
