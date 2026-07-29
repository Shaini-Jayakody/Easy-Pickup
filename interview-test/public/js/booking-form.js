/**
 * Booking Form Script - Enhanced with Calendar Integration
 */

$(document).ready(function() {
    console.log('Booking form script loaded!');

    // ===== Get CSRF Token =====
    function getCsrfToken() {
        return $('meta[name="csrf-token"]').attr('content');
    }

    // ===== Update Car Details Display =====
    function updateCarDetails() {
        const $select = $('#car_id');
        const selectedOption = $select.find('option:selected');
        
        if (selectedOption.val()) {
            const name = selectedOption.data('name') || selectedOption.text().split(' - ')[0];
            const plate = selectedOption.data('plate') || '';
            const ref = selectedOption.data('ref') || '';
            const price = selectedOption.data('price') || 0;
            
            $('#display-car-name').text(name);
            $('#display-car-plate').text(plate);
            $('#display-car-ref').text(ref);
            $('#display-car-price').text('Rs. ' + parseFloat(price).toFixed(2));
            $('#car-details').fadeIn(300);
        } else {
            $('#car-details').fadeOut(300);
        }
        
        updateCostEstimate();
    }

    // ===== Calculate Duration and Cost =====
    function calculateDuration() {
        var start = $('#rental_start_date').val();
        var end = $('#rental_end_date').val();
        
        if (start && end) {
            var startDate = new Date(start);
            var endDate = new Date(end);
            
            if (endDate > startDate) {
                var diffMs = endDate - startDate;
                var diffHours = Math.round(diffMs / (1000 * 60 * 60));
                $('#duration-text').text(diffHours + ' hours');
                $('#duration-display').fadeIn(300);
                updateCostEstimate();
                return true;
            } else {
                $('#duration-display').fadeOut(300);
                return false;
            }
        }
        $('#duration-display').fadeOut(300);
        return false;
    }

    // ===== Update Cost Estimate =====
    function updateCostEstimate() {
        const start = $('#rental_start_date').val();
        const end = $('#rental_end_date').val();
        const pricePerHour = parseFloat($('#car_id option:selected').data('price') || 0);
        
        if (start && end && pricePerHour > 0) {
            const startDate = new Date(start);
            const endDate = new Date(end);
            
            if (endDate > startDate) {
                const diffMs = endDate - startDate;
                const diffHours = Math.round(diffMs / (1000 * 60 * 60));
                const totalCost = diffHours * pricePerHour;
                $('#cost-text').text('Rs. ' + totalCost.toFixed(2));
                $('#duration-display').fadeIn(300);
                return;
            }
        }
        
        if (pricePerHour > 0) {
            $('#cost-text').text('Rs. 0.00');
        }
    }

    function validateDateSelection() {
        var carId = $('#car_id').val();
        var start = $('#rental_start_date').val();
        var end = $('#rental_end_date').val();

        if (!carId || !start || !end) {
            return null;
        }

        var startDate = new Date(start);
        var endDate = new Date(end);
        var now = new Date();

        if (endDate <= startDate) {
            return 'Rental end date must be after the start date.';
        }

        if (startDate < now) {
            return 'Rental must start in the future.';
        }

        var diffHours = Math.round((endDate - startDate) / (1000 * 60 * 60));
        if (diffHours < 2) {
            return 'Rental duration must be at least 2 hours.';
        }

        var diffDays = Math.round((endDate - startDate) / (1000 * 60 * 60 * 24));
        if (diffDays > 30) {
            return 'Rental duration cannot exceed 1 month.';
        }

        return null;
    }

    // ===== Check Car Availability =====
    function checkAvailability() {
        var carId = $('#car_id').val();
        var start = $('#rental_start_date').val();
        var end = $('#rental_end_date').val();
        
        var $status = $('#availability-status');
        var validationError = validateDateSelection();

        if (validationError) {
            $status.show().removeClass('alert-success alert-info alert-warning').addClass('alert-danger');
            $status.html('❌ ' + validationError);
            updateSubmitButton();
            return;
        }
        
        if (carId && start && end) {
            $status.show().removeClass('alert-success alert-danger alert-warning').addClass('alert-info');
            $status.html('<span class="spinner-border spinner-border-sm" role="status"></span> Checking availability...');
            
            $.ajax({
                url: '/bookings/check-availability',
                type: 'GET',
                data: {
                    car_id: carId,
                    start_date: start,
                    end_date: end
                },
                success: function(response) {
                    if (response && typeof response.available !== 'undefined') {
                        if (response.available) {
                            $status.removeClass('alert-info alert-danger alert-warning').addClass('alert-success');
                            $status.html('✅ Car is available for selected dates');
                            $('#car_id').removeClass('is-invalid').addClass('is-valid');
                        } else {
                            $status.removeClass('alert-info alert-success alert-warning').addClass('alert-danger');
                            $status.html(response.message || '❌ Car is NOT available for selected dates');
                            $('#car_id').removeClass('is-valid').addClass('is-invalid');
                        }
                    } else {
                        $status.removeClass('alert-info alert-success').addClass('alert-warning');
                        $status.html('⚠️ Availability check could not be confirmed. You can still continue, but please verify before booking.');
                    }
                    updateSubmitButton();
                },
                error: function(xhr) {
                    var message = '⚠️ Availability check could not be confirmed. You can still continue, but please verify before booking.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        message = '⚠️ ' + xhr.responseJSON.message;
                    }
                    $status.removeClass('alert-info alert-success alert-danger').addClass('alert-warning');
                    $status.html(message);
                    updateSubmitButton();
                }
            });
        } else {
            $status.hide();
        }
    }

    // ===== Update Submit Button State =====
    function updateSubmitButton() {
        var $submitBtn = $('#submit-btn');
        var carId = $('#car_id').val();
        var start = $('#rental_start_date').val();
        var end = $('#rental_end_date').val();
        var hasError = false;
        
        // Check for visible error messages
        $('.error-msg').each(function() {
            if ($(this).is(':visible') && $(this).text().length > 0) {
                hasError = true;
                return false;
            }
        });
        
        // Check availability status
        var statusText = $('#availability-status').text();
        if (statusText.includes('NOT available')) {
            hasError = true;
        }
        
        // Check if date range is valid
        if (start && end) {
            var startDate = new Date(start);
            var endDate = new Date(end);
            if (endDate <= startDate) {
                hasError = true;
            }
        }

        if (statusText.includes('must be at least 2 hours') || statusText.includes('cannot exceed 1 month') || statusText.includes('must start in the future') || statusText.includes('must be after the start date')) {
            hasError = true;
        }
        
        if (carId && start && end && !hasError && !statusText.includes('NOT available')) {
            $submitBtn.prop('disabled', false);
            $submitBtn.css('opacity', '1');
            $submitBtn.css('cursor', 'pointer');
        } else {
            $submitBtn.prop('disabled', true);
            $submitBtn.css('opacity', '0.6');
            $submitBtn.css('cursor', 'not-allowed');
        }
    }

    // ===== Event Handlers =====
    $('#car_id').on('change', function() {
        updateCarDetails();
        checkAvailability();
        updateSubmitButton();
        
        // Update calendar
        const carId = $(this).val();
        if (window.bookingCalendar) {
            window.bookingCalendar.setCarId(carId);
        }
    });

    // Pre-select the car when the form is opened
    if ($('#car_id').val()) {
        updateCarDetails();
        checkAvailability();
        updateSubmitButton();
    }

    $('#rental_start_date, #rental_end_date').on('change', function() {
        calculateDuration();
        checkAvailability();
        updateSubmitButton();
        updateCostEstimate();
    });

    // ===== Set minimum date to now =====
    var now = new Date();
    var year = now.getFullYear();
    var month = String(now.getMonth() + 1).padStart(2, '0');
    var day = String(now.getDate()).padStart(2, '0');
    var hours = String(now.getHours()).padStart(2, '0');
    var minutes = String(now.getMinutes()).padStart(2, '0');
    var minDateTime = year + '-' + month + '-' + day + 'T' + hours + ':' + minutes;
    
    $('#rental_start_date').attr('min', minDateTime);
    
    // Set end date min when start changes
    $('#rental_start_date').on('change', function() {
        $('#rental_end_date').attr('min', $(this).val());
        calculateDuration();
        checkAvailability();
        updateSubmitButton();
        updateCostEstimate();
    });

    // ===== Form Submit =====
    $('#booking-form').on('submit', function(e) {
        e.preventDefault();
        
        $('#submit-text').hide();
        $('#submit-spinner').show();
        $('#submit-btn').prop('disabled', true);
        
        var formData = {
            car_id: $('#car_id').val(),
            user_id: $('#user_id').val(),
            rental_start_date: $('#rental_start_date').val(),
            rental_end_date: $('#rental_end_date').val(),
            notes: $('#notes').val(),
            _token: getCsrfToken()
        };
        
        $.ajax({
            url: "/bookings/store",
            type: "POST",
            data: formData,
            dataType: "json",
            success: function(response) {
                $('#submit-text').show();
                $('#submit-spinner').hide();
                $('#submit-btn').prop('disabled', false);
                
                if (response.success) {
                    var swal = getSwal();
                    if (isSwalAvailable() && swal) {
                        swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: response.message,
                            confirmButtonText: 'OK',
                            confirmButtonColor: '#3b82f6'
                        }).then(function() {
                            window.location.href = "/bookings";
                        });
                    } else {
                        alert('✅ ' + response.message);
                        window.location.href = "/bookings";
                    }
                }
            },
            error: function(xhr) {
                $('#submit-text').show();
                $('#submit-spinner').hide();
                $('#submit-btn').prop('disabled', false);
                
                if (xhr.status === 422 && xhr.responseJSON.errors) {
                    var errors = xhr.responseJSON.errors;
                    var errorMsg = typeof errors === 'string' ? errors : errors.join('\n');
                    
                    var swal = getSwal();
                    if (isSwalAvailable() && swal) {
                        swal.fire({
                            icon: 'error',
                            title: 'Validation Error',
                            text: errorMsg,
                            confirmButtonColor: '#ef4444'
                        });
                    } else {
                        alert('❌ ' + errorMsg);
                    }
                } else {
                    var message = 'An error occurred. Please try again.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        message = xhr.responseJSON.message;
                    }
                    
                    var swal = getSwal();
                    if (isSwalAvailable() && swal) {
                        swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: message,
                            confirmButtonColor: '#ef4444'
                        });
                    } else {
                        alert('❌ ' + message);
                    }
                }
            }
        });
    });

    // ===== Get SweetAlert instance =====
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

    console.log('Booking form initialized successfully!');
});