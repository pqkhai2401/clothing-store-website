<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use OwenIt\Auditing\Events\AuditCustom;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    /**
     * Số lượng admin hệ thống (is_protected) tối đa được phép tồn tại cùng lúc.
     * Muốn đổi người, phải gỡ protected của người cũ trước rồi mới bật cho người mới.
     */
    private const MAX_PROTECTED_ADMINS = 5;

    /**
     * Sàn tuyệt đối — không bao giờ được để 0 admin protected, nếu không sẽ
     * không còn ai đủ quyền bật lại is_protected cho người khác (bế tắc vĩnh viễn).
     */
    private const MIN_PROTECTED_ADMINS = 2;

    /**
     * Display a listing of users.
     */
    public function index(Request $request)
    {
        $context = $this->resolveContext($request);
        $this->authorizeContext($request, $context['type']);

        $perPage = $this->resolvePerPage($request);
        $sortBy = $request->input('sort', $request->input('sort_by', 'id'));
        $sortDir = $request->input('direction', $request->input('sort_dir', 'asc'));

        $query = User::with('roles');
        $this->applyTypeFilter($query, $context['type']);

        if ($search = trim((string) $request->input('search', $request->input('keyword')))) {
            $query->where(function ($subQuery) use ($search) {
                $subQuery->where('username', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone_number', 'like', "%{$search}%");
            });
        }

        if (in_array($request->input('status'), ['0', '1'], true)) {
            $query->where('is_active', (bool) (int) $request->input('status'));
        }

        if (in_array($sortDir, ['asc', 'desc'], true)) {
            match ($sortBy) {
                'id' => $query->orderBy('id', $sortDir),
                'username' => $query->orderBy('username', $sortDir),
                'email' => $query->orderBy('email', $sortDir),
                default => $query->orderBy('id', 'asc'),
            };
        } else {
            $query->orderBy('id', 'asc');
        }

        $data = $query->paginate($perPage)->withQueryString();
        $roles = $this->rolesForContext($context['type']);

        if ($request->ajax()) {
            return response()->json([
                'html' => view('admin.users.partials.table', [
                    'data' => $data,
                    'roles' => $roles,
                    ...$context,
                ])->render(),
            ]);
        }

        return view('admin.users.index', [
            'data' => $data,
            'roles' => $roles,
            ...$context,
        ]);
    }

    /**
     * Show the form for creating a new user.
     */
    public function create()
    {
        $request = request();
        $context = $this->resolveContext($request);
        $this->authorizeContext($request, $context['type']);

        $currentUser             = $request->user();
        $currentIsProtectedAdmin = $currentUser->isAdmin() && (bool) $currentUser->is_protected;

        $roles = $this->rolesForContext($context['type']);
        if (! $currentIsProtectedAdmin) {
            $roles = $roles->filter(fn ($role) => $role->name !== UserRole::ADMIN->value)->values();
        }

        return view('admin.users.create', [
            'roles' => $roles,
            ...$context,
        ]);
    }

    /**
     * Store a newly created user.
     */
    public function store(Request $request)
    {
        $context = $this->resolveContext($request);
        $this->authorizeContext($request, $context['type']);

        $validated = $request->validate([
            'username' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'phone_number' => ['nullable', 'string', 'max:20'],
            'role_id' => [$context['type'] === 'customer' ? 'nullable' : 'required', 'integer', 'exists:roles,id'],
            'is_active' => ['required', 'boolean'],
            'password' => ['required', 'string', 'min:8', 'max:255'],
            'gender' => ['nullable', 'in:nam,nu,khac'],
            'avatar' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'city' => ['nullable', 'string', 'max:255'],
            'ward' => ['nullable', 'string', 'max:255'],
            'apartment_number' => ['nullable', 'string', 'max:255'],
            'lock_reason' => ['nullable', 'string', 'max:255'],
        ], $this->validationMessages());

        $currentUser          = $request->user();
        $currentIsNormalAdmin = $currentUser->isAdmin() && ! (bool) $currentUser->is_protected;

        $roleForUser = $this->roleForContext($context['type'], $validated['role_id'] ?? null);

        if ($currentIsNormalAdmin && $roleForUser?->name === UserRole::ADMIN->value) {
            return back()->withErrors(['role_id' => 'Quản trị viên thường chỉ được tạo tài khoản nhân viên.'])->withInput();
        }

        // Upload ảnh đại diện (nếu có) — tái dùng pattern ở User/ProfileController:
        // lưu vào disk "public", thư mục "avatars", store() tự sinh tên file duy nhất.
        $avatarPath = null;
        if ($request->hasFile('avatar')) {
            $avatarPath = $request->file('avatar')->store('avatars', 'public');
        }

        $user = User::create([
            'username'     => $validated['username'],
            'email'        => $validated['email'],
            'phone_number' => $validated['phone_number'] ?? null,
            'gender'       => $validated['gender'] ?? null,
            'avatar_url'   => $avatarPath,
            'is_active'    => (bool) $validated['is_active'],
            'password'     => Hash::make($validated['password']),
            'is_protected' => false,
        ]);

        if ($roleForUser) {
            $user->syncRoles([$roleForUser->name]);
        }

        if ($this->hasAddressInput($validated)) {
            $user->addresses()->create([
                'city' => $validated['city'] ?? '',
                'ward' => $validated['ward'] ?? '',
                'apartment_number' => $validated['apartment_number'] ?? '',
            ]);
        }

        return redirect()->route($context['routePrefix'].'.list')->with('success', 'Tạo '.$context['itemLabelLower'].' thành công');
    }

    /**
     * Display the specified user.
     */
    public function show(string $id)
    {
        $user = User::with([
            'roles',
            'addresses' => fn ($query) => $query->latest('id'),
        ])->findOrFail($id);
        $context = $this->resolveContext(request(), $user);
        $this->authorizeContext(request(), $context['type'], $user);

        if (request()->expectsJson() || request()->ajax()) {
            $currentUser      = request()->user();
            $currentIsNormal  = $currentUser->isAdmin() && ! (bool) $currentUser->is_protected;
            $roles = $this->rolesForContext($context['type']);
            if ($currentIsNormal) {
                $roles = $roles->filter(fn ($r) => $r->name !== UserRole::ADMIN->value)->values();
            }

            return response()->json([
                'user'      => $this->userModalPayload($user),
                'roles'     => $roles->map(fn ($role) => ['id' => $role->id, 'name' => $role->name])->values(),
                'show_role' => ($context['type'] !== 'customer'),
            ]);
        }

        return redirect()->route($context['routePrefix'].'.list');
    }

    /**
     * Hiển thị trang chỉnh sửa (bố cục giống trang Thêm) — thay cho modal cũ.
     */
    public function edit(string $id)
    {
        $request = request();
        $user = User::with([
            'roles',
            'addresses' => fn ($query) => $query->latest('id'),
        ])->findOrFail($id);
        $context = $this->resolveContext($request, $user);
        $this->authorizeContext($request, $context['type'], $user);

        $currentUser             = $request->user();
        $currentIsProtectedAdmin = $currentUser->isAdmin() && (bool) $currentUser->is_protected;

        $roles = $this->rolesForContext($context['type']);
        if (! $currentIsProtectedAdmin) {
            $roles = $roles->filter(fn ($role) => $role->name !== UserRole::ADMIN->value)->values();
        }

        return view('admin.users.edit-page', [
            'user'    => $user,
            'address' => $user->addresses->first(),
            'roles'   => $roles,
            ...$context,
        ]);
    }

    /**
     * Update the specified user.
     */
    public function update(Request $request, string $id)
    {
        $user = User::findOrFail($id);
        $context = $this->resolveContext($request, $user);
        $this->authorizeContext($request, $context['type'], $user);

        $validated = $request->validate([
            'username' => [
                'required',
                'string',
                'max:255',
            ],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'phone_number' => ['nullable', 'string', 'max:20'],
            'role_id' => [$context['type'] === 'all' ? 'required' : 'nullable', 'integer', 'exists:roles,id'],
            'is_active' => ['required', 'boolean'],
            'gender' => ['nullable', 'in:nam,nu,khac'],
            'avatar' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'city' => ['nullable', 'string', 'max:255'],
            'ward' => ['nullable', 'string', 'max:255'],
            'apartment_number' => ['nullable', 'string', 'max:255'],
            'lock_reason' => ['nullable', 'string', 'max:255'],
        ], $this->validationMessages());

        $currentUser = $request->user();
        $targetUser  = $user;

        $isSelf = (int) $currentUser->id === (int) $targetUser->id;

        $currentIsProtectedAdmin = $currentUser->isAdmin() && (bool) $currentUser->is_protected;
        $currentIsNormalAdmin    = $currentUser->isAdmin() && ! (bool) $currentUser->is_protected;

        $targetIsProtectedAdmin = $targetUser->isAdmin() && (bool) $targetUser->is_protected;
        $targetIsNormalAdmin    = $targetUser->isAdmin() && ! (bool) $targetUser->is_protected;

        $currentRoleId   = $targetUser->roles()->first()?->id;
        $statusChanged   = (bool) $validated['is_active'] !== (bool) $targetUser->is_active;
        $roleChanged     = ! empty($validated['role_id']) && (int) $validated['role_id'] !== (int) $currentRoleId;
        $protectedChanged = $request->has('is_protected')
            && (bool) $request->boolean('is_protected') !== (bool) $targetUser->is_protected;

        $infoChanged = $validated['username'] !== $targetUser->username
            || $validated['email'] !== $targetUser->email
            || ($validated['phone_number'] ?? null) !== $targetUser->phone_number
            || (array_key_exists('gender', $validated) && ($validated['gender'] ?? null) !== $targetUser->gender)
            || $request->hasFile('avatar');

        $currentAddress = $targetUser->addresses()->latest('id')->first();
        $addressChanged = $this->hasAddressInput($validated) && (
            ($validated['city'] ?? '') !== ($currentAddress->city ?? '')
            || ($validated['ward'] ?? '') !== ($currentAddress->ward ?? '')
            || ($validated['apartment_number'] ?? '') !== ($currentAddress->apartment_number ?? '')
        );

        // 1. Block self-lock
        if ($isSelf && $statusChanged && ! (bool) $validated['is_active']) {
            return $this->validationErrorResponse($request, 'is_active', 'Bạn không thể tự khóa tài khoản đang đăng nhập.');
        }

        // 2. Block self-role-change
        if ($isSelf && $roleChanged) {
            return $this->validationErrorResponse($request, 'role_id', 'Bạn không thể tự thay đổi vai trò của chính mình.');
        }

        // 3. Only protected admin can toggle is_protected
        if ($protectedChanged && ! $currentIsProtectedAdmin) {
            return $this->validationErrorResponse($request, 'is_protected', 'Chỉ admin hệ thống mới có quyền thay đổi quyền bảo vệ.');
        }

        // 4. Cannot self-unprotect
        if ($isSelf && $protectedChanged && ! $request->boolean('is_protected')) {
            return $this->validationErrorResponse($request, 'is_protected', 'Bạn không thể tự tắt quyền bảo vệ của chính mình.');
        }

        // 5. Cannot reduce protected admins below the minimum threshold
        if ($protectedChanged && ! $request->boolean('is_protected') && (bool) $targetUser->is_protected) {
            $remainingAfterChange = User::role(UserRole::ADMIN->value)
                ->where('is_protected', true)
                ->count() - 1;
            if ($remainingAfterChange < self::MIN_PROTECTED_ADMINS) {
                return $this->validationErrorResponse($request, 'is_protected', 'Không thể tắt quyền bảo vệ vì hệ thống cần tối thiểu '.self::MIN_PROTECTED_ADMINS.' admin hệ thống được bảo vệ.');
            }
        }

        // 5b. Cannot exceed the maximum number of protected admins
        if ($protectedChanged && $request->boolean('is_protected') && ! (bool) $targetUser->is_protected) {
            $currentProtectedCount = User::role(UserRole::ADMIN->value)
                ->where('is_protected', true)
                ->count();
            if ($currentProtectedCount >= self::MAX_PROTECTED_ADMINS) {
                return $this->validationErrorResponse($request, 'is_protected', 'Hệ thống đã có đủ '.self::MAX_PROTECTED_ADMINS.' admin hệ thống được bảo vệ. Hãy gỡ bảo vệ 1 người trước khi thêm người mới.');
            }
        }

        // 6. Cannot change role of protected admin
        if ($targetIsProtectedAdmin && $roleChanged) {
            return $this->validationErrorResponse($request, 'role_id', 'Không thể thay đổi vai trò của admin hệ thống.');
        }

        // 7. Cannot lock (deactivate) a protected admin
        if ($targetIsProtectedAdmin && $statusChanged && ! (bool) $validated['is_active']) {
            return $this->validationErrorResponse($request, 'is_active', 'Không thể khóa tài khoản admin hệ thống.');
        }

        // 7a. is_protected can only be set on admin accounts
        if ($protectedChanged && $request->boolean('is_protected') && ! $targetUser->isAdmin()) {
            return $this->validationErrorResponse($request, 'is_protected', 'Chỉ tài khoản admin mới có thể được cấp quyền bảo vệ.');
        }

        // 8. Normal admin cannot edit protected admin (any action)
        if ($currentIsNormalAdmin && $targetIsProtectedAdmin) {
            return $this->validationErrorResponse($request, 'permission', 'Quản trị viên thường không có quyền chỉnh sửa admin hệ thống.');
        }

        // 9. Normal admin cannot change any data of another normal admin
        if ($currentIsNormalAdmin && $targetIsNormalAdmin && ! $isSelf
            && ($statusChanged || $roleChanged || $protectedChanged || $infoChanged || $addressChanged)) {
            return $this->validationErrorResponse($request, 'permission', 'Quản trị viên thường không có quyền chỉnh sửa thông tin của quản trị viên khác.');
        }

        // 8b. Nobody except self may edit personal info/address of a protected
        // admin — not even another protected admin. is_protected/role/status
        // keep their own rules (3, 5, 5b, 6, 7) to preserve the handover flow.
        if ($targetIsProtectedAdmin && ! $isSelf && ($infoChanged || $addressChanged)) {
            return $this->validationErrorResponse($request, 'permission', 'Chỉ chính admin hệ thống mới có thể sửa thông tin cá nhân của tài khoản này.');
        }

        // 10. Normal admin cannot promote anyone to admin role
        if ($currentIsNormalAdmin && $roleChanged) {
            $newRole = Role::find((int) $validated['role_id']);
            if ($newRole?->name === UserRole::ADMIN->value) {
                return $this->validationErrorResponse($request, 'role_id', 'Quản trị viên thường không có quyền cấp vai trò quản trị viên.');
            }
        }

        // --- Build update data ---
        $willBeActive = (bool) $validated['is_active'];

        $updateData = [
            'username'     => $validated['username'],
            'email'        => $validated['email'],
            'phone_number' => $validated['phone_number'] ?? null,
            'gender'       => $validated['gender'] ?? null,
            'is_active'    => $willBeActive,
            'lock_reason'  => $willBeActive ? null : ($validated['lock_reason'] ?? null),
        ];

        // Ảnh đại diện mới (nếu có): lưu file mới, xóa file cũ nếu là ảnh tự upload
        // (đường dẫn tương đối trong disk public — không xóa URL ngoài như ảnh Google).
        if ($request->hasFile('avatar')) {
            $newAvatar = $request->file('avatar')->store('avatars', 'public');
            $oldAvatar = $targetUser->avatar_url;
            if ($oldAvatar && ! \Illuminate\Support\Str::startsWith($oldAvatar, ['http://', 'https://'])) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($oldAvatar);
            }
            $updateData['avatar_url'] = $newAvatar;
        }

        // Audit khóa tài khoản: chỉ ghi ai/lúc nào khi chuyển sang khóa; xóa dấu vết khi mở lại.
        if ($statusChanged) {
            $updateData['locked_by'] = $willBeActive ? null : Auth::id();
            $updateData['locked_at'] = $willBeActive ? null : now();
        }

        if ($currentIsProtectedAdmin && $request->has('is_protected') && $targetUser->isAdmin()) {
            $updateData['is_protected'] = $request->boolean('is_protected');
        }

        $user->update($updateData);

        if ($statusChanged && ! (bool) $validated['is_active']) {
            $this->revokeUserAccess($user);
        }

        $canChangeRole = ! $targetIsProtectedAdmin && ! $isSelf;
        if ($canChangeRole) {
            $roleForUser = $this->roleForContext($context['type'], $validated['role_id'] ?? null, $user);
            if ($roleForUser) {
                $oldRoleName = $user->roles()->first()?->name;
                $user->syncRoles([$roleForUser->name]);

                // Vai trò được lưu ở bảng pivot của Spatie nên không được audit
                // tự động — ghi thủ công một bản ghi "Đổi vai trò" vào nhật ký.
                if ($oldRoleName !== $roleForUser->name) {
                    $this->recordRoleChangeAudit($user, $oldRoleName, $roleForUser->name);
                }
            }
        }

        if (in_array($context['type'], ['staff', 'customer']) && ($this->hasAddressInput($validated) || $user->addresses()->exists())) {
            $addressData = [
                'city' => $validated['city'] ?? '',
                'ward' => $validated['ward'] ?? '',
                'apartment_number' => $validated['apartment_number'] ?? '',
            ];

            $address = $user->addresses()->latest('id')->first();

            if ($address) {
                $address->update($addressData);
            } else {
                $user->addresses()->create($addressData);
            }
        }

        $user->load([
            'roles',
            'addresses' => fn ($query) => $query->latest('id'),
        ]);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'message' => 'Cập nhật '.$context['itemLabelLower'].' thành công',
                'user' => $this->userModalPayload($user),
                'show_role' => ($context['type'] !== 'customer'),
            ]);
        }

        return redirect()->route($context['routePrefix'].'.list')->with('success', 'Cập nhật '.$context['itemLabelLower'].' thành công');
    }

    /**
     * Admin đặt lại mật khẩu cho người dùng khác — tách riêng khỏi update()
     * để không cần biết mật khẩu cũ nhưng vẫn ràng buộc: không tự reset cho
     * chính mình (dùng trang Hồ sơ cá nhân), và giữ nguyên các rule chống
     * leo thang đặc quyền giữa admin thường/protected. Sau khi reset, tài
     * khoản bị buộc đổi mật khẩu ở lần đăng nhập kế tiếp và được báo qua email.
     */
    public function resetPassword(Request $request, string $id)
    {
        $user = User::findOrFail($id);
        $context = $this->resolveContext($request, $user);
        $this->authorizeContext($request, $context['type'], $user);

        $validated = $request->validate([
            'password' => ['required', 'string', 'min:8', 'max:255', 'confirmed'],
        ], $this->validationMessages());

        $currentUser = $request->user();
        $targetUser  = $user;
        $isSelf = (int) $currentUser->id === (int) $targetUser->id;

        if ($isSelf) {
            return $this->validationErrorResponse($request, 'password', 'Vui lòng dùng trang Hồ sơ cá nhân để tự đổi mật khẩu.');
        }

        $currentIsNormalAdmin   = $currentUser->isAdmin() && ! (bool) $currentUser->is_protected;
        $targetIsProtectedAdmin = $targetUser->isAdmin() && (bool) $targetUser->is_protected;
        $targetIsNormalAdmin    = $targetUser->isAdmin() && ! (bool) $targetUser->is_protected;

        if ($targetIsProtectedAdmin && ! $isSelf) {
            return $this->validationErrorResponse($request, 'permission', 'Chỉ chính admin hệ thống mới có thể đặt lại mật khẩu của tài khoản này.');
        }

        if ($currentIsNormalAdmin && $targetIsNormalAdmin) {
            return $this->validationErrorResponse($request, 'permission', 'Quản trị viên thường không có quyền reset mật khẩu của quản trị viên khác.');
        }

        // Khách hàng đăng nhập ở storefront — nơi không có middleware buộc đổi
        // mật khẩu — nên không set must_change_password (sẽ chẳng bao giờ được
        // thực thi). Cờ này chỉ có ý nghĩa với admin/nhân viên trong khu vực admin.
        $targetIsCustomer = $targetUser->isCustomer();

        $targetUser->update([
            'password' => Hash::make($validated['password']),
            'must_change_password' => ! $targetIsCustomer,
        ]);

        $this->recordPasswordResetAudit($targetUser);
        $targetUser->notify(new \App\Notifications\PasswordResetByAdmin($currentUser->username));
        $this->revokeUserAccess($targetUser);

        $successMessage = $targetIsCustomer
            ? 'Đặt lại mật khẩu thành công. Hãy thông báo mật khẩu mới cho khách hàng.'
            : 'Đặt lại mật khẩu thành công. Tài khoản sẽ phải đổi mật khẩu ở lần đăng nhập kế tiếp.';

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'message' => $successMessage,
            ]);
        }

        return redirect()->route($context['routePrefix'].'.list')->with('success', $successMessage);
    }

    /**
     * Soft delete the specified user.
     */
    public function destroy(string $id)
    {
        $user = User::findOrFail($id);
        $context = $this->resolveContext(request(), $user);
        $this->authorizeContext(request(), $context['type'], $user);

        if ($user->id === auth()->id()) {
            return redirect()->route($context['routePrefix'].'.list')
                ->with('error', 'Bạn không thể xóa tài khoản của chính mình.');
        }

        if ($context['type'] === 'staff') {
            return redirect()->route($context['routePrefix'].'.list')
                ->with('error', 'Không thể xóa tài khoản nhân sự. Hãy chuyển trạng thái sang Ngưng hoạt động.');
        }

        if ($user->is_protected) {
            return redirect()->route($context['routePrefix'].'.list')
                ->with('error', 'Không thể xóa admin hệ thống.');
        }

        $user->delete();

        return redirect()->route($context['routePrefix'].'.list')->with('success', 'Xóa '.$context['itemLabelLower'].' thành công');
    }

    /**
     * Display soft deleted users.
     */
    public function trash(Request $request)
    {
        $context = $this->resolveContext($request);
        $this->authorizeContext($request, $context['type']);

        if ($context['type'] === 'staff') {
            return redirect()->route($context['routePrefix'].'.list')
                ->with('error', 'Chức năng thùng rác không áp dụng cho khu vực này.');
        }

        $perPage = $this->resolvePerPage($request);
        $keyword = trim($request->input('keyword', ''));

        $query = User::onlyTrashed()->with('roles')->orderBy('deleted_at', 'desc');
        $this->applyTypeFilter($query, $context['type']);

        if ($keyword !== '') {
            $query->where(function ($q) use ($keyword) {
                $q->where('username', 'like', "%{$keyword}%")
                  ->orWhere('email', 'like', "%{$keyword}%");
            });
        }

        $data = $query->paginate($perPage)->appends($request->except('page'));

        return view('admin.users.trash', [
            'data'    => $data,
            'keyword' => $keyword,
            ...$context,
        ]);
    }

    /**
     * Bulk soft-delete selected users of the resolved type.
     */
    public function bulkDelete(Request $request)
    {
        $context = $this->resolveContext($request);
        $this->authorizeContext($request, $context['type']);

        if (in_array($context['type'], ['staff', 'customer'], true)) {
            return back()->with('error', 'Không thể xóa hàng loạt tài khoản trong khu vực này.');
        }

        $ids = array_filter((array) $request->input('ids', []), 'is_numeric');

        // Never delete the currently logged-in account
        $ids = array_values(array_filter($ids, fn ($id) => (int) $id !== auth()->id()));

        if (empty($ids)) {
            return back()->with('error', 'Vui lòng chọn ít nhất một ' . $context['itemLabelLower'] . ' để xóa.');
        }

        $query = User::whereIn('id', $ids)->where('is_protected', false);
        $this->applyTypeFilter($query, $context['type']);
        $deleted = $query->delete();

        return back()->with('success', "Đã xóa {$deleted} {$context['itemLabelLower']} thành công.");
    }

    /**
     * Bulk update selected customer account statuses.
     */
    public function bulkUpdateStatus(Request $request)
    {
        $context = $this->resolveContext($request);
        $this->authorizeContext($request, $context['type']);

        if ($context['type'] !== 'customer') {
            abort(404);
        }

        $validated = $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['integer', 'exists:users,id'],
            'is_active' => ['required', 'boolean'],
        ], [
            'ids.required' => 'Vui lòng chọn ít nhất một khách hàng.',
            'ids.array' => 'Danh sách khách hàng không hợp lệ.',
            'ids.*.exists' => 'Khách hàng được chọn không hợp lệ.',
            'is_active.required' => 'Vui lòng chọn trạng thái cần cập nhật.',
            'is_active.boolean' => 'Trạng thái không hợp lệ.',
        ]);

        $updated = User::query()
            ->whereIn('id', $validated['ids'])
            ->whereHas('roles', fn ($roleQuery) => $roleQuery->where('name', UserRole::CUSTOMER->value))
            ->update([
                'is_active' => (bool) $validated['is_active'],
                'lock_reason' => (bool) $validated['is_active'] ? null : 'Cập nhật trạng thái hàng loạt',
            ]);

        return back()->with('success', "Đã cập nhật trạng thái {$updated} khách hàng.");
    }

    /**
     * Restore a soft deleted user.
     */
    public function restore(string $id)
    {
        $user = User::onlyTrashed()->findOrFail($id);
        $context = $this->resolveContext(request(), $user);
        $this->authorizeContext(request(), $context['type'], $user);
        if ($context['type'] === 'staff') {
            return redirect()->route($context['routePrefix'].'.list')
                ->with('error', 'Chức năng thùng rác không áp dụng cho khu vực này.');
        }
        $user->restore();

        return redirect()->route($context['routePrefix'].'.trash')->with('success', 'Khôi phục '.$context['itemLabelLower'].' thành công');
    }

    /**
     * Permanently delete a soft deleted user.
     */
    public function forceDelete(string $id)
    {
        $user = User::onlyTrashed()->findOrFail($id);
        $context = $this->resolveContext(request(), $user);
        $this->authorizeContext(request(), $context['type'], $user);
        if ($context['type'] === 'staff' || $user->is_protected) {
            return redirect()->route($context['routePrefix'].'.list')
                ->with('error', 'Không thể xóa vĩnh viễn tài khoản này.');
        }

        // Đơn hàng (orders.user_id) là FK RESTRICT — xóa cứng khách còn đơn sẽ lỗi CSDL.
        // Giữ tài khoản trong thùng rác để bảo toàn lịch sử đơn hàng.
        if ($user->orders()->exists()) {
            return redirect()->route($context['routePrefix'].'.trash')
                ->with('error', 'Không thể xóa vĩnh viễn khách hàng này vì vẫn còn đơn hàng liên kết. Tài khoản được giữ trong thùng rác.');
        }

        $user->forceDelete();

        return redirect()->route($context['routePrefix'].'.trash')->with('success', 'Xóa vĩnh viễn '.$context['itemLabelLower'].' thành công');
    }

    /**
     * Ghi một bản ghi nhật ký "Đổi vai trò" cho tài khoản.
     *
     * Vai trò lưu ở bảng pivot của Spatie nên laravel-auditing không tự ghi,
     * vì vậy phải phát AuditCustom thủ công với nhãn tiếng Việt.
     */
    private function recordRoleChangeAudit(User $user, ?string $oldRole, string $newRole): void
    {
        $labels = UserRole::labels();

        $user->auditEvent = 'role_updated';
        $user->isCustomEvent = true;
        $user->auditCustomOld = ['role' => $oldRole ? ($labels[$oldRole] ?? $oldRole) : 'Chưa có vai trò'];
        $user->auditCustomNew = ['role' => $labels[$newRole] ?? $newRole];

        Event::dispatch(new AuditCustom($user));
    }

    /**
     * Ghi một bản ghi nhật ký "Reset mật khẩu" riêng biệt khỏi event "updated"
     * mặc định — giúp phân biệt rõ trong audit trail và luôn được bảo vệ
     * khỏi bulk-delete/prune (xem ActivityLogController::isProtectedAudit()).
     */
    private function recordPasswordResetAudit(User $user): void
    {
        $user->auditEvent = 'password_reset';
        $user->isCustomEvent = true;
        $user->auditCustomOld = [];
        $user->auditCustomNew = ['password' => 'Đã được quản trị viên đặt lại'];

        Event::dispatch(new AuditCustom($user));
    }

    /**
     * Thu hồi quyền truy cập của user vừa bị khóa: xóa mọi session đang sống (driver=database)
     * để user bị đăng xuất ngay ở request kế tiếp, không phải đợi hết hạn session.
     * Kết hợp với middleware EnsureActiveAccount (chặn is_active=false ở mọi route cần auth).
     */
    private function revokeUserAccess(User $user): void
    {
        if (Schema::hasTable('sessions')) {
            DB::table('sessions')->where('user_id', $user->id)->delete();
        }

        // Khách hàng đăng nhập storefront bằng OAuth token (Passport) chứ không
        // chỉ qua session — phải thu hồi token để thực sự đăng xuất họ ngay.
        if (Schema::hasTable('oauth_access_tokens')) {
            DB::table('oauth_access_tokens')->where('user_id', $user->id)->update(['revoked' => true]);
        }
    }

    private function validationErrorResponse(Request $request, string $field, string $message)
    {
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'errors' => [$field => [$message]],
            ], 422);
        }

        return back()->withErrors([$field => $message])->withInput();
    }

    private function resolveContext(Request $request, ?User $user = null): array
    {
        $type = $request->route('account_type') ?: $request->query('type');

        // Route defaults không phải URI segment nên đôi khi không được bind đúng.
        // Fallback: đọc từ route name — đây là nguồn đáng tin cậy nhất.
        if (! $type) {
            $routeName = $request->route()?->getName() ?? '';
            if (str_starts_with($routeName, 'admin.staff.')) {
                $type = 'staff';
            } elseif (str_starts_with($routeName, 'admin.customers.')) {
                $type = 'customer';
            }
        }

        if (! $type && $user?->hasRole(UserRole::STAFF->value)) {
            $type = 'staff';
        }

        if (! $type && $user?->hasRole(UserRole::CUSTOMER->value)) {
            $type = 'customer';
        }

        $type = in_array($type, ['staff', 'customer', 'all'], true) ? $type : 'all';

        return match ($type) {
            'staff' => [
                'type' => 'staff',
                'pageTitle' => 'Quản lý nhân sự',
                'pageDescription' => 'Quản trị viên có thể thêm, sửa hoặc xóa tài khoản nhân viên.',
                'sectionTitle' => 'Quản lý tài khoản nhân viên',
                'listTitle' => 'Danh sách nhân sự',
                'itemLabel' => 'Quản trị viên',
                'itemLabelLower' => 'quản trị viên',
                'createLabel' => 'Thêm quản trị viên',
                'routePrefix' => 'admin.staff',
            ],
            'customer' => [
                'type' => 'customer',
                'pageTitle' => 'Quản lý khách hàng',
                'pageDescription' => 'Nhân viên và quản trị viên có thể thêm, sửa hoặc xóa tài khoản khách hàng.',
                'sectionTitle' => 'Quản lý tài khoản khách hàng',
                'listTitle' => 'Danh sách khách hàng',
                'itemLabel' => 'Khách hàng',
                'itemLabelLower' => 'khách hàng',
                'createLabel' => 'Thêm khách hàng',
                'routePrefix' => 'admin.customers',
            ],
            default => [
                'type' => 'all',
                'pageTitle' => 'Quản lý tài khoản',
                'pageDescription' => 'Danh sách tổng hợp tài khoản nhân sự và khách hàng.',
                'sectionTitle' => 'Quản lý tài khoản',
                'listTitle' => 'Danh sách tài khoản',
                'itemLabel' => 'Tài khoản',
                'itemLabelLower' => 'tài khoản',
                'createLabel' => 'Thêm tài khoản',
                'routePrefix' => 'admin.users',
            ],
        };
    }

    private function resolvePerPage(Request $request): int
    {
        $perPage = (int) $request->input('per_page', $request->input('perPage', 10));
        $allowedPerPages = [10, 25, 50, 100];

        return in_array($perPage, $allowedPerPages, true) ? $perPage : 10;
    }

    private function authorizeContext(Request $request, string $type, ?User $targetUser = null): void
    {
        $currentUser = $request->user();

        if (! $currentUser) {
            abort(401);
        }

        if ($type === 'customer') {
            if (! $currentUser->isAdmin() && ! $currentUser->isStaff()) {
                abort(403);
            }

            if ($targetUser && ! $targetUser->isCustomer()) {
                abort(403);
            }

            return;
        }

        if (! $currentUser->isAdmin()) {
            abort(403);
        }

        if ($type === 'staff' && $targetUser && ! $targetUser->isStaff() && ! $targetUser->isAdmin()) {
            abort(403);
        }
    }

    private function applyTypeFilter($query, string $type): void
    {
        if ($type === 'staff') {
            $query->whereHas('roles', fn ($roleQuery) => $roleQuery->whereIn('name', [
                UserRole::ADMIN->value,
                UserRole::STAFF->value,
            ]));
        }

        if ($type === 'customer') {
            $query->whereHas('roles', fn ($roleQuery) => $roleQuery->where('name', UserRole::CUSTOMER->value));
        }
    }

    private function rolesForContext(string $type)
    {
        if ($type === 'staff') {
            return Role::whereIn('name', [
                UserRole::STAFF->value,
                UserRole::ADMIN->value,
            ])->get();
        }

        if ($type === 'customer') {
            return Role::where('name', UserRole::CUSTOMER->value)->get();
        }

        return Role::orderBy('name')->get();
    }

    private function roleForContext(string $type, ?int $fallbackRoleId, ?User $user = null): ?Role
    {
        if ($type === 'staff') {
            if ($fallbackRoleId) {
                $role = Role::whereIn('name', [
                    UserRole::STAFF->value,
                    UserRole::ADMIN->value,
                ])->find($fallbackRoleId);

                if ($role) {
                    return $role;
                }
            }

            return Role::firstOrCreate(
                ['name' => UserRole::STAFF->value, 'guard_name' => 'web']
            );
        }

        $roleName = match ($type) {
            'customer' => UserRole::CUSTOMER->value,
            default => null,
        };

        if ($roleName) {
            return Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
        }

        return $fallbackRoleId ? Role::find($fallbackRoleId) : null;
    }

    private function hasAddressInput(array $validated): bool
    {
        return collect(['city', 'ward', 'apartment_number'])
            ->contains(fn (string $field) => filled($validated[$field] ?? null));
    }

    private function userModalPayload(User $user): array
    {
        $address = $user->addresses->first();
        $role = $user->roles->first();

        return [
            'id' => $user->id,
            'username' => $user->username,
            'avatar_url' => $user->avatar_display_url,
            'email' => $user->email,
            'phone_number' => $user->phone_number,
            'role_id' => $role?->id,
            'role_name' => $role?->name,
            'is_active' => (bool) $user->is_active,
            'is_protected' => (bool) $user->is_protected,
            'status_label' => $user->is_active ? 'Hoạt động' : 'Ngưng hoạt động',
            'lock_reason' => $user->lock_reason,
            'city' => $address?->city,
            'ward' => $address?->ward,
            'apartment_number' => $address?->apartment_number,
            'created_at' => optional($user->created_at)->format('d/m/Y H:i'),
            'updated_at' => optional($user->updated_at)->format('d/m/Y H:i'),
        ];
    }

    private function validationMessages(): array
    {
        return [
            'username.required' => 'Vui lòng nhập họ và tên',
            'username.max' => 'Họ và tên không được vượt quá 255 ký tự',
            'email.required' => 'Vui lòng nhập email',
            'email.email' => 'Email không đúng định dạng',
            'email.unique' => 'Email đã tồn tại',
            'phone_number.max' => 'Số điện thoại không được vượt quá 20 ký tự',
            'role_id.required' => 'Vui lòng chọn vai trò',
            'role_id.exists' => 'Vai trò không hợp lệ',
            'is_active.required' => 'Vui lòng chọn trạng thái',
            'is_active.boolean' => 'Trạng thái không hợp lệ',
            'password.required' => 'Vui lòng nhập mật khẩu',
            'password.min' => 'Mật khẩu phải có ít nhất 6 ký tự',
            'password.confirmed' => 'Mật khẩu xác nhận không khớp',
            'gender.in' => 'Giới tính không hợp lệ',
            'avatar.image' => 'File tải lên phải là một hình ảnh',
            'avatar.mimes' => 'Ảnh đại diện chỉ chấp nhận: jpeg, png, jpg, webp',
            'avatar.max' => 'Dung lượng ảnh đại diện không được vượt quá 2MB',
            'city.max' => 'Tỉnh, thành phố không được vượt quá 255 ký tự',
           
            'ward.max' => 'Phường, xã không được vượt quá 255 ký tự',
            'apartment_number.max' => 'Số nhà không được vượt quá 255 ký tự',
            'lock_reason.max' => 'Lý do ngưng hoạt động không được vượt quá 255 ký tự',
        ];
    }
}