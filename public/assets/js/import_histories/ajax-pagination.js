function loadImportHistoriesPage(page, perPage) {
  const importHistoriesContainer = document.querySelector(
    ".history-imports-list-container"
  );

  //show loading indicator
  if (importHistoriesContainer) {
    importHistoriesContainer.innerHTML = `<div class="text-center py-4"><i class="fas fa-spinner fa-spin"></i>${__(
      "common.loading"
    )}</div>`;
  }

  // console.log("container lay duoc la: ", importHistoriesContainer);
  const baseUrl = importHistoriesContainer?.dataset.baseUrl;
  // console.log("baseUrl la duoc la: ", baseUrl);

  const params = new URLSearchParams({
    active_tab: "listimporthistories",
    page: String(page),
    perPage: String(perPage),
    tab: "listimporthistories",
  });

  const requestUrl = `${baseUrl}?${params.toString()}`;

  //make ajax request with only bank accounts parameters
  fetch(requestUrl, {
    headers: {
      "X-Requested-With": "XMLHttpRequest",
    },
  })
    .then((response) => {
      return response.text();
    })
    .then((html) => {
      const parser = new DOMParser();
      const doc = parser.parseFromString(html, "text/html");

      //update the import histories list container
      const newImportHistoriesList = doc.querySelector(
        ".history-imports-list-container"
      );

      if (newImportHistoriesList && importHistoriesContainer) {
        importHistoriesContainer.innerHTML = newImportHistoriesList.innerHTML;
      }

      //reinitialize event listeners on the new pagination links
      initImportHistoriesAjaxPagination();
      const newPerPageSelect = document.getElementById("rowsPerPage");

      if (newPerPageSelect) {
        newPerPageSelect.removeEventListener(
          "change",
          handleImportHistoriesPerPageChange
        );
        newPerPageSelect.addEventListener(
          "change",
          handleImportHistoriesPerPageChange
        );
      }

      //update url without page reload
      const url = new URL(window.location);
      const cleanUrl = url.pathname;
      history.replaceState(
        {},
        "",
        `${cleanUrl}?active_tab=listimporthistories&page=${page}&perPage=${perPage}`
      );
    })
    .catch((error) => {
      if (importHistoriesContainer) {
        importHistoriesContainer.innerHTML = `<div class="alert-danger">${__(
          "common.no_data"
        )}</div>`;
      }
    });
}

//define handler function to avoid duplicate listeners
function handleImportHistoriesPerPageChange() {
  loadImportHistoriesPage(1, this.value);
}

function initImportHistoriesAjaxPagination() {
  const paginationLinks = document.querySelectorAll(
    "#listimporthistories .pagination a"
  );

  paginationLinks.forEach(function (link) {
    link.addEventListener("click", function (e) {
      e.preventDefault();

      //lấy page từ thẻ a được click
      let page = Number(this.dataset.page);
      console.log("page lấy được là: ", page);
      if (!Number.isFinite(page)) {
        const href = this.getAttribute("href") || "";
        console.log("href lấy được là:", href);

        try {
          const url = new URL(href, window.location.origin);
          const page = Number(url.searchParams.get("page")) || 1;
        } catch {
          const n = Number(this.textContent.trim());
          page = Number.isFinite(n) ? n : 1;
        }
      }
      const perPage =
        Number(document.getElementById("rowsPerPage")?.value) || 10;
      loadImportHistoriesPage(page, perPage);
    });
  });
}
