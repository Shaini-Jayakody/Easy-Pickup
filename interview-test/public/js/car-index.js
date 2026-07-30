/**
 * Car Index Script
 * Handles DataTable initialization, filtering, and delete operations
 */

$(document).ready(function() {
    console.log('Car index script loaded!');

    // Get CSRF Token
    function getCsrfToken() {
        return $('meta[name="csrf-token"]').attr('content');
    }

    // Check if user is admin/manager
    function isAdminUser() {
        return typeof isAdmin !== 'undefined' && isAdmin === true;
    }

    function isAuthenticatedUserFlag() {
        return typeof isAuthenticatedUser !== 'undefined' && isAuthenticatedUser === true;
    }

    // Get columns based on user role
    function getColumns() {
        var columns = [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'ref_no', name: 'ref_no' },
            { data: 'name', name: 'name' },
            { 
                data: 'color', 
                name: 'color',
                orderable: true,
                searchable: true
            },
            { data: 'model_name', name: 'model.name' },
            { data: 'brand', name: 'model.brand.name' },
            { 
                data: 'rent_price_per_hour', 
                name: 'rent_price_per_hour',
                render: function(data) {
                    if (data && data !== 'N/A') {
                        return data;
                    }
                    return 'N/A';
                }
            },
            { data: 'transmition', name: 'transmition' },
            { data: 'number_plate', name: 'number_plate' },
            { data: 'engine_number', name: 'engine_number' },
            { data: 'chassis_number', name: 'chassis_number' }
        ];
        
        // Add action column for authenticated users
        if (isAuthenticatedUserFlag()) {
            columns.push({ 
                data: 'action', 
                name: 'action', 
                orderable: false, 
                searchable: false,
                className: 'text-center',
                width: '80px'
            });
        }
        
        return columns;
    }

    //  Initialize DataTable 
    var table = $('#cars-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "/car",
            data: function (d) {
                d.brand_id = $('#brand-filter').val();
            },
            error: function(xhr, error, thrown) {
                console.log('DataTable error:', error);
                console.log('Status:', xhr.status);
                console.log('Response:', xhr.responseText);
            }
        },
        columns: getColumns(),
        pageLength: 10,
        responsive: true,
        language: {
            search: "Search:",
            lengthMenu: "Show _MENU_ entries",
            info: "Showing _START_ to _END_ of _TOTAL_ entries",
            infoEmpty: "No entries found",
            infoFiltered: "(filtered from _MAX_ total entries)",
        }
    });

    //Reload table if brand filter changes 
    $('#brand-filter').on('change', function() {
        table.ajax.reload();
    });


    // DELETE FUNCTIONALITY

    //Get SweetAlert instance 
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

  // Delete Car Handler
$(document).on('click', '.delete-car', function(e) {
    e.preventDefault();
    e.stopPropagation();
    
    // Check if button is disabled
    if ($(this).prop('disabled')) {
        var title = $(this).attr('title') || 'This car cannot be deleted because it has active bookings.';
        var swal = getSwal();
        if (isSwalAvailable() && swal) {
            swal.fire({
                icon: 'warning',
                title: 'Cannot Delete',
                text: title,
                confirmButtonColor: '#d9534f'
            });
        } else {
            alert('⚠️ ' + title);
        }
        return;
    }
    
    var carId = $(this).data('id');
    var carName = $(this).data('name') || 'this car';
    var $button = $(this);
    var swal = getSwal();
    
    console.log('Delete button clicked - ID:', carId, 'Name:', carName);
    
    if (isSwalAvailable() && swal) {
        swal.fire({
            title: 'Are you sure?',
            html: "You are about to delete <strong>" + carName + "</strong><br>This action cannot be undone!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel'
        }).then(function(result) {
            if (result.isConfirmed) {
                deleteCar(carId, $button);
            }
        });
    } else {
        if (confirm('Are you sure you want to delete "' + carName + '"?')) {
            deleteCar(carId, $button);
        }
    }
});
    //  Delete Car AJAX Request 
    function deleteCar(carId, $button) {
        console.log('Deleting car ID:', carId);
        
        // Save original content
        var originalHtml = $button.html();
        
        // Disable button and show loading state
        $button.prop('disabled', true).html('<span class="spinner-border spinner-border-xs" role="status" aria-hidden="true"></span>');
        
        var csrfToken = getCsrfToken();
        console.log('CSRF Token:', csrfToken);
        
        $.ajax({
            url: '/car/' + carId + '/delete',
            type: 'POST',
            data: {
                _token: csrfToken,
                _method: 'DELETE'
            },
            dataType: 'json',
            success: function(response) {
                console.log('Delete success:', response);
                
                // Restore button
                $button.prop('disabled', false).html(originalHtml);
                
                if (response.success) {
                    var swal = getSwal();
                    if (isSwalAvailable() && swal) {
                        swal.fire({
                            icon: 'success',
                            title: 'Deleted!',
                            text: response.message || 'Car deleted successfully.',
                            timer: 2000,
                            showConfirmButton: false,
                            timerProgressBar: true
                        });
                    } else {
                        alert('✅ ' + (response.message || 'Car deleted successfully.'));
                    }
                    // Reload DataTable
                    table.ajax.reload();
                } else {
                    var swal = getSwal();
                    var message = response.message || 'Failed to delete car.';
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
            },
            error: function(xhr) {
                console.error('Delete error:', xhr);
                console.error('Status:', xhr.status);
                console.error('Response:', xhr.responseText);
                
                // Restore button
                $button.prop('disabled', false).html(originalHtml);
                
                var message = 'An error occurred while deleting.';
                
                // Try to get error message from response
                try {
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        message = xhr.responseJSON.message;
                    } else if (xhr.responseText) {
                        var response = JSON.parse(xhr.responseText);
                        if (response.message) {
                            message = response.message;
                        }
                    }
                } catch (e) {
                    console.log('Could not parse error response');
                }
                
                // Check for specific error codes
                if (xhr.status === 403) {
                    message = 'You do not have permission to delete this car.';
                } else if (xhr.status === 404) {
                    message = 'Car not found.';
                } else if (xhr.status === 500) {
                    message = 'Server error. Please try again later.';
                } else if (xhr.status === 419) {
                    message = 'Session expired. Please refresh the page and try again.';
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
        });
    }

    console.log('Car index initialized successfully!');
    console.log('Is Admin User:', isAdminUser());
});