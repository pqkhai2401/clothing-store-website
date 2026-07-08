<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Collection;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class CollectionController extends Controller
{
    public function index()
    {
        $collections = Collection::withCount('products')->latest()->paginate(10);
        return view('admin.collections.index', compact('collections'));
    }

    public function create()
    {
        $products = Product::where('status', true)->orderBy('name')->get();
        return view('admin.collections.create', compact('products'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:collections,slug',
            'description' => 'nullable|string',
            'banner' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'status' => 'nullable|boolean',
            'products' => 'nullable|array',
            'products.*' => 'integer|exists:products,id',
        ]);

        $slug = $request->filled('slug') 
            ? Str::slug($request->input('slug')) 
            : Str::slug($request->input('name'));

        if (Collection::where('slug', $slug)->exists()) {
            $slug = $slug . '-' . time();
        }

        $bannerPath = null;
        if ($request->hasFile('banner')) {
            $path = $request->file('banner')->store('collections', 'public');
            $bannerPath = 'storage/' . $path;
        }

        $collection = Collection::create([
            'name' => $request->input('name'),
            'slug' => $slug,
            'description' => $request->input('description'),
            'banner' => $bannerPath,
            'status' => $request->input('status', true),
        ]);

        if ($request->has('products')) {
            $collection->products()->sync($request->input('products'));
        }

        return redirect()
            ->route('admin.collections.list')
            ->with('success', 'Tạo bộ sưu tập thành công.');
    }

    public function edit(string $id)
    {
        $collection = Collection::with('products')->findOrFail($id);
        $products = Product::where('status', true)->orderBy('name')->get();
        $selectedProductIds = $collection->products->pluck('id')->toArray();

        return view('admin.collections.edit', compact('collection', 'products', 'selectedProductIds'));
    }

    public function update(Request $request, string $id)
    {
        $collection = Collection::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:collections,slug,' . $id,
            'description' => 'nullable|string',
            'banner' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'status' => 'nullable|boolean',
            'products' => 'nullable|array',
            'products.*' => 'integer|exists:products,id',
        ]);

        $slug = $request->filled('slug') 
            ? Str::slug($request->input('slug')) 
            : Str::slug($request->input('name'));

        if (Collection::where('slug', $slug)->where('id', '!=', $id)->exists()) {
            $slug = $slug . '-' . time();
        }

        $bannerPath = $collection->banner;
        if ($request->hasFile('banner')) {
            // Delete old banner if exists
            if ($collection->banner) {
                $oldPath = str_replace('storage/', '', $collection->banner);
                Storage::disk('public')->delete($oldPath);
            }
            $path = $request->file('banner')->store('collections', 'public');
            $bannerPath = 'storage/' . $path;
        }

        $collection->update([
            'name' => $request->input('name'),
            'slug' => $slug,
            'description' => $request->input('description'),
            'banner' => $bannerPath,
            'status' => $request->input('status', true),
        ]);

        $collection->products()->sync($request->input('products', []));

        return redirect()
            ->route('admin.collections.list')
            ->with('success', 'Cập nhật bộ sưu tập thành công.');
    }

    public function destroy(string $id)
    {
        $collection = Collection::findOrFail($id);

        // Delete banner if exists
        if ($collection->banner) {
            $oldPath = str_replace('storage/', '', $collection->banner);
            Storage::disk('public')->delete($oldPath);
        }

        $collection->products()->detach();
        $collection->delete();

        return redirect()
            ->route('admin.collections.list')
            ->with('success', 'Xóa bộ sưu tập thành công.');
    }
}
