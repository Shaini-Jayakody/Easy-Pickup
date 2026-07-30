@extends('layouts.master')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="panel panel-primary brand-model-index">
            <div class="panel-heading">
                <h3 class="panel-title">Car Brands</h3>
                <a href="{{ route('car.brands.create') }}" class="btn btn-success btn-sm pull-right" style="margin-top: -5px;">
                    + New Brand
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

                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Brand Name</th>
                                <th>Models Count</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($brands as $brand)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td><strong>{{ $brand->name }}</strong></td>
                                    <td>{{ $brand->models_count }}</td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="{{ route('car.brands.edit', $brand->id) }}" class="action-btn edit-btn" title="Edit Brand">
                                                {!! \App\Helpers\IconHelper::edit(16) !!}
                                            </a>
                                            <button class="action-btn delete-btn delete-brand" 
                                                    data-id="{{ $brand->id }}" 
                                                    data-name="{{ $brand->name }}" 
                                                    title="Delete Brand">
                                                {!! \App\Helpers\IconHelper::delete(16) !!}
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center">No brands found.</td>
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
<script src="{{ asset('js/brand-index.js') }}"></script>
@endpush
@endsection