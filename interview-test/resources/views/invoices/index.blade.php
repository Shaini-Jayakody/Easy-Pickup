@extends('layouts.master')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="panel panel-primary">
            <div class="panel-heading">
                <h3 class="panel-title">Invoices</h3>
                @auth
                    {{-- Only show "New Invoice" button for Admin/Manager --}}
                    @if(auth()->user() && in_array(auth()->user()->role, ['admin', 'manager']))
                        <a href="{{ route('invoices.create') }}" class="btn btn-success btn-sm pull-right" style="margin-top: -5px;">
                            + New Invoice
                        </a>
                    @endif
                @endauth
            </div>
            <div class="panel-body">
                <!-- Filters -->
                <div class="row filter-row">
                    <!-- Status Filter -->
                    <div class="col-md-2">
                        <label for="status-filter">Status:</label>
                        <select id="status-filter" class="form-control">
                            <option value="">All Statuses</option>
                            <option value="pending">Pending</option>
                            <option value="paid">Paid</option>
                            <option value="failed">Failed</option>
                        </select>
                    </div>
                    
                    <!-- Payment Method Filter -->
                    <div class="col-md-2">
                        <label for="method-filter">Payment Method:</label>
                        <select id="method-filter" class="form-control">
                            <option value="">All Methods</option>
                            <option value="cash">Cash</option>
                            <option value="card">Card</option>
                            <option value="bank_transfer">Bank Transfer</option>
                        </select>
                    </div>
                    
                    <!-- Date From Filter -->
                    <div class="col-md-2">
                        <label for="date-from">Date From:</label>
                        <input type="date" id="date-from" class="form-control">
                    </div>
                    
                    <!-- Date To Filter -->
                    <div class="col-md-2">
                        <label for="date-to">Date To:</label>
                        <input type="date" id="date-to" class="form-control">
                    </div>
                    
                    <!-- Admin Filters - Only for Admin/Manager -->
                    @if(auth()->user() && in_array(auth()->user()->role, ['admin', 'manager']))
                    <!-- NIC Filter -->
                    <div class="col-md-2">
                        <label for="nic-filter">NIC:</label>
                        <input type="text" id="nic-filter" class="form-control" placeholder="Search by NIC...">
                    </div>
                    
                    <!-- Car Filter -->
                    <div class="col-md-2">
                        <label for="car-filter">Car:</label>
                        <select id="car-filter" class="form-control">
                            <option value="">All Cars</option>
                            @foreach($cars ?? [] as $car)
                                <option value="{{ $car->id }}">{{ $car->name }} - {{ $car->number_plate }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <!-- Customer Name Filter -->
                    <div class="col-md-2">
                        <label for="customer-filter">Customer:</label>
                        <input type="text" id="customer-filter" class="form-control" placeholder="Search by name...">
                    </div>
                    @endif
                    
                    <!-- Clear Filters Button -->
                    <div class="col-md-2">
                        <label>&nbsp;</label>
                        <button id="clear-filters" class="btn btn-default form-control">Clear Filters</button>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="invoices-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Invoice Ref</th>
                                <th>Booking Ref</th>
                                <th>Customer</th>
                                <th>NIC</th>
                                <th>Car</th>
                                <th>Total Cost</th>
                                <th>Payment Method</th>
                                <th>Status</th>
                                <th>Date</th>
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
</div>

<!-- Invoice Details Modal -->
<div class="modal fade" id="invoiceModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Invoice Details</h4>
            </div>
            <div class="modal-body" id="invoice-details">
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
<script src="{{ asset('js/invoice-index.js') }}"></script>
@endpush