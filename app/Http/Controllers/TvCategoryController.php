<?php

namespace App\Http\Controllers;

use App\Models\TvCategory;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class TvCategoryController extends Controller
{
    /**
     * Display a listing of TV categories.
     */
    public function index(Request $request): Response
    {
        $query = TvCategory::withCount('tvs');

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        
        $allowedSortFields = ['created_at', 'name', 'status'];
        if (in_array($sortBy, $allowedSortFields)) {
            $query->orderBy($sortBy, $sortOrder);
        }

        $tvCategories = $query->paginate(15)->withQueryString();

        return Inertia::render('admin/tv/categories/index', [
            'tvCategories' => $tvCategories,
            'filters' => $request->only(['search', 'status', 'sort_by', 'sort_order']),
            'statusOptions' => [
                'active' => 'Active',
                'inactive' => 'Inactive',
            ],
        ]);
    }

    /**
     * Show the form for creating a new TV category.
     */
    public function create(): Response
    {
        return Inertia::render('admin/tv/categories/create');
    }

    /**
     * Store a newly created TV category in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:tv_categories,name',
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive',
        ], [
            'name.required' => 'Nama kategori wajib diisi.',
            'name.unique' => 'Nama kategori sudah ada.',
            'name.max' => 'Nama kategori maksimal 100 karakter.',
            'status.required' => 'Status wajib dipilih.',
            'status.in' => 'Status harus active atau inactive.',
        ]);

        TvCategory::create($validated);

        return redirect()->route('admin.tv.categories.index')
            ->with('success', 'Kategori TV berhasil dibuat.');
    }

    /**
     * Display the specified TV category.
     */
    public function show(TvCategory $tvCategory): Response
    {
        $tvCategory->load(['tvs' => function ($query) {
            $query->orderBy('created_at', 'desc');
        }]);

        return Inertia::render('admin/tv/categories/show', [
            'tvCategory' => $tvCategory,
        ]);
    }

    /**
     * Show the form for editing the specified TV category.
     */
    public function edit(TvCategory $tvCategory): Response
    {
        return Inertia::render('admin/tv/categories/edit', [
            'tvCategory' => $tvCategory,
        ]);
    }

    /**
     * Update the specified TV category in storage.
     */
    public function update(Request $request, TvCategory $tvCategory): RedirectResponse
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('tv_categories', 'name')->ignore($tvCategory->id),
            ],
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive',
        ], [
            'name.required' => 'Nama kategori wajib diisi.',
            'name.unique' => 'Nama kategori sudah ada.',
            'name.max' => 'Nama kategori maksimal 100 karakter.',
            'status.required' => 'Status wajib dipilih.',
            'status.in' => 'Status harus active atau inactive.',
        ]);

        $tvCategory->update($validated);

        return redirect()->route('admin.tv.categories.index')
            ->with('success', 'Kategori TV berhasil diperbarui.');
    }

    /**
     * Remove the specified TV category from storage.
     */
    public function destroy(TvCategory $tvCategory): RedirectResponse
    {
        // Check if category has associated TVs
        if ($tvCategory->tvs()->count() > 0) {
            return redirect()->route('admin.tv.categories.index')
                ->with('error', 'Kategori tidak dapat dihapus karena masih memiliki TV terkait.');
        }

        $tvCategory->delete();

        return redirect()->route('admin.tv.categories.index')
            ->with('success', 'Kategori TV berhasil dihapus.');
    }
}