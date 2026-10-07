<?php
// 230443370 Katlego Malaka | Group KN3

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductSize;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::withCount('sizes')->latest()->paginate(15);
        return view('admin.products.index', compact('products'));
    }

    public function create()
    {
        return view('admin.products.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:150',
            'description' => 'nullable|string',
            'category'    => 'required|in:tshirt,cap,tote_bag',
            'price'       => 'required|numeric|min:0',
            'is_featured' => 'boolean',
            'image'       => 'nullable|image|max:2048',
            'sizes'       => 'nullable|array',
            'sizes.*'     => 'integer|min:0',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('products', 'public');
            $imagePath = 'storage/' . $imagePath;
        }

        $product = Product::create([
            'name'           => $data['name'],
            'description'    => $data['description'] ?? null,
            'category'       => $data['category'],
            'price'          => $data['price'],
            'is_featured'    => $request->boolean('is_featured'),
            'image_path'     => $imagePath,
            'stock_quantity' => array_sum($data['sizes'] ?? []),
        ]);

        foreach ($data['sizes'] ?? [] as $size => $qty) {
            ProductSize::create([
                'product_id'     => $product->id,
                'size'           => $size,
                'stock_quantity' => $qty,
            ]);
        }

        return redirect()->route('admin.products.index')
            ->with('success', 'Product created successfully.');
    }

    public function edit(Product $product)
    {
        $product->load('sizes');
        $allSizes = ['S', 'M', 'L', 'XL', 'XXL'];
        return view('admin.products.edit', compact('product', 'allSizes'));
    }

    public function update(Request $request, Product $product)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:150',
            'description' => 'nullable|string',
            'category'    => 'required|in:tshirt,cap,tote_bag',
            'price'       => 'required|numeric|min:0',
            'is_featured' => 'boolean',
            'image'       => 'nullable|image|max:2048',
            'sizes'       => 'nullable|array',
            'sizes.*'     => 'integer|min:0',
        ]);

        $imagePath = $product->image_path;
        if ($request->hasFile('image')) {
            $imagePath = 'storage/' . $request->file('image')->store('products', 'public');
        }

        $product->update([
            'name'           => $data['name'],
            'description'    => $data['description'] ?? null,
            'category'       => $data['category'],
            'price'          => $data['price'],
            'is_featured'    => $request->boolean('is_featured'),
            'image_path'     => $imagePath,
            'stock_quantity' => array_sum($data['sizes'] ?? []),
        ]);

        foreach ($data['sizes'] ?? [] as $size => $qty) {
            ProductSize::updateOrCreate(
                ['product_id' => $product->id, 'size' => $size],
                ['stock_quantity' => $qty]
            );
        }

        return redirect()->route('admin.products.index')
            ->with('success', 'Product updated.');
    }

    public function destroy(Product $product)
    {
        $product->delete();
        return redirect()->route('admin.products.index')
            ->with('success', 'Product deleted.');
    }

    public function toggleFeatured(Product $product)
    {
        $product->update(['is_featured' => !$product->is_featured]);
        return redirect()->back()->with('success', 'Featured status updated.');
    }
}
