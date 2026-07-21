@extends('layouts.master')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="panel panel-primary">
            <div class="panel-heading">
                <h3 class="panel-title">Car List</h3>
                <a href="{{ route('car.form') }}" class="btn btn-success btn-sm pull-right" style="margin-top: -5px;">
                    + Add New Car
                </a>
            </div>
            <div class="panel-body">
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
                        <!-- DataTables will populate this automatically -->
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
    $('#cars-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('car') }}",
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
});
</script>
@endpush