@extends('layouts.master')

@section('content')
<div class="row">
    <div class="col-md-6 col-md-offset-3">
        <div class="panel panel-primary">
            <div class="panel-heading">
                <h3 class="panel-title">{{ isset($model) ? 'Edit Model' : 'New Model' }}</h3>
            </div>
            <div class="panel-body">
                <div id="alert-container"></div>

                <form id="model-form">
                    @csrf
                    @if(isset($model))
                        <input type="hidden" name="model_id" id="model_id" value="{{ $model->id }}">
                    @endif

                    <div class="form-group">
                        <label for="brand_id">Brand <span class="text-danger">*</span></label>
                        <select class="form-control" name="brand_id" id="brand_id" required>
                            <option value="">-- Select Brand --</option>
                            @foreach($brands as $brand)
                                <option value="{{ $brand->id }}" 
                                    {{ isset($model) && $model->brand_id == $brand->id ? 'selected' : '' }}>
                                    {{ $brand->name }}
                                </option>
                            @endforeach
                        </select>
                        <span class="text-danger error-msg" id="brand_id-error"></span>
                    </div>

                    <div class="form-group">
                        <label for="name">Model Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="name" id="name" 
                               value="{{ isset($model) ? $model->name : '' }}" 
                               placeholder="Enter model name..." required>
                        <span class="text-danger error-msg" id="name-error"></span>
                    </div>

                    <div class="form-group">
                        <label for="year">Year</label>
                        <input type="number" class="form-control" name="year" id="year" 
                               value="{{ isset($model) ? $model->year : '' }}" 
                               min="1900" max="{{ date('Y') }}" placeholder="e.g., 2020">
                        <span class="text-danger error-msg" id="year-error"></span>
                    </div>

                    <div class="form-group" style="margin-top:20px;">
                        <a href="{{ route('car.models.index') }}" class="btn btn-default">Cancel</a>
                        <button type="submit" class="btn btn-primary pull-right" id="submit-btn">
                            <span id="submit-text">{{ isset($model) ? 'Update Model' : 'Save Model' }}</span>
                            <span id="submit-spinner" style="display:none;">
                                <span class="spinner-border spinner-border-sm" role="status"></span> 
                                {{ isset($model) ? 'Updating...' : 'Saving...' }}
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="{{ asset('js/model-form.js') }}"></script>
@endpush
@endsection