@extends('layouts.master')

@section('content')
@php
    // Check if we're editing or creating
    $isEdit = isset($booking);
    $selectedCarId = $isEdit ? $booking->car_id : request('car_id');
    $selectedUserId = $isEdit ? $booking->user_id : request('user_id', Auth::id());
    $pageTitle = $isEdit ? 'Edit Booking #' . $booking->booking_ref_no : 'New Booking';
    $buttonText = $isEdit ? 'Update Booking' : 'Create Booking';
    
    // Calculate estimated cost for edit mode
    $estimatedCost = 0;
    $duration = 0;
    if ($isEdit && $booking->car) {
        $duration = $booking->getDurationInHours();
        $estimatedCost = $duration * ($booking->car->rent_price_per_hour ?? 0);
    }
@endphp

<div class="row">
    <div class="col-md-8 col-md-offset-2">
        <div class="panel panel-primary">
            <div class="panel-heading">
                <h3 class="panel-title">{{ $pageTitle }}</h3>
            </div>
            <div class="panel-body">
                <div id="alert-container"></div>

                <form id="booking-form">
                    @csrf
                    @if($isEdit)
                        @method('PUT')
                    @endif

                    <!-- Car Selection -->
                    <div class="form-group">
                        <label for="car_id">Select Car <span class="text-danger">*</span></label>
                        <select class="form-control" name="car_id" id="car_id" required>
                            <option value="">-- Select Car --</option>
                            @foreach($cars as $car)
                                <option value="{{ $car->id }}" 
                                    data-ref="{{ $car->ref_no }}"
                                    data-plate="{{ $car->number_plate }}"
                                    data-price="{{ $car->rent_price_per_hour }}"
                                    data-name="{{ $car->name }}"
                                    {{ (string) $car->id === (string) $selectedCarId ? 'selected' : '' }}>
                                    {{ $car->name }} - {{ $car->number_plate }} ({{ $car->ref_no }})
                                </option>
                            @endforeach
                        </select>
                        <span class="text-danger error-msg" id="car_id-error"></span>
                    </div>

                    <!-- Car Details Display -->
                    <div id="car-details" class="well well-sm car-details-box" style="display:{{ $selectedCarId ? 'block' : 'none' }};">
                        <div class="row">
                            <div class="col-md-4">
                                <strong>Car:</strong> <span id="display-car-name">-</span>
                            </div>
                            <div class="col-md-4">
                                <strong>Plate No:</strong> <span id="display-car-plate">-</span>
                            </div>
                            <div class="col-md-4">
                                <strong>Ref No:</strong> <span id="display-car-ref">-</span>
                            </div>
                        </div>
                        <div class="row" style="margin-top: 5px;">
                            <div class="col-md-4">
                                <strong>Price/Hour:</strong> <span id="display-car-price">-</span>
                            </div>
                        </div>
                    </div>

                    <!-- Calendar -->
                    <div id="calendar-container" class="calendar-wrapper">
                        <!-- Calendar will be rendered here -->
                    </div>

                    <div class="form-group">
                        <label for="rental_start_date">Rental Start Date & Time <span class="text-danger">*</span></label>
                        <input type="datetime-local" class="form-control" name="rental_start_date" id="rental_start_date" 
                               value="{{ $isEdit && $booking->rental_start_date ? $booking->rental_start_date->format('Y-m-d\TH:i') : '' }}" required>
                        <span class="text-danger error-msg" id="rental_start_date-error"></span>
                    </div>

                    <div class="form-group">
                        <label for="rental_end_date">Rental End Date & Time <span class="text-danger">*</span></label>
                        <input type="datetime-local" class="form-control" name="rental_end_date" id="rental_end_date" 
                               value="{{ $isEdit && $booking->rental_end_date ? $booking->rental_end_date->format('Y-m-d\TH:i') : '' }}" required>
                        <span class="text-danger error-msg" id="rental_end_date-error"></span>
                    </div>

                    <!-- Availability Status -->
                    <div id="availability-status" class="alert" style="display:none;"></div>

                    <!-- Duration & Estimated Cost Display -->
                    <div class="panel panel-info" id="cost-estimate-panel" style="display:{{ $isEdit ? 'block' : 'none' }}; margin-top:15px;">
                        <div class="panel-heading">
                            <h4 class="panel-title">Booking Estimate</h4>
                        </div>
                        <div class="panel-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <label>Duration</label>
                                    <p class="form-control-static" style="font-size:18px; font-weight:bold; color:#333;" id="duration-text">
                                        {{ $isEdit ? $duration : 0 }} hours
                                    </p>
                                </div>
                                <div class="col-md-4">
                                    <label>Price Per Hour</label>
                                    <p class="form-control-static" style="font-size:18px; font-weight:bold; color:#333;" id="price-text">
                                        Rs. {{ $isEdit && $booking->car ? number_format($booking->car->rent_price_per_hour ?? 0, 2) : '0.00' }}
                                    </p>
                                </div>
                                <div class="col-md-4">
                                    <label>Estimated Cost</label>
                                    <p class="form-control-static" style="font-size:20px; font-weight:bold; color:#28a745;" id="cost-text">
                                        Rs. {{ $isEdit ? number_format($estimatedCost, 2) : '0.00' }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <input type="hidden" name="user_id" id="user_id" value="{{ $selectedUserId }}">
                    @if($isEdit)
                        <input type="hidden" name="booking_id" id="booking_id" value="{{ $booking->booking_id }}">
                    @endif

                    <div class="form-group">
                        <label for="notes">Notes</label>
                        <textarea class="form-control" name="notes" id="notes" rows="3" placeholder="Any special requests or notes...">{{ $isEdit ? $booking->notes : '' }}</textarea>
                        <span class="text-danger error-msg" id="notes-error"></span>
                    </div>

                    <div class="form-group form-actions">
                        <a href="{{ route('bookings.index') }}" class="btn btn-default">Cancel</a>
                        <button type="submit" class="btn btn-primary pull-right" id="submit-btn" {{ $isEdit ? '' : 'disabled' }}>
                            <span id="submit-text">{{ $buttonText }}</span>
                            <span id="submit-spinner" style="display: none;">
                                <span class="spinner-border spinner-border-sm" role="status"></span> 
                                {{ $isEdit ? 'Updating...' : 'Creating...' }}
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Pass data to JavaScript
    window.isEditMode = {{ isset($booking) ? 'true' : 'false' }};
    window.bookingId = {{ isset($booking) ? $booking->booking_id : 'null' }};
</script>
<script src="{{ asset('js/booking-calendar.js') }}"></script>
<script src="{{ asset('js/booking-form.js') }}"></script>
@endpush