<x-guest-layout>
    
    <div class="panel-body">
        <h3 style="margin-top:0; margin-bottom:20px; text-align:center; color:#2c3e50;">Create Account</h3>

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul style="margin:0; padding-left:20px;">
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
                <input id="name" type="text" class="form-control" name="name" value="{{ old('name') }}" placeholder="John Doe">
                <div id="name-error" class="error-msg"></div>
            </div>

   <!-- ID Number -->
<div class="form-group">
    <label for="id_num">ID Number <span style="color:#dc3545;">*</span></label>
    <input id="id_num" type="text" class="form-control" name="id_num" value="{{ old('id_num') }}" placeholder="123456789V or 123456789012" maxlength="12">
    <div id="id_num-error" class="error-msg"></div>
    <div id="id-format-hint" class="id-format-hint show hint">Format: 123456789V (old) or 123456789012 (new)</div>
</div>

            <!-- Email -->
            <div class="form-group">
                <label for="email">Email Address <span style="color:#dc3545;">*</span></label>
                <input id="email" type="email" class="form-control" name="email" value="{{ old('email') }}" placeholder="you@example.com">
                <div id="email-error" class="error-msg"></div>
            </div>

           <!-- Password -->
            <div class="form-group">
                <label for="password">Password <span style="color:#dc3545;">*</span></label>
                <div class="password-wrapper">
                    <input id="password" type="password" class="form-control" name="password" placeholder="Min 8 characters">
                    <button type="button" class="eye-toggle" id="togglePass" aria-label="Toggle password visibility">
                        <img src="{{ asset('images/eye-open.svg') }}" alt="Show password" id="pass-icon" width="20" height="20">
                    </button>
                </div>
                <div class="strength-wrapper">
                    <div class="strength-bar">
                        <div id="strength-bar" class="strength-fill"></div>
                    </div>
                    <span id="strength-text" class="strength-text">Enter a password</span>
                </div>
                <div id="password-error" class="error-msg"></div>
            </div>

            <!-- Confirm Password -->
            <div class="form-group">
                <label for="password_confirmation">Confirm Password <span style="color:#dc3545;">*</span></label>
                <div class="password-wrapper">
                    <input id="password_confirmation" type="password" class="form-control" name="password_confirmation" placeholder="Confirm password">
                    <button type="button" class="eye-toggle" id="toggleConfirm" aria-label="Toggle password visibility">
                        <img src="{{ asset('images/eye-open.svg') }}" alt="Show password" id="confirm-icon" width="20" height="20">
                    </button>
                </div>
                <div id="password_confirmation-error" class="error-msg"></div>
            </div>

            <!-- Age -->
            <div class="form-group">
                <label for="age">Age <span style="color:#dc3545;">*</span></label>
                <input id="age" type="number" class="form-control" name="age" value="{{ old('age') }}" placeholder="18" min="18" max="100">
                <div id="age-error" class="error-msg"></div>
            </div>

            <!-- Gender -->
            <div class="form-group">
                <label for="gender">Gender <span style="color:#dc3545;">*</span></label>
                <select id="gender" class="form-control" name="gender">
                    <option value="">Select Gender</option>
                    <option value="Male" {{ old('gender') == 'Male' ? 'selected' : '' }}>Male</option>
                    <option value="Female" {{ old('gender') == 'Female' ? 'selected' : '' }}>Female</option>
                    <option value="Other" {{ old('gender') == 'Other' ? 'selected' : '' }}>Other</option>
                </select>
                <div id="gender-error" class="error-msg"></div>
            </div>

            <!-- Address -->
            <div class="form-group">
                <label for="address">Address <span style="color:#dc3545;">*</span></label>
                <textarea id="address" class="form-control" name="address" rows="2" placeholder="123 Main St, City">{{ old('address') }}</textarea>
                <div id="address-error" class="error-msg"></div>
            </div>

             <button type="submit" class="btn-dark btn-block">Register</button>

            <div class="text-center">
                <a href="{{ route('login') }}">Already have an account? <strong>Login</strong></a>
            </div>
        </form>
    </div>


    <!-- JS -->
     <script>
        // Pass asset paths to JavaScript
        const ASSET_PATHS = {
            eyeOpen: "{{ asset('images/eye-open.svg') }}",
            eyeClosed: "{{ asset('images/eye-closed.svg') }}"
        };
    </script>
    <script src="{{ asset('js/register.js') }}"></script>
</x-guest-layout>