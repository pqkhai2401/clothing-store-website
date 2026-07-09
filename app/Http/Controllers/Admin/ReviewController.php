<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function index(Request $request)
    {
        $search     = trim((string) $request->input('search', $request->input('keyword')));
        $keyword    = $search;
        $ratingFilter = $request->input('rating');
        $statusFilter = $request->input('status');
        $perPage    = in_array((int) $request->input('per_page'), [10, 25, 50], true)
            ? (int) $request->input('per_page')
            : 10;

        $query = Review::with(['user', 'product'])
            ->orderBy('created_at', 'desc');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('comment', 'like', "%{$search}%")
                  ->orWhereHas('product', fn ($p) => $p->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('user', fn ($u) => $u->where('username', 'like', "%{$search}%"));
            });
        }

        if ($ratingFilter) {
            $query->where('rating', $ratingFilter);
        }

        // Lọc theo trạng thái kiểm duyệt (pending/approved/rejected/flagged).
        if ($statusFilter && in_array($statusFilter, [
            Review::STATUS_PENDING,
            Review::STATUS_APPROVED,
            Review::STATUS_REJECTED,
            Review::STATUS_FLAGGED,
        ], true)) {
            $query->where('status', $statusFilter);
        }

        $reviews = $query->paginate($perPage)->withQueryString();

        if ($request->ajax()) {
            return response()->json([
                'html' => view('admin.reviews.partials.table', compact('reviews'))->render(),
            ]);
        }

        return view('admin.reviews.index', compact('reviews', 'keyword', 'ratingFilter', 'statusFilter', 'perPage'));
    }

    /**
     * Admin DUYỆT thủ công một đánh giá -> hiển thị công khai.
     * Dùng cho các review đang 'flagged' (AI không chắc) hoặc bị 'rejected' oan.
     */
    public function approve(string $id)
    {
        $review = Review::findOrFail($id);
        $review->update(['status' => Review::STATUS_APPROVED]);

        return back()->with('success', 'Đã duyệt đánh giá. Đánh giá sẽ hiển thị công khai trên trang sản phẩm.');
    }

    /**
     * Admin TỪ CHỐI thủ công một đánh giá -> ẩn khỏi website.
     */
    public function reject(string $id)
    {
        $review = Review::findOrFail($id);
        $review->update(['status' => Review::STATUS_REJECTED]);

        return back()->with('success', 'Đã từ chối đánh giá. Đánh giá sẽ bị ẩn khỏi trang sản phẩm.');
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
        $keyword = trim((string) $request->input('search', $request->input('keyword')));
        $perPage = in_array((int) $request->input('per_page'), [10, 25, 50], true)
            ? (int) $request->input('per_page') : 10;

        $query = Review::onlyTrashed()->with(['user', 'product'])->orderBy('deleted_at', 'desc');
        if ($keyword !== '') {
            $query->where(function ($q) use ($keyword) {
                $q->where('comment', 'like', "%{$keyword}%")
                  ->orWhereHas('product', fn ($p) => $p->where('name', 'like', "%{$keyword}%"));
            });
        }

        $reviews = $query->paginate($perPage)->withQueryString();

        if ($request->ajax()) {
            return response()->json([
                'html' => view('admin.reviews.partials.trash-table', compact('reviews'))->render(),
            ]);
        }

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
