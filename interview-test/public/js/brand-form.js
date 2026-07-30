/**
 * Brand Form Script
 * Handles brand create and edit
 */

$(document).ready(function() {
    console.log('Brand form script loaded!');

    // Get CSRF Token 
    function getCsrfToken() {
        return $('meta[name="csrf-token"]').attr('content');
    }

    // Submit Brand Form
    $('#brand-form').on('submit', function(e) {
        e.preventDefault();
        
        // Get the brand ID from hidden field
        var brandId = $('#brand_id').val();
        var isEdit = brandId && brandId !== '';
        var formData = $(this).serialize();
        
        // Build URL
        var url = isEdit ? '/car/brands/' + brandId + '/update' : '/car/brands/store';
        
        console.log('Submitting to:', url);
        console.log('Is Edit:', isEdit);
        console.log('Brand ID:', brandId);
        
        // Show loading state
        $('#submit-text').hide();
        $('#submit-spinner').show();
        $('#submit-btn').prop('disabled', true);
        $('.error-msg').text('').hide();
        
        $.ajax({
            url: url,
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                $('#submit-text').show();
                $('#submit-spinner').hide();
                $('#submit-btn').prop('disabled', false);
                
                if (response.success) {
                    var swal = getSwal();
                    if (isSwalAvailable() && swal) {
                        swal.fire({
                            icon: 'success',
                            title: isEdit ? 'Updated!' : 'Created!',
                            text: response.message,
                            confirmButtonText: 'OK',
                            confirmButtonColor: '#28a745'
                        }).then(function() {
                            window.location.href = '/car/brands';
                        });
                    } else {
                        alert('✅ ' + response.message);
                        window.location.href = '/car/brands';
                    }
                } else {
                    alert('❌ ' + (response.message || 'Something went wrong.'));
                }
            },
            error: function(xhr) {
                $('#submit-text').show();
                $('#submit-spinner').hide();
                $('#submit-btn').prop('disabled', false);
                
                console.log('Error response:', xhr);
                
                if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                    var errors = xhr.responseJSON.errors;
                    $('.error-msg').text('').hide();
                    $.each(errors, function(field, messages) {
                        $('#' + field + '-error').text(messages[0]).show();
                    });
                } else {
                    var message = xhr.responseJSON?.message || 'Error saving brand.';
                    
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

    // Get SweetAlert instance 
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

    console.log('Brand form initialized successfully!');
});