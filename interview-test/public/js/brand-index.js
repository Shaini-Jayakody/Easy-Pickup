/**
 * Brand Index Script
 * Handles brand CRUD operations
 */

$(document).ready(function() {
    console.log('Brand index script loaded!');

    // Get CSRF Token 
    function getCsrfToken() {
        return $('meta[name="csrf-token"]').attr('content');
    }

    // Delete Brand
    $(document).on('click', '.delete-brand', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        var id = $(this).data('id');
        var name = $(this).data('name');
        var $btn = $(this);
        var originalHtml = $btn.html();
        
        console.log('Delete button clicked - ID:', id, 'Name:', name);
        
        if (confirm('Are you sure you want to delete brand "' + name + '"?')) {
            // Show loading state
            $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-xs" role="status"></span>');
            
            $.ajax({
                url: '/car/brands/' + id + '/delete',
                type: 'DELETE', 
                data: {
                    _token: getCsrfToken()
                },
                dataType: 'json',
                success: function(response) {
                    console.log('Delete success:', response);
                    $btn.prop('disabled', false).html(originalHtml);
                    
                    if (response.success) {
                        var swal = getSwal();
                        if (isSwalAvailable() && swal) {
                            swal.fire({
                                icon: 'success',
                                title: 'Deleted!',
                                text: response.message || 'Brand deleted successfully.',
                                timer: 2000,
                                showConfirmButton: false,
                                timerProgressBar: true
                            });
                        } else {
                            alert('✅ ' + (response.message || 'Brand deleted successfully.'));
                        }
                        location.reload();
                    } else {
                        alert('❌ ' + (response.message || 'Error deleting brand.'));
                    }
                },
                error: function(xhr) {
                    console.error('Delete error:', xhr);
                    console.error('Status:', xhr.status);
                    console.error('Response:', xhr.responseText);
                    $btn.prop('disabled', false).html(originalHtml);
                    
                    var message = 'Error deleting brand.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        message = xhr.responseJSON.message;
                    }
                    alert('❌ ' + message);
                }
            });
        }
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

    console.log('Brand index initialized successfully!');
});