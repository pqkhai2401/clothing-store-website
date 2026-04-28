document.addEventListener("DOMContentLoaded", function () {
  // console.log("hàm setup form accountant pagination được gọi");
  // Initialize formaccounts pagination functionality
  setupFormAccountsPagination();
  setupFormAccountsRowsPerPage();

  // Make functions available globally
  window.loadFormAccountsPageWithAjax = loadFormAccountsPageWithAjax;
});

// Function to setup AJAX pagination for formaccounts
function setupFormAccountsPagination() {
  // Get all pagination links within the formaccounts list container
  const paginationLinks = document.querySelectorAll(
    ".form-accounts-list-container .pagination a"
  );

  // Add click event to all pagination links
  paginationLinks.forEach((link) => {
    link.addEventListener("click", function (e) {
      e.preventDefault();
      const url = new URL(this.getAttribute("href"), window.location.origin);
      // console.log("load page với url là: ", url);
      url.searchParams.set("tab", "listformaccounts");
      loadFormAccountsPageWithAjax(url.toString(), "pagination");
    });
  });
}

// Function to setup rows per page functionality for formaccounts
function setupFormAccountsRowsPerPage() {
  const rowsPerPageSelect = document.getElementById("form-accounts-per-page");
  if (rowsPerPageSelect) {
    // Remove existing event listeners to prevent duplicates
    const newSelect = rowsPerPageSelect.cloneNode(true);
    rowsPerPageSelect.parentNode.replaceChild(newSelect, rowsPerPageSelect);

    newSelect.addEventListener("change", function () {
      const currentUrl = new URL(window.location.href);
      currentUrl.searchParams.set("form_accounts_per_page", this.value);
      // Reset to first page when changing rows per page
      currentUrl.searchParams.set("form_accounts_page", "1");
      // Add tab parameter for AJAX handling
      currentUrl.searchParams.set("tab", "listformaccounts");

      loadFormAccountsPageWithAjax(currentUrl.toString());
    });
  }
}

// Show loading indicator for formaccounts
// function showFormAccountsLoadingIndicator() {
//   let loader = document.getElementById("formaccounts-ajax-loader");
//   if (!loader) {
//     loader = document.createElement("div");
//     loader.id = "formaccounts-ajax-loader";
//     loader.innerHTML =
//       '<div class="text-center py-4"><i class="fas fa-spinner fa-spin"></i> ' +
//       window.translations.loading +
//       "</div>";
//     loader.style.position = "absolute";
//     loader.style.top = "50%";
//     loader.style.left = "50%";
//     loader.style.transform = "translate(-50%, -50%)";
//     loader.style.backgroundColor = "rgba(255, 255, 255, 0.9)";
//     loader.style.padding = "20px";
//     loader.style.borderRadius = "5px";
//     loader.style.zIndex = "1000";

//     const container = document.querySelector(".form-accounts-list-container");
//     if (container) {
//       container.style.position = "relative";
//       container.appendChild(loader);
//     }
//   }
//   loader.style.display = "block";
// }

// // Hide loading indicator for formaccounts
// function hideFormAccountsLoadingIndicator() {
//   const loader = document.getElementById("formaccounts-ajax-loader");
//   if (loader) {
//     loader.style.display = "none";
//   }
// }

// Update loadFormAccountsPageWithAjax function
function loadFormAccountsPageWithAjax(url, mode = "pagination") {
  // console.log("Loading formaccounts with URL:", url, "Mode:", mode);

  if (mode === "language_change") {
    console.log("Reloading FormAccount list due to language change");
  }

  window.AjaxPagination.showLoadingIndicatorTab(
    ".form-accounts-list-container"
  );

  return fetch(url, {
    headers: {
      "X-Requested-With": "XMLHttpRequest",
      Accept: "text/html",
      "X-CSRF-TOKEN":
        document
          .querySelector('meta[name="csrf-token"]')
          ?.getAttribute("content") || "",
    },
  })
    .then((response) => {
      if (!response.ok)
        throw new Error(`HTTP error! status: ${response.status}`);
      return response.text();
    })
    .then((html) => {
      const container = document.querySelector(".form-accounts-list-container");

      if (container) {
        // Update container with new HTML content
        container.innerHTML = html;

        // Re-initialize event handlers for the updated content
        setupFormAccountsPagination();
        setupFormAccountsRowsPerPage();
      }
      window.AjaxPagination.hideLoadingIndicatorTab();
    })
    .catch((error) => {
      console.error("Error loading formaccounts:", error);
      window.AjaxPagination.hideLoadingIndicatorTab();
      throw error; // Re-throw to allow promise chain to handle it
    });
}
