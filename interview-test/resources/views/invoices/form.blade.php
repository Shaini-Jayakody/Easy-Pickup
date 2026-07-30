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
                        <label for="booking_id">Select Completed Booking <span class="text-danger">*</span></label>
                        <select class="form-control" name="booking_id" id="booking_id" required>
                            <option value="">-- Select Booking --</option>
                            @foreach($bookings as $booking)
                                <option value="{{ $booking->booking_id }}" 
                                    data-ref="{{ $booking->booking_ref_no ?? '' }}"
                                    data-user="{{ $booking->user->name ?? 'N/A' }}"
                                    data-nic="{{ $booking->user->id_num ?? 'N/A' }}"
                                    data-email="{{ $booking->user->email ?? 'N/A' }}"
                                    data-car="{{ $booking->car->name ?? 'N/A' }}"
                                    data-car-ref="{{ $booking->car->ref_no ?? 'N/A' }}"
                                    data-plate="{{ $booking->car->number_plate ?? 'N/A' }}"
                                    data-price="{{ $booking->car->rent_price_per_hour ?? 0 }}"
                                    data-start="{{ $booking->rental_start_date ?? '' }}"
                                    data-end="{{ $booking->rental_end_date ?? '' }}">
                                    {{ $booking->booking_ref_no ?? 'N/A' }} - 
                                    <strong>{{ $booking->user->name ?? 'N/A' }}</strong> 
                                    ({{ $booking->car->name ?? 'No Car' }} - {{ $booking->car->number_plate ?? 'N/A' }})
                                </option>
                            @endforeach
                        </select>
                        <span class="help-block text-danger error-msg" id="booking_id-error"></span>
                    </div>

                    <!-- ============================================ -->
                    <!-- CUSTOMER DETAILS - Hidden by default -->
                    <!-- ============================================ -->
                    <div class="panel panel-default invoice-panel" id="customer-details-panel" style="display:none;">
                        <div class="panel-heading">
                            <h4 class="panel-title">Customer Details</h4>
                        </div>
                        <div class="panel-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Customer Name</label>
                                        <p class="form-control-static" id="display-customer-name"><em>Select a booking</em></p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>NIC Number</label>
                                        <p class="form-control-static" id="display-customer-nic"><em>Select a booking</em></p>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Email</label>
                                        <p class="form-control-static" id="display-customer-email"><em>Select a booking</em></p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Booking Reference</label>
                                        <p class="form-control-static" id="display-booking-ref"><em>Select a booking</em></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ============================================ -->
                    <!-- CAR DETAILS - Hidden by default -->
                    <!-- ============================================ -->
                    <div class="panel panel-default invoice-panel" id="car-details-panel" style="display:none;">
                        <div class="panel-heading">
                            <h4 class="panel-title">Car Details</h4>
                        </div>
                        <div class="panel-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Car Name</label>
                                        <p class="form-control-static" id="display-car-name"><em>Select a booking</em></p>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Car Reference Number</label>
                                        <p class="form-control-static" id="display-car-ref"><em>Select a booking</em></p>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Car Number Plate</label>
                                        <p class="form-control-static" id="display-car-plate"><em>Select a booking</em></p>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Rent Price Per Hour</label>
                                        <p class="form-control-static" id="display-price-per-hour"><em>Select a booking</em></p>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Rental Start Date</label>
                                        <p class="form-control-static" id="display-rental-start"><em>Select a booking</em></p>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Rental End Date</label>
                                        <p class="form-control-static" id="display-rental-end"><em>Select a booking</em></p>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Expected Hours</label>
                                        <p class="form-control-static" id="display-expected-hours"><em>Select a booking</em></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ============================================ -->
                    <!-- RETURNED DATE - Input -->
                    <!-- ============================================ -->
                    <div class="form-group">
                        <label for="returned_date">Returned Date & Time <span class="text-danger">*</span></label>
                        <input type="datetime-local" class="form-control" name="returned_date" id="returned_date" required>
                        <span class="help-block text-danger error-msg" id="returned_date-error"></span>
                    </div>

                    <!-- ============================================ -->
                    <!-- INVOICE PREVIEW - Hidden by default -->
                    <!-- ============================================ -->
                    <div id="invoice-preview" class="invoice-preview-container" style="display:none;">
                        <div class="panel panel-info">
                            <div class="panel-heading">
                                <h4 class="panel-title">Invoice Preview</h4>
                            </div>
                            <div class="panel-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <table class="table table-condensed table-striped invoice-preview-table">
                                            <tbody>
                                                <tr>
                                                    <td><strong>Expected Hours:</strong></td>
                                                    <td class="text-right"><span id="preview-expected-hours">0</span> hrs</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Actual Hours:</strong></td>
                                                    <td class="text-right"><span id="preview-actual-hours">0</span> hrs</td>
                                                </tr>
                                                <tr id="extra-hours-row">
                                                    <td><strong>Extra Hours:</strong></td>
                                                    <td class="text-right"><span id="preview-extra-hours">0</span> hrs</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Price Per Hour:</strong></td>
                                                    <td class="text-right">Rs. <span id="preview-price">0.00</span></td>
                                                </tr>
                                                <tr id="extra-rate-row">
                                                    <td><strong>Extra Hour Rate (2x):</strong></td>
                                                    <td class="text-right">Rs. <span id="preview-extra-rate">0.00</span></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="col-md-6">
                                        <table class="table table-condensed table-striped invoice-preview-table">
                                            <tbody>
                                                <tr>
                                                    <td><strong>Base Cost:</strong></td>
                                                    <td class="text-right">Rs. <span id="preview-base-cost">0.00</span></td>
                                                </tr>
                                                <tr id="extra-cost-row">
                                                    <td><strong>Extra Cost:</strong></td>
                                                    <td class="text-right">Rs. <span id="preview-extra-cost">0.00</span></td>
                                                </tr>
                                                <tr id="discount-row" style="display:none;">
                                                    <td><strong>Discount:</strong></td>
                                                    <td class="text-right"><span id="preview-discount-label">0%</span> (Rs. <span id="preview-discount">0.00</span>)</td>
                                                </tr>
                                                <tr id="fine-preview-row">
                                                    <td><strong>Additional Charges / Fine:</strong></td>
                                                    <td class="text-right">
                                                        Rs. <span id="preview-fine">0.00</span>
                                                        <small class="text-muted fine-reason-display" id="fine-reason-display"></small>
                                                    </td>
                                                </tr>
                                                <tr class="total-row">
                                                    <td><strong>Total Cost:</strong></td>
                                                    <td class="text-right text-success">Rs. <span id="preview-total">0.00</span></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ============================================ -->
                    <!-- ADDITIONAL CHARGES / FINE - Button to show -->
                    <!-- ============================================ -->
                    <div class="form-group" style="margin-top:15px;">
                        <button type="button" class="btn btn-warning btn-sm" id="show-fine-btn">
                            <span class="glyphicon glyphicon-plus"></span> Add Additional Charges / Fine
                        </button>
                    </div>

                    <!-- ============================================ -->
                    <!-- ADDITIONAL CHARGES / FINE - Hidden by default -->
                    <!-- ============================================ -->
                    <div class="panel panel-warning fine-panel" id="fine-panel" style="display:none;">
                        <div class="panel-heading">
                            <h4 class="panel-title">
                                <span class="glyphicon glyphicon-warning-sign"></span> Additional Charges / Fine
                            </h4>
                        </div>
                        <div class="panel-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="fine_amount">Amount (Rs.)</label>
                                        <input type="number" class="form-control" name="fine_amount" id="fine_amount" step="0.01" min="0" placeholder="0.00" value="0">
                                        <span class="help-block text-danger error-msg" id="fine_amount-error"></span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="fine_reason">Reason</label>
                                        <input type="text" class="form-control" name="fine_reason" id="fine_reason" placeholder="e.g., Damage fee, Cleaning fee">
                                        <span class="help-block text-danger error-msg" id="fine_reason-error"></span>
                                    </div>
                                </div>
                            </div>
                            <div class="alert alert-info fine-info-alert" style="margin-top:10px;">
                                <span class="glyphicon glyphicon-info-sign"></span>
                                <strong>Note:</strong> Add any additional charges such as damage fees, cleaning fees, or other fines here.
                            </div>
                        </div>
                    </div>

                    <!-- ============================================ -->
                    <!-- PAYMENT DETAILS -->
                    <!-- ============================================ -->
                    <div class="form-group">
                        <label for="payment_method">Payment Method <span class="text-danger">*</span></label>
                        <select class="form-control" name="payment_method" id="payment_method" required>
                            <option value="">-- Select Payment Method --</option>
                            <option value="cash">Cash</option>
                            <option value="card">Card</option>
                            <option value="bank_transfer">Bank Transfer</option>
                        </select>
                        <span class="help-block text-danger error-msg" id="payment_method-error"></span>
                    </div>

                    <div class="form-group">
                        <label for="notes">Notes</label>
                        <textarea class="form-control" name="notes" id="notes" rows="3" placeholder="Additional notes..."></textarea>
                        <span class="help-block text-danger error-msg" id="notes-error"></span>
                    </div>

                    <!-- ============================================ -->
                    <!-- FORM ACTIONS -->
                    <!-- ============================================ -->
                    <div class="form-group invoice-form-actions">
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