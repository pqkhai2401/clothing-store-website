<?php

namespace App\Http\Controllers\Admin;

use App\Exports\ActivityLogsExport;
use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ActivityLogController extends Controller
{
    /**
     * Danh sách mốc thời gian cho phép khi dọn log cũ (số ngày).
     */
    private const PRUNE_OPTIONS = [30, 90, 180, 365];

    /**
     * Các trường nhạy cảm trên User mà nếu bị đổi thì bản ghi audit liên quan
     * không được phép xóa (dù bulk-delete hay prune) — phục vụ điều tra sự cố
     * (đổi mật khẩu, khóa/mở khóa, gỡ bảo vệ admin) ngay cả khi chính admin
     * thực hiện thao tác đó cố tình xóa dấu vết.
     */
    private const PROTECTED_USER_FIELDS = ['password', 'is_active', 'is_protected', 'must_change_password', 'locked_by', 'locked_at'];

    /**
     * Hiển thị danh sách nhật ký hoạt động.
     */
    public function index(Request $request)
    {
        $perPage = $this->resolvePerPage($request);

        $data = $this->buildFilteredQuery($request)
            ->with(['user', 'auditable'])
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();

        $viewData = [
            'data'           => $data,
            'perPage'        => $perPage,
            'keyword'        => trim((string) $request->input('search', '')),
            'eventFilter'    => (string) $request->input('event', ''),
            'subjectFilter'  => (string) $request->input('subject', ''),
            'dateFrom'       => (string) $request->input('date_from', ''),
            'dateTo'         => (string) $request->input('date_to', ''),
            'eventOptions'   => ActivityLog::eventOptions(),
            'subjectOptions' => ActivityLog::subjectOptions(),
        ];

        if ($request->ajax()) {
            return response()->json([
                'html'  => view('admin.logs.partials.table', $viewData)->render(),
                'stats' => view('admin.logs.partials.stats', ['stats' => $this->buildStats()])->render(),
            ]);
        }

        $viewData['stats'] = $this->buildStats();

        return view('admin.logs.index', $viewData);
    }

    /**
     * Trả về nội dung chi tiết (diff cũ → mới) cho modal.
     */
    public function show(Request $request, string $id)
    {
        $log = ActivityLog::with(['user', 'auditable'])->findOrFail($id);

        return view('admin.logs.partials.detail', ['log' => $log])->render();
    }

    /**
     * Xuất danh sách nhật ký ra Excel (ưu tiên các dòng được chọn, nếu không thì theo bộ lọc).
     */
    public function export(Request $request)
    {
        $fileName = 'nhat-ky-lam-viec-' . now()->format('Ymd_His') . '.xlsx';

        return Excel::download(new ActivityLogsExport($request), $fileName);
    }

    /**
     * Xóa hàng loạt các bản ghi nhật ký được chọn.
     */
    public function bulkDelete(Request $request)
    {
        $ids = array_filter((array) $request->input('ids', []), 'is_numeric');

        if (empty($ids)) {
            return back()->with('error', 'Vui lòng chọn ít nhất một bản ghi để xóa.');
        }

        $candidates = ActivityLog::whereIn('id', $ids)->get();
        $deletableIds = $candidates->reject(fn (ActivityLog $log) => $this->isProtectedAudit($log))->pluck('id');

        if ($deletableIds->isEmpty()) {
            return back()->with('error', 'Các bản ghi đã chọn thuộc nhóm nhật ký bảo mật (đổi vai trò, khóa tài khoản, đổi mật khẩu, gỡ bảo vệ admin) nên không thể xóa.');
        }

        $deleted = ActivityLog::whereIn('id', $deletableIds)->delete();
        $skipped = $candidates->count() - $deletableIds->count();

        $message = "Đã xóa {$deleted} bản ghi nhật ký.";
        if ($skipped > 0) {
            $message .= " {$skipped} bản ghi nhạy cảm liên quan tài khoản được giữ lại để phục vụ audit.";
        }

        return back()->with('success', $message);
    }

    /**
     * Dọn (xóa) các bản ghi nhật ký cũ hơn một mốc thời gian.
     */
    public function prune(Request $request)
    {
        $validated = $request->validate([
            'older_than_days' => ['required', 'integer', 'in:' . implode(',', self::PRUNE_OPTIONS)],
        ], [
            'older_than_days.required' => 'Vui lòng chọn mốc thời gian cần dọn.',
            'older_than_days.in'       => 'Mốc thời gian không hợp lệ.',
        ]);

        $threshold = now()->subDays((int) $validated['older_than_days']);

        $deleted = 0;
        ActivityLog::where('created_at', '<', $threshold)
            ->orderBy('id')
            ->chunkById(500, function ($logs) use (&$deleted) {
                $deletableIds = $logs->reject(fn (ActivityLog $log) => $this->isProtectedAudit($log))->pluck('id');

                if ($deletableIds->isNotEmpty()) {
                    $deleted += ActivityLog::whereIn('id', $deletableIds)->delete();
                }
            });

        return back()->with('success', "Đã dọn {$deleted} bản ghi nhật ký cũ hơn {$validated['older_than_days']} ngày (bản ghi nhạy cảm liên quan tài khoản được giữ lại).");
    }

    /**
     * Bản ghi audit liên quan tới User mà chạm vào trường nhạy cảm (mật khẩu,
     * trạng thái hoạt động, cờ bảo vệ) hoặc sự kiện đổi vai trò — không được
     * phép xóa qua bulk-delete/prune để giữ tính bất biến của audit trail.
     */
    private function isProtectedAudit(ActivityLog $log): bool
    {
        if ($log->auditable_type !== User::class) {
            return false;
        }

        if (in_array($log->event, ['role_updated', 'password_reset'], true)) {
            return true;
        }

        if ($log->event === 'updated') {
            $touchedFields = array_unique(array_merge(
                array_keys((array) $log->old_values),
                array_keys((array) $log->new_values)
            ));

            return (bool) array_intersect($touchedFields, self::PROTECTED_USER_FIELDS);
        }

        return false;
    }

    /**
     * Dựng query đã áp bộ lọc — dùng chung cho danh sách và export.
     */
    public function buildFilteredQuery(Request $request): Builder
    {
        $query = ActivityLog::query();

        // Tìm kiếm theo người thực hiện (username/email) hoặc địa chỉ IP
        if ($search = trim((string) $request->input('search', ''))) {
            $userIds = User::query()
                ->where('username', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->pluck('id');

            $query->where(function ($sub) use ($search, $userIds) {
                $sub->where('ip_address', 'like', "%{$search}%");
                if ($userIds->isNotEmpty()) {
                    $sub->orWhere(function ($inner) use ($userIds) {
                        $inner->where('user_type', User::class)
                            ->whereIn('user_id', $userIds);
                    });
                }
            });
        }

        // Lọc theo loại hành động
        if (($event = (string) $request->input('event', '')) !== ''
            && array_key_exists($event, ActivityLog::EVENT_LABELS)) {
            $query->where('event', $event);
        }

        // Lọc theo loại đối tượng
        if (($subject = (string) $request->input('subject', '')) !== ''
            && array_key_exists($subject, ActivityLog::SUBJECT_LABELS)) {
            $query->where('auditable_type', $subject);
        }

        // Lọc theo khoảng ngày (created_at)
        if ($from = $request->input('date_from')) {
            $query->whereDate('created_at', '>=', $from);
        }
        if ($to = $request->input('date_to')) {
            $query->whereDate('created_at', '<=', $to);
        }

        return $query;
    }

    private function buildStats(): array
    {
        return [
            'total'        => ActivityLog::count(),
            'today'        => ActivityLog::whereDate('created_at', today())->count(),
            'this_week'    => ActivityLog::where('created_at', '>=', now()->startOfWeek())->count(),
            'active_users' => ActivityLog::whereNotNull('user_id')->distinct('user_id')->count('user_id'),
        ];
    }

    private function resolvePerPage(Request $request): int
    {
        $perPage = (int) $request->input('per_page', $request->input('perPage', 10));
        $allowedPerPages = [10, 25, 50, 100];

        return in_array($perPage, $allowedPerPages, true) ? $perPage : 10;
    }
}
