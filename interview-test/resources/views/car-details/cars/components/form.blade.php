@extends('layouts.master')

@section('content')
<div class="panel panel-primary col-sm-offset-3 col-sm-6">
    <div class="panel-heading" style="padding: 10px 15px;">
        <div style="text-align: center; font-size: 16px; font-weight: 600;">
            @if(isset($car))
                Edit Car
            @else
                Add Car
            @endif
        </div>
    </div>
    <div class="panel-body" style="padding: 15px 20px;">
        <div id="alert-container"></div>

        <form id="car-form">
            @csrf
            
            <!-- Hidden field for Edit mode -->
            @if(isset($car))
                @method('PUT')
                <input type="hidden" name="car_id" id="car_id" value="{{ $car->id }}">
            @else
                <input type="hidden" name="car_id" id="car_id" value="">
            @endif

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group" style="margin-bottom: 12px;">
                        <label for="car_brand" style="font-size: 13px; font-weight: 600;">Brand <span style="color:#dc3545;">*</span></label>
                        <select class="form-control select2" name="car_brand" id="car_brand" style="width: 100%; height: 34px;" required>
                            <option value="">Search Brand...</option>
                            @foreach($brands as $brand)
                                <option value="{{$brand->id}}" {{ isset($car) && $car->model->brand_id == $brand->id ? 'selected' : '' }}>
                                    {{$brand->name}}
                                </option>
                            @endforeach
                        </select>
                        <span class="text-danger error-msg" id="car_brand-error" style="font-size: 11px;"></span>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group" style="margin-bottom: 12px;">
                        <label for="car_model" style="font-size: 13px; font-weight: 600;">Model <span style="color:#dc3545;">*</span></label>
                        <select class="form-control select2" name="car_model" id="car_model" style="width: 100%; height: 34px;" required>
                            <option value="">Search Model...</option>
                            @foreach($models as $model)
                                <option value="{{$model->id}}" data-brand="{{$model->brand_id}}" 
                                    {{ isset($car) && $car->model_id == $model->id ? 'selected' : '' }}>
                                    {{$model->name}}
                                </option>
                            @endforeach
                        </select>
                        <span class="text-danger error-msg" id="car_model-error" style="font-size: 11px;"></span>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group" style="margin-bottom: 12px;">
                        <label for="car_name" style="font-size: 13px; font-weight: 600;">Car Name <span style="color:#dc3545;">*</span></label>
                        <input type="text" class="form-control" name="car_name" id="car_name" placeholder="Car name" 
                               value="{{ isset($car) ? $car->name : '' }}" style="height: 34px; font-size: 13px;" required>
                        <span class="text-danger error-msg" id="car_name-error" style="font-size: 11px;"></span>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group" style="margin-bottom: 12px;">
                        <label for="car_color" style="font-size: 13px; font-weight: 600;">Color <span style="color:#dc3545;">*</span></label>
                        <input type="text" class="form-control" name="car_color" id="car_color" placeholder="Color" 
                               value="{{ isset($car) ? $car->color : '' }}" style="height: 34px; font-size: 13px;" required>
                        <span class="text-danger error-msg" id="car_color-error" style="font-size: 11px;"></span>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group" style="margin-bottom: 12px;">
                        <label for="number_plate" style="font-size: 13px; font-weight: 600;">Number Plate <span style="color:#dc3545;">*</span></label>
                        <input type="text" class="form-control" name="number_plate" id="number_plate" placeholder="ABC-1234" 
                               value="{{ isset($car) ? $car->number_plate : '' }}" style="height: 34px; font-size: 13px;" required>
                        <span class="text-danger error-msg" id="number_plate-error" style="font-size: 11px;"></span>
                        <small style="font-size: 10px; color: #888;">Unique</small>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group" style="margin-bottom: 12px;">
                        <label for="rent_price_per_hour" style="font-size: 13px; font-weight: 600;">Rent Price/Hour (Rs.) <span style="color:#dc3545;">*</span></label>
                        <input type="number" class="form-control" name="rent_price_per_hour" id="rent_price_per_hour" 
                               placeholder="2500" step="10" min="500" 
                               value="{{ isset($car) ? $car->rent_price_per_hour : '' }}" 
                               style="height: 34px; font-size: 13px;" required>
                        <span class="text-danger error-msg" id="rent_price_per_hour-error" style="font-size: 11px;"></span>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group" style="margin-bottom: 12px;">
                        <label for="eng_number" style="font-size: 13px; font-weight: 600;">Engine Number <span style="color:#dc3545;">*</span></label>
                        <input type="text" class="form-control" name="eng_number" id="eng_number" placeholder="Engine number" 
                               value="{{ isset($car) ? $car->engine_number : '' }}" style="height: 34px; font-size: 13px;" required>
                        <span class="text-danger error-msg" id="eng_number-error" style="font-size: 11px;"></span>
                        <small style="font-size: 10px; color: #888;">Unique</small>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group" style="margin-bottom: 12px;">
                        <label for="chas_number" style="font-size: 13px; font-weight: 600;">Chassis Number <span style="color:#dc3545;">*</span></label>
                        <input type="text" class="form-control" name="chas_number" id="chas_number" placeholder="Chassis number" 
                               value="{{ isset($car) ? $car->chassis_number : '' }}" style="height: 34px; font-size: 13px;" required>
                        <span class="text-danger error-msg" id="chas_number-error" style="font-size: 11px;"></span>
                        <small style="font-size: 10px; color: #888;">Unique</small>
                    </div>
                </div>
            </div>

            <div class="form-group" style="margin-bottom: 12px;">
                <label for="car_trans" style="font-size: 13px; font-weight: 600;">Transmission <span style="color:#dc3545;">*</span></label>
                <select class="form-control" name="car_trans" id="car_trans" style="height: 34px; font-size: 13px;" required>
                    <option value="">Select Transmission</option>
                    <option value="Auto" {{ isset($car) && $car->transmition == 'Auto' ? 'selected' : '' }}>Auto</option>
                    <option value="Manual" {{ isset($car) && $car->transmition == 'Manual' ? 'selected' : '' }}>Manual</option>
                    <option value="Tiptronic" {{ isset($car) && $car->transmition == 'Tiptronic' ? 'selected' : '' }}>Tiptronic</option>
                </select>
                <span class="text-danger error-msg" id="car_trans-error" style="font-size: 11px;"></span>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 15px;">
                <a href="{{ route('car') }}" class="btn btn-default btn-sm" style="padding: 6px 20px; font-size: 13px;">Cancel</a>
                <button type="submit" class="btn btn-primary btn-sm" id="submit-btn" style="padding: 6px 20px; font-size: 13px;" disabled>
                    <span id="submit-text">
                        @if(isset($car))
                            Update
                        @else
                            Submit
                        @endif
                    </span>
                    <span id="submit-spinner" style="display: none;">
                        <span class="spinner-border spinner-border-sm" role="status"></span> 
                        @if(isset($car))
                            Updating...
                        @else
                            Saving...
                        @endif
                    </span>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/car.js') }}"></script>
<script src="{{ asset('js/car-form.js') }}"></script>

<script>
    // Pass car ID to JavaScript for uniqueness checks during update
    var carId = {{ isset($car) ? $car->id : 'null' }};
    
    // Pass edit mode flag
    var isEditMode = {{ isset($car) ? 'true' : 'false' }};
    
    console.log('Car form loaded - Edit Mode:', isEditMode);
    console.log('Car ID:', carId);
</script>
@endpush