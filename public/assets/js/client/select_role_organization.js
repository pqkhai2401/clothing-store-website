//SELECT
//function để cập nhật danh sách role options dựa trên organization đã chọn
function updateRoleOptions() {
  $(".organization-role-row").each(function () {
    const $row = $(this);
    const $orgSelect = $row.find('select[name="organizationId[]"]');
    const $roleSelect = $row.find('select[name="roleId[]"]');
    const currentOrgId = $orgSelect.val();
    const currentRoleId = $roleSelect.val();

    if (currentOrgId) {
      //lấy tất cả role đã được chọn cho organization này
      const usedRoles = [];
      $(".organization-role-row").each(function () {
        const $otherRow = $(this);
        const $otherOrgSelect = $otherRow.find(
          'select[name="organizationId[]"]'
        );
        const $otherRoleSelect = $otherRow.find('select[name="roleId[]"]');

        if (
          $otherOrgSelect.val() === currentOrgId &&
          $otherRoleSelect.val() &&
          $otherRow[0] !== $row[0]
        ) {
          usedRoles.push($otherRoleSelect.val());
        }
      });

      //cập nhật options cho role select
      updateRoleSelectOptions($roleSelect, usedRoles, currentRoleId);
    }
  });
}

//function để cập nhật options của role select
function updateRoleSelectOptions($roleSelect, usedRoles, currentRoleId) {
  const $options = $roleSelect.find("option");
  const currentValue = $roleSelect.val();

  $options.each(function () {
    const $option = $(this);
    const optionValue = $option.val();

    //giữ lại option hiện tại và option disabled
    if (optionValue === "" || optionValue === currentRoleId) {
      $option.show();
    } else if (usedRoles.includes(optionValue)) {
      $option.hide();
    } else {
      $option.show();
    }
  });

  //nếu role hiện tại bị ẩn, reset về empty
  if (currentValue && usedRoles.includes(currentValue)) {
    $roleSelect.val("");
  }
}

//function để reset tất cả role options
function resetAllRoleOptions() {
  $(".organization-role-row").each(function () {
    const $row = $(this);
    const $roleSelect = $row.find('select[name="roleId[]"]');

    //hiển thị lại tất cả options
    $roleSelect.find("option").each(function () {
      $(this).show();
    });
  });
}

//function để cập nhật danh sách organization options dựa trên role đã chọn
function updateOrganizationOptions() {
  $(".organization-role-row").each(function () {
    const $row = $(this);
    const $orgSelect = $row.find('select[name="organizationId[]"]');
    const $roleSelect = $row.find('select[name="roleId[]"]');
    const currentOrgId = $orgSelect.val();
    const currentRoleId = $roleSelect.val();

    if (currentRoleId) {
      //lấy tất cả organization đã được chọn role này
      const usedOrgs = [];
      $(".organization-role-row").each(function () {
        const $otherRow = $(this);
        const $otherOrgSelect = $otherRow.find(
          'select[name="organizationId[]"]'
        );
        const $otherRoleSelect = $otherRow.find('select[name="roleId[]"]');

        if (
          $otherRoleSelect.val() === currentRoleId &&
          $otherOrgSelect.val() &&
          $otherRow[0] !== $row[0]
        ) {
          usedOrgs.push($otherOrgSelect.val());
        }
      });

      //cập nhật options cho organization select
      updateOrganizationSelectOptions($orgSelect, usedOrgs, currentOrgId);
    }
  });
}

//function để cập nhật options của organization select
function updateOrganizationSelectOptions($orgSelect, usedOrgs, currentOrgId) {
  const $options = $orgSelect.find("option");
  const currentValue = $orgSelect.val();

  $options.each(function () {
    const $option = $(this);
    const optionValue = $option.val();

    //giữ lại option hiện tại và option disabled
    if (optionValue === "" || optionValue === currentOrgId) {
      $option.show();
    } else if (usedOrgs.includes(optionValue)) {
      $option.hide();
    } else {
      $option.show();
    }
  });

  //nếu organization hiện tại bị ẩn, reset về empty
  if (
    currentValue &&
    usedOrgs.includes(currentValue) &&
    currentValue !== currentOrgId
  ) {
    $orgSelect.val("");
  }
}

//function để lấy danh sách organization đã được sử dụng hiện tại
function getCurrentUsedOrganizations() {
  const usedOrgs = [];
  const roleCountByOrg = {};
  $(".organization-role-row").each(function () {
    const $row = $(this);
    const $orgSelect = $row.find('select[name="organizationId[]"]');
    const $roleSelect = $row.find('select[name="roleId[]"]');
    const currentOrgId = $orgSelect.val();

    if (currentOrgId) {
      //lấy tất cả role được chọn cho organization này
      const usedRoles = [];
      $(".organization-role-row").each(function () {
        const $otherRow = $(this);
        const $otherOrgSelect = $otherRow.find(
          'select[name="organizationId[]"]'
        );
        const $otherRoleSelect = $otherRow.find('select[name="roleId[]"]');

        if (
          $otherOrgSelect.val() === currentOrgId &&
          $otherRoleSelect.val() &&
          $otherRow[0] !== $row[0]
        ) {
          //tăng bộ đếm cho orgId
          roleCountByOrg[currentOrgId] =
            (roleCountByOrg[currentOrgId] || 0) + 1;
        }
      });
    }
  });
  //sau khi duyệt xong, lọc ra các org có value = 2 (2 role submitter/approver)
  for (const orgId in roleCountByOrg) {
    if (roleCountByOrg[orgId] === 2) {
      usedOrgs.push(orgId);
    }
  }
  return usedOrgs;
}

//function để cập nhập orgaization options cho tất cả rows
function updateAllOrganizationOptions() {
  const currentlyUsedOrgs = getCurrentUsedOrganizations();

  $(".organization-role-row").each(function () {
    const $row = $(this);
    const $orgSelect = $row.find('select[name="organizationId[]"]');
    const $roleSelect = $row.find('select[name="roleId[]"]');
    const currentOrgId = $orgSelect.val();

    //chỉ cập nhật nếu role không phải là accountant
    if ($roleSelect.val() !== "2") {
      updateOrganizationSelectOptions(
        $orgSelect,
        currentlyUsedOrgs,
        currentOrgId
      );
    }
  });
}

//event handlers
$(document).on("change", 'select[name="organizationId[]"]', function () {
  const $field = $(this);
  const $row = $field.closest(".organization-role-row");

  //clear error for this field
  clearFieldError($field);

  //update role options based on selected organization
  updateRoleOptions();

  //cập nhật organization options cho tất cả rows khác
  setTimeout(function () {
    updateAllOrganizationOptions();
  }, 100);

  //clear role selection if it's no longer required valid
  const $roleSelect = $row.find('select[name="roleId[]"]');
  if ($roleSelect.val()) {
    const currentOrgId = $field.val();
    const currentRoleId = $roleSelect.val();

    //check if current role is still available for this organization
    const usedRoles = [];
    $(".organization-role-row").each(function () {
      const $otherRow = $(this);
      const $otherOrgSelect = $otherRow.find('select[name="organizationId[]"]');
      const $otherRoleSelect = $otherRow.find('select[name="roleId[]"]');

      if (
        $otherOrgSelect.val() === currentOrgId &&
        $otherRoleSelect.val() &&
        $otherRow[0] !== $row[0]
      ) {
        usedRoles.push($otherRoleSelect.val());
      }
    });

    if (usedRoles.includes(currentRoleId)) {
      $roleSelect.val("");
    }
  }
});

$(document).on("change", 'select[name="roleId[]"]', function () {
  const $field = $(this);
  const $row = $field.closest(".organization-role-row");

  //clear error for this field
  clearFieldError($field);

  updateOrganizationOptions();

  //kiểm tra nếu chọn role là accountant
  if ($field.val() === "2") {
    //gọi hàm removeOtherRowsIfOnlyAccountant từ create.blade.php
    if (typeof removeOtherRowsIfOnlyAccountant === "function") {
      removeOtherRowsIfOnlyAccountant();

      //reset tất cả role options sau khi xóa các row
      setTimeout(function () {
        resetAllRoleOptions();
      }, 100);
    }
  } else {
    //nếu không phải accountant, cập nhật role options bình thường
    updateRoleOptions();

    //cập nhật organization options cho tất cả rows
    setTimeout(function () {
      updateAllOrganizationOptions();
    }, 100);
  }
});
