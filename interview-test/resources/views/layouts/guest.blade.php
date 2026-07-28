<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Car Sale</title>

    <!-- Bootstrap 3 CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
    
    <!-- Your Custom CSS -->
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">

    @stack('styles')
</head>
<body>
    <div class="container">
        <div class="row">
            <div class="col-md-6 col-md-offset-3" style="margin-top: 30px;">
                <div class="panel panel-default">
                    <div class="panel-heading text-center" style="background: #f5f7fa; border-bottom: 2px solid #e0e0e0; padding: 15px 20px;">
                        <div class="home-brand" style="font-size: 28px; font-weight: 700; color: #2c3e50; display: inline-block;">
                            Easy <span style="color: #87CEEB;">Pickup</span>
                        </div>
                    </div>
                    <div class="panel-body">
                        {{ $slot }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
    @stack('scripts')
</body>
</html>