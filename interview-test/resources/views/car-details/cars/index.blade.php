@extends('layouts.master')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="panel panel-primary">
            <div class="panel-heading">
                <h3 class="panel-title">Car List</h3>
                @auth
                    @if(in_array(Auth::user()->role, ['admin', 'manager']))
                        <a href="{{ route('car.form') }}" class="btn btn-success btn-sm pull-right" style="margin-top: -5px;">
                            + Add New Car
                        </a>
                    @endif
                @endauth
            </div>
            <div class="panel-body">
                <!-- Brand Filter Dropdown -->
                <div class="row" style="margin-bottom: 15px;">
                    <div class="col-md-3">
                        <label for="brand-filter">Filter by Brand:</label>
                        <select id="brand-filter" class="form-control" style="height: 40px;">
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
                            <th>COLOR</th>
                            <th>MODEL</th>
                            <th>BRAND</th>
                            <th>PRICE (LKR/HR)</th>
                            <th>TRANSMISSION</th>
                            <th>PLATE NO</th>
                            <th>ENGINE NO</th>
                            <th>CHASSIS NO</th>
                            @auth
                                @if(in_array(Auth::user()->role, ['admin', 'manager']))
                                    <th>ACTION</th>
                                @endif
                            @endauth
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
<!-- Pass isAdmin variable to JavaScript -->
<script>
    var isAdmin = {{ auth()->check() && in_array(auth()->user()->role ?? '', ['admin', 'manager']) ? 'true' : 'false' }};
</script>
<script src="{{ asset('js/car-index.js') }}"></script>
@endpush