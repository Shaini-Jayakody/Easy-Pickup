@extends('layouts.master')

@section('content')
<div class="row">
    <div class="col-md-6 col-md-offset-3">
        <div class="panel panel-primary">
            <div class="panel-heading">
                <h3 class="panel-title">{{ isset($brand) ? 'Edit Brand' : 'New Brand' }}</h3>
            </div>
            <div class="panel-body">
                <div id="alert-container"></div>

                <form id="brand-form">
                    @csrf
                    @if(isset($brand))
                        <input type="hidden" name="brand_id" id="brand_id" value="{{ $brand->id }}">
                    @endif

                    <div class="form-group">
                        <label for="name">Brand Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="name" id="name" 
                               value="{{ isset($brand) ? $brand->name : '' }}" 
                               placeholder="Enter brand name..." required>
                        <span class="text-danger error-msg" id="name-error"></span>
                    </div>

                    <div class="form-group" style="margin-top:20px;">
                        <a href="{{ route('car.brands.index') }}" class="btn btn-default">Cancel</a>
                        <button type="submit" class="btn btn-primary pull-right" id="submit-btn">
                            <span id="submit-text">{{ isset($brand) ? 'Update Brand' : 'Save Brand' }}</span>
                            <span id="submit-spinner" style="display:none;">
                                <span class="spinner-border spinner-border-sm" role="status"></span> 
                                {{ isset($brand) ? 'Updating...' : 'Saving...' }}
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="{{ asset('js/brand-form.js') }}"></script>
@endpush
@endsection