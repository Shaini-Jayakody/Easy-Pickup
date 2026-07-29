@extends('layouts.master')

@section('content')
@php
    $selectedCarId = request('car_id');
    $selectedUserId = request('user_id', Auth::id());
@endphp
<div class="row">
    <div class="col-md-8 col-md-offset-2">
        <div class="panel panel-primary">
            <div class="panel-heading">
                <h3 class="panel-title">New Booking</h3>
            </div>
            <div class="panel-body">
                <div id="alert-container"></div>

                <form id="booking-form">
                    @csrf

                    <!-- Car Selection with Details -->
                    <div class="form-group">
                        <label for="car_id">Select Car <span style="color:#dc3545;">*</span></label>
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
                    <div id="car-details" class="well well-sm" style="display:none; background-color: #f9f9f9; border-left: 4px solid #337ab7;">
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

                    <div class="form-group">
                        <label for="rental_start_date">Rental Start Date & Time <span style="color:#dc3545;">*</span></label>
                        <input type="datetime-local" class="form-control" name="rental_start_date" id="rental_start_date" required>
                        <span class="text-danger error-msg" id="rental_start_date-error"></span>
                    </div>

                    <div class="form-group">
                        <label for="rental_end_date">Rental End Date & Time <span style="color:#dc3545;">*</span></label>
                        <input type="datetime-local" class="form-control" name="rental_end_date" id="rental_end_date" required>
                        <span class="text-danger error-msg" id="rental_end_date-error"></span>
                    </div>

                    <!-- Availability Status -->
                    <div id="availability-status" class="alert" style="display:none;"></div>

                    <!-- Duration Display -->
                    <div class="form-group" id="duration-display" style="display:none;">
                        <div class="row">
                            <div class="col-md-6">
                                <label>Duration</label>
                                <p class="form-control-static" id="duration-text">0 hours</p>
                            </div>
                            <div class="col-md-6">
                                <label>Estimated Cost</label>
                                <p class="form-control-static" id="cost-text">Rs. 0.00</p>
                            </div>
                        </div>
                    </div>

                    <input type="hidden" name="user_id" id="user_id" value="{{ $selectedUserId }}">

                    <div class="form-group">
                        <label for="notes">Notes</label>
                        <textarea class="form-control" name="notes" id="notes" rows="3" placeholder="Any special requests or notes..."></textarea>
                        <span class="text-danger error-msg" id="notes-error"></span>
                    </div>

                    <div class="form-group" style="margin-top: 20px;">
                        <a href="{{ route('bookings.index') }}" class="btn btn-default">Cancel</a>
                        <button type="submit" class="btn btn-primary pull-right" id="submit-btn" disabled>
                            <span id="submit-text">Create Booking</span>
                            <span id="submit-spinner" style="display: none;">
                                <span class="spinner-border spinner-border-sm" role="status"></span> Creating...
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
<script src="{{ asset('js/booking-form.js') }}"></script>
<script src="{{ asset('js/booking-calendar.js') }}"></script>
@endpush