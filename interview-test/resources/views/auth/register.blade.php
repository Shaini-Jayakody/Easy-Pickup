<x-guest-layout>
    <div class="panel-body">
        @php
            $rules = App\Helpers\ValidationHelper::getRules();
        @endphp

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="list-unstyled">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('register') }}" id="register-form">
            @csrf

            <div class="form-group">
                <label for="name">Full Name <span class="text-danger">*</span></label>
                <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" 
                       name="name" value="{{ old('name') }}" required autofocus
                       data-validate="name">
                <span class="text-danger error-message" id="name-error"></span>
                @error('name')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="id_num">ID Number <span class="text-danger">*</span></label>
                <input id="id_num" type="text" class="form-control @error('id_num') is-invalid @enderror" 
                       name="id_num" value="{{ old('id_num') }}" required
                       data-validate="id_num">
                <span class="text-danger error-message" id="id_num-error"></span>
                @error('id_num')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="email">Email Address <span class="text-danger">*</span></label>
                <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" 
                       name="email" value="{{ old('email') }}" required
                       data-validate="email">
                <span class="text-danger error-message" id="email-error"></span>
                @error('email')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="password">Password <span class="text-danger">*</span></label>
                <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" 
                       name="password" required
                       data-validate="password">
                <div class="password-strength-container" style="margin-top: 5px;">
                    <div class="progress" style="height: 5px;">
                        <div id="password-strength-bar" class="progress-bar" role="progressbar" 
                             style="width: 0%; background-color: #ddd;"></div>
                    </div>
                    <span id="password-strength-text" class="text-muted small"></span>
                </div>
                <span class="text-danger error-message" id="password-error"></span>
                @error('password')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="password_confirmation">Confirm Password <span class="text-danger">*</span></label>
                <input id="password_confirmation" type="password" class="form-control" 
                       name="password_confirmation" required
                       data-validate="password_confirmation">
                <span class="text-danger error-message" id="password_confirmation-error"></span>
            </div>

            <div class="form-group">
                <label for="age">Age <span class="text-danger">*</span></label>
                <input id="age" type="number" class="form-control @error('age') is-invalid @enderror" 
                       name="age" value="{{ old('age') }}" required min="18" max="100"
                       data-validate="age">
                <span class="text-danger error-message" id="age-error"></span>
                @error('age')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="gender">Gender <span class="text-danger">*</span></label>
                <select id="gender" class="form-control @error('gender') is-invalid @enderror" 
                        name="gender" required data-validate="gender">
                    <option value="">Select Gender</option>
                    <option value="Male" {{ old('gender') == 'Male' ? 'selected' : '' }}>Male</option>
                    <option value="Female" {{ old('gender') == 'Female' ? 'selected' : '' }}>Female</option>
                    <option value="Other" {{ old('gender') == 'Other' ? 'selected' : '' }}>Other</option>
                </select>
                <span class="text-danger error-message" id="gender-error"></span>
                @error('gender')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="address">Address <span class="text-danger">*</span></label>
                <textarea id="address" class="form-control @error('address') is-invalid @enderror" 
                          name="address" rows="2" required
                          data-validate="address">{{ old('address') }}</textarea>
                <span class="text-danger error-message" id="address-error"></span>
                @error('address')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <button type="submit" class="btn btn-primary btn-block" id="submit-btn">Register</button>
            </div>

            <div class="text-center">
                <a href="{{ route('login') }}">Already have an account? Login</a>
            </div>
        </form>
    </div>
</x-guest-layout>

@push('scripts')
<script>
$(document).ready(function() {
    // Get validation rules from PHP trait via helper
    var rules = @json($rules);

    // Real-time validation on input/change
    $('[data-validate]').on('input change', function() {
        var field = $(this).data('validate');
        validateField($(this), field);
    });

    function validateField($input, fieldName) {
        var value = $input.val().trim();
        var $error = $('#' + fieldName + '-error');
        var rule = rules[fieldName];
        
        if (!rule) return true;
        
        // Required validation
        if (rule.required && value.length === 0) {
            showError($input, $error, rule.messages.required);
            return false;
        }
        
        // Min length validation
        if (value.length > 0 && rule.min && value.length < rule.min) {
            showError($input, $error, rule.messages.min);
            return false;
        }
        
        // Max length validation
        if (value.length > 0 && rule.max && value.length > rule.max) {
            showError($input, $error, rule.messages.max);
            return false;
        }
        
        // Pattern validation
        if (value.length > 0 && rule.pattern) {
            var regex = new RegExp(rule.pattern.slice(1, -1)); // Remove the slashes
            if (!regex.test(value)) {
                showError($input, $error, rule.messages.pattern);
                return false;
            }
        }
        
        // Values validation (for dropdowns)
        if (value.length > 0 && rule.values && !rule.values.includes(value)) {
            showError($input, $error, rule.messages.values);
            return false;
        }
        
        // Age specific validation
        if (fieldName === 'age' && value.length > 0) {
            var age = parseInt(value);
            if (isNaN(age)) {
                showError($input, $error, 'Please enter a valid age.');
                return false;
            }
            if (rule.min && age < rule.min) {
                showError($input, $error, rule.messages.min);
                return false;
            }
            if (rule.max && age > rule.max) {
                showError($input, $error, rule.messages.max);
                return false;
            }
        }
        
        // Password confirmation
        if (fieldName === 'password') {
            checkPasswordStrength(value);
            validatePasswordConfirmation();
        }
        
        if (fieldName === 'password_confirmation') {
            validatePasswordConfirmation();
        }
        
        // All valid
        showSuccess($input, $error);
        return true;
    }

    function showError($input, $error, message) {
        $error.text(message).show();
        $input.css('border-color', '#a94442').removeClass('is-valid').addClass('is-invalid');
    }

    function showSuccess($input, $error) {
        $error.text('').hide();
        $input.css('border-color', '#3c763d').removeClass('is-invalid').addClass('is-valid');
    }

    function checkPasswordStrength(password) {
        var strength = 0;
        if (password.length >= 8) strength++;
        if (/[a-z]/.test(password)) strength++;
        if (/[A-Z]/.test(password)) strength++;
        if (/[0-9]/.test(password)) strength++;
        if (/[@$!%*?&]/.test(password)) strength++;
        
        var colors = ['#ddd', '#ff4444', '#ff6666', '#ffaa44', '#44bb44', '#22aa22'];
        var labels = ['', 'Very Weak', 'Weak', 'Fair', 'Good', 'Strong'];
        var width = (strength / 5 * 100);
        
        $('#password-strength-bar').css({
            'width': width + '%',
            'background-color': colors[strength]
        });
        $('#password-strength-text').text(labels[strength] || '');
    }

    function validatePasswordConfirmation() {
        var password = $('#password').val();
        var confirm = $('#password_confirmation').val();
        var $error = $('#password_confirmation-error');
        var $input = $('#password_confirmation');
        
        if (confirm.length === 0) {
            showError($input, $error, 'Please confirm your password.');
            return false;
        }
        if (password !== confirm) {
            showError($input, $error, 'Passwords do not match.');
            return false;
        }
        showSuccess($input, $error);
        return true;
    }

    // Form submission validation
    $('#register-form').on('submit', function(e) {
        var isValid = true;
        
        $('[data-validate]').each(function() {
            var field = $(this).data('validate');
            if (!validateField($(this), field)) {
                isValid = false;
            }
        });
        
        if (!isValid) {
            e.preventDefault();
            // Scroll to first error
            var firstError = $('.error-message:visible').first();
            if (firstError.length) {
                $('html, body').animate({
                    scrollTop: firstError.closest('.form-group').offset().top - 100
                }, 500);
                firstError.closest('.form-group').find('input, select, textarea').focus();
            }
        }
    });
});
</script>
@endpush