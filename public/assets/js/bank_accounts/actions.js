// Global functions for modal management
function createNewBankAccount(el) {
  // Set modal to create mode
  $("#bankAccountModalMode").val("create");
  $("#bankAccountId").val("");
  $("#bankAccountOrganizationId").val(el.dataset.organizationId);

  // Update modal title and icon
  $("#bankAccountModalIcon").removeClass("fa-edit").addClass("fa-plus");
  $("#bankAccountModalTitle").text(`${__("organization.create_bank_account")}`);

  // Clear form
  $("#bankAccountForm")[0].reset();
  $("#bankAccountForm")
    .find(".is-invalid, .is-valid")
    .removeClass("is-invalid is-valid");
  $("#bankAccountForm").find(".invalid-feedback").text("");

  // Load form accounts for dropdown
  loadFormAccountsForDropdown(el, "add");

  // Show modal
  $("#bankAccountModal").modal("show");
}

// Handle form submission - Global function
function saveBankAccount() {
  // Validate form first
  if (!$("#bankAccountForm").valid()) {
    // Focus on first invalid field
    $("#bankAccountForm").find(".is-invalid").first().focus();
    return false;
  }

  //show loading state
  const $saveBtn = $("#saveBankAccountBtn");
  const originalText = $saveBtn.html();
  $saveBtn
    .prop("disabled", true)
    .html(
      `<i class="fas fa-spinner fa-spin me-1"></i>${__("common.saving")}...`
    );

  const modalMode = $("#bankAccountModalMode").val();
  const bankAccountId = $("#bankAccountId").val();
  const organizationId = $("#bankAccountOrganizationId").val();

  const formData = {
    accountHolder: $("#accountHolder").val(),
    bankNumber: $("#bankNumber").val(),
    bankName: $("#bankName").val(),
    // totalAmount: $('#totalAmount').val(),
    linkedAccountId: $("#linkedAccountId").val(),
    organizationId: organizationId,
    _token: $('meta[name="csrf-token"]').attr("content"),
  };

  //lấy role của user hiện tại
  const userRole = $('meta[name="user-role"]').attr("content");

  // Determine URL and method based on mode
  const url =
    modalMode === "edit"
      ? `/${userRole}/organization/bank-account/${bankAccountId}/update`
      : `/${userRole}/organization/bank-account/store`;
  const method = modalMode === "edit" ? "PUT" : "POST";
  // Make AJAX request
  $.ajax({
    url: url,
    method: method,
    data: formData,
    success: function (response) {
      if (response.success) {
        $("#bankAccountModal").modal("hide");
        showSuccessMessage(response.message);
        // Reload the bank accounts list
        loadBankAccountsList();
      } else {
        showErrorMessage(response.message);
      }
    },
    error: function (xhr) {
      if (xhr.status === 422) {
        const errors = xhr.responseJSON.errors;
        Object.keys(errors).forEach(function (key) {
          const input = $(`#${key}`);
          input.addClass("is-invalid");
          input.siblings(".invalid-feedback").text(errors[key][0]);
        });
      } else {
        const errorMessage =
          modalMode === "create"
            ? `${__("organization.error_creating_bank_account")}`
            : `${__("organization.error_updating_bank_account")}`;
        showErrorMessage(errorMessage);
      }
    },
    complete: function () {
      // Reset loading state
      $saveBtn.html(originalText).prop("disabled", false);
    },
  });
}

function editBankAccount(el) {
  // Set modal to edit mode
  $("#bankAccountModalMode").val("edit");
  $("#bankAccountId").val(el.dataset.id);

  // Update modal title and icon
  $("#bankAccountModalIcon").removeClass("fa-plus").addClass("fa-edit");
  $("#bankAccountModalTitle").text(`${__("organization.edit_bank_account")}`);

  // Set form data
  $("#accountHolder").val(el.dataset.accountHolder);
  $("#bankNumber").val(el.dataset.bankNumber);
  $("#bankName").val(el.dataset.bankName);

  // Get organization ID from the page context
  const organizationId = el.dataset.organizationId;

  // Set organization ID in the form
  $("#bankAccountOrganizationId").val(organizationId);

  // Load form accounts and set selected value
  loadFormAccountsForDropdown(el, "edit");

  // Clear validation states
  $("#bankAccountForm")
    .find(".is-invalid, .is-valid")
    .removeClass("is-invalid is-valid");
  $("#bankAccountForm").find(".invalid-feedback").text("");

  // Show modal
  $("#bankAccountModal").modal("show");
}

function deleteBankAccount(el) {
  // gọi api để check xem có liên kết với form nào không
  $.ajax({
    url: `/${el.dataset.roleName}/organization/bank-account/${el.dataset.id}/check-links`,
    method: "GET",
    success: function (res) {
      //has links - show cannot delete modal
      if (typeof showUniversalDeleteModal === "function") {
        if (res.data) {
          //heading, label, name, explanation
          showUniversalDeleteModal("cannotDelete", {
            heading: `${__("organization.cannot_delete_bank_account")}`,
            label: `${__("organization.bank_account_name")}:`,
            name: el.dataset.accountHolder,
            explanation: `${__("organization.cannot_delete_explanation")}`,
          });
        } else {
          showUniversalDeleteModal("confirmDelete", {
            heading: `${__("common.confirm_deletion")}`,
            label: `${__("organization.bank_account_name")}:`,
            name: el.dataset.accountHolder,
            explanation: `${__(
              "organization.confirm_delete_bank_account_message"
            )}`,
            deleteId: el.dataset.id,
            deleteUrl: el.dataset.deleteUrl,
          });
        }
      }
    },
  });
}

// Function to load form accounts for dropdown
function loadFormAccountsForDropdown(el, type) {
  const dropdown = $("#linkedAccountId");
  // Show loading state
  dropdown
    .html(`<option value="">${__("common.loading")}...</option>`)
    .prop("disabled", true);

  // Make AJAX request to get form accounts
  $.ajax({
    url: `/${el.dataset.roleName}/organization/${el.dataset.organizationId}/form-accounts`,
    method: "GET",
    data: {
      editing_bank_account_id: el.dataset.id,
    },
    headers: {
      "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
      "X-Requested-With": "XMLHttpRequest",
    },
    success: function (response) {
      if (response.success && response.data && response.data.length > 0) {
        if (type === "add") {
          dropdown.html(
            `<option value="">${__(
              "organization.select_accountant_account"
            )}</option>`
          );
        } else {
          dropdown.html("");
        }

        response.data.forEach(function (account) {
          const isSelected =
            el.dataset.linkedAccountId &&
            el.dataset.linkedAccountId == account.id
              ? "selected"
              : "";
          const displayText = `${account.accountNumber} - ${account.accountName}`;
          dropdown.append(
            `<option value="${account.id}" ${isSelected}>${displayText}</option>`
          );
        });
      } else {
        dropdown.append(
          `<option value="" disabled>
          ${__("organization.no_form_accounts_available")}
           </option>`
        );
      }

      //sau khi load xong, gọi api check linked giữa bankaccount với forms
      if (el.dataset.id) {
        $.ajax({
          url: `/${el.dataset.roleName}/organization/bank-account/${el.dataset.id}/check-links`,
          method: "GET",
          success: function (res) {
            if (res.data) {
              dropdown.prop("disabled", true);
            } else {
              dropdown.prop("disabled", false);
            }
          },
          error: function () {
            dropdown.prop("disabled", false); //fallback
          },
        });
      } else {
        dropdown.prop("disabled", false);
      }
    },
    error: function (xhr) {
      dropdown.html(
        `<option value="">${__("organization.error_loading_accounts")}</option>`
      );
      dropdown.prop("disabled", false);
    },
  });
}

// Function to reload bank accounts list
function loadBankAccountsList() {
  // Lấy thông tin pagination hiện tại, với fallback values
  let currentPage = 1;
  let perPage = 10;

  // Thử lấy từ pagination data element
  const paginationData = $("#bankAccountsPaginationData");
  if (paginationData.length > 0) {
    currentPage = paginationData.data("current-page") || 1;
  }

  // Thử lấy từ per-page selector
  const perPageSelect = $("#bank-accounts-per-page");
  if (perPageSelect.length > 0 && perPageSelect.val()) {
    perPage = perPageSelect.val();
  }

  // Use existing AJAX function if available, otherwise reload page
  if (typeof window.loadBankAccountsPage === "function") {
    window.loadBankAccountsPage(currentPage, perPage);
  } else {
    // Fallback: reload the page with correct tab
    const url = new URL(window.location);
    url.searchParams.set("active_tab", "listbankaccounts");
    window.location.href = url.toString();
  }
}

// Utility functions for notifications
function showSuccessMessage(message) {
  showNotification("success", message);
}

function showErrorMessage(message) {
  showNotification("error", message);
}

// Notification function (consistent with organization detail page)
function showNotification(type, message) {
  // Create notification element in top-right corner (consistent with user notes pattern)
  const notification = document.createElement("div");
  notification.className = `alert alert-${
    type === "success" ? "success" : "danger"
  } alert-dismissible fade show position-fixed`;
  notification.style.cssText =
    "top: 20px; right: 20px; z-index: 9999; min-width: 300px;";

  notification.innerHTML = `
        <i class="fas fa-${
          type === "success" ? "check-circle" : "exclamation-circle"
        } me-2"></i>
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;

  // Add to body
  document.body.appendChild(notification);

  // Auto-remove after 3 seconds (consistent with user notes pattern)
  setTimeout(() => {
    if (notification.parentNode) {
      notification.parentNode.removeChild(notification);
    }
  }, 3000);
}

//gán các hàm thành toàn cục
window.loadBankAccountsList = loadBankAccountsList;
window.showSuccessMessage = showSuccessMessage;
window.showErrorMessage = showErrorMessage;
