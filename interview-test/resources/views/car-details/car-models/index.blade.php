@extends('layouts.master')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="panel panel-primary brand-model-index">
            <div class="panel-heading">
                <h3 class="panel-title">Car Models</h3>
                <a href="{{ route('car.models.create') }}" class="btn btn-success btn-sm pull-right" style="margin-top: -5px;">
                    + New Model
                </a>
            </div>
            <div class="panel-body">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                        {{ session('success') }}
                    </div>
                @endif
                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                        {{ session('error') }}
                    </div>
                @endif

                <div class="row filter-row">
                    <div class="col-md-3">
                        <label>Filter by Brand:</label>
                        <select id="brand-filter" class="form-control filter-dropdown">
                            <option value="">All Brands</option>
                            @foreach($brands as $brand)
                                <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Model Name</th>
                                <th>Brand</th>
                                <th>Year</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="models-table-body">
                            @forelse($models as $model)
                                <tr data-brand="{{ $model->brand_id }}">
                                    <td>{{ $loop->iteration }}</td>
                                    <td><strong>{{ $model->name }}</strong></td>
                                    <td>{{ $model->brand->name ?? 'N/A' }}</td>
                                    <td>{{ $model->year ?? 'N/A' }}</td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="{{ route('car.models.edit', $model->id) }}" class="action-btn edit-btn" title="Edit Model">
                                                {!! \App\Helpers\IconHelper::edit(16) !!}
                                            </a>
                                            <button class="action-btn delete-btn delete-model" 
                                                    data-id="{{ $model->id }}" 
                                                    data-name="{{ $model->name }}" 
                                                    title="Delete Model">
                                                {!! \App\Helpers\IconHelper::delete(16) !!}
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center">No models found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="{{ asset('js/model-index.js') }}"></script>
@endpush
@endsection