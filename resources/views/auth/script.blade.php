<!-- Load jQuery first as it's required by Bootstrap and other libraries -->
<script src="{{ asset('assets/js/jquery.js') }}"></script>

<script src="{{ asset('assets/js/overlayscrollbars.browser.es6.min.js') }}"></script>

<script src="{{ asset('assets/js/popper.min.js') }}"></script>
<script src="{{ asset('assets/js/bootstrap.min.js') }}"></script>
<script src="{{ asset('assets/js/adminlte.js') }}"></script>
<script src="{{ asset('assets/js/Sortable.min.js') }}"></script>
<script src="{{ asset('assets/js/apexcharts.min.js') }}"></script>
<script src="{{ asset('assets/js/jsvectormap.min.js') }}"></script>
<script src="{{ asset('assets/js/world.js') }}"></script>
<script src="{{ asset('assets/js/url-validator.js') }}"></script>
<script src="{{ asset('assets/js/price-formatter.js') }}"></script>
<script src="{{ asset('assets/js/jquery-validation.js') }}"></script>
<script src="{{ asset('assets/js/ajax-pagination.js') }}"></script>
<script src="{{ asset('assets/js/modal/delete-modal.js') }}"></script>
<script src="{{ asset('assets/js/utils/ajax-utils.js') }}"></script>
<script src="{{ asset('assets/js/utils/notification-utils.js') }}"></script>
<script src="{{ asset('assets/js/helper/helper.js') }}"></script>

{{-- Temporarily disabled debug script --}}
{{-- <script src="{{ asset('assets/js/resend-modal-debug.js') }}"></script> --}}
<script>
    //end::Required Plugin(AdminLTE)--><!--begin::OverlayScrollbars Configure
    const SELECTOR_SIDEBAR_WRAPPER = '.sidebar-wrapper';
    const Default = {
        scrollbarTheme: 'os-theme-light',
        scrollbarAutoHide: 'leave',
        scrollbarClickScroll: true,
    };
    document.addEventListener('DOMContentLoaded', function() {
        const sidebarWrapper = document.querySelector(SELECTOR_SIDEBAR_WRAPPER);
        if (sidebarWrapper && typeof OverlayScrollbarsGlobal?.OverlayScrollbars !== 'undefined') {
            OverlayScrollbarsGlobal.OverlayScrollbars(sidebarWrapper, {
                scrollbars: {
                    theme: Default.scrollbarTheme,
                    autoHide: Default.scrollbarAutoHide,
                    clickScroll: Default.scrollbarClickScroll,
                },
            });
        }
    });

    //sortableassets/js
    const connectedSortables = document.querySelectorAll('.connectedSortable');
    connectedSortables.forEach((connectedSortable) => {
        let sortable = new Sortable(connectedSortable, {
            group: 'shared',
            handle: '.card-header',
        });
    });

    const cardHeaders = document.querySelectorAll('.connectedSortable .card-header');
    cardHeaders.forEach((cardHeader) => {
        cardHeader.style.cursor = 'move';
    });

    // Profile Modal Functionality
    document.addEventListener('DOMContentLoaded', function() {
        // Tìm phần tử modal và nút mở modal
        const profileModal = document.getElementById('profileModal');
        const profileModalTrigger = document.getElementById('profileModalTrigger');

        // Khởi tạo biến toàn cục
        window.profileRequestInProgress = false;
        window.currentProfileRequest = null;
        window.profileTimeout = null;
        //window.hasUnsavedChanges = false;
        //window.originalFormData = {};
        //window.isClosingModal = false;

        if (profileModal && profileModalTrigger) {
            // Xử lý sự kiện khi modal hiển thị
            profileModal.addEventListener('show.bs.modal', function(event) {
                console.log('Profile modal show event triggered');

                // Reset trạng thái modal và hiển thị loading
                resetProfileModalState();

                // Tải dữ liệu profile từ server
                fetchProfileData();
            });

            // Xử lý sự kiện khi modal đóng
            profileModal.addEventListener('hidden.bs.modal', function(event) {
                console.log('Profile modal hidden event triggered');

                // Hủy bỏ request đang chạy nếu có
                abortProfileRequest();

                // Reset trạng thái modal
                resetProfileModalState();

                // Reset unsaved changes tracking
                //window.hasUnsavedChanges = false;
                //window.originalFormData = {};
                //window.isClosingModal = false;
            });

           
        }

        // Setup unsaved changes confirmation modal
        //setupUnsavedChangesModal();
    });

    

    // Hàm reset trạng thái modal
    function resetProfileModalState() {
        console.log('Resetting profile modal state');

        // Hiển thị loading, ẩn nội dung và thông báo lỗi
        const profileLoading = document.getElementById('profileLoading');
        const profileContent = document.getElementById('profileContent');
        const profileError = document.getElementById('profileError');
        const profileMessages = document.getElementById('profileMessages');

        if (profileLoading) profileLoading.classList.remove('d-none');
        if (profileContent) profileContent.classList.add('d-none');
        if (profileError) profileError.classList.add('d-none');
        if (profileMessages) profileMessages.innerHTML = '';



        // Reset form nếu tồn tại
        const form = document.getElementById('profileForm');
        if (form) {
            form.reset();
            form.classList.remove('was-validated');

            // Reset các trường input
            const inputs = form.querySelectorAll('.form-control');
            inputs.forEach(input => {
                input.classList.remove('is-valid', 'is-invalid');
            });
        }

        // Reset ảnh đại diện
        const profileImage = document.getElementById('profileImage');
        if (profileImage) {
            profileImage.src = '{{ asset('assets/img/clone.png') }}';
        }
    }



    // Hàm hủy bỏ request đang chạy
    function abortProfileRequest() {
        if (window.currentProfileRequest) {
            console.log('Aborting current profile request');
            window.currentProfileRequest.abort();
            window.currentProfileRequest = null;
        }

        if (window.profileTimeout) {
            clearTimeout(window.profileTimeout);
            window.profileTimeout = null;
        }

        window.profileRequestInProgress = false;
    }

    // Hàm lấy dữ liệu profile từ server
    function fetchProfileData() {
        console.log('Fetching profile data');

        // Nếu đang có request thì không gửi request mới
        if (window.profileRequestInProgress) {
            console.log('Profile request already in progress, skipping');
            return;
        }

        // Đánh dấu đang có request
        window.profileRequestInProgress = true;

        // Hiển thị loading, ẩn nội dung và thông báo lỗi
        const profileLoading = document.getElementById('profileLoading');
        const profileContent = document.getElementById('profileContent');
        const profileError = document.getElementById('profileError');

        if (profileLoading) profileLoading.classList.remove('d-none');
        if (profileContent) profileContent.classList.add('d-none');
        if (profileError) profileError.classList.add('d-none');

        // Tạo AbortController để có thể hủy request
        const controller = new AbortController();
        window.currentProfileRequest = controller;

        // Thiết lập timeout để tránh loading vô hạn
        window.profileTimeout = setTimeout(() => {
            console.log('Profile request timed out');
            if (window.currentProfileRequest === controller) {
                controller.abort();
                showProfileError('Request timed out. Please try again.');
            }
        }, 15000);

        // Thêm tham số cache-busting để tránh cache
        const cacheBuster = new Date().getTime();
        const profileUrl = `/profile/me?_=${cacheBuster}`;

        // Gửi request lấy dữ liệu profile
        fetch(profileUrl, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ||
                        '',
                    'Cache-Control': 'no-cache, no-store, must-revalidate',
                    'Pragma': 'no-cache',
                    'Expires': '0'
                },
                signal: controller.signal,
                credentials: 'same-origin'
            })
            .then(response => {
                console.log('Profile response received, status:', response.status);
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                console.log('Profile data parsed successfully');
                if (data.success && data.data) {
                    populateProfileData(data.data);

                    // Hiển thị nội dung, ẩn loading
                    if (profileLoading) profileLoading.classList.add('d-none');
                    if (profileContent) profileContent.classList.remove('d-none');
                } else {
                    console.error('Invalid profile data received:', data);
                    showProfileError(data.message || 'Invalid profile data received');
                }
            })
            .catch(error => {
                // Không hiển thị lỗi nếu request bị hủy
                if (error.name === 'AbortError') {
                    console.log('Profile request was aborted');
                    return;
                }

                console.error('Error loading profile data:', error);

                // Hiển thị thông báo lỗi cụ thể
                let errorMessage = 'Network error occurred while loading profile data.';
                if (error.message.includes('Failed to fetch')) {
                    errorMessage = 'Unable to connect to server. Please check your internet connection.';
                } else if (error.message.includes('HTTP error')) {
                    errorMessage = 'Server error occurred. Please try again later.';
                }

                showProfileError(errorMessage);
            })
            .finally(() => {
                // Xóa timeout
                if (window.profileTimeout) {
                    clearTimeout(window.profileTimeout);
                    window.profileTimeout = null;
                }

                // Xóa tham chiếu đến request hiện tại
                if (window.currentProfileRequest === controller) {
                    window.currentProfileRequest = null;
                }

                // Đánh dấu không còn request đang chạy
                window.profileRequestInProgress = false;

                console.log('Profile data load completed');
            });
    }

    // Hàm điền dữ liệu vào form
    function populateProfileData(data) {
        console.log('Populating profile data...');

        try {
            // Lưu dữ liệu gốc để tham chiếu
            window.originalProfileData = data;

            // Tìm các phần tử trong DOM
            const profileImage = document.getElementById('profileImage');
            const profileFullname = document.getElementById('profileFullname');
            const form = document.getElementById('profileForm');

            // Kiểm tra xem các phần tử có tồn tại không
            if (!profileImage || !profileFullname || !form) {
                console.warn('Some profile elements not found, retrying in 200ms...');
                setTimeout(() => populateProfileData(data), 200);
                return;
            }

            // Cập nhật ảnh đại diện và thông tin cơ bản
            if (profileImage) {
                let imageUrl = data.image || '{{ asset('assets/img/clone.png') }}';
                // Thêm tham số cache-busting để đảm bảo tải ảnh mới
                if (data.image && data.image.includes('/storage/')) {
                    imageUrl += '?v=' + new Date().getTime();
                }
                profileImage.src = imageUrl;
                console.log('Profile image updated with URL:', imageUrl);
            }

            if (profileFullname) profileFullname.textContent = data.fullname || '-';

            // Điền dữ liệu vào form
            const formFields = {
                'editFullname': data.fullname || '',
                'editEmail': data.email || '',
                'editPhone': data.phone || '',
                'editCitizenId': data.citizenId || '',
                'editBirthday': data.birthday || '',
                'editGrantedDate': data.grantedDate || ''
            };

            // Điền từng trường vào form
            Object.entries(formFields).forEach(([fieldId, value]) => {
                const field = document.getElementById(fieldId);
                if (field) {
                    field.value = value;
                } else {
                    console.warn(`Form field ${fieldId} not found`);
                }
            });

            // Xóa trường mật khẩu
            const passwordField = document.getElementById('editPassword');
            const confirmPasswordField = document.getElementById('editPasswordConfirmation');
            if (passwordField) passwordField.value = '';
            if (confirmPasswordField) confirmPasswordField.value = '';

            // Store original form data for change detection
            //storeOriginalFormData();

            // Store original data for ProfileValidation (new system)
            if (window.ProfileValidation && typeof window.ProfileValidation.storeOriginalData === 'function') {
                window.ProfileValidation.storeOriginalData();
            }

            // Thiết lập sự kiện cho form (commented out as using new ProfileValidation system)
            //setupProfileFormValidation();

            console.log('Profile data populated successfully');
        } catch (error) {
            console.error('Error populating profile data:', error);
            showProfileError('Failed to populate profile data: ' + error.message);
        }
    }

    // Hàm thiết lập validation cho trường số (phone và citizen ID)
    function setupNumericFieldValidation() {
        const phoneField = document.getElementById('editPhone');
        const citizenIdField = document.getElementById('editCitizenId');

        // Setup validation for phone field
        if (phoneField) {
            setupNumericInputValidation(phoneField, 'phone number');
        }

        // Setup validation for citizen ID field
        if (citizenIdField) {
            setupNumericInputValidation(citizenIdField, 'citizen ID');
        }

        console.log('Numeric field validation setup complete');
    }

    // Hàm thiết lập validation cho một trường số cụ thể
    function setupNumericInputValidation(inputField, fieldType) {
        if (!inputField) return;

        // Prevent non-numeric characters on keypress
        inputField.addEventListener('keypress', function(e) {
            // Allow: backspace, delete, tab, escape, enter
            if ([8, 9, 27, 13, 46].indexOf(e.keyCode) !== -1 ||
                // Allow: Ctrl+A, Ctrl+C, Ctrl+V, Ctrl+X
                (e.keyCode === 65 && e.ctrlKey === true) ||
                (e.keyCode === 67 && e.ctrlKey === true) ||
                (e.keyCode === 86 && e.ctrlKey === true) ||
                (e.keyCode === 88 && e.ctrlKey === true)) {
                return;
            }

            // Ensure that it is a number and stop the keypress
            if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
                e.preventDefault();

                // Show brief visual feedback
                showNumericValidationFeedback(inputField, `Only numbers are allowed for ${fieldType}`);
            }
        });

        // Clean up any non-numeric characters on paste or input
        inputField.addEventListener('input', function(e) {
            const originalValue = e.target.value;
            const numericValue = originalValue.replace(/[^0-9]/g, '');

            if (originalValue !== numericValue) {
                e.target.value = numericValue;
                showNumericValidationFeedback(inputField, `Non-numeric characters removed from ${fieldType}`);
            }

            // Validate field length and format
            validateNumericField(inputField, fieldType);
        });

        // Additional validation on blur
        inputField.addEventListener('blur', function(e) {
            validateNumericField(inputField, fieldType);
        });

        console.log(`Numeric validation setup for ${fieldType} field`);
    }

    // Hàm validate trường số
    function validateNumericField(inputField, fieldType) {
        if (!inputField) return true;

        const value = inputField.value.trim();
        const isRequired = inputField.hasAttribute('required');
        let isValid = true;
        let errorMessage = '';

        // Clear previous validation states
        inputField.classList.remove('is-invalid', 'is-valid');

        // Check if required field is empty
        if (isRequired && !value) {
            isValid = false;
            errorMessage = `${fieldType.charAt(0).toUpperCase() + fieldType.slice(1)} is required`;
        }
        // If field has value, validate format and length
        else if (value) {
            // Check if value contains only numbers
            if (!/^[0-9]+$/.test(value)) {
                isValid = false;
                errorMessage = `${fieldType.charAt(0).toUpperCase() + fieldType.slice(1)} must contain only numbers`;
            }
            // Phone number specific validation
            else if (fieldType === 'phone number') {
                if (value.length < 10 || value.length > 11) {
                    isValid = false;
                    errorMessage = __('user.validation.phone.regex');
                }
            }
            // Citizen ID specific validation
            else if (fieldType === 'citizen ID') {
                if (value.length !== 12) {
                    isValid = false;
                    errorMessage = __('user.validation.citizen_id.regex');
                }
            }
        }

        // Apply validation styling
        if (!isValid) {
            inputField.classList.add('is-invalid');
            const invalidFeedback = getInvalidFeedback(inputField);
            if (invalidFeedback) {
                invalidFeedback.textContent = errorMessage;
                showElement(invalidFeedback);
            }
        } else if (value && isValid) {
            inputField.classList.add('is-valid');
            const invalidFeedback = getInvalidFeedback(inputField);
            if (invalidFeedback) {
                hideElement(invalidFeedback);
            }
        } else if (!value && !isRequired) {
            // Field is empty but not required - neutral state
            const invalidFeedback = getInvalidFeedback(inputField);
            if (invalidFeedback) {
                hideElement(invalidFeedback);
            }
        }

        return isValid;
    }

    // Hàm validate trường bắt buộc (cho các trường không phải số)
    function validateRequiredField(inputField) {
        if (!inputField || !inputField.hasAttribute('required')) return true;

        const value = inputField.value.trim();
        let isValid = true;
        let errorMessage = '';

        // Clear previous validation states
        inputField.classList.remove('is-invalid', 'is-valid');

        if (!value) {
            isValid = false;
            const fieldName = inputField.previousElementSibling?.textContent?.replace('*', '').trim() || 'This field';
            errorMessage = `${fieldName} is required`;
        }

        // Apply validation styling
        if (!isValid) {
            inputField.classList.add('is-invalid');
            const invalidFeedback = getInvalidFeedback(inputField);
            if (invalidFeedback) {
                invalidFeedback.textContent = errorMessage;
                showElement(invalidFeedback);
            }
        } else if (value) {
            inputField.classList.add('is-valid');
            const invalidFeedback = getInvalidFeedback(inputField);
            if (invalidFeedback) {
                hideElement(invalidFeedback);
            }
        }

        return isValid;
    }

    // Hàm kiểm tra tất cả các trường bắt buộc
    function validateAllRequiredFields() {
        const form = document.getElementById('profileForm');
        if (!form) return true;

        let allValid = true;
        const requiredFields = form.querySelectorAll('input[required]');

        requiredFields.forEach(field => {
            let fieldValid = true;

            // Use specific validation for numeric fields
            if (field.id === 'editPhone') {
                fieldValid = validateNumericField(field, 'phone number');
            } else if (field.id === 'editCitizenId') {
                fieldValid = validateNumericField(field, 'citizen ID');
            } else {
                fieldValid = validateRequiredField(field);
            }

            if (!fieldValid) {
                allValid = false;
            }
        });

        return allValid;
    }

    // Hàm hiển thị feedback tạm thời cho validation số
    function showNumericValidationFeedback(inputField, message) {
        // Create or update temporary feedback element
        let feedbackElement = inputField.parentNode.querySelector('.numeric-feedback');

        if (!feedbackElement) {
            feedbackElement = document.createElement('div');
            feedbackElement.className = 'numeric-feedback text-warning small mt-1';
            feedbackElement.style.display = 'none';
            inputField.parentNode.appendChild(feedbackElement);
        }

        feedbackElement.textContent = message;
        feedbackElement.style.display = 'block';

        // Auto-hide after 2 seconds
        setTimeout(() => {
            if (feedbackElement) {
                feedbackElement.style.display = 'none';
            }
        }, 2000);
    }

    // Hàm lấy phần tử hiển thị cảnh báo
    function getWarningFeedback(inputElement) {
        const parent = inputElement.closest('.input-group') || inputElement.parentNode;
        return parent.querySelector('.warning-feedback');
    }

    // Hàm lấy phần tử hiển thị lỗi
    function getInvalidFeedback(inputElement) {
        const parent = inputElement.closest('.input-group') || inputElement.parentNode;
        return parent.querySelector('.invalid-feedback');
    }

    // Hàm hiển thị phần tử
    function showElement(element) {
        if (element) {
            element.style.display = 'block';
        }
    }

    // Hàm ẩn phần tử
    function hideElement(element) {
        if (element) {
            element.style.display = 'none';
        }
    }

    // Hàm hiển thị lỗi validation
    function displayValidationErrors(errors) {
        Object.entries(errors).forEach(([field, messages]) => {
            const fieldName = field === 'password_confirmation' ? 'password_confirmation' : field;
            const input = document.querySelector(`[name="${fieldName}"]`);

            if (input) {
                // Thêm class lỗi
                input.classList.add('is-invalid');

                // Tìm phần tử hiển thị lỗi
                const feedback = getInvalidFeedback(input);
                if (feedback) {
                    feedback.textContent = messages[0];
                    showElement(feedback);
                }

                // Xử lý đặc biệt cho trường mật khẩu
                if (field === 'password') {
                    const warningFeedback = getWarningFeedback(input);
                    if (warningFeedback) {
                        hideElement(warningFeedback);
                    }
                }
            } else if (field === 'image') {
                // Hiển thị lỗi cho trường image
                showProfileMessage(`Image error: ${messages[0]}`, 'danger');
            }
        });
    }

    // Hàm xóa lỗi validation
    function clearValidationErrors() {
        const form = document.getElementById('profileForm');
        if (!form) return;

        // Xóa class lỗi và ẩn thông báo lỗi
        const inputs = form.querySelectorAll('.form-control');
        inputs.forEach(input => {
            input.classList.remove('is-invalid');

            const invalidFeedback = getInvalidFeedback(input);
            if (invalidFeedback) {
                hideElement(invalidFeedback);
            }
        });
    }

    // Hàm cập nhật thông tin người dùng trên header
    function updateHeaderUserInfo() {
        try {
            // Cập nhật avatar trên header
            const profileImage = document.getElementById('profileImage');
            const headerAvatar = document.querySelector('.user-menu .user-image');

            if (profileImage && headerAvatar) {
                headerAvatar.src = profileImage.src;
            }

            // Cập nhật tên người dùng trên header
            const fullnameInput = document.getElementById('editFullname');
            const headerFullname = document.querySelector('.user-menu .d-none.d-md-inline');

            if (fullnameInput && headerFullname) {
                headerFullname.textContent = fullnameInput.value;
            }
        } catch (error) {
            console.error('Error updating header user info:', error);
        }
    }

    // Hàm hiển thị thông báo lỗi
    function showProfileError(message) {
        console.log('Showing profile error:', message);

        const profileLoading = document.getElementById('profileLoading');
        const profileContent = document.getElementById('profileContent');
        const profileError = document.getElementById('profileError');
        const profileErrorMessage = document.getElementById('profileErrorMessage');

        // Ẩn loading và nội dung, hiển thị lỗi
        if (profileLoading) profileLoading.classList.add('d-none');
        if (profileContent) profileContent.classList.add('d-none');
        if (profileError) profileError.classList.remove('d-none');
        if (profileErrorMessage) profileErrorMessage.textContent = message;

        // Thiết lập sự kiện cho nút retry
        const retryBtn = document.querySelector('#profileError button');
        if (retryBtn) {
            retryBtn.onclick = function() {
                fetchProfileData();
            };
        }
    }

    // Hàm hiển thị thông báo
    function showProfileMessage(message, type) {
        const messagesContainer = document.getElementById('profileMessages');
        if (!messagesContainer) return;

        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
        alertDiv.innerHTML = `
            <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'} me-1"></i>${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;

        // Xóa thông báo cũ
        messagesContainer.innerHTML = '';
        messagesContainer.appendChild(alertDiv);

        // Tự động xóa sau 5 giây
        setTimeout(() => {
            if (alertDiv.parentNode) {
                alertDiv.remove();
            }
        }, 5000);
    }

</script>
