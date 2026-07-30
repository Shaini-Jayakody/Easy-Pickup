@extends('layouts.master')

@section('content')
<div class="row">
    <div class="col-md-8 col-md-offset-2">
        <div class="panel panel-primary">
            <div class="panel-heading">
                <h3 class="panel-title">Generate Invoice</h3>
            </div>
            <div class="panel-body">
                <div id="alert-container"></div>

                <!-- Show message if no bookings available -->
                @if($bookings->isEmpty())
                    <div class="alert alert-warning">
                        <h4><span class="glyphicon glyphicon-info-sign"></span> No Bookings Available</h4>
                        <p>There are no completed bookings without invoices.</p>
                        <p><strong>To create an invoice:</strong></p>
                        <ol>
                            <li>A booking must be marked as <strong>"Completed"</strong></li>
                            <li>The booking must not have an existing invoice</li>
                        </ol>
                        <p>
                            <a href="{{ route('bookings.index') }}" class="btn btn-primary btn-sm">
                                <span class="glyphicon glyphicon-calendar"></span> Go to Bookings
                            </a>
                        </p>
                    </div>
                @else
                    <div class="alert alert-info">
                        <strong>{{ $bookings->count() }}</strong> booking(s) available for invoicing.
                    </div>
                @endif

                <form id="invoice-form">
                    @csrf

                    <!-- Booking Selection -->
                    <div class="form-group">
                        <label for="booking_id">Select Completed Booking <span class="invoice-text-danger">*</span></label>
                        <select class="form-control" name="booking_id" id="booking_id" required>
                            <option value="">-- Select Booking --</option>
                            @foreach($bookings as $booking)
                                <option value="{{ $booking->booking_id }}" 
                                    data-ref="{{ $booking->booking_ref_no }}"
                                    data-user="{{ $booking->user->name }}"
                                    data-nic="{{ $booking->user->id_num }}"
                                    data-email="{{ $booking->user->email }}"
                                    data-car="{{ $booking->car->name }}"
                                    data-car-ref="{{ $booking->car->ref_no }}"
                                    data-plate="{{ $booking->car->number_plate }}"
                                    data-price="{{ $booking->car->rent_price_per_hour }}"
                                    data-start="{{ $booking->rental_start_date }}"
                                    data-end="{{ $booking->rental_end_date }}">
                                    {{ $booking->booking_ref_no }} - 
                                    <strong>{{ $booking->user->name }}</strong> 
                                    ({{ $booking->car->name }} - {{ $booking->car->number_plate }})
                                </option>
                            @endforeach
                        </select>
                        <span class="invoice-error-msg" id="booking_id-error"></span>
                    </div>

                    <!-- ============================================ -->
                    <!-- CUSTOMER DETAILS - Read Only -->
                    <!-- ============================================ -->
                    <div class="panel panel-default invoice-panel-default" id="customer-details-panel" style="display:none;">
                        <div class="panel-heading">
                            <h4 class="panel-title">Customer Details</h4>
                        </div>
                        <div class="panel-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Customer Name</label>
                                        <p class="invoice-form-control-static" id="display-customer-name"><em>Select a booking</em></p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>NIC Number</label>
                                        <p class="invoice-form-control-static" id="display-customer-nic"><em>Select a booking</em></p>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Email</label>
                                        <p class="invoice-form-control-static" id="display-customer-email"><em>Select a booking</em></p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Booking Reference</label>
                                        <p class="invoice-form-control-static" id="display-booking-ref"><em>Select a booking</em></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ============================================ -->
                    <!-- CAR DETAILS - Read Only -->
                    <!-- ============================================ -->
                    <div class="panel panel-default invoice-panel-default" id="car-details-panel" style="display:none;">
                        <div class="panel-heading">
                            <h4 class="panel-title">Car Details</h4>
                        </div>
                        <div class="panel-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Car Name</label>
                                        <p class="invoice-form-control-static" id="display-car-name"><em>Select a booking</em></p>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Car Reference Number</label>
                                        <p class="invoice-form-control-static" id="display-car-ref"><em>Select a booking</em></p>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Car Number Plate</label>
                                        <p class="invoice-form-control-static" id="display-car-plate"><em>Select a booking</em></p>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Rent Price Per Hour</label>
                                        <p class="invoice-form-control-static" id="display-price-per-hour"><em>Select a booking</em></p>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Rental Start Date</label>
                                        <p class="invoice-form-control-static" id="display-rental-start"><em>Select a booking</em></p>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Rental End Date</label>
                                        <p class="invoice-form-control-static" id="display-rental-end"><em>Select a booking</em></p>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Expected Hours</label>
                                        <p class="invoice-form-control-static" id="display-expected-hours"><em>Select a booking</em></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ============================================ -->
                    <!-- RETURNED DATE - Input -->
                    <!-- ============================================ -->
                    <div class="form-group">
                        <label for="returned_date">Returned Date & Time <span class="invoice-text-danger">*</span></label>
                        <input type="datetime-local" class="form-control" name="returned_date" id="returned_date" required>
                        <span class="invoice-error-msg" id="returned_date-error"></span>
                    </div>

                    <!-- ============================================ -->
                    <!-- INVOICE PREVIEW - Shows all calculations -->
                    <!-- ============================================ -->
                    <div id="invoice-preview" style="display:none; margin-top:15px;">
                        <div class="panel panel-info">
                            <div class="panel-heading">
                                <h4 class="panel-title">Invoice Preview</h4>
                            </div>
                            <div class="panel-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <table class="table table-condensed invoice-preview-table">
                                            <tr>
                                                <td><strong>Expected Hours:</strong></td>
                                                <td><span id="preview-expected-hours">0</span> hrs</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Actual Hours:</strong></td>
                                                <td><span id="preview-actual-hours">0</span> hrs</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Extra Hours:</strong></td>
                                                <td><span id="preview-extra-hours">0</span> hrs</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Price Per Hour:</strong></td>
                                                <td>Rs. <span id="preview-price">0.00</span></td>
                                            </tr>
                                            <tr>
                                                <td><strong>Extra Hour Rate (2x):</strong></td>
                                                <td>Rs. <span id="preview-extra-rate">0.00</span></td>
                                            </tr>
                                        </table>
                                    </div>
                                    <div class="col-md-6">
                                        <table class="table table-condensed invoice-preview-table">
                                            <tr>
                                                <td><strong>Base Cost:</strong></td>
                                                <td>Rs. <span id="preview-base-cost">0.00</span></td>
                                            </tr>
                                            <tr>
                                                <td><strong>Extra Cost:</strong></td>
                                                <td>Rs. <span id="preview-extra-cost">0.00</span></td>
                                            </tr>
                                            <tr id="discount-row" class="invoice-hidden-row">
                                                <td><strong>Discount:</strong></td>
                                                <td><span id="preview-discount-label">0%</span> (Rs. <span id="preview-discount">0.00</span>)</td>
                                            </tr>
                                            <tr id="fine-row" class="invoice-hidden-row">
                                                <td><strong>Late Return Fine:</strong></td>
                                                <td>Rs. <span id="preview-fine">0.00</span></td>
                                            </tr>
                                            <tr style="font-size:20px; font-weight:bold; color:#10b981; border-top:2px solid #10b981;">
                                                <td><strong>Total Cost:</strong></td>
                                                <td>Rs. <span id="preview-total">0.00</span></td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ============================================ -->
                    <!-- PAYMENT DETAILS -->
                    <!-- ============================================ -->
                    <div class="form-group">
                        <label for="payment_method">Payment Method <span class="invoice-text-danger">*</span></label>
                        <select class="form-control" name="payment_method" id="payment_method" required>
                            <option value="">-- Select Payment Method --</option>
                            <option value="cash">Cash</option>
                            <option value="card">Card</option>
                            <option value="bank_transfer">Bank Transfer</option>
                        </select>
                        <span class="invoice-error-msg" id="payment_method-error"></span>
                    </div>

                    <div class="form-group">
                        <label for="notes">Notes</label>
                        <textarea class="form-control" name="notes" id="notes" rows="3" placeholder="Additional notes..."></textarea>
                        <span class="invoice-error-msg" id="notes-error"></span>
                    </div>

                    <!-- ============================================ -->
                    <!-- FORM ACTIONS -->
                    <!-- ============================================ -->
                    <div class="form-group" style="margin-top:20px;">
                        <a href="{{ route('invoices.index') }}" class="btn btn-default">Cancel</a>
                        <button type="submit" class="btn btn-success pull-right" id="submit-btn" disabled>
                            <span id="submit-text">Generate Invoice</span>
                            <span id="submit-spinner" style="display:none;">
                                <span class="spinner-border spinner-border-sm" role="status"></span> Processing...
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('css/invoice-form.css') }}">
@endpush

@push('scripts')
<script src="{{ asset('js/invoice-form.js') }}"></script>
@endpush