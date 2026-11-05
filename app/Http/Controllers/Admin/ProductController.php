<?php
// PRT362S — Eben Supply | Group KN3

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductSize;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ProductController extends Controller
{
    /** Uploaded images live under public/ so they work without a storage symlink. */
    private const UPLOAD_DIR = 'images/products/uploads';

    public function index()
    {
        $products = Product::withCount('sizes')->latest()->paginate(15);
        return view('admin.products.index', compact('products'));
    }

    public function create()
    {
        $product = new Product(['is_featured' => false]);
        $product->setRelation('sizes', collect());

        return view('admin.products.create', compact('product'));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request, null);

        $product = DB::transaction(function () use ($request, $data) {
            $product = Product::create($this->attributes($request, $data) + [
                'image_path' => $request->hasFile('image') ? $this->storeImage($request->file('image')) : null,
            ]);
            $this->syncStock($product, $data);

            return $product;
        });

        return redirect()->route('admin.products.index')
            ->with('success', "Product \"{$product->name}\" created.");
    }

    public function edit(Product $product)
    {
        $product->load('sizes');
        return view('admin.products.edit', compact('product'));
    }

    public function update(Request $request, Product $product)
    {
        $data = $this->validated($request, $product);

        DB::transaction(function () use ($request, $data, $product) {
            $attributes = $this->attributes($request, $data);

            if ($request->hasFile('image')) {
                $this->deleteUploadedImage($product->image_path);
                $attributes['image_path'] = $this->storeImage($request->file('image'));
            }

            $product->update($attributes);
            $this->syncStock($product, $data);
        });

        return redirect()->route('admin.products.index')
            ->with('success', 'Product updated.');
    }

    /**
     * Soft delete: past orders keep pointing at the product. It is removed from
     * every shopper's cart so nobody can check out with it.
     */
    public function destroy(Product $product)
    {
        DB::transaction(function () use ($product) {
            $product->cartItems()->delete();
            $product->delete();
        });

        return redirect()->route('admin.products.index')
            ->with('success', 'Product deleted.');
    }

    public function toggleFeatured(Product $product)
    {
        $product->update(['is_featured' => !$product->is_featured]);
        return redirect()->back()->with('success', 'Featured status updated.');
    }

    private function validated(Request $request, ?Product $product): array
    {
        $data = $request->validate([
            'name'           => 'required|string|max:150',
            'description'    => 'nullable|string',
            'category'       => ['required', Rule::in(Product::categoryKeys())],
            'price'          => 'required|numeric|min:0.01|max:99999',
            'is_featured'    => 'boolean',
            'is_kids'        => 'boolean',
            // Every product needs a real photo; it can be kept when editing.
            'image'          => ($product?->image_path ? 'nullable' : 'required') . '|image|max:2048',
            'remove_sale'    => 'boolean',
            'sale_price'     => 'exclude_if:remove_sale,1|nullable|required_with:sale_starts_at,sale_ends_at|numeric|gt:0|lt:price',
            'sale_starts_at' => 'exclude_if:remove_sale,1|nullable|date',
            'sale_ends_at'   => array_filter(['exclude_if:remove_sale,1', 'nullable', 'date', $request->filled('sale_starts_at') ? 'after:sale_starts_at' : null]),
            'sizing'         => 'required|in:sized,one_size',
            'stock_quantity' => 'required_if:sizing,one_size|nullable|integer|min:0',
            'sizes'          => 'required_if:sizing,sized|nullable|array',
            'sizes.*'        => 'integer|min:0',
        ]);

        if ($data['sizing'] === 'sized') {
            $allowed = ProductSize::sizesFor($request->boolean('is_kids'));
            $unknown = array_diff(array_keys($data['sizes'] ?? []), $allowed);
            if ($unknown) {
                throw ValidationException::withMessages(['sizes' => 'Not a ' . ($request->boolean('is_kids') ? 'kids' : 'adult') . ' size: ' . implode(', ', $unknown)]);
            }
        }

        if ($request->boolean('remove_sale')) {
            $data['sale_price'] = $data['sale_starts_at'] = $data['sale_ends_at'] = null;
        }

        return $data;
    }

    private function attributes(Request $request, array $data): array
    {
        return [
            'name'        => $data['name'],
            'description' => $data['description'] ?? null,
            'category'    => $data['category'],
            'price'       => $data['price'],
            'is_featured' => $request->boolean('is_featured'),
            'is_kids'     => $request->boolean('is_kids'),
            // Sale dates are entered in store time (config app.timezone).
            'sale_price'     => $data['sale_price'] ?? null,
            'sale_starts_at' => $data['sale_starts_at'] ?? null,
            'sale_ends_at'   => $data['sale_ends_at'] ?? null,
        ];
    }

    /**
     * Sized products keep one row per size of their set (adult or kids) and a
     * total that matches; one-size
     * products have no size rows and store their stock on the product.
     */
    private function syncStock(Product $product, array $data): void
    {
        if ($data['sizing'] === 'one_size') {
            $product->sizes()->delete();
            $product->update(['stock_quantity' => (int) $data['stock_quantity']]);
            return;
        }

        $sizes = $product->sizeOptions();
        $product->sizes()->whereNotIn('size', $sizes)->delete();

        foreach ($sizes as $size) {
            ProductSize::updateOrCreate(
                ['product_id' => $product->id, 'size' => $size],
                ['stock_quantity' => (int) ($data['sizes'][$size] ?? 0)]
            );
        }
        $product->update(['stock_quantity' => $product->sizes()->sum('stock_quantity')]);
    }

    private function storeImage(UploadedFile $file): string
    {
        File::ensureDirectoryExists(public_path(self::UPLOAD_DIR));
        $name = $file->hashName();
        $file->move(public_path(self::UPLOAD_DIR), $name);

        return self::UPLOAD_DIR . '/' . $name;
    }

    private function deleteUploadedImage(?string $path): void
    {
        // Only remove files this controller uploaded, never the seeded catalogue images.
        if ($path && str_starts_with($path, self::UPLOAD_DIR . '/')) {
            File::delete(public_path($path));
        }
    }
}
