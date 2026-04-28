{{--
How to use:

1. Include this Blade file in your main layout or specific pages where you need the delete confirmation modal.

2. Add the following data attributes to any delete button or link:
- data-delete-url: The URL to send the delete request to (required)
- data-delete-name: The name of the item being deleted (optional, used in the confirmation message)
- data-delete-type: The type of item being deleted (optional, used in the confirmation message)

Example:
<button type="button" class="btn btn-danger"
	data-delete-url="{{ route('admin.customer.delete', $customer->id) }}"
	data-delete-name="{{ $customer->name }}"
	data-delete-type="khách hàng">
	Xoá
</button>

3. The modal will automatically handle the click event, show a confirmation dialog, and submit a delete request if the user confirms.
--}}

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-labelledby="deleteConfirmModalLabel"
	aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered">
		<div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
			<div class="modal-body text-center p-5">
				<!-- Icon -->
				<div class="mb-4">
					<div class="delete-icon-wrapper mx-auto d-flex align-items-center justify-content-center"
						style="width: 80px; height: 80px; background: linear-gradient(135deg, #ffe5e5 0%, #fff0f0 100%); border-radius: 50%; box-shadow: 0 4px 15px rgba(220, 53, 69, 0.15);">
						<i class="fas fa-trash-alt" style="font-size: 32px; color: #dc3545;"></i>
					</div>
				</div>

				<!-- Title -->
				<h5 class="fw-bold mb-3" id="deleteConfirmModalLabel" style="color: #2d3748; font-size: 1.5rem;">
					Xác nhận xóa
				</h5>

				<!-- Description -->
				<p class="text-muted mb-4" style="font-size: 0.95rem; line-height: 1.6; color: #64748b;">
					Bạn có chắc chắn muốn xóa? Hành động này không thể hoàn tác.
				</p>

				<!-- Buttons -->
				<div class="d-flex gap-3 justify-content-center flex-wrap">
					<button type="button" class="btn btn-cancel" data-bs-dismiss="modal"
						style="background-color: #f1f5f9 !important; color: #475569 !important; border: none !important; border-radius: 12px !important; font-weight: 500 !important; min-width: 120px !important; padding: 10px 24px !important; transition: all 0.3s ease !important;">
						Hủy bỏ
					</button>
					<button type="button" class="btn btn-confirm" id="confirmDeleteBtn"
						style="background-color: #dc3545 !important; color: white !important; border: none !important; border-radius: 12px !important; font-weight: 500 !important; min-width: 120px !important; padding: 10px 24px !important; box-shadow: 0 4px 12px rgba(220, 53, 69, 0.3) !important; transition: all 0.3s ease !important;">
						Xác nhận
					</button>
				</div>
			</div>
		</div>
	</div>
</div>

<style>
	/* Hover effects */
	.btn-cancel:hover {
		background-color: #e2e8f0 !important;
		transform: translateY(-1px);
	}

	.btn-confirm:hover {
		background-color: #c82333 !important;
		box-shadow: 0 6px 16px rgba(220, 53, 69, 0.4) !important;
		transform: translateY(-1px);
	}

	.btn-cancel:active,
	.btn-confirm:active {
		transform: translateY(0);
	}

	/* Modal backdrop */
	.modal-backdrop.show {
		opacity: 0.6;
	}

	/* Animation */
	.delete-icon-wrapper {
		animation: pulse-icon 2s ease-in-out infinite;
	}

	@keyframes pulse-icon {

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
	window.openDeleteConfirmModal = function (config) {
		const {
			title = 'Xác nhận xóa',
			message = 'Bạn có chắc chắn muốn xóa? Hành động này không thể hoàn tác.',
			deleteUrl = null,
			onConfirm = null
		} = config;

		$('#deleteConfirmModalLabel').text(title);
		$('#deleteConfirmModal .text-muted').text(message);
		$('#confirmDeleteBtn').off('click');
		$('#confirmDeleteBtn').on('click', function () {
			if (onConfirm) {
				onConfirm();
			} else if (deleteUrl) {
				const form = $('<form>', {
					'method': 'POST',
					'action': deleteUrl
				});

				form.append($('<input>', {
					'type': 'hidden',
					'name': '_token',
					'value': $('meta[name="csrf-token"]').attr('content') || '{{ csrf_token() }}'
				}));

				form.append($('<input>', {
					'type': 'hidden',
					'name': '_method',
					'value': 'DELETE'
				}));

				$('body').append(form);

				form.submit();
			}

			$('#deleteConfirmModal').modal('hide');
		});

		$('#deleteConfirmModal').modal('show');
	};

	$(document).ready(function () {
		$(document).on('click', '[data-delete-url]', function (e) {
			e.preventDefault();
			e.stopPropagation();

			const deleteUrl = $(this).data('delete-url');
			const itemName = $(this).data('delete-name') || '';
			const itemType = $(this).data('delete-type') || 'mục này';

			window.openDeleteConfirmModal({
				title: `Xóa ${itemType}?`,
				message: itemName ?
					`Bạn có chắc chắn muốn xóa "${itemName}"? Hành động này không thể hoàn tác.` :
					`Bạn có chắc chắn muốn xóa ${itemType}? Hành động này không thể hoàn tác.`,
				deleteUrl: deleteUrl
			});
		});
	});
</script>
