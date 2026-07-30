@extends('layouts.master')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="panel panel-primary">
            <div class="panel-heading">
                <h3 class="panel-title">Dashboard</h3>
            </div>
            <div class="panel-body">
                <!-- Welcome Section -->
                <div class="welcome-section">
                    <h4>Welcome, {{ Auth::user()->name }}!</h4>
                    <p>You are logged in as: <strong>{{ ucfirst(Auth::user()->role ?? 'User') }}</strong></p>
                </div>

                <hr>

                <!-- Statistics Cards -->
                <div class="row stats-row">
                    <div class="col-md-3 col-sm-6">
                        <div class="stat-card stat-card-primary">
                            <div class="stat-icon">
                                <span class="glyphicon glyphicon-th-list"></span>
                            </div>
                            <div class="stat-info">
                                <span class="stat-number" id="total-bookings">0</span>
                                <span class="stat-label">Total Bookings</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="stat-card stat-card-success">
                            <div class="stat-icon">
                                <span class="glyphicon glyphicon-ok-circle"></span>
                            </div>
                            <div class="stat-info">
                                <span class="stat-number" id="active-bookings">0</span>
                                <span class="stat-label">Active Bookings</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="stat-card stat-card-warning">
                            <div class="stat-icon">
                                <span class="glyphicon glyphicon-time"></span>
                            </div>
                            <div class="stat-info">
                                <span class="stat-number" id="pending-bookings">0</span>
                                <span class="stat-label">Pending</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="stat-card stat-card-danger">
                            <div class="stat-icon">
                                <span class="glyphicon glyphicon-remove-circle"></span>
                            </div>
                            <div class="stat-info">
                                <span class="stat-number" id="cancelled-bookings">0</span>
                                <span class="stat-label">Cancelled</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts Section -->
                <div class="row charts-row">
                    <div class="col-md-6">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <h4 class="panel-title">Booking Status Distribution</h4>
                            </div>
                            <div class="panel-body">
                                <canvas id="statusChart" height="200"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <h4 class="panel-title">Monthly Booking Trends</h4>
                            </div>
                            <div class="panel-body">
                                <canvas id="trendChart" height="200"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <h4 class="panel-title">Recent Activity</h4>
                            </div>
                            <div class="panel-body">
                                <div id="recent-activity">
                                    <div class="text-center text-muted">
                                        <span class="spinner-border spinner-border-sm" role="status"></span> Loading...
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="{{ asset('js/dashboard.js') }}"></script>
@endpush