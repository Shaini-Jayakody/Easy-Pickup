<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Easy Pickup</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
    <div class="splash-container">
        <div class="splash-content">
            <div class="divider-line"></div>

            <div class="welcome-message">
                <h4>Welcome to</h4>
                <div class="brand">
                    <h1>Easy <span>Pickup</span></h1>
                </div>
            </div>

            <!-- Car Animation -->
            <div class="car-animation-container">
                <div class="road-line"></div>
                <div class="car-wrapper">
                    <img src="{{ asset('images/car.png') }}" alt="Car" class="moving-car">
                </div>
                <div class="progress-fill"></div>
            </div>
        </div>
    </div>

    <script>
        // Auto-redirect to home page after 3 seconds
        setTimeout(function() {
            window.location.href = "{{ url('/home') }}";
        }, 3000);
    </script>
</body>
</html>