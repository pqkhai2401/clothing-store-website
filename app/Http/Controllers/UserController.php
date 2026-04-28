<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $sortBy = $request->input('sort_by', 'id');
        $sortDir = $request->input('sort_dir', 'desc');

        $query = User::query();

        $allowedDirs = ['asc', 'desc'];

        if (in_array($sortDir, $allowedDirs, true) && ! empty($sortBy)) {
            switch ($sortBy) {
                case 'id':
                    $query->orderBy('id', $sortDir);
                    break;
                case 'name':
                    $query->orderBy('name', $sortDir);
                    break;
                case 'user_name':
                    $query->orderBy('user_name', $sortDir);
                    break;
                default:
                    $query->orderBy('id', 'asc');
                    break;
            }
        } else {
            // Default order when no sort is selected.
            $query->orderBy('id', 'desc');
        }

        $data = $query->paginate($perPage);
        return view('users.show', compact('data'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('users.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $ROLES = UserRole::values();

        $validated = $request->validate([
            'name' => 'required|string|min:2|max:255',
            'user_name' => 'required|string|min:3|max:50|unique:users,user_name|regex:/^[a-zA-Z0-9_]+$/',
            'password' => 'required|string|min:6|max:255|confirmed',
            'role' => 'required|in:'.implode(',', $ROLES),
        ], [
            'name.required' => 'Vui lòng nhập họ và tên',
            'name.min' => 'Họ và tên phải có ít nhất 2 ký tự',
            'user_name.required' => 'Vui lòng nhập tên đăng nhập',
            'user_name.min' => 'Tên đăng nhập phải có ít nhất 3 ký tự',
            'user_name.unique' => 'Tên đăng nhập đã tồn tại',
            'user_name.regex' => 'Tên đăng nhập chỉ chứa chữ cái, số và dấu gạch dưới',
            'password.required' => 'Vui lòng nhập mật khẩu',
            'password.min' => 'Mật khẩu phải có ít nhất 6 ký tự',
            'password.confirmed' => 'Mật khẩu xác nhận không khớp',
            'role.required' => 'Vui lòng chọn vai trò',
            'role.in' => 'Vai trò không hợp lệ',
        ]);

        User::create([
            'name' => $validated['name'],
            'user_name' => $validated['user_name'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
        ]);

        return redirect()->route('admin.users.list')->with('success', 'Tạo người dùng thành công');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $user = User::findOrFail($id);
        return view('users.edit', compact('user'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $user = User::findOrFail($id);
        $ROLES = UserRole::values();

        $validated = $request->validate([
            'name' => 'required|string|min:2|max:255',
            'user_name' => 'required|string|min:3|max:50|unique:users,user_name,'.$id.'|regex:/^[a-zA-Z0-9_]+$/',
            'password' => 'nullable|string|min:6|max:255|confirmed',
            'role' => 'required|in:'.implode(',', $ROLES),
        ], [
            'name.required' => 'Vui lòng nhập họ và tên',
            'name.min' => 'Họ và tên phải có ít nhất 2 ký tự',
            'user_name.required' => 'Vui lòng nhập tên đăng nhập',
            'user_name.min' => 'Tên đăng nhập phải có ít nhất 3 ký tự',
            'user_name.unique' => 'Tên đăng nhập đã tồn tại',
            'user_name.regex' => 'Tên đăng nhập chỉ chứa chữ cái, số và dấu gạch dưới',
            'password.min' => 'Mật khẩu phải có ít nhất 6 ký tự',
            'password.confirmed' => 'Mật khẩu xác nhận không khớp',
            'role.required' => 'Vui lòng chọn vai trò',
            'role.in' => 'Vai trò không hợp lệ',
        ]);

        $updateData = [
            'name' => $validated['name'],
            'user_name' => $validated['user_name'],
            'role' => $validated['role'],
        ];

        // Only update password if provided
        if (! empty($validated['password'])) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        $user->update($updateData);

        return redirect()->route('admin.users.list')->with('success', 'Cập nhật người dùng thành công');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return redirect()->route('admin.users.list')->with('success', 'Xóa người dùng thành công');
    }
}
