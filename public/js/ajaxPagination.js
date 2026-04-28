document.addEventListener("DOMContentLoaded", function () {
  function loadFormsWithAjax(url) {
    let fetchUrl = new URL(url, window.location.origin);

    if (typeof window.getFilterParams === "function") {
      const filters = window.getFilterParams();
      Object.keys(filters).forEach((key) => {
        if (filters[key]) fetchUrl.searchParams.set(key, filters[key]);
        else fetchUrl.searchParams.delete(key);
      });
    }

    const keyword = sessionStorage.getItem("filterKeyword");
    if (keyword) {
      fetchUrl.searchParams.set("keyword", keyword);
    }

    const container = document.querySelector("#ajax-forms-container");

    if (container) {
      container.style.position = "relative"; 
      container.style.pointerEvents = "none"; 

      let overlay = document.createElement("div");
      overlay.className = "ajax-spinner-overlay";
      overlay.id = "loading-spinner";
      overlay.innerHTML = '<div class="ajax-spinner"></div>';
      container.appendChild(overlay);
    }

    fetch(fetchUrl, {
      headers: {
        "X-Requested-With": "XMLHttpRequest",
      },
    })
      .then((response) => response.text())
      .then((html) => {
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, "text/html");
        const newContent = doc.querySelector("#ajax-forms-container");

        if (newContent && container) {
          container.innerHTML = newContent.innerHTML;
        } else if (container) {
          container.innerHTML = html;
        }

        if (container) {
          container.style.pointerEvents = "auto";
        }

        updateBrowserUrl(fetchUrl.toString());
        setupAjaxEvents();
        document.dispatchEvent(new CustomEvent("ajaxFormsLoaded"));

        const offsetTop = container ? container.getBoundingClientRect().top + window.scrollY - 50 : 0;
      })
      .catch((error) => {
        console.error("Error loading content:", error);
        
        if (container) {
          container.style.pointerEvents = "auto";
          const spinner = container.querySelector("#loading-spinner");
          if (spinner) spinner.remove();
        }
      });
  }

  function changeRowsPerPage(value) {
    const url = new URL(window.location.href);
    url.searchParams.set("perPage", value);
    url.searchParams.set("page", "1");
    loadFormsWithAjax(url.toString());
  }

  function setupAjaxEvents() {
    const container = document.querySelector("#ajax-forms-container");
    if (!container) return;

    function getUrlWithPerPage(href) {
      if (!href) return ""; 
      const targetUrl = new URL(href, window.location.origin);
      const selectBox = container.querySelector(".custom-select-pagination");
      if (selectBox && selectBox.value) {
        targetUrl.searchParams.set("perPage", selectBox.value);
      }
      return targetUrl.toString();
    }

    const pageLinks = container.querySelectorAll(".pagination a");
    pageLinks.forEach((link) => {
      link.addEventListener("click", function (e) {
        e.preventDefault();

        const targetUrl =
          this.getAttribute("data-url") || this.getAttribute("href");

        if (targetUrl && targetUrl !== "javascript:void(0);") {
          loadFormsWithAjax(getUrlWithPerPage(targetUrl));
        }
      });
    });

    const sortLinks = container.querySelectorAll(".sort-link");
    sortLinks.forEach((link) => {
      link.addEventListener("click", function (e) {
        e.preventDefault();

        const targetUrl =
          this.getAttribute("data-url") || this.getAttribute("href");

        if (targetUrl && targetUrl !== "javascript:void(0);") {
          loadFormsWithAjax(getUrlWithPerPage(targetUrl));
        }
      });
    });
  }

  function updateBrowserUrl(url) {
    if (window.history && window.history.pushState) {
      const cleanUrl = new URL(url, window.location.origin);

      const paramsToHide = [
        "perPage",
        "page",
        "start_date",
        "end_date",
        "type",
        "keyword",
        "sort",
        "direction",
      ];
      paramsToHide.forEach((param) => cleanUrl.searchParams.delete(param));

      window.history.pushState(
        { path: cleanUrl.toString() },
        "",
        cleanUrl.toString(),
      );
    }
  }
  
  setupAjaxEvents();
  window.loadFormsWithAjax = loadFormsWithAjax;
  window.changeRowsPerPage = changeRowsPerPage;
});