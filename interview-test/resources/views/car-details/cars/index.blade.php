@extends('layouts.master')

@section('content')
    <table class="table table-striped">
        <thead>
        <tr>
            <th>#</th>
            <th>REF NO</th>
            <th>CAR NAME</th>
            <th>CAR MODEL</th>
            <th>CAR ENGINE</th>
            <th></th>
        </tr>
        </thead>
        <tbody>
        @foreach($cars as $index => $car)
            <tr>
                <td>{{ $cars->currentPage() + $index}}</td>
                <td>{{ $car->ref_no }}</td>
                <td>{{ $car->name }}</td>
                <td>{{ $car->model->name }}</td>
                <td>{{ $car->model->brand->name }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
    <ul class="pagination">
        {{ $cars->links() }}
    </ul>
@endsection