$(document).ready(function () {
  // Initialize form validation
  $("#bankAccountForm").validate({
    rules: {
      accountHolder: {
        required: true,
        // maxlength: 255,
      },

      bankNumber: {
        required: true,
        uniqueBankNumber: true,
        onlyNumber: true,
      },
      bankName: {
        required: true,
        // maxlength: 255,
      },
      linkedAccountId: {
        required: true,
      },
    },
    messages: {
      accountHolder: {
        required: `${__("organization.required_account_holder")}`,
        // maxlength:
        //   "{{ __('organization.max_string', ['attribute' => __('common.account_holder'), 'max' => 255]) }}",
      },
      bankNumber: {
        required: `${__("organization.required_bank_number")}`,
        uniqueBankNumber: `${__("organization.bank_number_exists")}`,
        onlyNumber: `${__("organization.validation.bank_number.numeric")}`,
      },
      bankName: {
        required: `${__("organization.enter_bank_name")}`,
        // maxlength:
        //   "{{ __('organization.max_string', ['attribute' => __('common.bank_name'), 'max' => 255]) }}",
      },
      linkedAccountId: {
        required: `${__("organization.select_accountant_account_required")}`,
      },
    },

    //Real time validation
    onfocusout: function (element) {
      $(element).valid();
    },

    onkeyup: function (element) {
      //Validate on keyup for better UX, but with debounce for AJAX calls
      if (
        $(element).hasClass("is-invalid") ||
        $(element).hasClass("is-valid")
      ) {
        if ($(element).attr("name") !== "bankNumber") {
          //Debounce bank number validation
          // clearTimeout(element.bankNumberValidationTimeout);
          // element.bankNumberValidationTimeout = setTimeout(() => {
          //   $(element).valid();
          // }, 500);
          // } else {
          $(element).valid();
        }
      }
    },
    onclick: function (element) {
      //validate select elements on click
      if (element.type === "select-one") {
        $(element).valid();
      }
    },

    errorElement: "div",
    errorClass: "invalid-feedback",

    errorPlacement: function (error, element) {
      error.insertAfter(element);
    },

    highlight: function (element) {
      $(element).addClass("is-invalid").removeClass("is-valid");
    },
    unhighlight: function (element) {
      $(element).removeClass("is-invalid").addClass("is-valid");
    },
    submitHandler: function (form) {
      saveBankAccount();
      return false;
    },
  });

  //Handle modal show event
  $("#bankAccountModal").on("show.bs.modal", function (event) {
    const modalMode = $("#bankAccountModalMode").val();

    //Reset validation states when modal opens
    $("#bankAccountForm")
      .find(".is-invalid, .is-valid")
      .removeClass("is-invalid is-valid");
    $("#bankAccountForm").find(".invalid-feedback").text("").hide();
  });

  //Handle modal hide event to reset form
  $("#bankAccountModal").on("hidden.bs.modal", function () {
    //reset the form
    $("#bankAccountForm")[0].reset();

    //clear validation states
    $("#bankAccountForm")
      .find(".is-invalid, .is-valid")
      .removeClass("is-invalid is-valid");
    $("#bankAccountForm").find(".invalid-feedback").text("").hide();

    //reset select elements
    $("#bankAccountForm").val("").trigger("change");

    //clear any pending validation timeouts
    $("#bankAccountForm")
      .find("input")
      .each(function () {
        if (this.bankNumberValidationTimeout) {
          clearTimeout(this.bankNumberValidationTimeout);
        }
      });
  });
});

$.validator.addMethod(
  "onlyNumber",
  function (value, element) {
    return this.optional(element) || /^[0-9]+$/.test(value);
  },
  `organization.validation.bank_number.numeric`
);

$.validator.addMethod(
  "uniqueBankNumber",
  function (value, element) {
    if (!value) return true;
    const organizationId = $("#bankAccountOrganizationId").val();
    const bankAccountId = $("#bankAccountId").val();
    const modalMode = $("#bankAccountModalMode").val();

    if (!organizationId) return true;

    let isValid = true;

    const userRole = $('meta[name="user-role"]').attr("content");
    $.ajax({
      url: `/${userRole}/organization/bank-account/check-unique-bank-number`,
      method: "POST",
      async: false,
      data: {
        bankNumber: value,
        organizationId: organizationId,
        bankAccountId: modalMode === "edit" ? bankAccountId : null,
        _token: $('meta[name="csrf-token"]').attr("content"),
      },
      success: function (response) {
        isValid = response.data === true;
      },
      error: function (xhr, status, error) {
        console.error("Bank number validation error: ", error);
        isValid = true;
      },
    });
    return isValid;
  },
  `${__("organization.bank_number_exists")}`
);
