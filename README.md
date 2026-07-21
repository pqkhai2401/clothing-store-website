# CLOTHING STORE WEBSITE

Website bán hàng thời trang trực tuyến xây dựng bằng Laravel. Hệ thống bao gồm giao diện người dùng (Blade), trang quản trị nội bộ (Admin Panel) và REST API.

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
| **Frontend** | Blade Templates, static CSS/JS (asset()) |

---

## Cấu trúc dự án

```
clothing-store-website/
├── app/
│   ├── Enums/                    # Gender, OrderSource, OrderStatus, PaymentStatus, UserRole
│   ├── Helpers/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Admin/            # BrandController, CategoryController, CollectionController,
│   │   │   │                     # ColorController, DashboardController, GoodsReceiptController,
│   │   │   │                     # InventoryReportController, OrderController, ProductController,
│   │   │   │                     # ProfileController, RevenueController, ReviewController,
│   │   │   │                     # SizeController, StockIssueController, StocktakeController,
│   │   │   │                     # SupplierController, UserController, VoucherController,
│   │   │   │                     # WarehouseController, ActivityLogController,
│   │   │   │                     # Settings/ (SettingController, HeroBannerController)
│   │   │   ├── Api/              # VoucherController
│   │   │   └── User/             # AuthController, AddressController, CartController,
│   │   │                         # CheckoutController, ForgotPasswordController, HomeController,
│   │   │                         # LocationController, MomoController, PayosController,
│   │   │                         # OrderController, ProductController, ProfileController,
│   │   │                         # RecommendationController, ReviewController, WishlistController
│   │   ├── Middleware/
│   │   └── Requests/
│   ├── Models/                   # Xem danh sách models bên dưới
│   ├── Providers/
│   └── Services/                 # OrderCancellationService, PaymentReconciliationService, ...
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
│   └── css/js/                   # Static CSS/JS được include trực tiếp qua asset() trong Blade
│
└── routes/
    ├── web.php                   # Web routes (user + auth)
    ├── admin.php                 # Admin panel routes
    ├── api.php                   # REST API routes
    └── console.php                # Artisan console routes
```

## Cài đặt và chạy dự án

### Yêu cầu
- PHP >= 8.2 + Composer >= 2.0
- Extension PHP: `pdo_mysql`, `mbstring`, `gd`, `zip`, `exif`, `fileinfo` (cần cho maatwebsite/excel, mews/purifier)
- MySQL >= 8.0

> Repo có sẵn `package.json` (Tailwind CSS, Vite) nhưng hiện các view Blade không dùng directive `@vite` — CSS/JS được nhúng trực tiếp qua `asset()` từ `public/assets`, `public/css`, `public/js`. Vì vậy **không cần cài Node.js/npm** để chạy dự án; chỉ cần nếu sau này bạn muốn build lại Tailwind.

---

### Bước 1: Clone và cài đặt dependencies

```bash
git clone https://github.com/pqkhai2401/clothing-store-website.git
cd clothing-store-website

# Cài đặt PHP dependencies
composer install
```

### Bước 2: Cấu hình môi trường

```bash
cp .env.example .env
php artisan key:generate
```

Mở `.env` và cập nhật (file `.env.example` mặc định được cấu hình cho môi trường production trên DigitalOcean, cần đổi lại cho local):

```env
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

LOG_CHANNEL=stack
LOG_LEVEL=debug

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=clothing_store
DB_USERNAME=root
DB_PASSWORD=

# OAuth Google (tuỳ chọn)
GOOGLE_CLIENT_ID=
GOOGLE_CLIENT_SECRET=
GOOGLE_REDIRECT_URI="http://localhost:8000/auth/google/callback"

# Passport (điền sau khi chạy passport:install)
PASSPORT_CLIENT_ID=
PASSPORT_CLIENT_SECRET=

# Thanh toán PayOS (tuỳ chọn)
PAYOS_CLIENT_ID=
PAYOS_API_KEY=
PAYOS_CHECKSUM_KEY=

# Thanh toán MoMo — .env.example đã có sẵn credential sandbox công khai của MoMo,
# có thể dùng luôn để test mà không cần đăng ký
MOMO_PARTNER_CODE=MOMO
MOMO_ACCESS_KEY=F8BBA842ECF85
MOMO_SECRET_KEY=K951B6PE1waDMi640xX08PD3vg6EkVlz
MOMO_ENDPOINT=https://test-payment.momo.vn/v2/gateway/api/create

# Gemini AI (gợi ý sản phẩm + kiểm duyệt bình luận, tuỳ chọn)
GEMINI_API_KEY=
GEMINI_MODEL=gemini-flash-lite-latest
```

> Lưu ý: `SESSION_DRIVER`, `CACHE_STORE` và `QUEUE_CONNECTION` mặc định đều dùng `database`, nên bảng `sessions`, `cache` và `jobs` sẽ được tạo khi chạy migrate ở bước tiếp theo.

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

Từ Passport 12+, migration của package (`oauth_clients`, `oauth_access_tokens`, ...) không tự chạy cùng `php artisan migrate` — `passport:install` sẽ tự publish các migration này vào `database/migrations` rồi gọi `migrate` giúp bạn, nên chỉ cần chạy đúng 1 lệnh này là đủ, không cần migrate lại thủ công. Lệnh còn tạo cặp khoá mã hoá RSA (`storage/oauth-private.key`, `oauth-public.key`) dùng để ký JWT, và tạo sẵn 2 OAuth client: một **Personal Access Client** (dùng khi cần tạo token cá nhân, ví dụ qua Tinker) và một **Password Grant Client** (dùng cho API đăng nhập bằng username/password, kiểu mà REST API của dự án đang dùng để cấp token cho user). Sau khi chạy xong, lệnh sẽ in ra `Client ID` và `Client secret` — điền 2 giá trị đó vào `PASSPORT_CLIENT_ID` và `PASSPORT_CLIENT_SECRET` trong `.env`.

Lưu ý: mỗi lần chạy lại `passport:install` (không có cờ `--force`) sẽ tạo thêm client mới thay vì ghi đè, nên chỉ cần chạy một lần khi setup lần đầu.

### Bước 5: Tạo symlink storage

```bash
php artisan storage:link
```

### Bước 6: Chạy server

Chạy song song 2 terminal:

```bash
# Terminal 1 - Laravel
php artisan serve

# Terminal 2 - Queue worker
php artisan queue:listen --tries=1
```

`queue:listen` khởi động một worker lắng nghe bảng `jobs` (vì `.env` cấu hình `QUEUE_CONNECTION=database`) và xử lý các job chạy nền — ví dụ đối soát trạng thái thanh toán (`PaymentReconciliationService`), gửi email, v.v. Nếu không chạy lệnh này, các job dạng `dispatch()` sẽ nằm chờ mãi trong bảng `jobs` mà không được xử lý. `queue:listen` tự động nạp lại code mỗi khi có job mới nên phù hợp cho môi trường dev (khác với `queue:work` là chạy 1 lần rồi giữ nguyên code trong bộ nhớ, cần restart thủ công khi sửa code).

> `composer.json` có sẵn script `composer run dev` chạy đồng thời server + queue worker + log (Pail) + `npm run dev`, nhưng vì Vite hiện không được các view sử dụng nên bạn có thể bỏ qua và chỉ cần 2 terminal ở trên.

### Bước 7 (tuỳ chọn): Sinh tài liệu API (Swagger)

```bash
php artisan l5-swagger:generate
```

`config/l5-swagger.php` đặt `generate_always = false` mặc định, nghĩa là file `storage/api-docs/api-docs.json` **không tự sinh** — nếu bỏ qua bước này, trang `/api/documentation` sẽ báo lỗi không tìm thấy tài liệu. Cần chạy lại lệnh này mỗi khi sửa annotation `@OA\...` trong controller.

Truy cập:
- **User site:** `http://localhost:8000`
- **Admin panel:** `http://localhost:8000/admin`
- **API Docs (Swagger):** `http://localhost:8000/api/documentation`

---

## Tài khoản mặc định (sau khi seed)

Tài khoản admin dùng để test nhanh: `admin@gmail.com` / `Admin@123`. Ngoài ra `UserSeeder` còn tạo thêm vài tài khoản admin/staff khác gắn với email cá nhân của các thành viên nhóm — xem trực tiếp `database/seeders/UserSeeder.php` nếu cần.

---

