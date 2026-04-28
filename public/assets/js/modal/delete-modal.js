document.addEventListener("click", function (e) {
  const btn = e.target.closest && e.target.closest("#confirmDeleteBtn");
  if (btn) {
    const deleteId = btn.dataset.id;
    const deleteUrl = btn.dataset.url;
    // console.log("nhấn nút xóa với delete url như này: ", deleteUrl);
    if (deleteId && deleteUrl) {
      //determine module type from url or context
      const moduleType = getModuleTypeFromContext();
      //use generic delete handler
      window.handleGenericDelete(moduleType, deleteId, deleteUrl);
    }
  }
});

function getModuleTypeFromContext() {
  //check url patterns
  const url = window.location.href;
  if (
    url.includes("listbankaccounts") &&
    document.querySelector(".bank-accounts-list-container")
  ) {
    return "bankAccounts";
  }
  if (url.includes("users")) {
    return "users";
  }
  if (
    url.includes("listformaccounts") &&
    document.querySelector(".form-accounts-list-container")
  ) {
    return "formAccounts";
  }
  if (
    url.includes("admin") &&
    url.includes("history-import") &&
    document.querySelector(".import-list-container")
  ) {
    return "adminHistoryImport";
  }

  return "default";
}

// //show message functions
// function showMessage(message, type) {
//   //create a temporary alert
//   const alertDiv = document.createElement("div");
//   if (type === "success") {
//     alertDiv.className =
//       "alert alert-success alert-dismissible fade show position-fixed";
//     alertDiv.innerHTML = `
//           <i class="fas fa-check-circle me-2"></i>
//           ${message}
//           <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
//       `;
//   } else {
//     alertDiv.className =
//       "alert alert-danger alert-dismissible fade show position-fixed";
//     alertDiv.innerHTML = `
//           <i class="fas fa-exclamation-circle me-2"></i>
//           ${message}
//           <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
//       `;
//   }
//   alertDiv.style.top = "20px";
//   alertDiv.style.right = "20px";
//   alertDiv.style.zIndex = "9999";
//   alertDiv.style.minWidth = "300px";

//   document.body.appendChild(alertDiv);

//   //auto-remove after 5 seconds
//   setTimeout(() => {
//     if (alertDiv.parentNode) {
//       alertDiv.parentNode.removeChild(alertDiv);
//     }
//   }, 5000);
// }
