@extends('layouts.master')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="panel panel-primary">
            <div class="panel-heading">
                <h3 class="panel-title">Dashboard</h3>
            </div>
            <div class="panel-body">
                <h4>Welcome, {{ Auth::user()->name }}!</h4>
                <p>You are logged in as: <strong>{{ Auth::user()->role ?? 'User' }}</strong></p>
                <hr>
                
                <div class="row">
                    <div class="col-md-4">
                        <div class="panel panel-info">
                            <div class="panel-heading">Quick Links</div>
                            <div class="panel-body">
                                <ul class="list-unstyled">
                                    <li><a href="{{ route('car') }}">View Cars</a></li>
                                    <li><a href="#">Profile</a></li>
                                    <li><a href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Logout</a></li>
                                    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">@csrf</form>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection