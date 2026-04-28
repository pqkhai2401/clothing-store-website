//show message functions
function showMessage(message, type) {
  //create a temporary alert
  const alertDiv = document.createElement("div");
  if (type === "success") {
    alertDiv.className =
      "alert alert-success alert-dismissible fade show position-fixed";
    alertDiv.innerHTML = `
          <i class="fas fa-check-circle me-2"></i>
          ${message}
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      `;
  } else {
    alertDiv.className =
      "alert alert-danger alert-dismissible fade show position-fixed";
    alertDiv.innerHTML = `
          <i class="fas fa-exclamation-circle me-2"></i>
          ${message}
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      `;
  }
  alertDiv.style.top = "20px";
  alertDiv.style.right = "20px";
  alertDiv.style.zIndex = "9999";
  alertDiv.style.minWidth = "300px";

  document.body.appendChild(alertDiv);

  //auto-remove after 5 seconds
  setTimeout(() => {
    if (alertDiv.parentNode) {
      alertDiv.parentNode.removeChild(alertDiv);
    }
  }, 5000);
}
