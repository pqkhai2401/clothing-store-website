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

