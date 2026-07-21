<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use App\Models\HeroBanner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class HeroBannerController extends Controller
{
    public function index()
    {
        $heroBanners = HeroBanner::orderBy('sort_order')->orderByDesc('id')->paginate(10);

        return view('admin.hero-banners.index', compact('heroBanners'));
    }

    public function create()
    {
        return view('admin.hero-banners.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'nullable|string|max:255',
            'subtitle' => 'nullable|string|max:500',
            'button_text' => 'nullable|string|max:100',
            'button_link' => 'nullable|string|max:255',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'is_active' => 'nullable|boolean',
            'sort_order' => 'nullable|integer|min:0',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after:start_date',
        ]);

        $path = $request->file('image')->store('hero-banners', 'public');

        HeroBanner::create([
            'title' => $request->input('title'),
            'subtitle' => $request->input('subtitle'),
            'button_text' => $request->input('button_text'),
            'button_link' => $request->input('button_link'),
            'image_path' => 'storage/' . $path,
            'is_active' => $request->boolean('is_active'),
            'sort_order' => $request->input('sort_order', 0),
            'start_date' => $request->input('start_date'),
            'end_date' => $request->input('end_date'),
        ]);

        return redirect()
            ->route('admin.hero-banners.list')
            ->with('success', 'Tạo banner trang chủ thành công.');
    }

    public function edit(string $id)
    {
        $heroBanner = HeroBanner::findOrFail($id);

        return view('admin.hero-banners.edit', compact('heroBanner'));
    }

    public function update(Request $request, string $id)
    {
        $heroBanner = HeroBanner::findOrFail($id);

        $request->validate([
            'title' => 'nullable|string|max:255',
            'subtitle' => 'nullable|string|max:500',
            'button_text' => 'nullable|string|max:100',
            'button_link' => 'nullable|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'is_active' => 'nullable|boolean',
            'sort_order' => 'nullable|integer|min:0',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after:start_date',
        ]);

        $imagePath = $heroBanner->image_path;
        if ($request->hasFile('image')) {
            if ($heroBanner->image_path) {
                $oldPath = str_replace('storage/', '', $heroBanner->image_path);
                Storage::disk('public')->delete($oldPath);
            }
            $path = $request->file('image')->store('hero-banners', 'public');
            $imagePath = 'storage/' . $path;
        }

        $heroBanner->update([
            'title' => $request->input('title'),
            'subtitle' => $request->input('subtitle'),
            'button_text' => $request->input('button_text'),
            'button_link' => $request->input('button_link'),
            'image_path' => $imagePath,
            'is_active' => $request->boolean('is_active'),
            'sort_order' => $request->input('sort_order', 0),
            'start_date' => $request->input('start_date'),
            'end_date' => $request->input('end_date'),
        ]);

        return redirect()
            ->route('admin.hero-banners.list')
            ->with('success', 'Cập nhật banner trang chủ thành công.');
    }

    public function toggleStatus(string $id)
    {
        $heroBanner = HeroBanner::findOrFail($id);
        $heroBanner->update(['is_active' => ! $heroBanner->is_active]);

        return redirect()
            ->route('admin.hero-banners.list')
            ->with('success', $heroBanner->is_active
                ? 'Đã bật hiển thị banner trên trang chủ.'
                : 'Đã tắt hiển thị banner trên trang chủ.');
    }

    public function destroy(string $id)
    {
        $heroBanner = HeroBanner::findOrFail($id);

        if ($heroBanner->image_path) {
            $oldPath = str_replace('storage/', '', $heroBanner->image_path);
            Storage::disk('public')->delete($oldPath);
        }

        $heroBanner->delete();

        return redirect()
            ->route('admin.hero-banners.list')
            ->with('success', 'Xóa banner trang chủ thành công.');
    }
}
