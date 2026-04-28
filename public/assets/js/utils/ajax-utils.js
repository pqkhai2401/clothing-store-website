/**
 * Generic AJAX utility for CRUD operations
 * provides reusable functions for delete, refresh, and navigation
 */

/**
 * configuration for different modules
 */
window.ajaxModuleConfig = {
  formAccounts: {
    successMessage: "formaccount.form_account_delete_success",
    reloadFunction: "loadListTab",
    containerSelector: ".form-accounts-list-container",
    tabParam: "listformaccounts", //activetab
    page: "form_accounts_page",
    perPage: "form_accounts_per_page",
    perPageSelect: "form-accounts-per-page",
    paramPerPage:
      "?active_tab=listformaccounts&form_accounts_page=1&form-accounts_per_page=10",
    paginationLinks: "#listformaccounts .pagination .page-link",
    paginationData: "#formAccountsPaginationData",
  },
  bankAccounts: {
    successMessage: "organization.bank_account_deleted",
    reloadFunction: "loadListTab",
    containerSelector: ".bank-accounts-list-container",
    tabParam: "listbankaccounts",
    page: "bank-accounts_page",
    perPage: "bank-accounts_per_page",
    perPageSelect: "bank-accounts-per-page",
    paramPerPage:
      "?active_tab=listbankaccounts&bank-accounts_page=1&bank-accounts_per_page=10",
    paginationLinks: "#listbankaccounts .pagination .page-link",
    paginationData: "#bankAccountsPaginationData",
  },
  adminHistoryImport: {
    successMessage: "import.delete_success",
    reloadFunction: "loadPageContent",
    containerSelector: ".import-list-container",
    tabParam: "",
  },
};
/**
 * generic delete hanler
 */
window.handleGenericDelete = function (moduleType, deleteId, deleteUrl) {
  const config = window.ajaxModuleConfig[moduleType];
  const deleteBtn = document.getElementById("confirmDeleteBtn");
  // console.log("lấy được biến config từ module type: ", config);
  if (!deleteBtn) return;

  if (!deleteId || !deleteUrl) {
    return;
  }

  //show loading state
  deleteBtn.disabled = true;
  deleteBtn.innerHTML = `<i class="fas fa-spinner fa-spin me-1"></i> ${__(
    "common.deleting"
  )}`;

  fetch(deleteUrl, {
    method: "DELETE",
    headers: {
      "X-CSRF-TOKEN":
        document
          .querySelector('meta[name="csrf-token"]')
          ?.getAttribute("content") || "",
      "X-Requested-With": "XMLHttpRequest",
    },
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        //close modal
        const deleteModal = document.getElementById("universalDeleteModal");
        const deleteModalInstance = bootstrap.Modal.getInstance(deleteModal);
        deleteModalInstance.hide();

        //lấy thông tin url và container cho hàm reloadPageContent
        const currentUrl = new URL(window.location.href);
        const containerSelector = config.containerSelector || "";

        if (
          config.reloadFunction &&
          typeof window[config.reloadFunction] === "function"
        ) {
          try {
            if (config.reloadFunction === "loadPageContent") {
              window[config.reloadFunction](
                currentUrl,
                config.containerSelector,
                null,
                false,
                function () {
                  //chạy sau khi DOM mới đã được chèn
                  if (typeof window.updateImportCountInTitle === "function") {
                    window.updateImportCountInTitle();
                  }
                }
              );
            } else {
              window[config.reloadFunction](config);
            }
          } catch (err) {
            console.error("error inside reload function: ", err);
            throw err;
          }
          // debugger;
        } else if (typeof window.loadPageContent === "function") {
          window.loadPageContent(currentUrl.toString(), containerSelector);
        }

        //show success message
        const successMessage = config
          ? __(config.successMessage)
          : __("common.delete_success");
        setTimeout(() => {
          showMessage(successMessage, "success");
        }, 1000);
      } else {
        showMessage(
          __("common.delete_error") +
            ": " +
            (data.message || __("common.unknown_error")),
          "error"
        );
      }
    })
    .catch((error) => {
      showMessage(__("common.delete_network_error"), "error");
    })
    .finally(() => {
      //reset button state
      deleteBtn.disabled = false;
      deleteBtn.innerHTML = `<i class="fas fa-trash me-1"></i> ${__(
        "common.delete"
      )}`;
    });
};
