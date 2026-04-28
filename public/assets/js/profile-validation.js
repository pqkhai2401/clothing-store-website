/**
 * Profile Modal jQuery Validation
 * Handles form validation for user profile modal using jQuery Validate plugin
 */

const ProfileValidation = {
    validator: null,
    originalData: {},

    init() {
        this.setupCustomMethods();
        this.initValidation();
        this.setupEventListeners();
    },

    /**
     * Setup custom validation methods
     */
    setupCustomMethods() {
        // Phone number validation
        $.validator.addMethod('phoneNumber', function(value, element) {
            return this.optional(element) || /^[0-9]{1,20}$/.test(value);
        }, window.profileTranslations.phone);

        // Email validation
        $.validator.addMethod('emailUnique', function(value, element) {

            if (!value || !$.validator.methods.email.call(this, value, element)) {
                return true;
            }

            // Perform AJAX check
            let isValid = false;
            $.ajax({
                url: '/accountant/check-email',
                type: 'POST',
                async: false,
                data: {
                    email: value,
                    userId: window.currentUserId || null,
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                dataType: 'json',
                success: function(response) {
                    // Controller returns true if email is available, string message if exists
                    isValid = response === true;
                },
                error: function(xhr, status, error) {
                    isValid = true; // Allow on error to prevent blocking
                }
            });

            return isValid;
        }, 'Email này đã được sử dụng bởi người dùng khác. Vui lòng sử dụng email khác.');

        // Citizen ID validation
        $.validator.addMethod('citizenIdNumber', function(value, element) {
            return this.optional(element) || /^[0-9]{1,20}$/.test(value);
        }, window.profileTranslations.citizenId);

        // Birdthday validation
        $.validator.addMethod('birthdayBeforeToday', function(value, element){
            if(!value) return true; // Ignore if no value
            const inputDate = new Date(value);
            const today = new Date();

            // Remove time component
            inputDate.setHours(0,0,0,0);
            today.setHours(0,0,0,0);

            return inputDate < today;
        }, window.profileTranslations.birthday);

        // Granted date validation
        $.validator.addMethod('grantedDateBeforToday',function(value,element){
            if(!value) return true;
            const inputDate= new Date(value);
            const today=new Date();

            //Remove time component
            inputDate.setHours(0,0,0,0);
            today.setHours(0,0,0,0);

            return inputDate<=today;
        }, window.profileTranslations.citizenBeforeToday);

        // Granted date after birthday
        $.validator.addMethod('grantedDateAfterBirthday', function(value, element){
            const birthdayVal = $('#editBirthday').val();
            if(!birthdayVal || !value) return true;

            const birthdayDate = new Date(birthdayVal);
            const grantedDate = new Date(value);

            //Remove time component
            birthdayDate.setHours(0,0,0,0);
            grantedDate.setHours(0,0,0,0);

            return grantedDate>birthdayDate;
        },window.profileTranslations.citizenAfterBirthday);

        // Strong password validation
        $.validator.addMethod('strongPassword', function(value, element) {
            if (this.optional(element)) return true;
            
            const hasLowercase = /[a-z]/.test(value);
            const hasUppercase = /[A-Z]/.test(value);
            const hasDigit = /\d/.test(value);
            const hasSpecial = /[@$!%*?&]/.test(value);
            
            return hasLowercase && hasUppercase && hasDigit && hasSpecial;
        }, window.profileTranslations.passwordRegex);

        // Password confirmation validation
        $.validator.addMethod('passwordConfirmation', function(value, element) {
            const password = $('#editPassword').val();
            return this.optional(element) || value === password;
        }, 'Mật khẩu xác nhận không khớp');

        // File size validation (10MB)
        $.validator.addMethod('fileSize', function(value, element, param) {
            return this.optional(element) || (element.files[0] && element.files[0].size <= param * 1024 * 1024);
        }, 'Kích thước file không được vượt quá {0}MB');

        // Image file type validation
        $.validator.addMethod('imageType', function(value, element) {
            if (this.optional(element)) return true;
            const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
            return element.files[0] && allowedTypes.includes(element.files[0].type);
        }, 'Chỉ chấp nhận file ảnh (JPG, PNG, GIF)');
    },

    /**
     * Initialize form validation
     */
    initValidation() {
        const $form = $('#profileForm');
        if (!$form.length) return;

        this.validator = $form.validate({
            // Validation rules
            rules: {
                fullname: {
                    required: true,
                    minlength: 2,
                    maxlength: 255
                },
                email: {
                    required: true,
                    email: true,
                    maxlength: 255,
                    emailUnique:true
                },
                phone: {
                    required: true,
                    phoneNumber: true
                },
                citizenId: {
                    required: true,
                    citizenIdNumber: true
                },
                birthday: {
                    date: true,
                    birthdayBeforeToday: true
                },
                grantedDate: {
                    date: true,
                    grantedDateBeforToday:true,
                    grantedDateAfterBirthday:true
                },
                password: {
                    minlength: 6,
                    maxlength: 30,
                    strongPassword: true
                },
                password_confirmation: {
                    minlength: 6,
                    maxlength: 30,
                    passwordConfirmation: true
                },
                image: {
                    fileSize: 10,
                    imageType: true
                }
            },

            // Custom error messages
            messages: {
                fullname: {
                    required: 'Vui lòng nhập họ và tên',
                    minlength: 'Họ và tên phải có ít nhất 2 ký tự',
                    maxlength: 'Họ và tên không được vượt quá 255 ký tự'
                },
                email: {
                    required: 'Vui lòng nhập địa chỉ email',
                    email: 'Vui lòng nhập địa chỉ email hợp lệ',
                    maxlength: 'Email không được vượt quá 255 ký tự',
                    emailUnique: 'Email này đã được sử dụng.'
                },
                phone: {
                    required: 'Vui lòng nhập số điện thoại'
                },
                citizenId: {
                    required: 'Vui lòng nhập CCCD/CMND'
                },
                birthday: {
                    date: 'Vui lòng nhập ngày sinh hợp lệ'
                },
                grantedDate: {
                    date: 'Vui lòng nhập ngày cấp hợp lệ'
                },
                password: {
                    minlength: 'Mật khẩu phải có ít nhất 6 ký tự',
                    maxlength: 'Mật khẩu không được vượt quá 30 ký tự'
                },
                password_confirmation: {
                    minlength: 'Mật khẩu xác nhận phải có ít nhất 6 ký tự',
                    maxlength: 'Mật khẩu xác nhận không được vượt quá 30 ký tự'
                },
                image: {
                    fileSize: 'Kích thước ảnh không được vượt quá 10MB'
                }
            },

            // Error styling
            errorElement: 'div',
            errorClass: 'invalid-feedback',
            validClass: 'is-valid',

            // Error placement
            errorPlacement: function(error, element) {
                error.addClass('invalid-feedback d-block');
                
                if (element.parent('.input-group').length) {
                    error.insertAfter(element.parent());
                } else if (element.prop('type') === 'file') {
                    error.insertAfter(element.parent());
                } else {
                    error.insertAfter(element);
                }
            },

            // Highlight invalid fields
            highlight: function(element, errorClass, validClass) {
                $(element).addClass('is-invalid').removeClass('is-valid');
            },

            // Remove highlight from valid fields
            unhighlight: function(element, errorClass, validClass) {
                $(element).removeClass('is-invalid').addClass('is-valid');
            },

            // Real-time validation
            onkeyup: function(element) {
                if ($(element).hasClass('is-invalid') || $(element).hasClass('is-valid')) {
                    if($(element).attr('name')==='email'){
                        //Debounce email validation
                        clearTimeout(element.emailValidationTimeout);
                        element.emailValidationTimeout= setTimeout(()=>{
                            $(element).valid();
                        },800);//Wait 800ms after user stops typing
                    }else{
                        $(element).valid();
                    }
                }
            },

            onfocusout: function(element) {
                $(element).valid();
            },

            // Submit handler
            submitHandler: function(form) {
                ProfileValidation.saveProfile();
                return false; // Prevent default form submission
            }
        });
    },

    /**
     * Setup event listeners
     */
    setupEventListeners() {
        // Password field change - revalidate confirmation
        $('#editPassword').on('input', function() {
            const confirmField = $('#editPasswordConfirmation');
            if (confirmField.val()) {
                confirmField.valid();
            }
            ProfileValidation.checkForUnsavedChanges();
        });

        // Password confirmation field change
        $('#editPasswordConfirmation').on('input', function() {
            ProfileValidation.checkForUnsavedChanges();
        });

        // Image preview
        $('#profileImageInput').on('change', function() {
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    $('#profileImage').attr('src', e.target.result);
                };
                reader.readAsDataURL(this.files[0]);
            }
            ProfileValidation.checkForUnsavedChanges();
        });

        // Track changes for unsaved warning
        $('#profileForm input, #profileForm select, #profileForm textarea').on('input change', function() {
            ProfileValidation.checkForUnsavedChanges();
        });

        // Modal events
        $('#profileModal').on('shown.bs.modal', function() {
            // Note: storeOriginalData() is now called after data is populated in script.blade.php
            ProfileValidation.setupPasswordToggle();
        });

        $('#profileModal').on('hide.bs.modal', function(e) {
            if (ProfileValidation.hasUnsavedChanges()) {
                e.preventDefault();
                $('#unsavedChangesModal').modal('show');
            }
        });

        // Reset original data when modal is completely hidden
        $('#profileModal').on('hidden.bs.modal', function() {
            ProfileValidation.resetOriginalData();
        });

        // Unsaved changes modal buttons
        $('#discardChangesBtn').on('click', function() {
            ProfileValidation.discardChanges();
            $('#unsavedChangesModal').modal('hide');
            $('#profileModal').modal('hide');
        });

        $('#cancelProfileBtn').on('click', function() {
            if (ProfileValidation.hasUnsavedChanges()) {
                $('#unsavedChangesModal').modal('show');
            } else {
                $('#profileModal').modal('hide');
            }
        });
    },

    /**
     * Store original form data for comparison
     */
    storeOriginalData() {
        const form = document.getElementById('profileForm');
        if (!form) return;

        this.originalData = {};
        const formData = new FormData(form);
        for (let [key, value] of formData.entries()) {
            this.originalData[key] = value;
        }

        console.log('Original data stored:', this.originalData);
    },

    /**
     * Reset original form data
     */
    resetOriginalData() {
        this.originalData = {};
        console.log('Original data reset');
    },

    /**
     * Check if form has unsaved changes
     */
    hasUnsavedChanges() {
        const form = document.getElementById('profileForm');
        if (!form) return false;

        // If originalData is empty or not set, no changes can be detected
        if (!this.originalData || Object.keys(this.originalData).length === 0) {
            console.log('No original data to compare against');
            return false;
        }

        const currentData = {};
        const formData = new FormData(form);
        for (let [key, value] of formData.entries()) {
            currentData[key] = value;
        }

        const hasChanges = JSON.stringify(this.originalData) !== JSON.stringify(currentData);
        console.log('Unsaved changes check:', hasChanges, {
            original: this.originalData,
            current: currentData
        });

        return hasChanges;
    },

    /**
     * Check for unsaved changes and update UI
     */
    checkForUnsavedChanges() {
        // This method can be used to show/hide save button or other UI updates
        // Implementation depends on your UI requirements
    },

    /**
     * Discard changes and reset form
     */
    discardChanges() {
        const form = document.getElementById('profileForm');
        if (!form) return;

        // Reset form to original values
        Object.keys(this.originalData).forEach(key => {
            const field = form.querySelector(`[name="${key}"]`);
            if (field) {
                if(field.type==='file'){
                    field.value='';
                }else{
                    field.value = this.originalData[key];
                }
                
            }
        });

        // Reset validation state
        if (this.validator) {
            this.validator.resetForm();
            $('#profileForm .is-invalid, #profileForm .is-valid').removeClass('is-invalid is-valid');
        }
        const fileInput = form.querySelector('#profileImageInput');
        if(fileInput){
            fileInput.value = '';
            try{
                if(fileInput.files){
                    Object.defineProperty(fileInput,'files',{
                        value:null,
                        writable:true,
                        configurable:true
                    });
                }
            }catch(e){
                console.warn("Could not reset file input's files property");
            }
        }
    },

    /**
     * Setup password visibility toggle
     */
    setupPasswordToggle() {
        $('.toggle-password').off('click').on('click', function() {
            const targetId = $(this).prev('input').attr('id');
            const targetField = $('#' + targetId);
            const icon = $(this).find('i');
            
            if (targetField.attr('type') === 'password') {
                targetField.attr('type', 'text');
                icon.removeClass('fa-eye').addClass('fa-eye-slash');
            } else {
                targetField.attr('type', 'password');
                icon.removeClass('fa-eye-slash').addClass('fa-eye');
            }
        });
    },

    /**
     * Save profile using AJAX
     */
    saveProfile() {
        console.log('Saving profile with jQuery validation...');
        
        const form = document.getElementById('profileForm');
        if (!form) return;

        const formData = new FormData(form);
        const saveBtn = $('#saveProfileBtn');
        
        // Show loading state
        saveBtn.prop('disabled', true);
        saveBtn.html('<i class="fas fa-spinner fa-spin me-1"></i>' +window.profileTranslations.saving);

        // Clear previous messages
        $('#profileMessages').empty();

        // Make AJAX request
        fetch(window.isAccountantRoute ? '/accountant/profile/update' : '/profile', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'Cache-Control': 'no-cache, no-store, must-revalidate',
                'Pragma': 'no-cache',
                'Expires': '0'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                this.showMessage(profileTranslations.updateSuccess, 'success');
                this.storeOriginalData(); // Update original data
                
                // Update profile display if needed
                if (data.user) {
                    this.updateProfileDisplay(data.user);
                }else{
                    //Fallback: get data form form inputs
                    this.updateHeaderFromForm();
                }
                
                // Close modal after delay
                setTimeout(() => {
                    $('#profileModal').modal('hide');
                }, 1500);
            } else {
                this.showMessage(data.message || 'Có lỗi xảy ra khi cập nhật thông tin', 'danger');
                
                // Display validation errors
                if (data.errors) {
                    this.displayValidationErrors(data.errors);
                }
            }
        })
        .catch(error => {
            console.error('Error saving profile:', error);
            this.showMessage('Có lỗi xảy ra khi cập nhật thông tin: ' + error.message, 'danger');
        })
        .finally(() => {
            // Restore button state
            saveBtn.prop('disabled', false);
            saveBtn.html('<i class="fas fa-save me-1"></i>'+window.profileTranslations.save);
        });
    },

    //Fallback method
    updateHeaderFromForm(){
        const fullname = $('#editFullname').val();
        const imagePreview = $('#profileImage').attr('src');

        if(fullname){
            const headerName = $('#headerUserName');
            if(headerName.length > 0) {
                headerName.text(fullname);
                console.log('Header name updated from form:', fullname);
            }
        }

        if(imagePreview && imagePreview !== ''){
            // Add cache-busting if needed
            let imageUrl = imagePreview;
            if (imageUrl.includes('/storage/') && !imageUrl.includes('?v=')) {
                imageUrl += '?v=' + new Date().getTime();
            }

            const headerAvatar = $('#headerUserAvatar');
            if(headerAvatar.length > 0) {
                // Force reload by clearing src first
                headerAvatar.attr('src', '');
                setTimeout(() => {
                    headerAvatar.attr('src', imageUrl);
                    console.log('Header avatar updated from form with URL:', imageUrl);
                }, 10);
            }
        }
    },

    /**
     * Display validation errors from server
     */
    displayValidationErrors(errors) {
        Object.entries(errors).forEach(([field, messages]) => {
            const input = $(`[name="${field}"]`);
            if (input.length) {
                // Mark field as invalid
                input.addClass('is-invalid').removeClass('is-valid');
                
                // Show error message
                const errorDiv = $('<div class="invalid-feedback d-block"></div>');
                errorDiv.text(Array.isArray(messages) ? messages[0] : messages);
                
                // Remove existing error message
                input.siblings('.invalid-feedback').remove();
                
                // Add new error message
                if (input.parent('.input-group').length) {
                    errorDiv.insertAfter(input.parent());
                } else {
                    errorDiv.insertAfter(input);
                }
            }
        });
    },

    /**
     * Show message in modal
     */
    showMessage(message, type) {
        const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
        const icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-triangle';
        
        const alertHtml = `
            <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                <i class="fas ${icon} me-2"></i>${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        
        $('#profileMessages').html(alertHtml);
    },

    /**
     * Update profile display after successful save
     */
    updateProfileDisplay(user) {
        // Update profile image if changed
        if (user.image) {
            console.log('Updating image to:', user.image);

            // Ensure cache-busting for image URL if not already present
            let imageUrl = user.image;
            if (imageUrl && imageUrl.includes('/storage/') && !imageUrl.includes('?v=')) {
                imageUrl += '?v=' + new Date().getTime();
            }

            console.log('Final image URL with cache-busting:', imageUrl);

            // Update all profile images with force refresh
            $('.profile-avatar img').each(function() {
                $(this).attr('src', '');
                setTimeout(() => {
                    $(this).attr('src', imageUrl);
                }, 10);
            });

            //Update header avatar with force reload
            const headerAvatar = $('#headerUserAvatar');
            console.log('Header avatar element found: ', headerAvatar.length);
            if(headerAvatar.length > 0){
                // Force browser to reload image by setting src to empty first
                headerAvatar.attr('src', '');
                setTimeout(() => {
                    headerAvatar.attr('src', imageUrl);
                    console.log('Header avatar updated successfully with URL:', imageUrl);
                }, 10);
            } else {
                console.error('Header avatar element not found!');
            }
        }

        // Update profile name
        if (user.fullname) {
            console.log('Updating fullname to:', user.fullname);
            $('#profileFullname').text(user.fullname);
            $('.profile-name').text(user.fullname);

            // Debug header name update with force refresh
            const headerName = $('#headerUserName');
            console.log('Header name element found:', headerName.length);
            if (headerName.length > 0) {
                // Force DOM update by clearing and setting text
                headerName.text('');
                setTimeout(() => {
                    headerName.text(user.fullname);
                    console.log('Header name updated successfully:', user.fullname);
                }, 10);
            } else {
                console.error('Header name element not found!');
            }
        }
    }
};

// Initialize when document is ready
$(document).ready(function() {
    ProfileValidation.init();
});

// Make it globally available
window.ProfileValidation = ProfileValidation;
