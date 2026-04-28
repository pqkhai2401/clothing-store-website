<nav class="app-header navbar navbar-expand bg-body">
    <div class="container-fluid">
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" data-lte-toggle="sidebar" href="#" role="button">
                    <i class="fa-solid fa-bars"></i>
                </a>
            </li>
        </ul>

        <ul class="navbar-nav ms-auto">

            <li class="nav-item dropdown" id="notification-dropdown">
                <a class="nav-link" data-bs-toggle="dropdown" href="#" id="notification-bell">
                    <i class="fa-solid fa-bell"></i>
                    <span class="navbar-badge badge text-bg-warning" id="notification-count"
                        style="display: none;">0</span>
                </a>
                <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end" id="notification-dropdown-menu">
                    <!-- Phần thông báo -->
                    <span class="dropdown-header fs-6 fw-bold text-dark py-2 px-3 text-center" id="notification-header">
                        THÔNG BÁO
                    </span>
                    <div class="dropdown-divider"></div>
                    <div id="notification-list" class="mx-2"
                        style="max-height: 320px; overflow-y: auto; overflow-x: hidden;">
                        <!-- Notifications will be loaded here via AJAX -->
                    </div>
                    <div class="dropdown-divider"></div>
                    <div class="dropdown-item dropdown-footer text-center">
                        <div class="dropdown-item dropdown-footer text-center">
                            <a href="#" data-bs-toggle="modal" data-bs-target="#allNotificationsModal"
                                class="text-decoration-none text-primary">
                                <i class="fas fa-list me-1"></i> Tất cả thông báo
                            </a>
                        </div>
                    </div>
                </div>
            </li>

            <li class="nav-item dropdown user-menu">
                <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                    @php
                        $user = auth()->user();
                        $avatar = $user?->image ? asset($user->image) : asset('assets/img/clone.png');
                        $fullName = $user?->full_name ?? 'Người dùng';
                    @endphp
                    <img src="{{ $avatar }}" class="user-image rounded-circle shadow" alt="Image" id="headerUserAvatar"
                        style="width: 30px; height: 30px; object-fit: cover; margin-top: 3px" />
                    <span class="d-none d-md-inline p-1" id="headerUserName">{{ $fullName }}</span>
                </a>

                <ul class="dropdown-menu dropdown-menu-end" style="width: 150px">
                    <li>
                        <a href="{{ route('auth.logout') }}" class="dropdown-item">
                            <i class="fa-solid fa-sign-out-alt me-2"></i> Logout
                        </a>
                    </li>
                </ul>
            </li>
        </ul>
    </div>
</nav>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        fetchNotifications();
    });

    function readAndRedirect(event, id, url) {
        event.preventDefault();
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

        fetch(`/notification/${id}/read`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            }
        })
            .catch(error => console.error('Lỗi khi cập nhật trạng thái đã đọc:', error))
            .finally(() => {
                if (url && url !== '#' && url !== 'null') {
                    window.location.href = url;
                }
            });
    }
</script>