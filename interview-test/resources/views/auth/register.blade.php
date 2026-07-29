<x-guest-layout>
    <!-- Success/Error Messages -->
    @if (session('success'))
        <div class="alert alert-success" style="background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 12px 16px; border-radius: 6px; margin-bottom: 20px;">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="list-unstyled" style="margin:0;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('register') }}" id="register-form">
        @csrf

        <!-- Name -->
        <div class="form-group">
            <label for="name">Full Name <span style="color:#dc3545;">*</span></label>
            <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" 
                   name="name" value="{{ old('name') }}" required autofocus placeholder="John Doe">
            <span class="text-danger error-msg" id="name-error"></span>
        </div>

        <!-- ID Number -->
        <div class="form-group">
            <label for="id_num">ID Number <span style="color:#dc3545;">*</span></label>
            <input id="id_num" type="text" class="form-control @error('id_num') is-invalid @enderror" 
                   name="id_num" value="{{ old('id_num') }}" required placeholder="123456789V or 123456789012">
            <span class="text-danger error-msg" id="id_num-error"></span>
        </div>

        <!-- Email -->
        <div class="form-group">
            <label for="email">Email <span style="color:#dc3545;">*</span></label>
            <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" 
                   name="email" value="{{ old('email') }}" required placeholder="you@example.com">
            <span class="text-danger error-msg" id="email-error"></span>
        </div>

        <!-- Password with Eye Icon -->
        <div class="form-group">
            <label for="password">Password <span style="color:#dc3545;">*</span></label>
            <div class="password-wrapper">
                <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" 
                       name="password" required placeholder="Enter your password">
                <button type="button" class="eye-toggle" id="togglePass" aria-label="Toggle password visibility">
                    <img src="{{ asset('images/eye-open.svg') }}" alt="Show password" id="pass-icon" width="20" height="20">
                </button>
            </div>
            <span class="text-danger error-msg" id="password-error"></span>
        </div>

        <!-- Password Strength -->
        <div class="strength-wrapper">
            <div class="strength-bar">
                <div class="strength-fill" id="strength-bar"></div>
            </div>
            <span class="strength-text" id="strength-text">Enter a password</span>
        </div>

        <!-- Confirm Password with Eye Icon -->
        <div class="form-group">
            <label for="password_confirmation">Confirm Password <span style="color:#dc3545;">*</span></label>
            <div class="password-wrapper">
                <input id="password_confirmation" type="password" class="form-control" 
                       name="password_confirmation" required placeholder="Confirm your password">
                <button type="button" class="eye-toggle" id="toggleConfirm" aria-label="Toggle password visibility">
                    <img src="{{ asset('images/eye-open.svg') }}" alt="Show password" id="confirm-icon" width="20" height="20">
                </button>
            </div>
            <span class="text-danger error-msg" id="password_confirmation-error"></span>
        </div>

        <!-- Age -->
        <div class="form-group">
            <label for="age">Age <span style="color:#dc3545;">*</span></label>
            <input id="age" type="number" class="form-control @error('age') is-invalid @enderror" 
                   name="age" value="{{ old('age') }}" required placeholder="18" min="18" max="100">
            <span class="text-danger error-msg" id="age-error"></span>
        </div>

        <!-- Gender -->
        <div class="form-group">
            <label for="gender">Gender <span style="color:#dc3545;">*</span></label>
            <select id="gender" class="form-control @error('gender') is-invalid @enderror" name="gender" required>
                <option value="">Select Gender</option>
                <option value="Male" {{ old('gender') == 'Male' ? 'selected' : '' }}>Male</option>
                <option value="Female" {{ old('gender') == 'Female' ? 'selected' : '' }}>Female</option>
                <option value="Other" {{ old('gender') == 'Other' ? 'selected' : '' }}>Other</option>
            </select>
            <span class="text-danger error-msg" id="gender-error"></span>
        </div>

        <!-- Address -->
        <div class="form-group">
            <label for="address">Address <span style="color:#dc3545;">*</span></label>
            <textarea id="address" class="form-control @error('address') is-invalid @enderror" 
                      name="address" required placeholder="Your address" rows="2">{{ old('address') }}</textarea>
            <span class="text-danger error-msg" id="address-error"></span>
        </div>

        <div class="form-group">
            <button type="submit" class="btn-submit">Register</button>
        </div>

        <div class="login-link">
            <a href="{{ route('login') }}">
                Already have an account? <strong>Login</strong>
            </a>
        </div>
    </form>

    @push('scripts')
    <script src="{{ asset('js/register.js') }}"></script>
    @endpush
</x-guest-layout>