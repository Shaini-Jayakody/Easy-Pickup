/**
 * Car Form Validation
 * Real-time validation with AJAX uniqueness checks
 */

var CarValidation = (function() {
    'use strict';

    // ===== Validation Rules =====
    var rules = {
        car_brand: {
            required: true,
            messages: { required: 'Please select a brand.' }
        },
        car_model: {
            required: true,
            messages: { required: 'Please select a model.' }
        },
        car_name: {
            required: true,
            min: 2,
            max: 255,
            messages: {
                required: 'Car name is required.',
                min: 'Car name must be at least 2 characters.',
                max: 'Car name cannot exceed 255 characters.'
            }
        },
      car_color: {
    required: true,
    min: 3,
    max: 100,
    pattern: /^[a-zA-Z\s]+$/,  // Only letters and spaces
    messages: {
        required: 'Car color is required.',
        min: 'Color must be at least 2 characters.',
        max: 'Color cannot exceed 100 characters.',
        pattern: 'Color should only contain letters and spaces.'
    }
},
        number_plate: {
            required: true,
            min: 2,
            max: 20,
            messages: {
                required: 'Number plate is required.',
                min: 'Number plate must be at least 2 characters.',
                max: 'Number plate cannot exceed 20 characters.',
                unique: 'This number plate already exists. Please enter a unique number plate.'
            }
        },
        eng_number: {
            required: true,
            min: 2,
            max: 50,
            messages: {
                required: 'Engine number is required.',
                min: 'Engine number must be at least 2 characters.',
                max: 'Engine number cannot exceed 50 characters.',
                unique: 'This engine number already exists. Please enter a unique engine number.'
            }
        },
        chas_number: {
            required: true,
            min: 2,
            max: 255,
            messages: {
                required: 'Chassis number is required.',
                min: 'Chassis number must be at least 2 characters.',
                max: 'Chassis number cannot exceed 255 characters.',
                unique: 'This chassis number already exists. Please enter a unique chassis number.'
            }
        },
        rent_price_per_hour: {
            required: true,
            min: 500,
            max: 100000,
            messages: {
                required: 'Rent price per hour is required.',
                min: 'Rent price must be at least Rs. 500.',
                max: 'Rent price is too high.'
            }
        },
        car_trans: {
            required: true,
            values: ['Auto', 'Manual', 'Tiptronic'],
            messages: {
                required: 'Please select transmission type.',
                values: 'Invalid transmission type selected.'
            }
        }
    };

    // ===== Field Validators =====
    var validators = {
        required: function(value) {
            return value !== '' && value !== null && value !== undefined;
        },
        min: function(value, rule) {
            return value.length >= rule;
        },
        max: function(value, rule) {
            return value.length <= rule;
        },
        values: function(value, rule) {
            return rule.indexOf(value) !== -1;
        },
        numeric: function(value) {
            return !isNaN(parseFloat(value)) && isFinite(value);
        },
        minNumber: function(value, rule) {
            return parseFloat(value) >= rule;
        },
        maxNumber: function(value, rule) {
            return parseFloat(value) <= rule;
        }
    };

    // ===== Field Names =====
    var fieldNames = {
        car_brand: 'Brand',
        car_model: 'Model',
        car_name: 'Car Name',
        car_color: 'Car Color',
        number_plate: 'Number Plate',
        eng_number: 'Engine Number',
        chas_number: 'Chassis Number',
        rent_price_per_hour: 'Rent Price (LKR)',
        car_trans: 'Transmission'
    };

    // ===== Main Validation Function =====
    function validateField(fieldName, value, $input, $error) {
        var rule = rules[fieldName];
        if (!rule) return true;

        // Required validation
        if (rule.required && !validators.required(value)) {
            showError($input, $error, rule.messages.required);
            return false;
        }

        // Pattern validation (for color field)
        if (rule.pattern && value.length > 0) {
            if (!rule.pattern.test(value)) {
               showError($input, $error, rule.messages.pattern);
             return false;
            }
        }
        
        // For string length validations (name, color, etc.)
        if (typeof value === 'string' && value.length > 0 && rule.min && typeof rule.min === 'number' && rule.min < 100) {
            if (!validators.min(value, rule.min)) {
                showError($input, $error, rule.messages.min);
                return false;
            }
        }

        if (typeof value === 'string' && value.length > 0 && rule.max && typeof rule.max === 'number' && rule.max < 100) {
            if (!validators.max(value, rule.max)) {
                showError($input, $error, rule.messages.max);
                return false;
            }
        }

        // Values validation (for dropdowns)
        if (value.length > 0 && rule.values && !validators.values(value, rule.values)) {
            showError($input, $error, rule.messages.values);
            return false;
        }

        // Rent price validation - NUMERIC 
        if (fieldName === 'rent_price_per_hour' && value.length > 0) {
            var numValue = parseFloat(value);
            
            if (!validators.numeric(value)) {
                showError($input, $error, 'Please enter a valid number.');
                return false;
            }
            
            if (!validators.minNumber(numValue, rule.min)) {
                showError($input, $error, rule.messages.min);
                return false;
            }
            
            if (!validators.maxNumber(numValue, rule.max)) {
                showError($input, $error, rule.messages.max);
                return false;
            }
        }

        // String length validation for fields with min/max (exclude rent_price which uses numeric validation)
        if (fieldName !== 'rent_price_per_hour' && typeof value === 'string' && value.length > 0) {
            if (rule.min && typeof rule.min === 'number' && rule.min < 100 && !validators.min(value, rule.min)) {
                showError($input, $error, rule.messages.min);
                return false;
            }
            if (rule.max && typeof rule.max === 'number' && rule.max < 100 && !validators.max(value, rule.max)) {
                showError($input, $error, rule.messages.max);
                return false;
            }
        }

        showSuccess($input, $error);
        return true;
    }

    // ===== Unique Checks (AJAX) =====
    function checkUniqueness(fieldName, value, $input, $error, url) {
        if (value.length === 0) return true;

        var result = true;
        $.ajax({
            url: url,
            type: "GET",
            data: { value: value },
            async: false,
            success: function(response) {
                if (response.exists) {
                    showError($input, $error, rules[fieldName].messages.unique);
                    result = false;
                } else {
                    showSuccess($input, $error);
                    result = true;
                }
            },
            error: function(xhr, status, error) {
                console.log('AJAX Error for', fieldName + ':', status, error);
                result = true;
            }
        });
        return result;
    }

    // ===== UI Helpers =====
    function showError($input, $error, message) {
        $error.text(message).show();
        $input.removeClass('is-valid').addClass('is-invalid');
        $input.closest('.form-group').find('.select2-container').addClass('is-invalid');
    }

    function showSuccess($input, $error) {
        $error.text('').hide();
        $input.removeClass('is-invalid').addClass('is-valid');
        $input.closest('.form-group').find('.select2-container').removeClass('is-invalid');
    }

    function clearValidation($input) {
        $input.removeClass('is-valid is-invalid');
        $input.closest('.form-group').find('.select2-container').removeClass('is-valid is-invalid');
        $input.closest('.form-group').find('.error-msg').text('').hide();
    }

    function clearAllValidation() {
        $('.form-control').each(function() {
            clearValidation($(this));
        });
        $('.select2').each(function() {
            $(this).closest('.form-group').find('.select2-container').removeClass('is-valid is-invalid');
        });
        $('.error-msg').text('').hide();
    }

    // ===== Public API =====
    return {
        validate: function(fieldName) {
            var $input = $('#' + fieldName);
            var value = $input.is('select') ? $input.val() : $input.val().trim();
            var $error = $('#' + fieldName + '-error');
            return validateField(fieldName, value, $input, $error);
        },

        validateAll: function() {
            var isValid = true;
            var fields = Object.keys(rules);
            for (var i = 0; i < fields.length; i++) {
                if (!this.validate(fields[i])) {
                    isValid = false;
                }
            }
            return isValid;
        },

        checkEngineUniqueness: function() {
            var $input = $('#eng_number');
            var value = $input.val().trim();
            var $error = $('#eng_number-error');
            if (value.length === 0) return true;
            return checkUniqueness('eng_number', value, $input, $error, '/car/check-engine');
        },

        checkChassisUniqueness: function() {
            var $input = $('#chas_number');
            var value = $input.val().trim();
            var $error = $('#chas_number-error');
            if (value.length === 0) return true;
            return checkUniqueness('chas_number', value, $input, $error, '/car/check-chassis');
        },

        checkNumberPlateUniqueness: function() {
            var $input = $('#number_plate');
            var value = $input.val().trim();
            var $error = $('#number_plate-error');
            if (value.length === 0) return true;
            return checkUniqueness('number_plate', value, $input, $error, '/car/check-numberplate');
        },

        clearAll: function() {
            clearAllValidation();
        },

        getRules: function() {
            return rules;
        },

        getFieldNames: function() {
            return fieldNames;
        },

        getFieldError: function(fieldName) {
            var $error = $('#' + fieldName + '-error');
            return $error.text();
        },

        isFieldValid: function(fieldName) {
            var $input = $('#' + fieldName);
            return $input.hasClass('is-valid');
        },

        getInvalidFields: function() {
            var invalidFields = [];
            var fields = Object.keys(rules);
            for (var i = 0; i < fields.length; i++) {
                if (!this.isFieldValid(fields[i])) {
                    invalidFields.push(fields[i]);
                }
            }
            return invalidFields;
        },

        focusFirstInvalid: function() {
            var invalidFields = this.getInvalidFields();
            if (invalidFields.length > 0) {
                var firstField = invalidFields[0];
                var $input = $('#' + firstField);
                if ($input.is('select')) {
                    $input.next('.select2-container').find('.select2-selection').focus();
                } else {
                    $input.focus();
                }
                $('html, body').animate({
                    scrollTop: $input.closest('.form-group').offset().top - 100
                }, 500);
            }
        }
    };
})();