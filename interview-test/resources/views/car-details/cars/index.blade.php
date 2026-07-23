@extends('layouts.master')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="panel panel-primary">
            <div class="panel-heading">
                <h3 class="panel-title">Car List</h3>
            </div>
            <div class="panel-body">
                <!-- Brand Filter Dropdown -->
                <div class="row" style="margin-bottom: 15px;">
                    <div class="col-md-3">
                        <label for="brand-filter">Filter by Brand:</label>
                        <select id="brand-filter" class="form-control">
                            <option value="">All Brands</option>
                            @if(isset($brands) && $brands->count() > 0)
                                @foreach($brands as $brand)
                                    <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                                @endforeach
                            @else
                                <option value="" disabled>No brands available</option>
                            @endif
                        </select>
                    </div>
                </div>

                <table class="table table-bordered table-striped" id="cars-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>REF NO</th>
                            <th>CAR NAME</th>
                            <th>CAR MODEL</th>
                            <th>BRAND</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- DataTables will populate automatically -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    var table = $('#cars-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('car') }}",
            data: function (d) {
                d.brand_id = $('#brand-filter').val();
            }
        },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'ref_no', name: 'ref_no' },
            { data: 'name', name: 'name' },
            { data: 'model_name', name: 'model.name' },
            { data: 'brand', name: 'model.brand.name' }
        ],
        pageLength: 10,
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
</script>
@endpush
