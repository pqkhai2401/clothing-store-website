<div class="modal fade" id="allNotificationsModal" tabindex="-1" aria-labelledby="allNotificationsModalLabel"
	aria-hidden="true">
	<div class="modal-dialog modal-sm" style="max-width: 1200px;">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="allNotificationsModalLabel">
					<i class="fas fa-bell me-2"></i>Tất cả thông báo
				</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>

			<div class="modal-body">

				<div class="row mb-4">
					<div class="col-md-4">
						<label for="notificationStatusFilter" class="form-label fw-bold text-uppercase">Trạng
							thái</label>
						<select class="form-select" id="notificationStatusFilter">
							<option value="">Tất cả trạng thái</option>
							<option value="0">Chưa đọc</option>
							<option value="1">Đã đọc</option>
						</select>
					</div>
					<div class="col-md-4">
						<label for="notificationTypeFilter" name="type" class="form-label fw-bold text-uppercase">Loại
							thông báo</label>
						<select class="form-select" id="notificationTypeFilter">
						</select>
					</div>
					<div class="col-md-4">
						<label for="notificationSearchFilter" class="form-label fw-bold text-uppercase">Tìm kiếm</label>
						<div class="input-group">
							<input type="text" class="form-control" id="notificationSearchFilter"
								placeholder="Nhập từ khóa tìm kiếm...">
						</div>
					</div>
				</div>

				<div class="d-flex justify-content-end align-items-center mb-3">
					<button type="button" class="btn btn-primary btn-sm me-2" id="markAllReadBtn">
						<i class="fas fa-check-double me-1"></i>Đánh dấu tất cả đã đọc
					</button>
					<button type="button" class="btn btn-outline-secondary btn-sm" id="refreshNotificationsBtn">
						<i class="fas fa-sync-alt me-1"></i>Làm mới
					</button>
				</div>

				<div id="notificationsListContainer" class="border rounded-3 p-3 position-relative">

					<div class="text-center py-4 d-none" id="loadingSpinner"
						style="position: absolute; top:0; left:0; width:100%; height:100%; background:rgba(255,255,255,0.7); z-index:10; border-radius: 8px;">
						<i class="fas fa-spinner fa-spin fa-2x text-primary mt-5"></i>
					</div>
					<div id="list-data"></div>

				</div>


				<div id="pagination-container" class="mt-3"></div>
			</div>

			<div class="modal-footer bg-light">
				<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
					<i class="fas fa-times me-1"></i> Đóng
				</button>
			</div>
		</div>
	</div>
</div>

<style>
	#allNotificationsModal {
		z-index: 9999 !important;
	}

	#allNotificationsModal .modal-dialog {
		max-width: 90%;
	}

	#allNotificationsModal .modal-body {
		padding-bottom: 15px;
	}

	#notificationsListContainer {
		max-height: calc(100vh - 350px);
		overflow-y: auto;
		padding-right: 10px;
	}

	#notificationsListContainer::-webkit-scrollbar {
		width: 6px;
	}

	#notificationsListContainer::-webkit-scrollbar-thumb {
		background-color: #adb5bd;
		border-radius: 4px;
	}

	/* Individual notification container */
	.notification-container {
		margin-bottom: 8px;
		cursor: pointer;
	}

	.notification-container:last-child {
		margin-bottom: 0;
	}

	.notification-modal-item {
		padding: 14px 18px;
		border: 1px solid #e9ecef;
		transition: all 0.2s ease;
		cursor: pointer;
		border-radius: 8px;
		margin-bottom: 0;
	}

	.notification-modal-item:hover {
		background-color: #f8f9fa;
		transform: translateY(-1px);
		box-shadow: 0 4px 6px rgba(0, 0, 0, 0.04);
	}

	.notification-modal-item.unread {
		background-color: #fff3cd;
		border-left: 5px solid #ffc107;
		border-color: #ffe69c;
	}

	.notification-modal-item.unread:hover {
		background-color: #f1fac2 !important;
	}

	.notification-modal-item.read {
		background-color: #ffffff;
		border-left: 5px solid transparent;
	}

	.notification-modal-item.overdue {
		background-color: #f8d7da;
		border-left: 5px solid #dc3545;
		border-color: #f5c2c7;
	}

	.notification-modal-item.overdue:hover {
		background-color: #f1b0b7 !important;
	}

	.notification-modal-title {
		font-weight: 600;
		color: #212529;
		margin-bottom: 6px;
		font-size: 1.05rem;
		line-height: 1.4;
	}

	.notification-modal-body {
		color: #495057;
		margin-bottom: 8px;
		font-size: 0.95rem;
		line-height: 1.5;
	}

	.notification-modal-meta {
		font-size: 0.85rem;
		color: #868e96;
		display: flex;
		justify-content: space-between;
		align-items: center;
	}

	.notification-modal-actions {
		display: flex;
		gap: 8px;
	}

	.notification-empty-state {
		text-align: center;
		padding: 40px 20px;
		color: #6c757d;
	}

	.notification-empty-state i {
		font-size: 3rem;
		margin-bottom: 16px;
		opacity: 0.5;
	}
</style>

<script>
	document.addEventListener("DOMContentLoaded", function () {
		const modal = document.getElementById('allNotificationsModal');
		const listDataContainer = document.getElementById('list-data');
		const paginationContainer = document.getElementById('pagination-container');
		const loadingSpinner = document.getElementById('loadingSpinner');

		let currentPage = 1;
		let currentStatus = '';
		let currentType = '';
		let currentSearch = '';

		window.readAndRedirect = async function (event, id, link, btnElement) {
			event.preventDefault();

			if (btnElement) {
				let itemDiv = btnElement.closest('.list-group-item');
				if (itemDiv) {
					itemDiv.style.backgroundColor = '#f8f9fa';
					itemDiv.style.borderLeft = '4px solid #dee2e6';

					let textDiv = itemDiv.querySelector('.text-dark');
					if (textDiv) {
						textDiv.classList.remove('fw-bold');
						textDiv.classList.add('fw-normal');
					}
				}
			}

			let apiUrl = `{{ route('notification.read', ['id' => ':id']) }}`.replace(':id', id);

			try {
				await callApi(apiUrl, 'POST');
			} catch (error) {
				console.error("Lỗi khi đánh dấu đã đọc:", error);
			} finally {
				if (link && link !== '#') {
					window.location.href = link;
				}
			}
		};

		modal.addEventListener('show.bs.modal', function () {
			fetchFullNotifications(1);
		});

		function renderTypeFilter(notiTypes) {
			const selectElement = document.getElementById('notificationTypeFilter');
			const currentValue = selectElement.value;

			selectElement.innerHTML = '<option value="">Tất cả các loại</option>';

			if (notiTypes) {
				const typesArray = Array.isArray(notiTypes) ? notiTypes : Object.values(notiTypes);

				typesArray.forEach(type => {
					if (!type) return;

					const option = document.createElement('option');
					option.value = type;
					option.textContent = type;

					if (type === currentValue) {
						option.selected = true;
					}
					selectElement.appendChild(option);
				});
			}
		}

		async function fetchFullNotifications(page = 1) {
			currentPage = page;

			loadingSpinner.classList.remove('d-none');
			if (listDataContainer) listDataContainer.style.opacity = '0.5';

			let perPageInput = document.querySelector('#pagination-container .custom-select-pagination');
			let perPage = perPageInput ? perPageInput.value : 10;

			let url = `{{ route('notification.list') }}?page=${page}&perPage=${perPage}`;
			if (currentStatus) url += `&status=${currentStatus}`;
			if (currentType) url += `&type=${currentType}`;
			if (currentSearch) url += `&search=${currentSearch}`;

			try {
				const res = await callApi(url);

				loadingSpinner.classList.add('d-none');
				if (listDataContainer) listDataContainer.style.opacity = '1';

				if (res.status === 'success') {
					renderFullList(res.data);

					paginationContainer.innerHTML = res.pagination_html || '';

					renderTypeFilter(res.notiType);

					const modalTitle = document.getElementById('allNotificationsModalLabel');
					if (modalTitle && res.totalNoti !== undefined) {
						modalTitle.innerHTML = `<i class="fas fa-bell me-2"></i>Tất cả thông báo (${res.totalNoti})`;
					}
				}
			} catch (err) {
				loadingSpinner.classList.add('d-none');
				listDataContainer.innerHTML = '<p class="text-center text-danger">Không thể tải dữ liệu.</p>';
			}
		}

		function renderFullList(notifications) {
			if (notifications.length === 0) {
				listDataContainer.innerHTML = '<div class="text-center py-5 text-muted">Không tìm thấy thông báo nào phù hợp.</div>';
				paginationContainer.innerHTML = '';
				return;
			}

            let html = '<div class="list-group list-group-flush mb-3">';
            notifications.forEach(noti => {
                const isRead = noti.is_read != 0;
                const d = new Date(noti.created_at);

                const time = d.toLocaleTimeString('vi-VN', {
                    hour: '2-digit',
                    minute: '2-digit'
                });

                const day = String(d.getDate()).padStart(2, '0');
                const month = String(d.getMonth() + 1).padStart(2, '0');
                const year = d.getFullYear();

                const date = `${time}   ${day}/${month}/${year}`;
                html += `
                <div class="list-group-item list-group-item-action border-rounded mb-2 p-3 d-flex align-items-center justify-content-between shadow-sm" 
                     style="border-radius: 10px; background-color: ${isRead ? '#f8f9fa' : '#ffffff'}; border-left: 4px solid ${isRead ? '#dee2e6' : '#0dcaf0'};">
                    <div class="me-3 flex-grow-1">
                        <div class="text-dark ${isRead ? 'fw-normal' : 'fw-bold'}" style="font-size: 16px;">${noti.message}</div>
                        <small class="text-muted"><i class="far fa-clock me-1"></i>${date}</small>
                    </div>
                    <div class="d-flex align-items-center">
                        <a href="${noti.computed_link}" onclick="readAndRedirect(event, ${noti.id}, '${noti.computed_link}')" 
                           class="btn btn-sm btn-outline-primary rounded-pill px-3">
                           Chi tiết
                        </a>
                    </div>
                </div>`;
			});
			html += '</div>';
			listDataContainer.innerHTML = html;
		}

		paginationContainer.addEventListener('click', function (e) {
			const pageLink = e.target.closest('a.page-link');
			if (pageLink) {
				e.preventDefault();
				let url = pageLink.getAttribute('data-url') || pageLink.getAttribute('href');

				if (url && url !== '#' && url !== 'javascript:void(0);') {
					let page = new URL(url, window.location.origin).searchParams.get("page");
					if (page) fetchFullNotifications(page);
				}
			}
		});

		paginationContainer.addEventListener('change', function (e) {
			if (e.target.classList.contains('custom-select-pagination')) {
				fetchFullNotifications(1);
			}
		});

		document.getElementById('notificationStatusFilter').addEventListener('change', function () {
			currentStatus = this.value;
			fetchFullNotifications(1);
		});

		document.getElementById('notificationTypeFilter').addEventListener('change', function () {
			currentType = this.value;
			fetchFullNotifications(1);
		});

		let searchTimer;
		document.getElementById('notificationSearchFilter').addEventListener('input', function () {
			clearTimeout(searchTimer);
			currentSearch = this.value;
			searchTimer = setTimeout(() => fetchFullNotifications(1), 500); // Debounce 500ms
		});

		document.getElementById('refreshNotificationsBtn').addEventListener('click', function () {
			currentPage = 1;

			fetchFullNotifications(1);

            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });

		document.getElementById('markAllReadBtn').addEventListener('click', async function () {
			if (!confirm('Bạn có muốn đánh dấu tất cả thông báo là đã đọc?')) return;

			try {
				await callApi(`{{ route('notification.readAll') }}`, 'POST');
				showToast('Đã đánh dấu tất cả thông báo là đã đọc!', 'success');
				fetchFullNotifications(1);
			} catch (error) { }
		});
	});
</script>