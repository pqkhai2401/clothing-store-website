# CLOTHING STORE WEBSITE

Website bán hàng thời trang trực tuyến xây dựng bằng Laravel. Hệ thống bao gồm giao diện người dùng (Blade + Tailwind CSS), trang quản trị nội bộ (Admin Panel) và REST API.

---

## Công nghệ sử dụng

| Thành phần | Chi tiết |
|---|---|
| **PHP** | >= 8.2 |
| **Laravel** | 13.x |
| **Database** | MySQL |
| **Authentication** | Laravel Passport 13.x (API), Session (web) |
| **OAuth** | Laravel Socialite 5.x (Google) |
| **Role & Permission** | Spatie Laravel Permission 8.x |
| **Audit Log** | owen-it/laravel-auditing 14.x |
| **Excel Export/Import** | maatwebsite/excel 3.1 |
| **API Docs** | darkaonline/l5-swagger 11.x |
| **Frontend** | Blade Templates, Tailwind CSS, Vite |

---

## Cấu trúc dự án

```
clothing-store-website/
├── app/
│   ├── Enums/                    # Gender, OrderStatus, PaymentStatus, UserRole
│   ├── Helpers/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Admin/            # BrandController, CategoryController, ColorController,
│   │   │   │                     # DashboardController, OrderController, ProductController,
│   │   │   │                     # ReviewController, SizeController, UserController
│   │   │   ├── Api/              # ImageController
│   │   │   └── Web/              # AuthController
│   │   ├── Middleware/
│   │   └── Requests/
│   ├── Models/                   # Xem danh sách models bên dưới
│   ├── Providers/
│   └── Repositories/
│
├── database/
│   ├── migrations/
│   └── seeders/
│
├── resources/
│   ├── views/
│   │   ├── admin/                # Giao diện quản trị (Blade)
│   │   ├── auth/                 # Đăng nhập, đăng ký, quên mật khẩu
│   │   ├── user/                 # Giao diện người dùng (Blade)
│   │   ├── layouts/
│   │   └── components/
│   └── css/js/                   # Vite assets
│
└── routes/
    ├── web.php                   # Web routes (user + auth)
    ├── admin.php                 # Admin panel routes
    └── api.php                   # REST API routes
```

---

## Models (Database Schema)

| Model | Mô tả |
|---|---|
| `User` | Tài khoản người dùng (customer / staff / admin) |
| `Address` | Địa chỉ giao hàng của người dùng |
| `Category` | Danh mục sản phẩm (hỗ trợ danh mục cha - con) |
| `Brand` | Thương hiệu sản phẩm |
| `Product` | Sản phẩm (tên, mô tả, giá, slug, giới tính...) |
| `ProductImage` | Ảnh gallery của sản phẩm |
| `ProductVariant` | Biến thể sản phẩm (màu + size + số lượng) |
| `Color` | Màu sắc |
| `Size` | Kích thước |
| `Tag` | Nhãn sản phẩm |
| `Wishlist` | Danh sách yêu thích |
| `Review` | Đánh giá sản phẩm |
| `Cart` | Giỏ hàng |
| `CartItem` | Chi tiết giỏ hàng |
| `PaymentMethod` | Phương thức thanh toán |
| `Order` | Đơn hàng |
| `OrderItem` | Chi tiết đơn hàng |
| `ProductView` | Lịch sử xem sản phẩm |
| `SearchHistory` | Lịch sử tìm kiếm |

---

## Tính năng chính

### Người dùng (User)
- Trang chủ hiển thị sản phẩm nổi bật
- Danh sách & tìm kiếm sản phẩm theo danh mục
- Trang chi tiết sản phẩm (gallery, biến thể màu/size, đánh giá)
- Giỏ hàng và thanh toán
- Đăng ký / đăng nhập (email + mật khẩu, hoặc Google OAuth)
- Quên mật khẩu

### Quản trị (Admin Panel)
| Module | Chức năng |
|---|---|
| Dashboard | Thống kê tổng quan |
| Sản phẩm | Danh sách, chỉnh sửa, xoá mềm, thùng rác |
| Danh mục | Danh sách, chỉnh sửa, xoá mềm (chặn nếu có sản phẩm đang bán) |
| Thương hiệu | Danh sách, chỉnh sửa, xoá mềm |
| Màu sắc | Danh sách, chỉnh sửa, xoá mềm |
| Kích thước | Danh sách, chỉnh sửa, xoá mềm |
| Đơn hàng | Danh sách, chi tiết, cập nhật trạng thái / thanh toán |
| Đánh giá | Danh sách, kiểm duyệt |
| Khách hàng | Danh sách, tạo, xem chi tiết, xoá mềm |
| Nhân viên | Danh sách, tạo, phân quyền (yêu cầu permission `manage-staff`) |

### Hệ thống phân quyền
- Role: `admin`, `staff`, `customer`
- Permission: `manage-staff` (kiểm soát truy cập module nhân viên)
- Middleware `auth.login` + `admin` bảo vệ toàn bộ khu vực admin

---

## Cài đặt và chạy dự án

### Yêu cầu
- PHP >= 8.2 + Composer >= 2.0
- Node.js >= 18 + npm
- MySQL >= 8.0

---

### Bước 1: Clone và cài đặt dependencies

```bash
git clone <repository-url>
cd clothing-store-website

# Cài đặt PHP dependencies
composer install

# Cài đặt Node dependencies
npm install
```

### Bước 2: Cấu hình môi trường

```bash
cp .env.example .env
php artisan key:generate
```

Mở `.env` và cập nhật:

```env
APP_URL=http://localhost:8000

DB_DATABASE=clothing_store
DB_USERNAME=root
DB_PASSWORD=

# OAuth Google (tuỳ chọn)
GOOGLE_CLIENT_ID=
GOOGLE_CLIENT_SECRET=
GOOGLE_REDIRECT_URI=http://localhost:8000/auth/google/callback

# Passport (điền sau khi chạy passport:install)
PASSPORT_CLIENT_ID=
PASSPORT_CLIENT_SECRET=
```

### Bước 3: Tạo database và migrate

```sql
CREATE DATABASE clothing_store CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

```bash
php artisan migrate --seed
```

### Bước 4: Cài đặt Passport

```bash
php artisan passport:install
```

Sau đó điền `PASSPORT_CLIENT_ID` và `PASSPORT_CLIENT_SECRET` vào `.env`.

### Bước 5: Tạo symlink storage

```bash
php artisan storage:link
```

### Bước 6: Build assets và chạy server

```bash
# Terminal 1 - Vite (dev)
npm run dev

# Terminal 2 - Laravel
php artisan serve
```

Truy cập:
- **User site:** `http://localhost:8000`
- **Admin panel:** `http://localhost:8000/admin`
- **API Docs (Swagger):** `http://localhost:8000/api/documentation`

---

## Tài khoản mặc định (sau khi seed)

Xem file `database/seeders/` để biết tài khoản admin/staff mặc định được tạo sẵn.

---

## API

Backend cung cấp REST API tại `/api/*`, bảo vệ bằng Laravel Passport.

Xem tài liệu API đầy đủ tại: `http://localhost:8000/api/documentation`
