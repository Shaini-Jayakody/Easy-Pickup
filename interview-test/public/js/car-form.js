/**
 * Car Form Script
 * Handles form functionality: Select2, validation, AJAX submission
 */

$(document).ready(function() {
    console.log('Car form script loaded!');
    console.log('SweetAlert2 loaded:', typeof Swal !== 'undefined' || typeof window.Swal !== 'undefined');

    //Initialize Select2
    function initSelect2() {
        $('#car_brand').select2({
            placeholder: 'Search Brand...',
            allowClear: true,
            width: '100%'
        });

        $('#car_model').select2({
            placeholder: 'Search Model...',
            allowClear: true,
            width: '100%'
        });
    }

    // Filter models based on brand 
    function filterModelsByBrand() {
        $('#car_brand').on('change', function() {
            var brandId = $(this).val();
            var $modelSelect = $('#car_model');
            
            $modelSelect.val(null).trigger('change');
            
            $modelSelect.find('option').each(function() {
                var $option = $(this);
                var optionBrand = $option.data('brand');
                
                if (brandId === '' || optionBrand == brandId) {
                    $option.show();
                } else {
                    $option.hide();
                }
            });
            
            $modelSelect.trigger('change');
            CarValidation.validate('car_brand');
            updateSubmitButton();
        });
    }

    //Color Field - Allow Only Letters 
    function restrictColorInput() {
        $('#car_color').on('input', function() {
            var value = $(this).val();
            var cleaned = value.replace(/[^a-zA-Z\s]/g, '');
            if (value !== cleaned) {
                $(this).val(cleaned);
            }
            CarValidation.validate('car_color');
            updateSubmitButton();
        });

        $('#car_color').on('keydown', function(e) {
            if (e.key === 'Backspace' || e.key === 'Delete' || e.key === 'Tab' || 
                e.key === 'Escape' || e.key === 'Enter' || e.key === 'ArrowLeft' || 
                e.key === 'ArrowRight' || e.key === 'ArrowUp' || e.key === 'ArrowDown' ||
                e.key === 'Home' || e.key === 'End' || e.key === 'Shift') {
                return;
            }
            
            if (e.key >= '0' && e.key <= '9') {
                e.preventDefault();
                return false;
            }
        });
    }

    //Price Field
    function formatPrice() {
        $('#rent_price_per_hour').on('blur', function() {
            var value = $(this).val().trim();
            if (value.length > 0 && !isNaN(value) && value !== '') {
                var numValue = parseFloat(value);
                if (numValue >= 0) {
                    $(this).val(numValue.toFixed(2));
                    CarValidation.validate('rent_price_per_hour');
                    updateSubmitButton();
                }
            }
        });

        $('#rent_price_per_hour').on('input', function() {
            var value = $(this).val();
            value = value.replace(/[^0-9.]/g, '');
            var parts = value.split('.');
            if (parts.length > 2) {
                value = parts[0] + '.' + parts.slice(1).join('');
            }
            $(this).val(value);
            updateSubmitButton();
        });

        $('#rent_price_per_hour').on('keydown', function(e) {
            var value = $(this).val();
            if (e.key === '.' && value.indexOf('.') !== -1) {
                e.preventDefault();
                return false;
            }
        });
    }

    //Update Submit Button State
    function updateSubmitButton() {
        var $submitBtn = $('#submit-btn');
        var hasErrors = false;
        
        $('.error-msg').each(function() {
            var errorText = $(this).text().trim();
            if ($(this).is(':visible') && errorText.length > 0) {
                hasErrors = true;
                return false;
            }
        });
        
        if (!hasErrors) {
            $('.is-invalid').each(function() {
                var fieldId = $(this).attr('id');
                var $error = $('#' + fieldId + '-error');
                if ($error.length === 0 || $error.text().trim().length === 0) {
                    hasErrors = true;
                    return false;
                }
            });
        }
        
        if (hasErrors) {
            $submitBtn.prop('disabled', true);
            $submitBtn.css('opacity', '0.6');
            $submitBtn.css('cursor', 'not-allowed');
        } else {
            var allFilled = true;
            
            $('.form-control[required], .select2[required]').each(function() {
                var $this = $(this);
                var value = $this.val();
                
                if ($this.is('select') || $this.hasClass('select2')) {
                    if (!value || value === '' || value === null) {
                        allFilled = false;
                        return false;
                    }
                } else {
                    if (!value || value.trim() === '') {
                        allFilled = false;
                        return false;
                    }
                }
            });
            
            if (allFilled) {
                $submitBtn.prop('disabled', false);
                $submitBtn.css('opacity', '1');
                $submitBtn.css('cursor', 'pointer');
            } else {
                $submitBtn.prop('disabled', true);
                $submitBtn.css('opacity', '0.6');
                $submitBtn.css('cursor', 'not-allowed');
            }
        }
    }

    //Check if in Edit Mode
    function isEditMode() {
        return $('#car_id').length > 0 && $('#car_id').val() !== '';
    }

    // Get Form Action URL
    function getFormAction() {
        if (isEditMode()) {
            var carId = $('#car_id').val();
            return '/car/' + carId + '/update';
        }
        return '/car/save';
    }

    //Attach validation events
    function attachValidationEvents() {
        $('#car_brand').on('change', function() { 
            CarValidation.validate('car_brand');
            updateSubmitButton();
        });
        
        $('#car_model').on('change', function() { 
            CarValidation.validate('car_model');
            updateSubmitButton();
        });
        
        $('#car_name').on('input', function() { 
            CarValidation.validate('car_name');
            updateSubmitButton();
        });
        
        $('#number_plate').on('input', function() { 
            CarValidation.validate('number_plate');
            updateSubmitButton();
        }).on('blur', function() {
            var value = $(this).val().trim();
            if (value.length > 0) {
                CarValidation.checkNumberPlateUniqueness();
                updateSubmitButton();
            }
        });
        
        $('#eng_number').on('input', function() { 
            CarValidation.validate('eng_number');
            updateSubmitButton();
        }).on('blur', function() {
            var value = $(this).val().trim();
            if (value.length > 0) {
                CarValidation.checkEngineUniqueness();
                updateSubmitButton();
            }
        });
        
        $('#chas_number').on('input', function() { 
            CarValidation.validate('chas_number');
            updateSubmitButton();
        }).on('blur', function() {
            var value = $(this).val().trim();
            if (value.length > 0) {
                CarValidation.checkChassisUniqueness();
                updateSubmitButton();
            }
        });
        
        $('#car_trans').on('change', function() { 
            CarValidation.validate('car_trans');
            updateSubmitButton();
        });
    }

    //Check if SweetAlert is available
    function getSwal() {
        if (typeof Swal !== 'undefined') {
            return Swal;
        }
        if (typeof window.Swal !== 'undefined') {
            return window.Swal;
        }
        return null;
    }

    function isSwalAvailable() {
        return getSwal() !== null;
    }

    //Show SweetAlert Popup with Two Options
    function showSuccessPopup(message, redirectUrl) {
        var swal = getSwal();
        
        if (!swal) {
            alert('✅ Success!\n\n' + message + '\n\nRedirecting to car list...');
            window.location.href = redirectUrl;
            return;
        }
        
        swal.fire({
            icon: 'success',
            title: 'Success!',
            text: message,
            confirmButtonText: 'View Cars',
            showCancelButton: true,
            cancelButtonText: 'Stay Here',
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#6c757d',
            timer: 5000,
            timerProgressBar: true
        }).then(function(result) {
            if (result.isConfirmed) {
                window.location.href = redirectUrl;
            } else if (result.isDismissed && result.dismiss === swal.DismissReason.timer) {
                window.location.href = redirectUrl;
            }
        });
    }

    //Show Error Popup
    function showErrorPopup(message) {
        var swal = getSwal();
        
        if (!swal) {
            alert('❌ Error!\n\n' + message);
            return;
        }
        
        swal.fire({
            icon: 'error',
            title: 'Error!',
            text: message,
            confirmButtonText: 'OK',
            confirmButtonColor: '#dc3545'
        });
    }

    //Submit the form
    function submitForm() {
        console.log('Submitting form...');
        console.log('Is Edit Mode:', isEditMode());
        
        // Show loading state
        $('#submit-text').hide();
        $('#submit-spinner').show();
        $('#submit-btn').prop('disabled', true);
        
        // Prepare form data
        var formData = {
            car_brand: $('#car_brand').val(),
            car_model: $('#car_model').val(),
            car_name: $('#car_name').val().trim(),
            car_color: $('#car_color').val().trim(),
            number_plate: $('#number_plate').val().trim(),
            eng_number: $('#eng_number').val().trim(),
            chas_number: $('#chas_number').val().trim(),
            rent_price_per_hour: $('#rent_price_per_hour').val(),
            car_trans: $('#car_trans').val(),
            _token: $('input[name="_token"]').val()
        };
        
        // If editing, add _method for PUT
        var url = getFormAction();
        if (isEditMode()) {
            formData._method = 'PUT';
            formData.car_id = $('#car_id').val();
        }
        
        console.log('Form data:', formData);
        console.log('Submit URL:', url);
        
        // Send AJAX request
        $.ajax({
            url: url,
            type: "POST",
            data: formData,
            dataType: "json",
            success: function(response) {
                console.log('Success response:', response);
                $('#submit-text').show();
                $('#submit-spinner').hide();
                $('#submit-btn').prop('disabled', false);
                
                if (response.success) {
                    var action = isEditMode() ? 'updated' : 'added';
                    showSuccessPopup(
                        response.message || 'Car ' + action + ' successfully!',
                        '/car'
                    );
                    
                    // Reset form for create mode, but keep data for edit mode
                    if (!isEditMode()) {
                        $('#car-form')[0].reset();
                        CarValidation.clearAll();
                        $('#car_brand').val(null).trigger('change');
                        $('#car_model').val(null).trigger('change');
                    }
                    updateSubmitButton();
                }
            },
            error: function(xhr) {
                console.log('Error response:', xhr);
                $('#submit-text').show();
                $('#submit-spinner').hide();
                $('#submit-btn').prop('disabled', false);
                
                // Handle validation errors
                if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                    var errors = xhr.responseJSON.errors;
                    console.log('Validation errors:', errors);
                    
                    if (typeof errors === 'object') {
                        $.each(errors, function(field, messages) {
                            var $error = $('#' + field + '-error');
                            if ($error.length) {
                                $error.text(messages[0]).show();
                                $('#' + field).removeClass('is-valid').addClass('is-invalid');
                            }
                        });
                    }
                    
                    var firstError = typeof errors === 'string' ? errors : errors[Object.keys(errors)[0]][0];
                    showErrorPopup(firstError || 'Please fix the errors above.');
                    updateSubmitButton();
                } else if (xhr.status === 403) {
                    showErrorPopup(xhr.responseJSON ? xhr.responseJSON.message : 'You do not have permission.');
                } else {
                    var errorMsg = 'An error occurred. Please try again.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    }
                    showErrorPopup(errorMsg);
                }
            }
        });
    }

    //Handle form submission
    function handleFormSubmit() {
        $('#car-form').on('submit', function(e) {
            e.preventDefault();
            console.log('Form submit event triggered!');
            
            // Validate all fields
            var isValid = CarValidation.validateAll();
            console.log('Validation result:', isValid);
            
            if (!isValid) {
                CarValidation.focusFirstInvalid();
                updateSubmitButton();
                return;
            }
            
            // Check if any field has an error already
            var hasError = false;
            $('.error-msg').each(function() {
                if ($(this).is(':visible') && $(this).text().length > 0) {
                    hasError = true;
                }
            });
            
            if (hasError) {
                CarValidation.focusFirstInvalid();
                updateSubmitButton();
                return;
            }
            
            // Submit the form
            submitForm();
        });
    }

    //Initialize everything
    function init() {
        console.log('Initializing car form...');
        console.log('SweetAlert2 available:', isSwalAvailable());
        
        if ($('#car-form').length === 0) {
            console.log('Car form not found on this page');
            return;
        }
        
        console.log('Car form found, initializing...');
        console.log('Edit Mode:', isEditMode());
        console.log('Car ID:', $('#car_id').val());
        
        initSelect2();
        filterModelsByBrand();
        restrictColorInput(); 
        formatPrice();         
        attachValidationEvents();
        handleFormSubmit();
        
        // Initial check for submit button state
        updateSubmitButton();
        
        console.log('Car form initialized successfully!');
    }

    // Start the form
    init();
});