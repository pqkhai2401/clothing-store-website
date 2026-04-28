function loadBankAccountsPage(page, perPage) {
  const bankAccountsContainer = document.querySelector(
    // "#listbankaccounts .bank-accounts-list-container"
    ".bank-accounts-list-container"
  );

  // Show loading indicator
  if (bankAccountsContainer) {
    window.AjaxPagination.showLoadingIndicatorTab(
      ".bank-accounts-list-container"
    );
  }

  const baseUrl = bankAccountsContainer?.dataset.baseUrl;

  const params = new URLSearchParams({
    active_tab: "listbankaccounts",
    "bank-accounts_page": String(page),
    "bank-accounts_per_page": String(perPage),
  });
  const requestUrl = `${baseUrl}?${params.toString()}`;
  // Make AJAX request with ONLY bank accounts parameters
  fetch(requestUrl, {
    headers: {
      "X-Requested-With": "XMLHttpRequest",
    },
  })
    .then((response) => {
      //console.log('Response status:', response.status);
      return response.text();
    })
    .then((html) => {
      //console.log('Response received, parsing HTML...');
      // Parse HTML response
      const parser = new DOMParser();
      const doc = parser.parseFromString(html, "text/html");

      // Update the bank accounts list container
      const newBankAccountsList = doc.querySelector(
        ".bank-accounts-list-container"
      );
      //console.log('New bank accounts list found:', !!newBankAccountsList);

      if (newBankAccountsList && bankAccountsContainer) {
        bankAccountsContainer.innerHTML = newBankAccountsList.innerHTML;
        //console.log('Bank accounts list updated successfully');
      }

      // Reinitialize event listeners on the new pagination links
      initBankAccountsAjaxPagination();
      // console.log("gọi xong hàm khởi tạo bankaccount ajax");
      // Re-attach event listener to the new per page select
      const newPerPageSelect = document.getElementById(
        "bank-accounts-per-page"
      );
      //console.log('AJAX reload - bank accounts per page select found:', !!newPerPageSelect);
      if (newPerPageSelect) {
        // Remove existing listeners to avoid duplicates
        newPerPageSelect.removeEventListener(
          "change",
          handleBankAccountsPerPageChange
        );
        newPerPageSelect.addEventListener(
          "change",
          handleBankAccountsPerPageChange
        );
      }

      // Update URL without page reload
      const url = new URL(window.location);
      const cleanUrl = url.pathname;
      history.replaceState(
        {},
        "",
        `${cleanUrl}?active_tab=listbankaccounts&bank-accounts_page=${page}&bank-accounts_per_page=${perPage}`
      );
      window.AjaxPagination.hideLoadingIndicatorTab();
    })
    .catch((error) => {
      console.error("Error loading bank accounts page:", error);
      window.AjaxPagination.hideLoadingIndicatorTab();
      if (bankAccountsContainer) {
        bankAccountsContainer.innerHTML = `<div class="alert alert-danger">${__(
          "common.no_data"
        )}</div>`;
      }
    });
}

// Define handler function to avoid duplicate listeners
function handleBankAccountsPerPageChange() {
  //console.log('Bank accounts per page changed to:', this.value);
  loadBankAccountsPage(1, this.value);
}

function initBankAccountsAjaxPagination() {
  //Add click handler to all pagination links in the bank accounts tab
  //console.log('Initializing bank accounts AJAX pagination...');
  const paginationLinks = document.querySelectorAll(
    "#listbankaccounts .pagination .page-link"
  );
  //console.log('Found bank accounts pagination links:', paginationLinks.length);

  paginationLinks.forEach(function (link) {
    link.addEventListener("click", function (e) {
      e.preventDefault();
      const href = this.getAttribute("href");
      //console.log('Bank accounts pagination link clicked:', href);
      const url = new URL(href, window.location.origin);
      const page =
        url.searchParams.get("bank-accounts_page") ||
        url.searchParams.get("page") ||
        1;
      const perPage =
        document.getElementById("bank-accounts-per-page")?.value || 10;
      //console.log('Loading bank accounts page:', page, 'per page:', perPage);
      loadBankAccountsPage(page, perPage);
    });
  });
}
