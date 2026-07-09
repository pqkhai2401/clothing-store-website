<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ProfileController extends Controller
{
    public function index()
    {
        return view('user.profile.index', ['user' => Auth::user()]);
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'username' => ['required', 'string', 'min:2', 'max:255', "regex:/^[\p{L}\s.'-]+$/u"],
            'phone_number' => ['required', 'string', 'regex:/^(0|\+84)(3|5|7|8|9)[0-9]{8}$/'],
            'gender' => ['required', 'in:nam,nu,khac'],
            // File ảnh đại diện là tùy chọn, chỉ validate khi người dùng có upload file mới
            'avatar' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
        ], [
            'username.required' => 'Vui lòng nhập họ và tên.',
            'username.min' => 'Họ và tên phải có ít nhất 2 ký tự.',
            'username.max' => 'Họ và tên không được vượt quá 255 ký tự.',
            'username.regex' => 'Họ và tên không hợp lệ.',
            'phone_number.required' => 'Vui lòng nhập số điện thoại.',
            'phone_number.regex' => 'Số điện thoại không hợp lệ.',
            'gender.required' => 'Vui lòng chọn giới tính.',
            'gender.in' => 'Giới tính không hợp lệ.',
            'avatar.image' => 'File tải lên phải là một hình ảnh.',
            'avatar.mimes' => 'Ảnh đại diện chỉ chấp nhận định dạng: jpeg, png, jpg, gif.',
            'avatar.max' => 'Dung lượng ảnh đại diện không được vượt quá 2MB.',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $phoneNumber = $this->normalizeVietnamesePhone($request->input('phone_number'));

        $existingPhone = User::where('phone_number', $phoneNumber)
            ->where('id', '!=', $user->id)
            ->exists();

        if ($existingPhone) {
            return back()->withErrors(['phone_number' => 'Số điện thoại đã được sử dụng.'])->withInput();
        }

        $updateData = [
            'username' => trim($request->input('username')),
            'phone_number' => $phoneNumber,
            'gender' => $request->input('gender'),
        ];

        // Chỉ xử lý ảnh đại diện khi người dùng thực sự có chọn file mới để upload
        if ($request->hasFile('avatar')) {
            // Nếu tài khoản đã có ảnh đại diện cũ, xóa hẳn file cũ khỏi ổ cứng
            // trước khi lưu ảnh mới để tránh rác file tồn đọng trong storage
            if ($user->avatar_url) {
                Storage::disk('public')->delete($user->avatar_url);
            }

            // Lưu file ảnh mới vào thư mục "avatars" trong disk "public"
            // store() sẽ tự sinh tên file duy nhất và trả về đường dẫn tương đối
            $avatarPath = $request->file('avatar')->store('avatars', 'public');

            $updateData['avatar_url'] = $avatarPath;
        }

        $user->update($updateData);

        return back()->with('success', 'Cập nhật thông tin thành công!');
    }

    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => ['required', 'string'],
            'new_password' => ['required', 'string', 'min:8', 'max:64'],
            'new_password_confirmation' => ['required', 'same:new_password'],
        ], [
            'current_password.required' => 'Vui lòng nhập mật khẩu hiện tại.',
            'new_password.required' => 'Vui lòng nhập mật khẩu mới.',
            'new_password.min' => 'Mật khẩu mới phải có ít nhất 8 ký tự.',
            'new_password.max' => 'Mật khẩu mới không được vượt quá 64 ký tự.',
            'new_password_confirmation.required' => 'Vui lòng xác nhận mật khẩu mới.',
            'new_password_confirmation.same' => 'Xác nhận mật khẩu không khớp.',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $user = Auth::user();

        if (!Hash::check($request->input('current_password'), $user->password)) {
            return back()->withErrors(['current_password' => 'Mật khẩu hiện tại không chính xác.']);
        }

        $user->update([
            'password' => Hash::make($request->input('new_password')),
        ]);

        return back()->with('success', 'Đổi mật khẩu thành công!');
    }

    private function normalizeVietnamesePhone(string $phoneNumber): string
    {
        $phoneNumber = preg_replace('/[\s.\-]+/', '', trim($phoneNumber));

        if (str_starts_with($phoneNumber, '+84')) {
            return '0' . substr($phoneNumber, 3);
        }

        return $phoneNumber;
    }
}