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
                // Add filters if needed
                d.status = $('#status-filter').val();
            },
            error: function(xhr, error, thrown) {
                console.log('DataTable error:', error);
                console.log('Status:', xhr.status);
                console.log('Response:', xhr.responseText);
            }
        },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'booking_ref_no', name: 'booking_ref_no' },
            { data: 'user_name', name: 'user.name' },
            { data: 'user_nic', name: 'user.id_num' },
            { data: 'car_name', name: 'car.name' },
            { data: 'car_plate', name: 'car.number_plate' },
            { data: 'duration', name: 'duration', orderable: false, searchable: false },
            { data: 'status', name: 'status', orderable: false, searchable: false },
            { data: 'action', name: 'action', orderable: false, searchable: false }
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

    // ===== Reload table when filters change =====
    $(document).on('change', '#status-filter', function() {
        table.ajax.reload();
    });

    // ============================================
    // BOOKING DETAILS MODAL
    // ============================================

    // ===== View Booking =====
    $(document).on('click', '.view-booking', function(e) {
        e.preventDefault();
        var id = $(this).data('id');
        
        $.ajax({
            url: '/bookings/' + id,
            type: 'GET',
            success: function(response) {
                var booking = response.booking;
                var html = `
                    <div class="row">
                        <div class="col-md-6">
                            <h4>Customer Details</h4>
                            <p><strong>Name:</strong> ${booking.user?.name || 'N/A'}</p>
                            <p><strong>NIC:</strong> ${booking.user?.id_num || booking.user?.nic || 'N/A'}</p>
                            <p><strong>Email:</strong> ${booking.user?.email || 'N/A'}</p>
                        </div>
                        <div class="col-md-6">
                            <h4>Car Details</h4>
                            <p><strong>Car:</strong> ${booking.car?.name || 'N/A'}</p>
                            <p><strong>Plate No:</strong> ${booking.car?.number_plate || booking.car?.plate_no || 'N/A'}</p>
                            <p><strong>Ref No:</strong> ${booking.car?.ref_no || 'N/A'}</p>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <h4>Rental Period</h4>
                            <p><strong>Start:</strong> ${new Date(booking.rental_start_date || booking.rental_start).toLocaleString()}</p>
                            <p><strong>End:</strong> ${new Date(booking.rental_end_date || booking.rental_end).toLocaleString()}</p>
                            <p><strong>Duration:</strong> ${(booking.duration_in_hours || booking.duration || 0)} hours</p>
                        </div>
                        <div class="col-md-6">
                            <h4>Status</h4>
                            <p><span class="${booking.status_badge}">${booking.status_text}</span></p>
                            <p><strong>Booking Ref:</strong> ${booking.booking_ref_no || booking.ref_no || 'N/A'}</p>
                            ${booking.notes ? `<p><strong>Notes:</strong> ${booking.notes}</p>` : ''}
                        </div>
                    </div>
                `;
                $('#booking-details').html(html);
                $('#bookingModal').modal('show');
            },
            error: function(xhr) {
                alert('Error loading booking details.');
                console.error(xhr);
            }
        });
    });

    // ============================================
    // BOOKING STATUS MANAGEMENT
    // ============================================

    // ===== Update Status Function =====
    function updateStatus(id, action, actionLabel) {
        if (!confirm('Are you sure you want to ' + actionLabel + ' this booking?')) {
            return;
        }

        var $button = $('.' + action + '-booking[data-id="' + id + '"]');
        var originalHtml = $button.html();
        
        // Show loading state
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
                    // Show success message
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
                    // Reload DataTable
                    table.ajax.reload();
                } else {
                    alert('❌ ' + (response.message || 'Error updating status.'));
                }
            },
            error: function(xhr) {
                var message = 'Error updating status.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }
                alert('❌ ' + message);
                // Restore button
                $button.prop('disabled', false).html(originalHtml);
            }
        });
    }

    // ===== Confirm Booking =====
    $(document).on('click', '.confirm-booking', function() {
        var id = $(this).data('id');
        updateStatus(id, 'confirm', 'confirm');
    });

    // ===== Issue Car =====
    $(document).on('click', '.issue-booking', function() {
        var id = $(this).data('id');
        updateStatus(id, 'issue', 'issue the car for');
    });

    // ===== Return Car =====
    $(document).on('click', '.return-booking', function() {
        var id = $(this).data('id');
        updateStatus(id, 'return', 'return');
    });

    // ===== Complete Booking =====
    $(document).on('click', '.complete-booking', function() {
        var id = $(this).data('id');
        updateStatus(id, 'complete', 'complete');
    });

    // ===== Cancel Booking =====
    $(document).on('click', '.cancel-booking', function() {
        var id = $(this).data('id');
        updateStatus(id, 'cancel', 'cancel');
    });

    // ============================================
    // HELPER FUNCTIONS
    // ============================================

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

    console.log('Booking index initialized successfully!');
});