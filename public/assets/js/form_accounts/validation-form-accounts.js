// // $(document).ready(function(){
// //     //Custom validation method for positive numbers
// //     $.validator.addMethod("positiveNumber", function(value,element){
// //         return this.optional(element)||value>=0;
// //     },"Please enter a positive number");

// //     //Initialize form validation
// //     $("#formAccountForm").validate({
// //         // Enable validation on focus out
// //             onfocusout: function(element) {
// //                 $(element).valid();
// //             },
// //         //Validation rules
// //         rules:{
// //             accountNumber:{
// //                 required:true,
// //                 maxlength:50
// //             },
// //             accountName:{
// //                 required:true,
// //                 maxlength:255
// //             },
// //             initialBalance:{
// //                 required:true,
// //                 number:true,
// //                 positiveNumber:true
// //             },
// //             changeAmount:{
// //                 number:true
// //             },
// //             endingBalance:{
// //                 required:true,
// //                 number:true,
// //                 positiveNumber:true
// //             },
// //             categoryId:{
// //                 required:true
// //             }
// //         },
// //         //Custom error messages
// //         messages:{
// //             accountNumber:{
// //                 required:"Please enter account number",
// //                 maxlength: "Account number cannot exceed 50 characters"
// //             },
// //             accountName:{
// //                 required:"Please enter account name",
// //                 maxlength:"Account name cannot exceed 255 characters"
// //             },
// //             initialBalance:{
// //                 required: "Please enter initial balance",
// //                 number: "Please enter a valid number",
// //                 positiveNumber:"Initial balance must be positive"
// //             },
// //             changeAmout:{
// //                 number: "Please enter a valid number"
// //             },
// //             endingBalance:{
// //                 required:"Please enter ending balance",
// //                 number:"Please enter a valid number",
// //                 positiveNumber:"Ending balance must be positive"
// //             },
// //             categoryId:{
// //                 required: "Please select a category"
// //             }
// //         },
// //         //Error element configuration
// //         errorElement:'span',
// //         errorClass: 'invalid-feedback',
// //         errorPlacement: function(error, element){
// //             error.insertAfter(element);
// //         },
// //         highlight:function(element){
// //             $(element).addClass('is-invalid');
// //         },
// //         unhighlight:function(element){
// //             $(element).removeClass('is-invalid');
// //         },

// //         //Submit handler
// //         submitHandler:function(form){
// //             //Prevent double submission
// //             $("#saveFormAccountBtn").prop('disabled',true);

// //             //Get form data
// //             const formData= new FormData(form);
// //             const mode = $('#modalMode').val();
// //             const formAccountId= $('#formAccountId').val();

// //             //Set up AJAX request
// //             let url = mode = 'edit'?
// //             `/accountant/form-account/update/${formAccountId}`:
// //             `/accountant/form-account/store`;

// //             //If editing, append PUT method
// //             if(mode=='edit'){
// //                 formData.append('_method','PUT');
// //             }

// //             //Submit form via AJAX
// //             $.ajax({
// //                 url:url,
// //                 type:formData,
// //                 processData: false,
// //                 contentType:false,
// //                 success: function(response){
// //                     if(response.success){
// //                         //Hide modal
// //                         $('#formAccountModal').modal('hide');

// //                         //Show success message
// //                         showToast(response.message,'success');

// //                         //Refresh list
// //                         refreshFormAccountsList();
// //                     }else{
// //                         showToast(response.message||'Error occurred', 'error');
// //                     }
// //                 },
// //                 error: function(xhr){
// //                     let message = 'Error occurred';
// //                     if(xhr.response&& xhr.responseJSON.message){
// //                         message = xhr.responseJSON.message;
// //                     }
// //                     showToast(message,'error');
// //                 },
// //                 complete:function(){
// //                     $('#saveFormAccountBtn').prop('disabled',false);
// //                 }
// //             })
// //         }
// //     })
// // });

// //Helper function to refresh the list
// // function refreshFormAccountsList(){
// //     const currentUrl = new URL(window.location.href);
// //     currentUrl.searchParams.set('tab','form-accounts');
// //     loadFormAccountsPageWithAjax(currentUrl.toString());
// // }
// // function clearAllValidationErrors(){
// //     const form = document.getElementById('formAccountForm');
// //     if(form){
// //         // Clear all is-invalid classes
// //         form.querySelectorAll('.is-invalid').forEach(element=>{
// //             element.classList.remove('is-invalid');
// //         });

// //         // Hide all invalid-feedback messages
// //         form.querySelectorAll('.invalid-feedback').forEach(element=>{
// //             element.style.display='none';
// //             element.textContent='';
// //         });
// //     }
// // }

// // Export validation configuration function
// function initFormValidation(translations) {
//     return {
//         // Enable validation on focus out
//         onfocusout: function(element) {
//             $(element).valid();
//         },
//         // Define validation rules
//         rules: {
//             accountNumber: {
//                 required: true,
//                 maxlength: 50
//             },
//             accountName: {
//                 required: true,
//                 maxlength: 255
//             },
//             accountName_en: {
//                 required: true,
//                 maxlength: 255,
//                 minlength: 2,
//                 validEnglishName: true
//             },
//             initialBalance: {
//                 required: true,
//                 number: true,
//                 min: 0
//             },
//             changeAmount: {
//                 number: true
//             },
//             // endingBalance: {
//             //     required: true,
//             //     number: true,
//             //     min: 0
//             // },
//             categoryId: {
//                 required: true
//             }
//         },
//         // Use passed translations
//         messages: translations,
//         // Error element configuration
//         errorElement: 'span',
//         errorClass: 'invalid-feedback',
//         // Error placement
//         errorPlacement: function(error, element) {
//             error.insertAfter(element);
//         },
//         // Highlight error fields
//         highlight: function(element) {
//             $(element).addClass('is-invalid');
//         },
//         // Un-highlight valid fields
//         unhighlight: function(element) {
//             $(element).removeClass('is-invalid');
//         }
//     };
// }

// // Initialize validation when document is ready
// $(document).ready(function() {
//     const formAccountForm = $("#formAccountForm");
//     if (formAccountForm.length) {
//         // Default translations
//         const defaultTranslations = {
//             accountNumber: {
//                 required: "Please enter account number",
//                 maxlength: "Account number cannot exceed 50 characters"
//             },
//             accountName: {
//                 required: "Please enter account name",
//                 maxlength: "Account name cannot exceed 255 characters"
//             },
//             accountName_en: {
//                 required: "Please enter English name",
//                 maxlength: "English name cannot exceed 255 characters",
//                 minlength: "English name must be at least 2 characters",
//                 validEnglishName: "English name can only contain letters, numbers, spaces, hyphens, dots, commas, and parentheses"
//             },
                
//             initialBalance: {
//                 required: "Please enter initial balance",
//                 number: "Please enter a valid number",
//                 min: "Initial balance must be positive"
//             },
//             changeAmount: {
//                 number: "Please enter a valid number"
//             },
//             // endingBalance: {
//             //     required: "Please enter ending balance",
//             //     number: "Please enter a valid number",
//             //     min: "Ending balance must be positive"
//             // },
//             categoryId: {
//                 required: "Please select a category"
//             }
//         };

//         formAccountForm.validate(initFormValidation(defaultTranslations));
//     }
// });