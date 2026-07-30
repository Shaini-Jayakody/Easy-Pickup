/**
 * Invoice Form Script
 * Handles booking selection, preview, and invoice generation
 */

$(document).ready(function() {
    console.log('Invoice form script loaded!');

    // ===== Get CSRF Token =====
    function getCsrfToken() {
        return $('meta[name="csrf-token"]').attr('content');
    }

    // ===== Update All Details When Booking Selected =====
    function updateBookingDetails() {
        const $select = $('#booking_id');
        const selectedOption = $select.find('option:selected');
        
        if (selectedOption.val()) {
            // Customer Details
            const userName = selectedOption.data('user') || '-';
            const userNic = selectedOption.data('nic') || '-';
            const userEmail = selectedOption.data('email') || '-';
            const bookingRef = selectedOption.data('ref') || '-';
            
            $('#display-customer-name').text(userName);
            $('#display-customer-nic').text(userNic);
            $('#display-customer-email').text(userEmail);
            $('#display-booking-ref').text(bookingRef);
            
            // Car Details
            const carName = selectedOption.data('car') || '-';
            const carRef = selectedOption.data('car-ref') || '-';
            const carPlate = selectedOption.data('plate') || '-';
            const price = selectedOption.data('price') || 0;
            const start = selectedOption.data('start') || '-';
            const end = selectedOption.data('end') || '-';
            
            $('#display-car-name').text(carName);
            $('#display-car-ref').text(carRef);
            $('#display-car-plate').text(carPlate);
            $('#display-price-per-hour').text('Rs. ' + parseFloat(price).toFixed(2));
            $('#display-rental-start').text(start ? new Date(start).toLocaleString() : '-');
            $('#display-rental-end').text(end ? new Date(end).toLocaleString() : '-');
            
            // Calculate Expected Hours
            let expectedHours = 0;
            if (start && end) {
                const startDate = new Date(start);
                const endDate = new Date(end);
                expectedHours = Math.round((endDate - startDate) / (1000 * 60 * 60));
            }
            $('#display-expected-hours').text(expectedHours + ' hrs');
            
            // Show panels
            $('#customer-details-panel').fadeIn(300);
            $('#car-details-panel').fadeIn(300);
            
            // Set min date for returned date
            if (start) {
                $('#returned_date').attr('min', start);
            }
            
            // Preview invoice
            previewInvoice();
        } else {
            // Hide panels
            $('#customer-details-panel').fadeOut(300);
            $('#car-details-panel').fadeOut(300);
            $('#invoice-preview').fadeOut(300);
        }
    }

    // ===== Preview Invoice =====
    function previewInvoice() {
        const bookingId = $('#booking_id').val();
        const returnedDate = $('#returned_date').val();
        
        if (!bookingId || !returnedDate) {
            $('#invoice-preview').fadeOut(300);
            updateSubmitButton();
            return;
        }
        
        $.ajax({
            url: '/invoices/preview',
            type: 'POST',
            data: {
                booking_id: bookingId,
                returned_date: returnedDate,
                _token: getCsrfToken()
            },
            success: function(response) {
                if (response.success) {
                    const d = response.details;
                    
                    // Hours
                    $('#preview-expected-hours').text(d.expected_hours);
                    $('#preview-actual-hours').text(d.actual_hours);
                    $('#preview-extra-hours').text(d.extra_hours);
                    
                    // Pricing
                    $('#preview-price').text(d.price_per_hour.toFixed(2));
                    $('#preview-extra-rate').text(d.extra_hour_rate.toFixed(2));
                    
                    // Costs
                    $('#preview-base-cost').text(d.base_cost.toFixed(2));
                    $('#preview-extra-cost').text(d.extra_cost.toFixed(2));
                    
                    // Discount - Hide if no discount
                    if (d.discount_percentage > 0) {
                        $('#discount-row').removeClass('hidden');
                        $('#preview-discount-label').text(d.discount_percentage + '% (' + d.discount_label + ')');
                        $('#preview-discount').text(d.discount_amount.toFixed(2));
                    } else {
                        $('#discount-row').addClass('hidden');
                    }
                    
                    // Fine - Hide if no fine
                    if (d.fine_amount > 0) {
                        $('#fine-row').removeClass('hidden');
                        $('#preview-fine').text(d.fine_amount.toFixed(2));
                    } else {
                        $('#fine-row').addClass('hidden');
                    }
                    
                    // Total
                    $('#preview-total').text(d.total_cost.toFixed(2));
                    
                    $('#invoice-preview').fadeIn(300);
                    updateSubmitButton();
                }
            },
            error: function(xhr) {
                $('#invoice-preview').fadeOut(300);
                updateSubmitButton();
            }
        });
    }

    // ===== Update Submit Button =====
    function updateSubmitButton() {
        const $submitBtn = $('#submit-btn');
        const bookingId = $('#booking_id').val();
        const returnedDate = $('#returned_date').val();
        const paymentMethod = $('#payment_method').val();
        
        if (bookingId && returnedDate && paymentMethod) {
            $submitBtn.prop('disabled', false);
            $submitBtn.css('opacity', '1');
            $submitBtn.css('cursor', 'pointer');
        } else {
            $submitBtn.prop('disabled', true);
            $submitBtn.css('opacity', '0.6');
            $submitBtn.css('cursor', 'not-allowed');
        }
    }

    // ===== Validate Form =====
    function validateForm() {
        const bookingId = $('#booking_id').val();
        const returnedDate = $('#returned_date').val();
        const paymentMethod = $('#payment_method').val();
        let isValid = true;
        let errors = [];

        // Clear previous errors
        $('.error-msg').hide().text('');

        if (!bookingId) {
            $('#booking_id-error').text('Please select a booking.').show();
            isValid = false;
        }

        if (!returnedDate) {
            $('#returned_date-error').text('Please enter the returned date and time.').show();
            isValid = false;
        }

        if (!paymentMethod) {
            $('#payment_method-error').text('Please select a payment method.').show();
            isValid = false;
        }

        return isValid;
    }

    // ===== Event Handlers =====
    $('#booking_id').on('change', function() {
        updateBookingDetails();
        updateSubmitButton();
        $('.error-msg').hide();
    });

    $('#returned_date').on('change', function() {
        previewInvoice();
        updateSubmitButton();
        $('#returned_date-error').hide();
    });

    $('#payment_method').on('change', function() {
        updateSubmitButton();
        $('#payment_method-error').hide();
    });

    // ===== Set default returned date =====
    const now = new Date();
    const year = now.getFullYear();
    const month = String(now.getMonth() + 1).padStart(2, '0');
    const day = String(now.getDate()).padStart(2, '0');
    const hours = String(now.getHours()).padStart(2, '0');
    const minutes = String(now.getMinutes()).padStart(2, '0');
    const defaultDateTime = year + '-' + month + '-' + day + 'T' + hours + ':' + minutes;
    $('#returned_date').attr('max', defaultDateTime);
    $('#returned_date').val(defaultDateTime);

    // ===== Form Submit =====
    $('#invoice-form').on('submit', function(e) {
        e.preventDefault();
        
        // Validate
        if (!validateForm()) {
            // Scroll to first error
            const firstError = $('.error-msg:visible').first();
            if (firstError.length) {
                $('html, body').animate({
                    scrollTop: firstError.closest('.form-group').offset().top - 100
                }, 500);
            }
            return;
        }
        
        $('#submit-text').hide();
        $('#submit-spinner').show();
        $('#submit-btn').prop('disabled', true);
        
        const formData = {
            booking_id: $('#booking_id').val(),
            returned_date: $('#returned_date').val(),
            payment_method: $('#payment_method').val(),
            notes: $('#notes').val(),
            _token: getCsrfToken()
        };
        
        $.ajax({
            url: '/invoices/store',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                $('#submit-text').show();
                $('#submit-spinner').hide();
                $('#submit-btn').prop('disabled', false);
                
                if (response.success) {
                    const swal = getSwal();
                    if (isSwalAvailable() && swal) {
                        swal.fire({
                            icon: 'success',
                            title: 'Invoice Generated!',
                            text: response.message,
                            confirmButtonText: 'OK',
                            confirmButtonColor: '#28a745'
                        }).then(function() {
                            window.location.href = '/invoices';
                        });
                    } else {
                        alert('✅ ' + response.message);
                        window.location.href = '/invoices';
                    }
                }
            },
            error: function(xhr) {
                $('#submit-text').show();
                $('#submit-spinner').hide();
                $('#submit-btn').prop('disabled', false);
                
                if (xhr.status === 422 && xhr.responseJSON.errors) {
                    const errors = xhr.responseJSON.errors;
                    const errorMsg = typeof errors === 'string' ? errors : errors.join('\n');
                    
                    // Show errors on fields
                    if (typeof errors === 'object') {
                        $.each(errors, function(field, messages) {
                            const $error = $('#' + field + '-error');
                            if ($error.length) {
                                $error.text(messages[0]).show();
                                $('#' + field).addClass('is-invalid');
                            }
                        });
                    }
                    
                    const swal = getSwal();
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
                    const message = xhr.responseJSON?.message || 'Error creating invoice. Please try again.';
                    
                    const swal = getSwal();
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

    // ===== Get SweetAlert =====
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

    console.log('Invoice form initialized successfully!');
});