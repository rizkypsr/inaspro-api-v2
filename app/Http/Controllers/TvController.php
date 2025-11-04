<?php

namespace App\Http\Controllers;

use App\Models\Tv;
use App\Models\TvCategory;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class TvController extends Controller
{
    /**
     * Display a listing of TVs.
     */
    public function index(Request $request): Response
    {
        $query = Tv::with('tvCategory');

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('link', 'like', "%{$search}%")
                  ->orWhereHas('tvCategory', function ($categoryQuery) use ($search) {
                      $categoryQuery->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Category filter
        if ($request->filled('tv_category_id')) {
            $query->where('tv_category_id', $request->tv_category_id);
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        
        $allowedSortFields = ['created_at', 'title', 'status'];
        if (in_array($sortBy, $allowedSortFields)) {
            $query->orderBy($sortBy, $sortOrder);
        }

        $tvs = $query->paginate(15)->withQueryString();

        return Inertia::render('admin/tv/index', [
            'tvs' => $tvs,
            'filters' => [
                'search' => $request->get('search'),
                'category' => $request->get('tv_category_id') ?: 'all',
                'status' => $request->get('status') ?: 'all',
                'sort' => $request->get('sort_by', 'created_at') . '_' . $request->get('sort_order', 'desc'),
            ],
            'tvCategories' => TvCategory::select('id', 'name')->active()->get(),
            'statusOptions' => [
                'active' => 'Active',
                'inactive' => 'Inactive',
            ],
        ]);
    }

    /**
     * Show the form for creating a new TV.
     */
    public function create(): Response
    {
        return Inertia::render('admin/tv/create', [
            'tvCategories' => TvCategory::select('id', 'name')->active()->get(),
        ]);
    }

    /**
     * Store a newly created TV in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'tv_category_id' => 'required|exists:tv_categories,id',
            'title' => 'required|string|max:200',
            'link' => 'required|url',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'status' => 'required|in:active,inactive',
        ], [
            'tv_category_id.required' => 'Kategori TV wajib dipilih.',
            'tv_category_id.exists' => 'Kategori TV tidak valid.',
            'title.required' => 'Judul TV wajib diisi.',
            'title.max' => 'Judul TV maksimal 200 karakter.',
            'link.required' => 'Link TV wajib diisi.',
            'link.url' => 'Link harus berupa URL yang valid.',
            'image.image' => 'File harus berupa gambar.',
            'image.mimes' => 'Gambar harus berformat: jpeg, png, jpg, gif, webp.',
            'image.max' => 'Ukuran gambar maksimal 2MB.',
            'status.required' => 'Status wajib dipilih.',
            'status.in' => 'Status harus active atau inactive.',
        ]);

        DB::beginTransaction();

        try {
            // Handle image upload
            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('tv-images', 'public');
                $validated['image'] = $imagePath;
            }

            Tv::create($validated);

            DB::commit();

            return redirect()->route('admin.tv.index')
                ->with('success', 'TV berhasil dibuat.');
        } catch (\Exception $e) {
            DB::rollBack();
            
            // Delete uploaded image if exists
            if (isset($validated['image'])) {
                Storage::disk('public')->delete($validated['image']);
            }

            return redirect()->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan saat membuat TV.');
        }
    }

    /**
     * Display the specified TV.
     */
    public function show(Tv $tv): Response
    {
        $tv->load('tvCategory');

        return Inertia::render('admin/tv/show', [
            'tv' => $tv,
        ]);
    }

    /**
     * Show the form for editing the specified TV.
     */
    public function edit(Tv $tv): Response
    {
        $tv->load('tvCategory');

        return Inertia::render('admin/tv/edit', [
            'tv' => $tv,
            'tvCategories' => TvCategory::select('id', 'name')->active()->get(),
        ]);
    }

    /**
     * Update the specified TV in storage.
     */
    public function update(Request $request, Tv $tv): RedirectResponse
    {
        $validated = $request->validate([
            'tv_category_id' => 'required|exists:tv_categories,id',
            'title' => 'required|string|max:200',
            'link' => 'required|url',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'status' => 'required|in:active,inactive',
        ], [
            'tv_category_id.required' => 'Kategori TV wajib dipilih.',
            'tv_category_id.exists' => 'Kategori TV tidak valid.',
            'title.required' => 'Judul TV wajib diisi.',
            'title.max' => 'Judul TV maksimal 200 karakter.',
            'link.required' => 'Link TV wajib diisi.',
            'link.url' => 'Link harus berupa URL yang valid.',
            'image.image' => 'File harus berupa gambar.',
            'image.mimes' => 'Gambar harus berformat: jpeg, png, jpg, gif, webp.',
            'image.max' => 'Ukuran gambar maksimal 2MB.',
            'status.required' => 'Status wajib dipilih.',
            'status.in' => 'Status harus active atau inactive.',
        ]);

        DB::beginTransaction();

        try {
            $oldImage = $tv->image;

            // Handle image upload
            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('tv-images', 'public');
                $validated['image'] = $imagePath;
            }

            $tv->update($validated);

            // Delete old image if new image was uploaded
            if ($request->hasFile('image') && $oldImage) {
                Storage::disk('public')->delete($oldImage);
            }

            DB::commit();

            return redirect()->route('admin.tv.index')
                ->with('success', 'TV berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            
            // Delete uploaded image if exists
            if (isset($validated['image'])) {
                Storage::disk('public')->delete($validated['image']);
            }

            return redirect()->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan saat memperbarui TV.');
        }
    }

    /**
     * Remove the specified TV from storage.
     */
    public function destroy(Tv $tv): RedirectResponse
    {
        DB::beginTransaction();

        try {
            $imagePath = $tv->image;

            $tv->delete();

            // Delete associated image
            if ($imagePath) {
                Storage::disk('public')->delete($imagePath);
            }

            DB::commit();

            return redirect()->route('admin.tv.index')
                ->with('success', 'TV berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat menghapus TV.');
        }
    }
}