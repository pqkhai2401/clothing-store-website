{{--
How to use:

1. Include this Blade file in your main layout or specific pages where you need the update confirmation modal.

2. Add the following data attributes to any update button or link:
- data-update-url: The URL to send the update request to (required)
- data-update-name: The name of the item being updated (optional, used in the confirmation message)
- data-update-type: The type of item being updated (optional, used in the confirmation message)
- data-update-method: HTTP method for the update (optional, default: PUT, e.g. PATCH)

Example:
<button type="button" class="btn btn-primary" data-update-url="{{ route('admin.customer.update', $customer->id) }}"
	data-update-name="{{ $customer->name }}" data-update-type="khách hàng" data-update-method="PATCH">
	Cập nhật
</button>

3. The modal will automatically handle the click event, show a confirmation dialog, and submit an update request if the
user confirms.
--}}

<!-- Update Confirmation Modal -->
<div class="modal fade" id="updateConfirmModal" tabindex="-1" aria-labelledby="updateConfirmModalLabel"
	aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered">
		<div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
			<div class="modal-body text-center p-5">
				<!-- Icon -->
				<div class="mb-4">
					<div class="update-icon-wrapper mx-auto d-flex align-items-center justify-content-center"
						style="width: 80px; height: 80px; background: linear-gradient(135deg, #fef3c7 0%, #fff7ed 100%); border-radius: 50%; box-shadow: 0 4px 15px rgba(245, 158, 11, 0.18);">
						<i class="fas fa-edit" style="font-size: 32px; color: #f59e0b;"></i>
					</div>
				</div>

				<!-- Title -->
				<h5 class="fw-bold mb-3" id="updateConfirmModalLabel" style="color: #0f172a; font-size: 1.5rem;">
					Xác nhận cập nhật
				</h5>

				<!-- Description -->
				<p class="text-muted mb-4" style="font-size: 0.95rem; line-height: 1.6; color: #64748b;">
					Bạn có chắc chắn muốn cập nhật? Hành động này sẽ ghi đè dữ liệu hiện tại.
				</p>

				<!-- Buttons -->
				<div class="d-flex gap-3 justify-content-center">
					<button type="button" class="btn btn-update-cancel px-4 py-2" data-bs-dismiss="modal"
						style="background-color: #f1f5f9; color: #475569; border: none; border-radius: 12px; font-weight: 500; min-width: 120px; transition: all 0.3s ease;">
						Hủy bỏ
					</button>
					<button type="button" class="btn btn-update-confirm px-4 py-2" id="confirmUpdateBtn"
						style="background-color: #f59e0b; color: white; border: none; border-radius: 12px; font-weight: 500; min-width: 120px; box-shadow: 0 4px 12px rgba(245, 158, 11, 0.35); transition: all 0.3s ease;">
						Xác nhận
					</button>
				</div>
			</div>
		</div>
	</div>
</div>

<style>
	/* Hover effects */
	.btn-update-cancel:hover {
		background-color: #e2e8f0 !important;
		transform: translateY(-1px);
	}

	.btn-update-confirm:hover {
		background-color: #d97706 !important;
		box-shadow: 0 6px 16px rgba(217, 119, 6, 0.45) !important;
		transform: translateY(-1px);
	}

	.btn-update-cancel:active,
	.btn-update-confirm:active {
		transform: translateY(0);
	}

	/* Modal backdrop */
	.modal-backdrop.show {
		opacity: 0.6;
	}

	/* Animation */
	.update-icon-wrapper {
		animation: pulse-update-icon 2s ease-in-out infinite;
	}

	@keyframes pulse-update-icon {

		0%,
		100% {
			transform: scale(1);
		}

		50% {
			transform: scale(1.05);
		}
	}
</style>

<script>
	window.openUpdateConfirmModal = function (config) {
		const {
			title = 'Xác nhận cập nhật',
			message = 'Bạn có chắc chắn muốn cập nhật? Hành động này sẽ ghi đè dữ liệu hiện tại.',
			updateUrl = null,
			updateMethod = 'PUT',
			onConfirm = null
		} = config;

		$('#updateConfirmModalLabel').text(title);
		$('#updateConfirmModal .text-muted').text(message);
		$('#confirmUpdateBtn').off('click');
		$('#confirmUpdateBtn').on('click', function () {
			if (onConfirm) {
				onConfirm();
			} else if (updateUrl) {
				const form = $('<form>', {
					'method': 'POST',
					'action': updateUrl
				});

				form.append($('<input>', {
					'type': 'hidden',
					'name': '_token',
					'value': $('meta[name="csrf-token"]').attr('content') || '{{ csrf_token() }}'
				}));

				const normalizedMethod = String(updateMethod || '').toUpperCase();
				if (normalizedMethod && normalizedMethod !== 'POST') {
					form.append($('<input>', {
						'type': 'hidden',
						'name': '_method',
						'value': normalizedMethod
					}));
				}

				$('body').append(form);

				form.submit();
			}

			$('#updateConfirmModal').modal('hide');
		});

		$('#updateConfirmModal').modal('show');
	};

	$(document).ready(function () {
		$(document).on('click', '[data-update-url]', function (e) {
			e.preventDefault();
			e.stopPropagation();

			const updateUrl = $(this).data('update-url');
			const itemName = $(this).data('update-name') || '';
			const itemType = $(this).data('update-type') || 'mục này';
			const updateMethod = $(this).data('update-method') || 'PUT';

			window.openUpdateConfirmModal({
				title: `Cập nhật ${itemType}?`,
				message: itemName ?
					`Bạn có chắc chắn muốn cập nhật "${itemName}"? Hành động này sẽ ghi đè dữ liệu hiện tại.` :
					`Bạn có chắc chắn muốn cập nhật ${itemType}? Hành động này sẽ ghi đè dữ liệu hiện tại.`,
				updateUrl: updateUrl,
				updateMethod: updateMethod
			});
		});
	});
</script>
