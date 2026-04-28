//common function to load data from URL
function loadDataFromUrl(containerSelector) {
  console.log("loadDataFromUrl", containerSelector);
  const currentUrl = window.location.pathname;
  const searchParams = new URLSearchParams(window.location.search);

  //show loading indicator
  window.AjaxPagination.showLoadingIndicator();
  //build query parameters for AJAX
  const params = new URLSearchParams();
  for (const [key, value] of searchParams.entries()) {
    params.append(key, value);
  }

  //add AJAX request header
  const fetchOptions = {
    headers: {
      "X-Requested-With": "XMLHttpRequest",
      Accept: "text/html",
    },
  };
  console.log("url", `${currentUrl}?${params.toString()}`);

  //send AJAX request
  fetch(`${currentUrl}?${params.toString()}`, fetchOptions)
    .then((response) => {
      console.log("response", response);
      if (!response.ok) {
        throw new Error("Network response was not ok");
      }
      return response.text();
    })
    .then((html) => {
      //update user list
      if (containerSelector) {
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, "text/html");
        //tìm container hiện tại trong DOM
        const container = document.querySelector(containerSelector);
        if (container) {
          const newContent = doc.querySelector(containerSelector);
          if (newContent) {
            container.innerHTML = newContent.innerHTML;
          }
        } else {
          console.error("Container not found");
        }
      }

      //update search input value
      const searchInput = document.getElementById("search-input");
      if (searchInput) {
        const searchQuery = searchParams.get("search");
        searchInput.value = searchQuery || "";
      }

      //hide loading indicator
      window.AjaxPagination.hideLoadingIndicator();
    })
    .catch((error) => {
      console.error("Error loading data:", error);
      window.AjaxPagination.hideLoadingIndicator();

      //show error via toastr if available
      if (typeof toastr !== "undefined") {
        toastr.error("{{ __('common.error_loading_data') }}");
      } else {
        alert("{{ __('common.error_loading_data') }}");
      }
    });
}
