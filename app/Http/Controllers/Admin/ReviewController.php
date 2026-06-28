<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function index(Request $request)
    {
        $keyword    = trim((string) $request->input('keyword'));
        $ratingFilter = $request->input('rating');
        $perPage    = in_array((int) $request->input('per_page'), [10, 25, 50], true)
            ? (int) $request->input('per_page')
            : 10;

        $query = Review::with(['user', 'product'])
            ->orderBy('created_at', 'desc');

        if ($keyword !== '') {
            $query->where(function ($q) use ($keyword) {
                $q->where('comment', 'like', "%{$keyword}%")
                  ->orWhereHas('product', fn ($p) => $p->where('name', 'like', "%{$keyword}%"))
                  ->orWhereHas('user', fn ($u) => $u->where('username', 'like', "%{$keyword}%"));
            });
        }

        if ($ratingFilter) {
            $query->where('rating', $ratingFilter);
        }

        $reviews = $query->paginate($perPage)->appends($request->except('page'));

        return view('admin.reviews.index', compact('reviews', 'keyword', 'ratingFilter', 'perPage'));
    }

    public function destroy(string $id)
    {
        $review = Review::findOrFail($id);
        $review->delete();

        return redirect()->route('admin.reviews.list')->with('success', 'Xóa đánh giá thành công');
    }

    public function bulkDelete(Request $request)
    {
        $ids = array_filter((array) $request->input('ids', []), 'is_numeric');

        if (empty($ids)) {
            return back()->with('error', 'Vui lòng chọn ít nhất một đánh giá để xóa.');
        }

        $deleted = Review::whereIn('id', $ids)->delete();

        return back()->with('success', "Đã xóa {$deleted} đánh giá thành công.");
    }

    public function trash(Request $request)
    {
        $keyword = trim((string) $request->input('keyword'));
        $perPage = in_array((int) $request->input('per_page'), [10, 25, 50], true)
            ? (int) $request->input('per_page') : 10;

        $query = Review::onlyTrashed()->with(['user', 'product'])->orderBy('deleted_at', 'desc');
        if ($keyword !== '') {
            $query->where(function ($q) use ($keyword) {
                $q->where('comment', 'like', "%{$keyword}%")
                  ->orWhereHas('product', fn ($p) => $p->where('name', 'like', "%{$keyword}%"));
            });
        }

        $reviews = $query->paginate($perPage)->appends($request->except('page'));

        return view('admin.reviews.trash', compact('reviews', 'keyword', 'perPage'));
    }

    public function restore(string $id)
    {
        Review::onlyTrashed()->findOrFail($id)->restore();

        return redirect()->route('admin.reviews.trash')->with('success', 'Khôi phục đánh giá thành công');
    }

    public function forceDelete(string $id)
    {
        Review::onlyTrashed()->findOrFail($id)->forceDelete();

        return redirect()->route('admin.reviews.trash')->with('success', 'Xóa vĩnh viễn đánh giá thành công');
    }
}
