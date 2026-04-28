//Products management
let productIndex = 0;

//Add product row
function addProductRow(productId = null) {
  const tbody = document.getElementById("productsTableBody");
  const row = document.createElement("tr");
  row.id = `productRow_${productIndex}`;

  //render option select unit
  let optionsHtml = `<option value="">${__("form.select_unit")}</option>`;
  for (const [value, label] of Object.entries(productUnits)) {
    optionsHtml += `<option value="${value}">${label}</option>`;
  }

  //thêm hidden input để lưu id của sản phẩm
  const hiddenIdInput = productId
    ? `<input type="hidden" name="products[${productIndex}][id]" value="${productId}">`
    : "";

  row.innerHTML = `
        ${hiddenIdInput}
        <td>
            <input type="text" name="products[${productIndex}][name]"
                class="form-control product-name" required 
                data-msg-required="${__("form.required_products_name")}">
        </td>
        <td>
            <select name="products[${productIndex}][unit]"
                class="form-select product-unit" required
                data-msg-required="${__("form.required_products_unit")}">
                ${optionsHtml}
            </select>
        </td>
        <td>
            <input type="number" min="0" step="0.01" name="products[${productIndex}][unitPrice]"
                class="form-control product-unit-price" required
                data-msg-required="${__("form.required_products_unit_price")}">
        </td>
        <td>
            <input type="number" min="1" name="products[${productIndex}][quantity]"
                class="form-control product-quantity" required
                data-msg-required="${__("form.required_products_quantity")}">
        </td>
        <td>
            <input type="number" min="0" step="0.01" name="products[${productIndex}][taxPercent]"
                class="form-control product-tax-percent" required
                data-msg-required="${__("form.required_products_tax_percent")}">
        </td>
        <td>
            <input type="text" name="products[${productIndex}][totalAmount]"
                class="form-control product-total-amount" readonly required
                data-msg-required="${__(
                  "form.required_products_total_amount"
                )}">
        </td>
        <td class="text-center">
            <button type="button" class="btn btn-sm btn-danger" onclick="removeProductRow(${productIndex})">
                <i class="fas fa-trash"></i>
            </button>
        </td>
    `;

  tbody.appendChild(row);

  //add event listeners for calculation
  addCalculationListeners(row);

  // Cập nhật validation cho row mới
  if (window.FormManager && window.FormManager.Validation) {
    const $form = $("#form-create");
    if ($form.length) {
      // Thêm validation rules cho các field mới
      $form.find(`#productRow_${productIndex} .product-name`).rules("add", {
        required: true,
        messages: { required: window.productTranslations.requiredProductName },
      });
      $form.find(`#productRow_${productIndex} .product-unit`).rules("add", {
        required: true,
        messages: { required: window.productTranslations.requiredUnit },
      });
      $form
        .find(`#productRow_${productIndex} .product-unit-price`)
        .rules("add", {
          required: true,
          messages: { required: window.productTranslations.requiredUnitPrice },
        });
      $form.find(`#productRow_${productIndex} .product-quantity`).rules("add", {
        required: true,
        messages: { required: window.productTranslations.requiredQuantity },
      });
      $form
        .find(`#productRow_${productIndex} .product-tax-percent`)
        .rules("add", {
          required: true,
          messages: { required: window.productTranslations.requiredTaxPercent },
        });
      $form
        .find(`#productRow_${productIndex} .product-total-amount`)
        .rules("add", {
          required: true,
          messages: {
            required: window.productTranslations.requiredTotalAmount,
          },
        });
    }
  }

  productIndex++;
}

//add calculation listeners to a row
function addCalculationListeners(row) {
  const unitPriceInput = row.querySelector(".product-unit-price");
  const quantityInput = row.querySelector(".product-quantity");
  const taxPercentInput = row.querySelector(".product-tax-percent");
  const totalAmount = row.querySelector(".product-total-amount");

  const calculateTotal = () => {
    const unitPrice = parseFloat(unitPriceInput.value) || 0;
    const quantity = parseFloat(quantityInput.value) || 0;
    const taxPercent = parseFloat(taxPercentInput.value) || 0;

    const subtotal = unitPrice * quantity;
    const taxAmount = subtotal * (taxPercent / 100);
    const total = subtotal + taxAmount;

    totalAmount.value = total;
    updateTotalPrice();
  };

  unitPriceInput.addEventListener("input", calculateTotal);
  quantityInput.addEventListener("input", calculateTotal);
  taxPercentInput.addEventListener("input", calculateTotal);
}

//remove product row
function removeProductRow(index) {
  const row = document.getElementById(`productRow_${index}`);
  if (row) {
    row.remove();
    updateTotalPrice();
  }
}

//update total price
function updateTotalPrice() {
  const totalAmountInputs = document.querySelectorAll(".product-total-amount");
  if (!totalAmountInputs) {
    return;
  }
  let total = 0;

  totalAmountInputs.forEach((input) => {
    const value = parseFloat(input.value) || 0;
    total += value;
  });

  const totalPriceInput = document.getElementById("totalPrice");
  if (totalPriceInput) {
    totalPriceInput.value = total;
  }
}

//initialize when DOM is loaded
document.addEventListener("DOMContentLoaded", function () {
  //add event listener for add product button
  const addProductBtn = document.getElementById("addProductRowBtn");
  if (addProductBtn) {
    addProductBtn.addEventListener("click", addProductRow);
  }
});

//function load product units
function loadExistingProducts(formDetails) {
  if (!formDetails || formDetails.length === 0) {
    return;
  }

  //xóa tất cả sản phẩm hiện tại
  const tbody = document.getElementById("productsTableBody");
  tbody.innerHTML = "";

  //reset product index
  productIndex = 0;

  //load từng sản phẩm
  formDetails.forEach((product, index) => {
    addProductRow(product.id);

    //Lấy row vừa tạo
    const row = document.getElementById(`productRow_${productIndex - 1}`);

    //điền dữ liệu vào các input
    const nameInput = row.querySelector(".product-name");
    const unitSelect = row.querySelector(".product-unit");
    const unitPriceInput = row.querySelector(".product-unit-price");
    const quantityInput = row.querySelector(".product-quantity");
    const taxPercentInput = row.querySelector(".product-tax-percent");
    const totalAmountInput = row.querySelector(".product-total-amount");

    if (nameInput) nameInput.value = product.name || "";
    if (unitSelect) unitSelect.value = product.unit || 0;
    if (unitPriceInput) unitPriceInput.value = product.price || 0;
    if (quantityInput) quantityInput.value = product.quantity || 0;
    if (taxPercentInput)
      taxPercentInput.value = parseFloat(product.taxPercent || 0);
    if (totalAmountInput) totalAmountInput.value = product.totalAmount || 0;

    //trigger calculation để cập nhật total
    const event = new Event("input", { bubbles: true });
    unitPriceInput.dispatchEvent(event);
  });

  //cập nhật tổng tiền
  updateTotalPrice();
}

//add product row function (global scope for onclick)
window.addProductRow = addProductRow;
window.removeProductRow = removeProductRow;
window.loadExistingProducts = loadExistingProducts;
