/**
 * Car Index Script
 * Handles DataTable initialization and filtering
 */

$(document).ready(function() {
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
            { data: 'chassis_number', name: 'chassis_number' }
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

    // Reload table if brand filter changes
    $('#brand-filter').on('change', function() {
        table.ajax.reload();
    });
});