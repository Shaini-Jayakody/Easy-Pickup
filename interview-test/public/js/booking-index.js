/**
 * Booking Index Script
 * Handles DataTable initialization, booking actions, and status management
 */

$(document).ready(function() {
    // ===== Get CSRF Token =====
    function getCsrfToken() {
        return $('meta[name="csrf-token"]').attr('content');
    }

    // ===== Initialize DataTable =====
    var table = $('#bookings-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "/bookings",
            data: function (d) {
                d.status = $('#status-filter').val();
            },
            error: function(xhr, error, thrown) {
                console.log('DataTable error:', error);
                console.log('Status:', xhr.status);
                console.log('Response:', xhr.responseText);
                $('#bookings-table tbody').html('<tr><td colspan="11" class="text-center text-danger">Error loading data. Please refresh the page.</td></tr>');
            }
        },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'booking_ref_no', name: 'booking_ref_no' },
            { data: 'user_name', name: 'user.name' },
            { data: 'user_nic', name: 'user.id_num' },
            { data: 'car_name', name: 'car.name' },
            { data: 'car_plate', name: 'car.number_plate' },
            { 
                data: 'rental_start_date', 
                name: 'rental_start_date',
                render: function(data) {
                    if (!data) return 'N/A';
                    var date = new Date(data);
                    return '<span class="date-display">' + date.toLocaleDateString() + '<br><small>' + date.toLocaleTimeString() + '</small></span>';
                }
            },
            { 
                data: 'rental_end_date', 
                name: 'rental_end_date',
                render: function(data) {
                    if (!data) return 'N/A';
                    var date = new Date(data);
                    return '<span class="date-display">' + date.toLocaleDateString() + '<br><small>' + date.toLocaleTimeString() + '</small></span>';
                }
            },
            { 
                data: 'duration', 
                name: 'duration', 
                orderable: false, 
                searchable: false,
                render: function(data) {
                    return data + ' hrs';
                }
            },
            { 
                data: 'status', 
                name: 'status', 
                orderable: false, 
                searchable: false,
            },
            { 
                data: 'action', 
                name: 'action', 
                orderable: false, 
                searchable: false,
                className: 'text-center'
            }
        ],
        pageLength: 10,
        responsive: true,
        order: [[6, 'desc']],
        language: {
            search: "Search:",
            lengthMenu: "Show _MENU_ entries",
            info: "Showing _START_ to _END_ of _TOTAL_ entries",
            infoEmpty: "No entries found",
            infoFiltered: "(filtered from _MAX_ total entries)",
            zeroRecords: "No matching bookings found"
        },
        // Draw callback to initialize dropdowns after each draw
        drawCallback: function() {
            initializeStatusDropdowns();
        }
    });

    // ===== Reload table when filters change =====
    $(document).on('change', '#status-filter', function() {
        table.ajax.reload();
    });

    // ===== Clear filters =====
    $(document).on('click', '#clear-filters', function() {
        $('#status-filter').val('').trigger('change');
        table.ajax.reload();
    });

    // ============================================
    // INITIALIZE STATUS DROPDOWNS (Admin/Manager)
    // ============================================

    function initializeStatusDropdowns() {
        $('.status-dropdown').off('change').on('change', function() {
            var $select = $(this);
            var bookingId = $select.data('booking-id');
            var newStatus = $select.val();
            var selectedText = $select.find('option:selected').text();
            
            // Show loading state
            $select.prop('disabled', true);
            
            // Get the status name for confirmation
            var statusText = selectedText.replace('→ ', '').trim();
            
            var swal = getSwal();
            if (isSwalAvailable() && swal) {
                swal.fire({
                    title: 'Change Status?',
                    text: 'Are you sure you want to change this booking to "' + statusText + '"?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#3b82f6',
                    cancelButtonColor: '#d9534f',
                    confirmButtonText: 'Yes, change it!',
                    cancelButtonText: 'Cancel'
                }).then(function(result) {
                    if (result.isConfirmed) {
                        performStatusDropdownUpdate(bookingId, newStatus, $select);
                    } else {
                        // Revert dropdown - reload table to reset
                        table.ajax.reload();
                    }
                });
            } else {
                if (confirm('Are you sure you want to change this booking to "' + statusText + '"?')) {
                    performStatusDropdownUpdate(bookingId, newStatus, $select);
                } else {
                    // Revert dropdown - reload table to reset
                    table.ajax.reload();
                }
            }
        });
    }

    // ===== Perform Status Update from Dropdown =====
    function performStatusDropdownUpdate(bookingId, newStatus, $select) {
        $.ajax({
            url: '/bookings/' + bookingId + '/status-dropdown',
            type: 'PUT',
            data: {
                _token: getCsrfToken(),
                status: newStatus
            },
            success: function(response) {
                if (response.success) {
                    var swal = getSwal();
                    if (isSwalAvailable() && swal) {
                        swal.fire({
                            icon: 'success',
                            title: 'Updated!',
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
                } else {
                    alert('❌ ' + (response.message || 'Error updating status.'));
                    table.ajax.reload();
                }
            },
            error: function(xhr) {
                var message = 'Error updating status.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }
                alert('❌ ' + message);
                // Reload table to reset dropdown
                table.ajax.reload();
            }
        });
    }

    // ============================================
    // BOOKING DETAILS MODAL
    // ============================================

    $(document).on('click', '.view-booking', function(e) {
        e.preventDefault();
        var id = $(this).data('id');
        
        // Show loading state
        $('#booking-details').html('<div class="text-center"><span class="spinner-border" role="status"></span> Loading...</div>');
        $('#bookingModal').modal('show');
        
        $.ajax({
            url: '/bookings/' + id,
            type: 'GET',
            success: function(response) {
                var booking = response.booking;
                var html = `
                    <div class="row">
                        <div class="col-md-6">
                            <h4><span class="glyphicon glyphicon-user"></span> Customer Details</h4>
                            <p><strong>Name:</strong> ${booking.user?.name || 'N/A'}</p>
                            <p><strong>NIC:</strong> ${booking.user?.id_num || booking.user?.nic || 'N/A'}</p>
                            <p><strong>Email:</strong> ${booking.user?.email || 'N/A'}</p>
                        </div>
                        <div class="col-md-6">
                            <h4><span class="glyphicon glyphicon-car"></span> Car Details</h4>
                            <p><strong>Car:</strong> ${booking.car?.name || 'N/A'}</p>
                            <p><strong>Plate No:</strong> ${booking.car?.number_plate || booking.car?.plate_no || 'N/A'}</p>
                            <p><strong>Ref No:</strong> ${booking.car?.ref_no || 'N/A'}</p>
                            <p><strong>Price/Hour:</strong> ${booking.car?.price_per_hour ? 'Rs. ' + booking.car.price_per_hour : 'N/A'}</p>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-md-4">
                            <h4><span class="glyphicon glyphicon-calendar"></span> Rental Period</h4>
                            <p><strong>Start:</strong> ${booking.rental_start_date ? new Date(booking.rental_start_date).toLocaleString() : 'N/A'}</p>
                            <p><strong>End:</strong> ${booking.rental_end_date ? new Date(booking.rental_end_date).toLocaleString() : 'N/A'}</p>
                            <p><strong>Duration:</strong> ${(booking.duration_in_hours || booking.duration || 0)} hours</p>
                        </div>
                        <div class="col-md-4">
                            <h4><span class="glyphicon glyphicon-info-sign"></span> Status</h4>
                            <p><span class="label-status label-${booking.status}">${booking.status_text}</span></p>
                            <p><strong>Booking Ref:</strong> ${booking.booking_ref_no || booking.ref_no || 'N/A'}</p>
                            <p><strong>Created:</strong> ${booking.created_at ? new Date(booking.created_at).toLocaleString() : 'N/A'}</p>
                        </div>
                        <div class="col-md-4">
                            <h4><span class="glyphicon glyphicon-pencil"></span> Notes</h4>
                            <p>${booking.notes || '<em>No notes</em>'}</p>
                        </div>
                    </div>
                `;
                $('#booking-details').html(html);
            },
            error: function(xhr) {
                $('#booking-details').html('<div class="alert alert-danger">Error loading booking details. Please try again.</div>');
                console.error(xhr);
            }
        });
    });

    // ============================================
    // CANCEL BOOKING (User)
    // ============================================

    var cancelBookingId = null;
    var cancelRefNo = null;

    // ===== Click handler for cancel button =====
    $(document).on('click', '.cancel-booking', function(e) {
        e.preventDefault();
        
        var id = $(this).data('id');
        var refNo = $(this).data('ref') || 'this booking';
        
        console.log('Cancel button clicked for booking:', id, refNo);
        
        // Store booking details for modal
        cancelBookingId = id;
        cancelRefNo = refNo;
        
        // Update modal
        $('#cancel-ref-no').text(refNo);
        $('#cancel-error').hide();
        $('#cancel-text').show();
        $('#cancel-spinner').hide();
        $('#confirm-cancel-btn').prop('disabled', false);
        
        // Show modal
        $('#cancelModal').modal('show');
    });

    // ===== Confirm cancel button =====
    $(document).on('click', '#confirm-cancel-btn', function() {
        if (!cancelBookingId) {
            console.error('No booking ID to cancel');
            return;
        }
        
        var $btn = $(this);
        var id = cancelBookingId;
        
        console.log('Confirming cancellation for booking:', id);
        
        // Show loading state
        $('#cancel-text').hide();
        $('#cancel-spinner').show();
        $btn.prop('disabled', true);
        $('#cancel-error').hide();
        
        $.ajax({
            url: '/bookings/' + id + '/cancel',
            type: 'POST',
            data: {
                _token: getCsrfToken()
            },
            success: function(response) {
                console.log('Cancel response:', response);
                
                if (response.success) {
                    $('#cancelModal').modal('hide');
                    
                    var swal = getSwal();
                    if (isSwalAvailable() && swal) {
                        swal.fire({
                            icon: 'success',
                            title: 'Cancelled!',
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
                } else {
                    $('#cancel-error').text(response.message || 'Error cancelling booking.').show();
                    $('#cancel-text').show();
                    $('#cancel-spinner').hide();
                    $btn.prop('disabled', false);
                }
            },
            error: function(xhr) {
                console.error('Cancel error:', xhr);
                
                var message = 'Error cancelling booking.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }
                $('#cancel-error').text(message).show();
                $('#cancel-text').show();
                $('#cancel-spinner').hide();
                $btn.prop('disabled', false);
            }
        });
    });

    // ===== Reset cancel modal when closed =====
    $(document).on('hidden.bs.modal', '#cancelModal', function() {
        cancelBookingId = null;
        cancelRefNo = null;
        $('#cancel-text').show();
        $('#cancel-spinner').hide();
        $('#confirm-cancel-btn').prop('disabled', false);
        $('#cancel-error').hide();
    });

    // ============================================
    // LEGACY STATUS MANAGEMENT (Admin - Button Based)
    // Kept for backward compatibility
    // ============================================

    function updateStatus(id, action, actionLabel) {
        var swal = getSwal();
        if (isSwalAvailable() && swal) {
            swal.fire({
                title: 'Confirm Action',
                text: 'Are you sure you want to ' + actionLabel + ' this booking?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#5cb85c',
                cancelButtonColor: '#d9534f',
                confirmButtonText: 'Yes, ' + actionLabel + ' it!',
                cancelButtonText: 'Cancel'
            }).then(function(result) {
                if (result.isConfirmed) {
                    performStatusUpdate(id, action, actionLabel);
                }
            });
        } else {
            if (confirm('Are you sure you want to ' + actionLabel + ' this booking?')) {
                performStatusUpdate(id, action, actionLabel);
            }
        }
    }

    function performStatusUpdate(id, action, actionLabel) {
        var $button = $('.' + action + '-booking[data-id="' + id + '"]');
        var originalHtml = $button.html();
        
        $button.prop('disabled', true).html('<span class="spinner-border spinner-border-xs"></span>');

        $.ajax({
            url: '/bookings/' + id + '/status',
            type: 'PUT',
            data: {
                _token: getCsrfToken(),
                action: action
            },
            success: function(response) {
                if (response.success) {
                    var swal = getSwal();
                    if (isSwalAvailable() && swal) {
                        swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: response.message,
                            timer: 2000,
                            showConfirmButton: false,
                            timerProgressBar: true
                        });
                    } else {
                        alert('✅ ' + response.message);
                    }
                    table.ajax.reload();
                } else {
                    alert('❌ ' + (response.message || 'Error updating status.'));
                    $button.prop('disabled', false).html(originalHtml);
                }
            },
            error: function(xhr) {
                var message = 'Error updating status.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }
                alert('❌ ' + message);
                $button.prop('disabled', false).html(originalHtml);
            }
        });
    }

    // Legacy button handlers (kept for backward compatibility)
    $(document).on('click', '.confirm-booking', function() {
        var id = $(this).data('id');
        updateStatus(id, 'confirm', 'confirm');
    });

    $(document).on('click', '.issue-booking', function() {
        var id = $(this).data('id');
        updateStatus(id, 'issue', 'issue the car for');
    });

    $(document).on('click', '.return-booking', function() {
        var id = $(this).data('id');
        updateStatus(id, 'return', 'return');
    });

    $(document).on('click', '.complete-booking', function() {
        var id = $(this).data('id');
        updateStatus(id, 'complete', 'complete');
    });

    // ============================================
    // HELPER FUNCTIONS
    // ============================================

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

    console.log('Booking index initialized successfully!');
});