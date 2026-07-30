/**
 * Invoice Form Script
 * Handles booking selection, preview, and invoice generation
 */

$(document).ready(function() {
    console.log('Invoice form script loaded!');

    function getCsrfToken() {
        return $('meta[name="csrf-token"]').attr('content');
    }

    function updateBookingDetails() {
        const $select = $('#booking_id');
        const selectedOption = $select.find('option:selected');
        
        if (selectedOption.val()) {
            // Customer Details
            $('#display-customer-name').text(selectedOption.data('user') || '-');
            $('#display-customer-nic').text(selectedOption.data('nic') || '-');
            $('#display-customer-email').text(selectedOption.data('email') || '-');
            $('#display-booking-ref').text(selectedOption.data('ref') || '-');
            
            // Car Details
            $('#display-car-name').text(selectedOption.data('car') || '-');
            $('#display-car-ref').text(selectedOption.data('car-ref') || '-');
            $('#display-car-plate').text(selectedOption.data('plate') || '-');
            
            // Get price from booking
            const price = parseFloat(selectedOption.data('price') || 0);
            $('#display-price-per-hour').text('Rs. ' + price.toFixed(2));
            
            const start = selectedOption.data('start');
            const end = selectedOption.data('end');
            $('#display-rental-start').text(start ? new Date(start).toLocaleString() : '-');
            $('#display-rental-end').text(end ? new Date(end).toLocaleString() : '-');
            
            let expectedHours = 0;
            if (start && end) {
                expectedHours = Math.ceil((new Date(end) - new Date(start)) / (1000 * 60 * 60));
            }
            $('#display-expected-hours').text(expectedHours + ' hrs');
            
            // Show panels
            $('#customer-details-panel').show();
            $('#car-details-panel').show();
            
            // Set min date for returned date (must be after start date)
            if (start) {
                const startDate = new Date(start);
                const formattedStart = startDate.getFullYear() + '-' + 
                    String(startDate.getMonth() + 1).padStart(2, '0') + '-' + 
                    String(startDate.getDate()).padStart(2, '0') + 'T' + 
                    String(startDate.getHours()).padStart(2, '0') + ':' + 
                    String(startDate.getMinutes()).padStart(2, '0');
                $('#returned_date').attr('min', formattedStart);
            }
            
            $('#returned_date').removeAttr('max');
            
            const now = new Date();
            const defaultDateTime = now.getFullYear() + '-' + 
                String(now.getMonth() + 1).padStart(2, '0') + '-' + 
                String(now.getDate()).padStart(2, '0') + 'T' + 
                String(now.getHours()).padStart(2, '0') + ':' + 
                String(now.getMinutes()).padStart(2, '0');
            $('#returned_date').val(defaultDateTime);
            
            $('#invoice-preview').show();
            
            previewInvoice();
        } else {
            $('#customer-details-panel').hide();
            $('#car-details-panel').hide();
            $('#invoice-preview').hide();
            $('#fine-panel').hide();
        }
    }

    function previewInvoice() {
        const bookingId = $('#booking_id').val();
        const returnedDate = $('#returned_date').val();
        
        if (!bookingId || !returnedDate) {
            $('#invoice-preview').show();
            $('#preview-expected-hours').text('0');
            $('#preview-actual-hours').text('0');
            $('#preview-extra-hours').text('0');
            $('#preview-price').text('0.00');
            $('#preview-extra-rate').text('0.00');
            $('#preview-base-cost').text('0.00');
            $('#preview-extra-cost').text('0.00');
            $('#preview-discount').text('0.00');
            $('#preview-fine').text('0.00');
            $('#preview-total').text('0.00');
            $('#discount-row').hide();
            $('#fine-panel').hide();
            updateSubmitButton();
            return;
        }
        
        $('#preview-total').text('Calculating...');
        
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
                    
                    console.log('Invoice Details:', d);
                    
                    // Hours Section
                    $('#preview-expected-hours').text(d.expected_hours);
                    $('#preview-actual-hours').text(d.actual_hours);
                    $('#preview-extra-hours').text(d.extra_hours);
                    
                    // Pricing Section
                    $('#preview-price').text(d.price_per_hour.toFixed(2));
                    $('#preview-extra-rate').text(d.extra_hour_rate.toFixed(2));
                    
                    // Cost Section
                    $('#preview-base-cost').text(d.base_cost.toFixed(2));
                    $('#preview-extra-cost').text(d.extra_cost.toFixed(2));
                    
                    // Discount - Only show if > 0
                    if (d.has_discount && d.discount_percentage > 0) {
                        $('#discount-row').show();
                        $('#preview-discount-label').text(d.discount_percentage + '% (' + d.discount_label + ')');
                        $('#preview-discount').text(d.discount_amount.toFixed(2));
                        console.log('Discount Applied:', d.discount_percentage + '% - ' + d.discount_label);
                    } else {
                        $('#discount-row').hide();
                        $('#preview-discount').text('0.00');
                        console.log('No Discount Applied - User has less than 5 completed bookings');
                    }
                    
                    // Fine - Always show (default 0)
                    $('#preview-fine').text('0.00');
                    $('#fine-reason-display').text('');
                    
                    // Reset fine fields
                    $('#fine_amount').val(0);
                    $('#fine_reason').val('');
                    $('#fine-panel').hide();
                    
                    // Total
                    $('#preview-total').text(d.total_cost.toFixed(2));
                    
                    $('#invoice-preview').show();
                    updateSubmitButton();
                } else {
                    $('#preview-total').text('Error');
                    updateSubmitButton();
                }
            },
            error: function(xhr) {
                console.error('Preview error:', xhr);
                $('#preview-total').text('Error loading data');
                updateSubmitButton();
            }
        });
    }

    function updateSubmitButton() {
        const bookingId = $('#booking_id').val();
        const returnedDate = $('#returned_date').val();
        const paymentMethod = $('#payment_method').val();
        $('#submit-btn').prop('disabled', !(bookingId && returnedDate && paymentMethod));
    }

    function recalculateTotalWithFine() {
        const baseCost = parseFloat($('#preview-base-cost').text()) || 0;
        const extraCost = parseFloat($('#preview-extra-cost').text()) || 0;
        const discount = parseFloat($('#preview-discount').text()) || 0;
        const fine = parseFloat($('#fine_amount').val()) || 0;
        
        const total = baseCost + extraCost - discount + fine;
        $('#preview-total').text(total.toFixed(2));
        $('#preview-fine').text(fine.toFixed(2));
        
        const fineReason = $('#fine_reason').val();
        if (fineReason) {
            $('#fine-reason-display').text(fineReason);
        } else {
            $('#fine-reason-display').text('');
        }
        
        // Show/hide fine panel
        if (parseFloat($('#fine_amount').val()) > 0 || $('#fine_reason').val()) {
            $('#fine-panel').show();
        } else {
            $('#fine-panel').hide();
        }
    }

   
    // EVENT HANDLERS
    
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

    // Fine amount changes - recalculate total and show panel
    $('#fine_amount').on('focus', function() {
        $('#fine-panel').show();
    });

    $('#fine_amount, #fine_reason').on('change keyup', function() {
        recalculateTotalWithFine();
        // Show panel if user is typing
        $('#fine-panel').show();
    });

    // Show fine panel button
    $('#show-fine-btn').on('click', function() {
        $('#fine-panel').toggle();
    });

  
    // SET DEFAULT RETURNED DATE

    const now = new Date();
    const defaultDateTime = now.getFullYear() + '-' + 
        String(now.getMonth() + 1).padStart(2, '0') + '-' + 
        String(now.getDate()).padStart(2, '0') + 'T' + 
        String(now.getHours()).padStart(2, '0') + ':' + 
        String(now.getMinutes()).padStart(2, '0');
    
    $('#returned_date').removeAttr('max');
    $('#returned_date').val(defaultDateTime);


    // AUTO-LOAD BOOKING IF ID IS PASSED
 
    if (window.selectedBookingId) {
        console.log('Pre-selecting booking ID:', window.selectedBookingId);
        $('#booking_id').val(window.selectedBookingId).trigger('change');
    }

    // FORM SUBMIT
   
    $('#invoice-form').on('submit', function(e) {
        e.preventDefault();
        
        let isValid = true;
        $('.error-msg').hide().text('');
        
        if (!$('#booking_id').val()) {
            $('#booking_id-error').text('Please select a booking.').show();
            isValid = false;
        }
        if (!$('#returned_date').val()) {
            $('#returned_date-error').text('Please enter the returned date and time.').show();
            isValid = false;
        }
        if (!$('#payment_method').val()) {
            $('#payment_method-error').text('Please select a payment method.').show();
            isValid = false;
        }
        
        if (!isValid) {
            $('.error-msg:visible').first().closest('.form-group').find('input, select').focus();
            return;
        }
        
        $('#submit-text').hide();
        $('#submit-spinner').show();
        $('#submit-btn').prop('disabled', true);
        
        // Get fine amount and reason
        const fineAmount = $('#fine_amount').val() || 0;
        const fineReason = $('#fine_reason').val() || null;
        
        console.log('Fine Amount:', fineAmount);
        console.log('Fine Reason:', fineReason);
        
        $.ajax({
            url: '/invoices/store',
            type: 'POST',
            data: {
                booking_id: $('#booking_id').val(),
                returned_date: $('#returned_date').val(),
                payment_method: $('#payment_method').val(),
                notes: $('#notes').val(),
                fine_amount: fineAmount,
                fine_reason: fineReason,
                _token: getCsrfToken()
            },
            dataType: 'json',
            success: function(response) {
                $('#submit-text').show();
                $('#submit-spinner').hide();
                $('#submit-btn').prop('disabled', false);
                
                if (response.success) {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
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
                    if (typeof errors === 'object') {
                        $.each(errors, function(field, messages) {
                            $('#' + field + '-error').text(messages[0]).show();
                            $('#' + field).addClass('is-invalid');
                        });
                    }
                    const errorMsg = typeof errors === 'string' ? errors : Object.values(errors).flat().join('\n');
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({ icon: 'error', title: 'Validation Error', text: errorMsg, confirmButtonColor: '#dc3545' });
                    } else {
                        alert('❌ ' + errorMsg);
                    }
                } else {
                    const message = xhr.responseJSON?.message || 'Error creating invoice. Please try again.';
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({ icon: 'error', title: 'Error!', text: message, confirmButtonColor: '#dc3545' });
                    } else {
                        alert('❌ ' + message);
                    }
                }
            }
        });
    });

   
    // INITIAL SETUP
   
    $('#invoice-preview').show();
    $('#preview-expected-hours').text('0');
    $('#preview-actual-hours').text('0');
    $('#preview-extra-hours').text('0');
    $('#preview-price').text('0.00');
    $('#preview-extra-rate').text('0.00');
    $('#preview-base-cost').text('0.00');
    $('#preview-extra-cost').text('0.00');
    $('#preview-discount').text('0.00');
    $('#preview-fine').text('0.00');
    $('#preview-total').text('0.00');
    $('#discount-row').hide();
    $('#fine-panel').hide();

    console.log('Invoice form initialized successfully!');
});