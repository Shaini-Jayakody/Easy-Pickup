@extends('layouts.master')

@section('content')
@php
    $selectedCarId = request('car_id');
    $selectedUserId = request('user_id', Auth::id());
@endphp
<div class="row">
    <div class="col-md-6 col-md-offset-3">
        <div class="panel panel-primary">
            <div class="panel-heading">
                <h3 class="panel-title">New Booking</h3>
            </div>
            <div class="panel-body">
                <div id="alert-container"></div>

                <form id="booking-form">
                    @csrf

                    <div class="form-group">
                        <label for="car_id">Select Car <span style="color:#dc3545;">*</span></label>
                        <select class="form-control" name="car_id" id="car_id" required>
                            <option value="">-- Select Car --</option>
                            @foreach($cars as $car)
                                <option value="{{ $car->id }}" {{ (string) $car->id === (string) $selectedCarId ? 'selected' : '' }}>
                                    {{ $car->name }} - {{ $car->number_plate }} ({{ $car->ref_no }})
                                </option>
                            @endforeach
                        </select>
                        <span class="text-danger error-msg" id="car_id-error"></span>
                        <small class="text-muted" id="availability-status"></small>
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

                    <div class="form-group" id="duration-display" style="display:none;">
                        <label>Duration</label>
                        <p class="form-control-static" id="duration-text">0 hours</p>
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
@endpush