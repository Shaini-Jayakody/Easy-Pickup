<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Easy Pickup - Home</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
    <div class="home-wrapper">
        <div class="home-content">
            <div class="home-brand">
                Easy <span>Pickup</span>
            </div>
            <p class="home-subtitle">Your Trusted Car Rental Partner</p>

            <p class="home-description">
                <strong>Rent a car</strong> quickly and easily with Easy Pickup.
                Choose from a wide range of vehicles at competitive prices.
                Book your ride in just a few clicks!
            </p>

            <div class="btn-group">
                <a href="#" class="btn btn-primary">Sign In</a>
                <a href="#" class="btn btn-dark">Sign Up</a>
            </div>

            <div class="divider">
                <span>or</span>
            </div>

            <a href="{{ url('/car') }}" class="btn btn-ghost">Browse Cars as Guest</a>

            <div class="features">
                <span>
                    <svg viewBox="0 0 24 24"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/></svg>
                    Locations
                </span>
                <span>
                    <svg viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg>
                    Easy Booking
                </span>
                <span>
                    <svg viewBox="0 0 24 24"><path d="M11.99 2C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zM12 20c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8zm.5-13H11v6l5.25 3.15.75-1.23-4.5-2.67z"/></svg>
                    24/7 Support
                </span>
            </div>

            <div class="footer">
                &copy; {{ date('Y') }} Easy Pickup. All rights reserved.
            </div>
        </div>
    </div>
</body>
</html>