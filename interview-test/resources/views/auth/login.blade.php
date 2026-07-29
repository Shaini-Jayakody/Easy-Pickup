<x-guest-layout>
    <!-- Success Message -->
    @if (session('success'))
        <div class="alert alert-success" style="background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 12px 16px; border-radius: 6px; margin-bottom: 20px;">
            {{ session('success') }}
        </div>
    @endif

    <!-- Error Messages -->
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="list-unstyled" style="margin:0;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div class="form-group">
            <label for="email">Email <span style="color:#dc3545;">*</span></label>
            <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" 
                   name="email" value="{{ old('email') }}" required autofocus placeholder="you@example.com">
        </div>

        <!-- Password with Eye Icon -->
        <div class="form-group">
            <label for="password">Password <span style="color:#dc3545;">*</span></label>
            <div class="password-wrapper">
                <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" 
                       name="password" required placeholder="Enter your password">
                <button type="button" class="eye-toggle" id="toggleLoginPass" 
                        data-eye-open="{{ asset('images/eye-open.svg') }}"
                        data-eye-closed="{{ asset('images/eye-closed.svg') }}"
                        aria-label="Toggle password visibility">
                    <img src="{{ asset('images/eye-open.svg') }}" alt="Show password" id="login-pass-icon" width="20" height="20">
                </button>
            </div>
        </div>

        <div class="form-group">
            <div class="checkbox">
                <label>
                    <input type="checkbox" name="remember"> Remember Me
                </label>
            </div>
        </div>

        <div class="form-group">
            <button type="submit" class="btn btn-primary btn-block">Login</button>
        </div>

        <div class="text-center" style="margin-top: 15px;">
            <a href="{{ route('register') }}" style="color: #666; text-decoration: none;">
                Don't have an account? <strong style="color: #87CEEB;">Register</strong>
            </a>
            <br>
            <a href="{{ route('password.request') }}" style="color: #666; text-decoration: none; font-size: 13px;">
                Forgot Password?
            </a>
        </div>
    </form>

    @push('scripts')
    <script src="{{ asset('js/login-form.js') }}"></script>
    @endpush
</x-guest-layout>