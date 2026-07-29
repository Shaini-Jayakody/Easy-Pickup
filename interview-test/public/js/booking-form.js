/**
 * Booking Form Script
 * Handles car selection, date validation, availability checking, and form submission
 */

$(document).ready(function() {
    console.log('Booking form script loaded!');

    // ===== Get CSRF Token =====
    function getCsrfToken() {
        return $('meta[name="csrf-token"]').attr('content');
    }

    // ===== Calculate Duration =====
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
                $('#duration-display').show();
                return true;
            } else {
                $('#duration-display').hide();
                return false;
            }
        }
        $('#duration-display').hide();
        return false;
    }

    // ===== Check Car Availability =====
    function checkAvailability() {
        var carId = $('#car_id').val();
        var start = $('#rental_start_date').val();
        var end = $('#rental_end_date').val();
        
        if (carId && start && end) {
            $.ajax({
                url: '/bookings/check-availability',
                type: 'GET',
                data: {
                    car_id: carId,
                    start_date: start,
                    end_date: end
                },
                success: function(response) {
                    if (response.available) {
                        $('#availability-status').html('<span style="color:green;">✅ Car is available for selected dates</span>');
                        $('#car_id').removeClass('is-invalid').addClass('is-valid');
                        updateSubmitButton();
                    } else {
                        $('#availability-status').html('<span style="color:red;">❌ Car is NOT available for selected dates</span>');
                        $('#car_id').removeClass('is-valid').addClass('is-invalid');
                        updateSubmitButton();
                    }
                },
                error: function() {
                    $('#availability-status').html('<span style="color:orange;">⚠️ Unable to check availability</span>');
                }
            });
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
        
        // Check if car is available (green text means available)
        var availabilityText = $('#availability-status').text();
        if (availabilityText.includes('NOT available')) {
            hasError = true;
        }
        
        if (carId && start && end && !hasError && !availabilityText.includes('NOT available')) {
            $submitBtn.prop('disabled', false);
            $submitBtn.css('opacity', '1');
            $submitBtn.css('cursor', 'pointer');
        } else {
            $submitBtn.prop('disabled', true);
            $submitBtn.css('opacity', '0.6');
            $submitBtn.css('cursor', 'not-allowed');
        }
    }

    // ===== Trigger availability check =====
    $('#car_id').on('change', function() {
        checkAvailability();
        updateSubmitButton();
    });

    // Pre-select the car when the form is opened from the car listing page
    if ($('#car_id').val()) {
        checkAvailability();
        updateSubmitButton();
    }

    $('#rental_start_date, #rental_end_date').on('change', function() {
        calculateDuration();
        checkAvailability();
        updateSubmitButton();
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
    });

    // ===== Form Submit =====
    $('#booking-form').on('submit', function(e) {
        e.preventDefault();
        
        $('#submit-text').hide();
        $('#submit-spinner').show();
        $('#submit-btn').prop('disabled', true);
        
        var formData = {
            car_id: $('#car_id').val(),
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
                            confirmButtonText: 'OK'
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
                            confirmButtonColor: '#dc3545'
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
                            confirmButtonColor: '#dc3545'
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