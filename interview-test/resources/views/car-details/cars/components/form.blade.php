@extends('layouts.master')

@section('content')
    <div class="panel panel-primary col-sm-offset-3 col-sm-6">
        <div class="panel-heading"><div class="col-sm-offset-5">Add Cars</div></div>
        <div class="panel-body">
                <form action="{{route('car.save')}}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label for="car_brand">Brand:</label>
                        <select class="form-control" name="car_brand" id="car_brand">
                            <option>Pleace Select Brand</option>
                            @foreach($brands as $brand)
                                <option value="{{$brand->id}}">{{$brand->name}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="car_model">Model:</label>
                        <select class="form-control" name="car_model" id="car_model">
                            <option>Pleace Select Model</option>
                            @foreach($models as $model)
                                <option value="{{$model->id}}">{{$model->name}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="car_name">Car Name:</label>
                        <input type="text" class="form-control" name="car_name" id="car_name">
                    </div>
                    <div class="form-group">
                        <label for="car_color">Car Color:</label>
                        <input type="text" class="form-control" name="car_color" id="car_color">
                    </div>
                    <div class="form-group">
                        <label for="car_color">Engine Number:</label>
                        <input type="text" class="form-control" name="eng_number" id="eng_number">
                    </div>
                    <div class="form-group">
                        <label for="chas_number">Chassis Number:</label>
                        <input type="text" class="form-control" name="chas_number" id="chas_number">
                    </div>
                    <div class="form-group">
                        <label for="car_trans">Transmission:</label>
                        <select class="form-control" name="car_trans" id="car_trans">
                            <option>Auto</option>
                            <option>Manuel</option>
                            <option>Tiptronic</option>
                        </select>
                    </div>
                    <button type="submit" class="btn-sm btn-primary pull-right">Submit</button>
                </form>
        </div>
    </div>
@endsection