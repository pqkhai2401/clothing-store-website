<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Display a listing of users.
     */
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $sortBy = $request->input('sort_by', 'id');
        $sortDir = $request->input('sort_dir', 'desc');

        $query = User::with('role');

        if (in_array($sortDir, ['asc', 'desc'], true)) {
            match ($sortBy) {
                'id' => $query->orderBy('id', $sortDir),
                'username' => $query->orderBy('username', $sortDir),
                'email' => $query->orderBy('email', $sortDir),
                default => $query->orderBy('id', 'desc'),
            };
        } else {
            $query->orderBy('id', 'desc');
        }

        $data = $query->paginate($perPage);

        return view('admin.users.show', compact('data'));
    }

    /**
     * Show the form for creating a new user.
     */
    public function create()
    {
        $roles = Role::orderBy('name')->get();

        return view('admin.users.create', compact('roles'));
    }

    /**
     * Store a newly created user.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'username' => ['required', 'string', 'min:3', 'max:50', 'regex:/^[a-zA-Z0-9_]+$/', 'unique:users,username'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'phone_number' => ['nullable', 'string', 'max:20'],
            'role_id' => ['required', 'integer', 'exists:roles,id'],
            'is_active' => ['required', 'boolean'],
            'password' => ['required', 'string', 'min:6', 'max:255', 'confirmed'],
        ], $this->validationMessages());

        User::create([
            'username' => $validated['username'],
            'email' => $validated['email'],
            'phone_number' => $validated['phone_number'] ?? null,
            'role_id' => $validated['role_id'],
            'is_active' => (bool) $validated['is_active'],
            'password' => Hash::make($validated['password']),
        ]);

        return redirect()->route('admin.users.list')->with('success', 'Tạo người dùng thành công');
    }

    /**
     * Display the specified user.
     */
    public function show(string $id)
    {
        $user = User::with('role')->findOrFail($id);

        return view('admin.users.detail', compact('user'));
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(string $id)
    {
        $user = User::with('role')->findOrFail($id);
        $roles = Role::orderBy('name')->get();

        return view('admin.users.edit', compact('user', 'roles'));
    }

    /**
     * Update the specified user.
     */
    public function update(Request $request, string $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'username' => [
                'required',
                'string',
                'min:3',
                'max:50',
                'regex:/^[a-zA-Z0-9_]+$/',
                Rule::unique('users', 'username')->ignore($user->id),
            ],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'phone_number' => ['nullable', 'string', 'max:20'],
            'role_id' => ['required', 'integer', 'exists:roles,id'],
            'is_active' => ['required', 'boolean'],
            'password' => ['nullable', 'string', 'min:6', 'max:255', 'confirmed'],
        ], $this->validationMessages());

        $updateData = [
            'username' => $validated['username'],
            'email' => $validated['email'],
            'phone_number' => $validated['phone_number'] ?? null,
            'role_id' => $validated['role_id'],
            'is_active' => (bool) $validated['is_active'],
        ];

        if (! empty($validated['password'])) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        $user->update($updateData);

        return redirect()->route('admin.users.list')->with('success', 'Cập nhật người dùng thành công');
    }

    /**
     * Soft delete the specified user.
     */
    public function destroy(string $id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return redirect()->route('admin.users.list')->with('success', 'Xóa người dùng thành công');
    }

    /**
     * Display soft deleted users.
     */
    public function trash(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $data = User::onlyTrashed()->with('role')->orderBy('deleted_at', 'desc')->paginate($perPage);

        if ($request->ajax()) {
            return view('admin.users.trash_list', compact('data'));
        }

        return view('admin.users.trash_show', compact('data'));
    }

    /**
     * Restore a soft deleted user.
     */
    public function restore(string $id)
    {
        $user = User::onlyTrashed()->findOrFail($id);
        $user->restore();

        return redirect()->route('admin.users.trash')->with('success', 'Khôi phục người dùng thành công');
    }

    /**
     * Permanently delete a soft deleted user.
     */
    public function forceDelete(string $id)
    {
        $user = User::onlyTrashed()->findOrFail($id);
        $user->forceDelete();

        return redirect()->route('admin.users.trash')->with('success', 'Xóa vĩnh viễn người dùng thành công');
    }

    private function validationMessages(): array
    {
        return [
            'username.required' => 'Vui lòng nhập username',
            'username.min' => 'Username phải có ít nhất 3 ký tự',
            'username.unique' => 'Username đã tồn tại',
            'username.regex' => 'Username chỉ chứa chữ cái, số và dấu gạch dưới',
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
        ];
    }
}
