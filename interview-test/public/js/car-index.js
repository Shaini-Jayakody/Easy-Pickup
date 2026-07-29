/**
 * Car Index Script
 * Handles DataTable initialization, filtering, and delete operations
 */

$(document).ready(function() {
    // ===== Initialize DataTable =====
    var table = $('#cars-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "/car",
            data: function (d) {
                d.brand_id = $('#brand-filter').val();
            }
        },
        columns: [
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
            { data: 'chassis_number', name: 'chassis_number' },
            { 
                data: 'action', 
                name: 'action', 
                orderable: false, 
                searchable: false 
            }
        ],
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

    // ===== Reload table if brand filter changes =====
    $('#brand-filter').on('change', function() {
        table.ajax.reload();
    });


    // DELETE FUNCTIONALITY

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

    // ===== Delete Car Handler =====
    $(document).on('click', '.delete-car', function() {
        var carId = $(this).data('id');
        var carName = $(this).data('name') || 'this car';
        var $button = $(this);
        var swal = getSwal();
        
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

    // ===== Delete Car AJAX Request =====
    function deleteCar(carId, $button) {
        // Disable button and show loading state
        var originalText = $button.text();
        $button.prop('disabled', true).html('<span class="spinner-border spinner-border-xs" role="status" aria-hidden="true"></span> Deleting...');
        
        $.ajax({
            url: '/car/' + carId + '/delete',
            type: 'DELETE',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    var swal = getSwal();
                    if (isSwalAvailable() && swal) {
                        swal.fire({
                            icon: 'success',
                            title: 'Deleted!',
                            text: response.message,
                            timer: 2000,
                            showConfirmButton: false,
                            timerProgressBar: true
                        });
                    } else {
                        alert('✅ ' + response.message);
                    }
                    // Reload DataTable
                    table.ajax.reload();
                }
            },
            error: function(xhr) {
                // Restore button
                $button.prop('disabled', false).text(originalText || 'Delete');
                
                var message = 'An error occurred while deleting.';
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
        });
    }

    console.log('Car index initialized successfully!');
});