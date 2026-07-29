@extends('layouts.master')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="panel panel-primary">
            <div class="panel-heading">
                <h3 class="panel-title">Bookings</h3>
                @auth
                    <a href="{{ route('bookings.create') }}" class="btn btn-success btn-sm pull-right" style="margin-top: -5px;">
                        + New Booking
                    </a>
                @endauth
            </div>
            <div class="panel-body">
                <!-- Status Filter -->
                <div class="row" style="margin-bottom: 15px;">
                    <div class="col-md-3">
                        <label for="status-filter">Filter by Status:</label>
                        <select id="status-filter" class="form-control">
                            <option value="">All Statuses</option>
                            <option value="pending">Pending</option>
                            <option value="confirmed">Confirmed</option>
                            <option value="active">Active</option>
                            <option value="returned">Returned</option>
                            <option value="completed">Completed</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                </div>

                <table class="table table-bordered table-striped" id="bookings-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Booking Ref</th>
                            <th>Customer</th>
                            <th>NIC</th>
                            <th>Car</th>
                            <th>Plate No</th>
                            <th>Duration</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- DataTables will populate -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Booking Details Modal -->
<div class="modal fade" id="bookingModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Booking Details</h4>
            </div>
            <div class="modal-body" id="booking-details">
                <!-- Loaded via AJAX -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/booking-index.js') }}"></script>
@endpush