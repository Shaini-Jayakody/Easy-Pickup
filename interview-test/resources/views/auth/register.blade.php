<x-guest-layout>
    <!-- ... form fields ... -->
    
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

    @push('scripts')
    <script src="{{ asset('js/login.js') }}"></script>
    @endpush
</x-guest-layout>