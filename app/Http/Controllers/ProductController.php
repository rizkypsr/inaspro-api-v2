<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    /**
     * Display a listing of products.
     */
    public function index(Request $request)
    {
        $query = Product::with(['category', 'variants'])
            ->withCount('variants');

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereHas('category', function ($categoryQuery) use ($search) {
                      $categoryQuery->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Filter by category
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->get('category_id'));
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }

        $products = $query->latest()->paginate(15)->withQueryString();

        return Inertia::render('admin/marketplace/products', [
            'products' => $products,
            'filters' => $request->only(['search', 'category_id', 'status']),
            'categories' => Category::select('id', 'name')->get(),
        ]);
    }

    /**
     * Show the form for creating a new product.
     */
    public function create()
    {
        return Inertia::render('admin/marketplace/products/create', [
            'categories' => Category::select('id', 'name')->get(),
        ]);
    }

    /**
     * Store a newly created product in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:150|unique:products,name',
            'description' => 'required|string',
            'base_price' => 'required|numeric|min:0',
            'status' => 'required|in:active,inactive',
            'variants' => 'required|array|min:1',
            'variants.*.sku' => 'required|string|max:100|unique:product_variants,sku',
            'variants.*.variant_name' => 'required|string|max:150',
            'variants.*.price' => 'required|numeric|min:0',
            'variants.*.stock' => 'required|integer|min:0',
            'variants.*.status' => 'required|in:active,inactive',
            'variants.*.image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);
        // Normalisasi base_price ke integer Rupiah (tanpa sen)
        $validated['base_price'] = $this->normalizeRupiah($validated['base_price']);

        DB::beginTransaction();
        
        try {
            // Create the product
            $product = Product::create($validated);

            // Create variants
            foreach ($validated['variants'] as $index => $variantData) {
                // Normalisasi harga varian ke integer Rupiah (tanpa sen)
                $variantData['price'] = $this->normalizeRupiah($variantData['price']);
                $variantData['product_id'] = $product->id;
                
                // Handle image upload
                if ($request->hasFile("variants.{$index}.image")) {
                    $image = $request->file("variants.{$index}.image");
                    $imageName = time() . '_' . $index . '_' . $image->getClientOriginalName();
                    $imagePath = $image->storeAs('product-variants', $imageName, 'public');
                    $variantData['image_url'] = '/storage/' . $imagePath;
                }
                
                // Remove the image field as it's not in the database
                unset($variantData['image']);
                
                ProductVariant::create($variantData);
            }

            DB::commit();

            return redirect()->route('admin.marketplace.products.index')
                ->with('success', 'Product and variants created successfully.');
        } catch (\Exception $e) {
            DB::rollback();
            
            return back()->withErrors(['error' => 'Failed to create product: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Display the specified product.
     */
    public function show(Product $product)
    {
        $product->load(['category', 'variants']);

        return Inertia::render('admin/marketplace/products/show', [
            'product' => $product,
        ]);
    }

    /**
     * Show the form for editing the specified product.
     */
    public function edit(Product $product)
    {
        $product->load(['category', 'variants']);

        return Inertia::render('admin/marketplace/products/edit', [
            'product' => $product,
            'categories' => Category::select('id', 'name')->get(),
        ]);
    }

    /**
     * Update the specified product in storage.
     */
    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => [
                'required',
                'string',
                'max:150',
                Rule::unique('products', 'name')->ignore($product->id),
            ],
            'description' => 'required|string',
            'base_price' => 'required|numeric|min:0',
            'status' => 'required|in:active,inactive',
            'variants' => 'required|array|min:1',
            'variants.*.id' => 'nullable|exists:product_variants,id',
            'variants.*.sku' => [
                'required',
                'string',
                'max:100',
                function ($attribute, $value, $fail) use ($product) {
                    $index = explode('.', $attribute)[1];
                    $variantId = request("variants.{$index}.id");
                    
                    $query = ProductVariant::where('sku', $value);
                    if ($variantId) {
                        $query->where('id', '!=', $variantId);
                    }
                    
                    if ($query->exists()) {
                        $fail('The SKU has already been taken.');
                    }
                }
            ],
            'variants.*.variant_name' => 'required|string|max:150',
            'variants.*.price' => 'required|numeric|min:0',
            'variants.*.stock' => 'required|integer|min:0',
            'variants.*.status' => 'required|in:active,inactive',
            'variants.*.image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);
        // Normalisasi base_price ke integer Rupiah (tanpa sen)
        $validated['base_price'] = $this->normalizeRupiah($validated['base_price']);

        DB::beginTransaction();
        
        try {
            // Update the product
            $product->update($validated);

            // Get existing variant IDs
            $existingVariantIds = $product->variants->pluck('id')->toArray();
            $submittedVariantIds = [];

            // Update atau buat varian
            foreach ($validated['variants'] as $index => $variantData) {
                // Normalisasi harga varian ke integer Rupiah (tanpa sen)
                $variantData['price'] = $this->normalizeRupiah($variantData['price']);
                $variantData['product_id'] = $product->id;
                
                // Handle image upload
                if ($request->hasFile("variants.{$index}.image")) {
                    $image = $request->file("variants.{$index}.image");
                    $imageName = time() . '_' . $index . '_' . $image->getClientOriginalName();
                    $imagePath = $image->storeAs('product-variants', $imageName, 'public');
                    $variantData['image_url'] = '/storage/' . $imagePath;
                }
                
                // Remove the image field as it's not in the database
                unset($variantData['image']);
                
                if (isset($variantData['id']) && $variantData['id']) {
                    // Update existing variant
                    $variant = ProductVariant::find($variantData['id']);
                    if ($variant && $variant->product_id === $product->id) {
                        // If no new image uploaded, keep the existing image_url
                        if (!isset($variantData['image_url'])) {
                            unset($variantData['image_url']);
                        }
                        $variant->update($variantData);
                        $submittedVariantIds[] = $variant->id;
                    }
                } else {
                    // Create new variant
                    unset($variantData['id']);
                    $variant = ProductVariant::create($variantData);
                    $submittedVariantIds[] = $variant->id;
                }
            }

            // Delete variants that were not submitted
            $variantsToDelete = array_diff($existingVariantIds, $submittedVariantIds);
            if (!empty($variantsToDelete)) {
                ProductVariant::whereIn('id', $variantsToDelete)->delete();
            }

            DB::commit();

            return redirect()->route('admin.marketplace.products.index')
                ->with('success', 'Product and variants updated successfully.');
        } catch (\Exception $e) {
            DB::rollback();
            
            return back()->withErrors(['error' => 'Failed to update product: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Remove the specified product from storage.
     */
    public function destroy(Product $product)
    {
        // Soft delete the product regardless of variants
        $product->delete();

        return redirect()->route('admin.marketplace.products.index')
            ->with('success', 'Product deleted successfully.');
    }

    /**
     * Restore the specified product from trash.
     */
    public function restore($id)
    {
        $product = Product::withTrashed()->findOrFail($id);
        $product->restore();

        return redirect()->route('admin.marketplace.products.index')
            ->with('success', 'Product restored successfully.');
    }

    /**
     * Permanently delete the specified product.
     */
    public function forceDelete($id)
    {
        $product = Product::withTrashed()->findOrFail($id);
        $product->forceDelete();

        return redirect()->route('admin.marketplace.products.index')
            ->with('success', 'Product permanently deleted.');
    }

    /**
     * Normalisasi input uang Indonesia (Rupiah) menjadi integer tanpa sen.
     * Menerima angka atau string dengan pemisah ribuan/decimal Indonesia.
     */
    private function normalizeRupiah($value): int
    {
        if ($value === null) {
            return 0;
        }

        // Jika sudah numeric, bulatkan ke integer Rupiah
        if (is_numeric($value)) {
            $amount = (float) $value;
            if ($amount < 0) {
                $amount = 0;
            }
            return (int) round($amount);
        }

        // Jika string: hilangkan karakter selain digit dan pemisah, lalu konversi
        $str = (string) $value;
        // Buang teks seperti 'Rp', spasi, dll. tapi pertahankan digit, koma, titik, minus
        $str = preg_replace('/[^0-9,.-]/', '', $str);
        // Hilangkan pemisah ribuan (.) khas Indonesia
        $str = str_replace('.', '', $str);
        // Ubah koma menjadi titik agar bisa di-cast sebagai float
        $str = str_replace(',', '.', $str);

        $amount = (float) $str;
        if ($amount < 0) {
            $amount = 0;
        }
        return (int) round($amount);
    }
}
