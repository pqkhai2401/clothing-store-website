<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    /**
     * Display a listing of users.
     */
    public function index(Request $request)
    {
        $context = $this->resolveContext($request);
        $this->authorizeContext($request, $context['type']);

        $perPage = $this->resolvePerPage($request);
        $sortBy = $request->input('sort_by', 'id');
        $sortDir = $request->input('sort_dir', 'asc');

        $query = User::with('roles');
        $this->applyTypeFilter($query, $context['type']);

        if ($keyword = trim((string) $request->input('keyword'))) {
            $query->where(function ($subQuery) use ($keyword) {
                $subQuery->where('username', 'like', "%{$keyword}%")
                    ->orWhere('email', 'like', "%{$keyword}%")
                    ->orWhere('phone_number', 'like', "%{$keyword}%");
            });
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

        $data = $query->paginate($perPage)->appends($request->except('page'));

        return view('admin.users.show', [
            'data' => $data,
            'roles' => $this->rolesForContext($context['type']),
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
        $roles = $this->rolesForContext($context['type']);

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
            'password' => ['required', 'string', 'min:6', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'ward' => ['nullable', 'string', 'max:255'],
            'apartment_number' => ['nullable', 'string', 'max:255'],
            'lock_reason' => ['nullable', 'string', 'max:255'],
        ], $this->validationMessages());

        $user = User::create([
            'username' => $validated['username'],
            'email' => $validated['email'],
            'phone_number' => $validated['phone_number'] ?? null,
            'is_active' => (bool) $validated['is_active'],
            'password' => Hash::make($validated['password']),
        ]);

        $roleForUser = $this->roleForContext($context['type'], $validated['role_id'] ?? null);
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
            return response()->json([
                'user' => $this->userModalPayload($user),
                'roles' => $this->rolesForContext($context['type'])->map(fn ($role) => [
                    'id' => $role->id,
                    'name' => $role->name,
                ])->values(),
                'show_role' => ($context['type'] !== 'customer'),
            ]);
        }

        return redirect()->route($context['routePrefix'].'.list');
    }

    /**
     * Update the specified user.
     */
    public function update(Request $request, string $id)
    {
        $user = User::findOrFail($id);
        $context = $this->resolveContext($request, $user);
        $this->authorizeContext($request, $context['type'], $user);

        $passwordRules = ['nullable', 'string', 'min:6', 'max:255', 'confirmed'];

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
            'password' => $passwordRules,
            'city' => ['nullable', 'string', 'max:255'],
            'ward' => ['nullable', 'string', 'max:255'],
            'apartment_number' => ['nullable', 'string', 'max:255'],
            'lock_reason' => ['nullable', 'string', 'max:255'],
        ], $this->validationMessages());

        $updateData = [
            'username' => $validated['username'],
            'email' => $validated['email'],
            'phone_number' => $validated['phone_number'] ?? null,
            'is_active' => (bool) $validated['is_active'],
            'lock_reason' => (bool) $validated['is_active'] ? null : ($validated['lock_reason'] ?? null),
        ];

        if (! empty($validated['password'])) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        $user->update($updateData);

        $roleForUser = $this->roleForContext($context['type'], $validated['role_id'] ?? null, $user);
        if ($roleForUser) {
            $user->syncRoles([$roleForUser->name]);
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
     * Soft delete the specified user.
     */
    public function destroy(string $id)
    {
        $user = User::findOrFail($id);
        $context = $this->resolveContext(request(), $user);
        $this->authorizeContext(request(), $context['type'], $user);
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

        $ids = array_filter((array) $request->input('ids', []), 'is_numeric');

        if (empty($ids)) {
            return back()->with('error', 'Vui lòng chọn ít nhất một ' . $context['itemLabelLower'] . ' để xóa.');
        }

        $query = User::whereIn('id', $ids);
        $this->applyTypeFilter($query, $context['type']);
        $deleted = $query->delete();

        return back()->with('success', "Đã xóa {$deleted} {$context['itemLabelLower']} thành công.");
    }

    /**
     * Restore a soft deleted user.
     */
    public function restore(string $id)
    {
        $user = User::onlyTrashed()->findOrFail($id);
        $context = $this->resolveContext(request(), $user);
        $this->authorizeContext(request(), $context['type'], $user);
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
        $user->forceDelete();

        return redirect()->route($context['routePrefix'].'.trash')->with('success', 'Xóa vĩnh viễn '.$context['itemLabelLower'].' thành công');
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
        if ($type === 'staff' && $user?->isAdmin()) {
            return $user->roles()->first();
        }

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
            'email' => $user->email,
            'phone_number' => $user->phone_number,
            'role_id' => $role?->id,
            'role_name' => $role?->name,
            'is_active' => (bool) $user->is_active,
            'status_label' => $user->is_active ? 'Đang hoạt động' : 'Ngưng hoạt động',
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
            'city.max' => 'Tỉnh, thành phố không được vượt quá 255 ký tự',
           
            'ward.max' => 'Phường, xã không được vượt quá 255 ký tự',
            'apartment_number.max' => 'Số nhà không được vượt quá 255 ký tự',
            'lock_reason.max' => 'Lý do ngưng hoạt động không được vượt quá 255 ký tự',
        ];
    }
}
