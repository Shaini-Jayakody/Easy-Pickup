/**
 * Invoice Index Script
 * Handles DataTable initialization, filters, and status management
 */

$(document).ready(function() {
    //Get CSRF Token
    function getCsrfToken() {
        return $('meta[name="csrf-token"]').attr('content');
    }

    //Check if user is admin/manager 
    function isAdmin() {
        var role = $('body').data('user-role') || '';
        return role === 'admin' || role === 'manager';
    }

    //Initialize DataTable 
    var table = $('#invoices-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "/invoices",
            data: function (d) {
                // Basic filters - everyone
                d.status = $('#status-filter').val();
                d.payment_method = $('#method-filter').val();
                d.date_from = $('#date-from').val();
                d.date_to = $('#date-to').val();
                
                // Admin filters - only if admin/manager
                if (isAdmin()) {
                    d.nic = $('#nic-filter').val();
                    d.car_id = $('#car-filter').val();
                    d.customer_name = $('#customer-filter').val();
                }
            },
            error: function(xhr, error, thrown) {
                console.log('DataTable error:', error);
                console.log('Status:', xhr.status);
                console.log('Response:', xhr.responseText);
                $('#invoices-table tbody').html('<tr><td colspan="11" class="text-center text-danger">Error loading data. Please refresh the page.</td></tr>');
            }
        },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'invoice_ref_no', name: 'invoice_ref_no' },
            { data: 'booking_ref', name: 'booking_ref' },
            { data: 'customer_name', name: 'customer_name' },
            { data: 'customer_nic', name: 'customer_nic' },
            { data: 'car_info', name: 'car_info' },
            { 
                data: 'total_cost', 
                name: 'total_cost',
                render: function(data) {
                    return data;
                }
            },
            { 
                data: 'payment_method', 
                name: 'payment_method',
                render: function(data) {
                    return data || 'N/A';
                }
            },
            { 
                data: 'status', 
                name: 'status',
                orderable: false,
                searchable: false
            },
            { 
                data: 'created_at', 
                name: 'created_at',
                render: function(data) {
                    if (!data) return 'N/A';
                    var date = new Date(data);
                    return date.toLocaleDateString() + ' ' + date.toLocaleTimeString();
                }
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
        order: [[9, 'desc']],
        language: {
            search: "Search:",
            lengthMenu: "Show _MENU_ entries",
            info: "Showing _START_ to _END_ of _TOTAL_ entries",
            infoEmpty: "No entries found",
            infoFiltered: "(filtered from _MAX_ total entries)",
            zeroRecords: "No matching invoices found"
        },
        drawCallback: function() {
            initializeStatusDropdowns();
        }
    });

  
    // FILTER HANDLERS
   
    $('#status-filter, #method-filter, #car-filter, #date-from, #date-to').on('change', function() {
        table.ajax.reload();
    });

    // NIC filter - with debounce
    var nicTimeout;
    $('#nic-filter').on('keyup', function() {
        clearTimeout(nicTimeout);
        nicTimeout = setTimeout(function() {
            table.ajax.reload();
        }, 500);
    });

    // Customer filter - with debounce
    var customerTimeout;
    $('#customer-filter').on('keyup', function() {
        clearTimeout(customerTimeout);
        customerTimeout = setTimeout(function() {
            table.ajax.reload();
        }, 500);
    });

    // Clear all filters
    $('#clear-filters').on('click', function() {
        $('#status-filter').val('');
        $('#method-filter').val('');
        $('#date-from').val('');
        $('#date-to').val('');
        $('#nic-filter').val('');
        $('#car-filter').val('');
        $('#customer-filter').val('');
        table.ajax.reload();
    });

    // INVOICE STATUS DROPDOWNS (Admin/Manager)


    function initializeStatusDropdowns() {
        $('.invoice-status-dropdown').off('change').on('change', function() {
            var $select = $(this);
            var invoiceId = $select.data('invoice-id');
            var newStatus = $select.val();
            var selectedText = $select.find('option:selected').text();
            
            // Show loading state
            $select.prop('disabled', true);
            
            // Get the status name for confirmation
            var statusText = selectedText.replace('▼ ', '').replace('→ ', '').trim();
            
            var swal = getSwal();
            if (isSwalAvailable() && swal) {
                swal.fire({
                    title: 'Change Invoice Status?',
                    text: 'Are you sure you want to change this invoice to "' + statusText + '"?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#3b82f6',
                    cancelButtonColor: '#d9534f',
                    confirmButtonText: 'Yes, change it!',
                    cancelButtonText: 'Cancel'
                }).then(function(result) {
                    if (result.isConfirmed) {
                        performStatusUpdate(invoiceId, newStatus, $select);
                    } else {
                        table.ajax.reload();
                    }
                });
            } else {
                if (confirm('Are you sure you want to change this invoice to "' + statusText + '"?')) {
                    performStatusUpdate(invoiceId, newStatus, $select);
                } else {
                    table.ajax.reload();
                }
            }
        });
    }

    function performStatusUpdate(invoiceId, newStatus, $select) {
        $.ajax({
            url: '/invoices/' + invoiceId + '/status',
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
                table.ajax.reload();
            }
        });
    }

    // VIEW INVOICE DETAILS
   
    $(document).on('click', '.view-invoice', function(e) {
        e.preventDefault();
        var id = $(this).data('id');
        
        $('#invoice-details').html('<div class="text-center"><span class="spinner-border" role="status"></span> Loading...</div>');
        $('#invoiceModal').modal('show');
        
        $.ajax({
            url: '/invoices/' + id,
            type: 'GET',
            success: function(response) {
                var invoice = response.invoice;
                var html = `
                    <div class="row">
                        <div class="col-md-6">
                            <h4><span class="glyphicon glyphicon-user"></span> Customer Details</h4>
                            <p><strong>Name:</strong> ${invoice.customer?.name || 'N/A'}</p>
                            <p><strong>NIC:</strong> ${invoice.customer?.nic || 'N/A'}</p>
                            <p><strong>Email:</strong> ${invoice.customer?.email || 'N/A'}</p>
                        </div>
                        <div class="col-md-6">
                            <h4><span class="glyphicon glyphicon-car"></span> Car Details</h4>
                            <p><strong>Car:</strong> ${invoice.car?.name || 'N/A'}</p>
                            <p><strong>Plate No:</strong> ${invoice.car?.number_plate || 'N/A'}</p>
                            <p><strong>Ref No:</strong> ${invoice.car?.ref_no || 'N/A'}</p>
                            <p><strong>Price/Hour:</strong> ${invoice.car?.price_per_hour ? 'Rs. ' + invoice.car.price_per_hour : 'N/A'}</p>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-md-4">
                            <h4><span class="glyphicon glyphicon-calendar"></span> Rental Details</h4>
                            <p><strong>Booking Ref:</strong> ${invoice.booking_ref || 'N/A'}</p>
                            <p><strong>Start:</strong> ${invoice.rental?.start_date ? new Date(invoice.rental.start_date).toLocaleString() : 'N/A'}</p>
                            <p><strong>End:</strong> ${invoice.rental?.end_date ? new Date(invoice.rental.end_date).toLocaleString() : 'N/A'}</p>
                            <p><strong>Returned:</strong> ${invoice.rental?.returned_date ? new Date(invoice.rental.returned_date).toLocaleString() : 'N/A'}</p>
                            <p><strong>Expected Hours:</strong> ${invoice.rental?.expected_hours || 0}</p>
                            <p><strong>Actual Hours:</strong> ${invoice.rental?.actual_hours || 0}</p>
                            <p><strong>Extra Hours:</strong> ${invoice.rental?.extra_hours || 0}</p>
                        </div>
                        <div class="col-md-4">
                            <h4><span class="glyphicon glyphicon-usd"></span> Cost Breakdown</h4>
                            <p><strong>Base Cost:</strong> Rs. ${(invoice.cost?.base_cost || 0).toFixed(2)}</p>
                            <p><strong>Extra Cost:</strong> Rs. ${(invoice.cost?.extra_cost || 0).toFixed(2)}</p>
                            <p><strong>Discount:</strong> ${invoice.cost?.discount_percentage || 0}% (Rs. ${(invoice.cost?.discount_amount || 0).toFixed(2)})</p>
                            <p><strong>Fine:</strong> Rs. ${(invoice.cost?.fine_amount || 0).toFixed(2)}</p>
                            <p style="font-size:18px; font-weight:bold; color:#10b981;"><strong>Total:</strong> Rs. ${(invoice.cost?.total_cost || 0).toFixed(2)}</p>
                        </div>
                        <div class="col-md-4">
                            <h4><span class="glyphicon glyphicon-info-sign"></span> Payment Details</h4>
                            <p><strong>Status:</strong> <span class="label-status label-${invoice.payment?.status}">${invoice.payment?.status}</span></p>
                            <p><strong>Method:</strong> ${invoice.payment?.method || 'N/A'}</p>
                            <p><strong>Paid At:</strong> ${invoice.payment?.paid_at ? new Date(invoice.payment.paid_at).toLocaleString() : 'N/A'}</p>
                            <p><strong>Invoice Ref:</strong> ${invoice.ref_no}</p>
                            <p><strong>Created:</strong> ${new Date(invoice.created_at).toLocaleString()}</p>
                            ${invoice.notes ? `<p><strong>Notes:</strong> ${invoice.notes}</p>` : ''}
                        </div>
                    </div>
                `;
                $('#invoice-details').html(html);
            },
            error: function(xhr) {
                $('#invoice-details').html('<div class="alert alert-danger">Error loading invoice details. Please try again.</div>');
                console.error(xhr);
            }
        });
    });

    
    // PRINT INVOICE


    $(document).on('click', '.print-invoice', function(e) {
        e.preventDefault();
        var id = $(this).data('id');
        window.open('/invoices/' + id + '/print', '_blank');
    });


    // HELPER FUNCTIONS
    

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

    console.log('Invoice index initialized successfully!');
});