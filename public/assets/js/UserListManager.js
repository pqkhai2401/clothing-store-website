class UserListManager {
  constructor(config) {
    this.container = config.container || ".user-list";
    this.userType = config.userType || "user";
    this.init();
  }

  init() {
    this.setupDeleteConfirmation();
    this.setupTooltips();
    this.setupAjaxPagination();
    this.setupRowsPerPage();
    this.setupDeleteModal();
  }

  setupDeleteConfirmation() {
    const deleteForms = document.querySelectorAll(".delete-form");
    const userType = this.userType;

    deleteForms.forEach((form) => {
      form.addEventListener("submit", function (e) {
        e.preventDefault();
        if (confirm(`Are you sure you want to delete this ${userType}?`)) {
          form.submit();
        }
      });
    });
  }

  setupTooltips() {
    var tooltipTriggerList = [].slice.call(
      document.querySelectorAll('[data-bs-toggle="tooltip"]')
    );
    tooltipTriggerList.map(function (tooltipTriggerEl) {
      return new bootstrap.Tooltip(tooltipTriggerEl);
    });
  }

  setupAjaxPagination() {
    // Get all pagination links
    const paginationLinks = document.querySelectorAll(".pagination a");

    // Add click event to all pagination links
    paginationLinks.forEach((link) => {
      link.addEventListener("click", (e) => {
        e.preventDefault();
        const url = link.getAttribute("href");
        this.loadPageWithAjax(url);
      });
    });
  }

  setupRowsPerPage() {
    const rowsPerPageSelect = document.getElementById("rowsPerPage");
    if (rowsPerPageSelect) {
      rowsPerPageSelect.addEventListener("change", () => {
        this.loadPageWithAjax(
          window.location.pathname + "?perPage=" + rowsPerPageSelect.value
        );
      });
    }
  }

  loadPageWithAjax(url) {
    // Show loading indicator
    this.showLoadingIndicator();

    fetch(url, {
      headers: {
        "X-Requested-With": "XMLHttpRequest",
      },
    })
      .then((response) => response.text())
      .then((html) => {
        // Update the table content
        document.querySelector(this.container).innerHTML = html;

        // Update browser URL without full page refresh
        this.updateBrowserUrl(url);

        // Re-initialize all event listeners
        this.init();

        // Hide loading indicator
        this.hideLoadingIndicator();
      })
      .catch((error) => {
        console.error("Error loading content:", error);
        this.hideLoadingIndicator();
      });
  }

  updateBrowserUrl(url) {
    if (window.history && window.history.pushState) {
      window.history.pushState(
        {
          path: url,
        },
        "",
        url
      );
    }
  }

  showLoadingIndicator() {
    // Create loading indicator if it doesn't exist
    if (!document.getElementById("ajax-loader")) {
      const loader = document.createElement("div");
      loader.id = "ajax-loader";
      loader.innerHTML = `
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            `;
      loader.style.cssText =
        "position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); z-index: 9999; background: rgba(255,255,255,0.7); padding: 20px; border-radius: 5px; box-shadow: 0 0 10px rgba(0,0,0,0.1);";
      document.body.appendChild(loader);
    } else {
      document.getElementById("ajax-loader").style.display = "block";
    }
  }

  hideLoadingIndicator() {
    const loader = document.getElementById("ajax-loader");
    if (loader) {
      loader.style.display = "none";
    }
  }

  setupDeleteModal() {
    const deleteModal = document.getElementById("deleteModal");
    const userType = this.userType;

    if (deleteModal) {
      deleteModal.addEventListener("show.bs.modal", function (event) {
        const button = event.relatedTarget;

        const formId = button.getAttribute("data-form-id");
        const formTitle = button.getAttribute("data-form-title");

        const formTitleEl = document.getElementById("formTitleToDelete");
        formTitleEl.textContent =
          formTitle ||
          `${userType.charAt(0).toUpperCase() + userType.slice(1)} #` + formId;

        const deleteForm = document.getElementById("deleteFormModal");
        deleteForm.action = `/admin/user/delete/${formId}`;

        const confirmButton = document.getElementById("confirmDeleteBtn");
        confirmButton.onclick = function () {
          deleteForm.submit();
        };
      });
    }
  }
}

// Global function for changing rows per page (used in HTML)
function changeRowsPerPage(value) {
  const url = new URL(window.location.href);
  url.searchParams.set("perPage", value);
  window.location.href = url.toString();
}

// Initialize on DOM content loaded
document.addEventListener("DOMContentLoaded", function () {
  // Determine user type based on URL or container class
  let userType = "user";
  const path = window.location.pathname;

  if (path.includes("accountant")) {
    userType = "accountant";
  } else if (path.includes("submitter")) {
    userType = "submitter";
  } else if (path.includes("approver")) {
    userType = "approver";
  }

  // Initialize user list manager
  const container =
    document.querySelector(".user-list") ||
    document.querySelector(".accountant-list") ||
    document.querySelector(".submitter-list") ||
    document.querySelector(".approver-list");

  if (container) {
    const userListManager = new UserListManager({
      container: "." + container.className.split(" ")[0],
      userType: userType,
    });
  }
});
