/**
 * Registration Form Validation
 * Professional validation with real-time feedback
 */

document.addEventListener('DOMContentLoaded', function() {
    console.log('Registration script loaded!');

    // Get all elements 
    const name = document.getElementById('name');
    const idNum = document.getElementById('id_num');
    const email = document.getElementById('email');
    const password = document.getElementById('password');
    const confirmPass = document.getElementById('password_confirmation');
    const age = document.getElementById('age');
    const gender = document.getElementById('gender');
    const address = document.getElementById('address');
    const form = document.getElementById('register-form');

    // Error elements
    const nameErr = document.getElementById('name-error');
    const idNumErr = document.getElementById('id_num-error');
    const emailErr = document.getElementById('email-error');
    const passErr = document.getElementById('password-error');
    const confirmErr = document.getElementById('password_confirmation-error');
    const ageErr = document.getElementById('age-error');
    const genderErr = document.getElementById('gender-error');
    const addressErr = document.getElementById('address-error');

    // Strength elements
    const strengthBar = document.getElementById('strength-bar');
    const strengthText = document.getElementById('strength-text');

    //Eye icon elements
    const passIcon = document.getElementById('pass-icon');
    const confirmIcon = document.getElementById('confirm-icon');

    // Asset paths from PHP 
    const EYE_OPEN = typeof ASSET_PATHS !== 'undefined' ? ASSET_PATHS.eyeOpen : '/images/eye-open.svg';
    const EYE_CLOSED = typeof ASSET_PATHS !== 'undefined' ? ASSET_PATHS.eyeClosed : '/images/eye-closed.svg';

    // Helper Functions
    function setValid(input) {
        input.classList.remove('invalid');
        input.classList.add('valid');
    }

    function setInvalid(input, errorEl, msg) {
        input.classList.remove('valid');
        input.classList.add('invalid');
        errorEl.textContent = msg;
        errorEl.style.display = 'block';
        errorEl.style.color = '#dc3545';
        console.log('❌ Error:', msg);
    }

    function setDefault(input) {
        input.classList.remove('valid', 'invalid');
    }

    function clearError(errorEl) {
        errorEl.textContent = '';
        errorEl.style.display = 'none';
    }

    //Password Strength
    function updateStrength(pwd) {
        let score = 0;
        if (pwd.length >= 8) score++;
        if (/[a-z]/.test(pwd)) score++;
        if (/[A-Z]/.test(pwd)) score++;
        if (/[0-9]/.test(pwd)) score++;
        if (/[@$!%*?&]/.test(pwd)) score++;

        const levels = [
            { w: '0%', c: '#ddd', t: 'Enter a password' },
            { w: '20%', c: '#ff4444', t: 'Very Weak' },
            { w: '40%', c: '#ff6666', t: 'Weak' },
            { w: '60%', c: '#ffaa44', t: 'Fair' },
            { w: '80%', c: '#44bb44', t: 'Good' },
            { w: '100%', c: '#22aa22', t: 'Strong' }
        ];
        
        const l = levels[score] || levels[0];
        if (strengthBar) {
            strengthBar.style.width = l.w;
            strengthBar.style.background = l.c;
        }
        if (strengthText) {
            strengthText.textContent = l.t;
            strengthText.style.color = l.c;
        }
    }

    //ID NUMBER VALIDATION 
    function validateIdNumber(id) {
        id = id.trim().toUpperCase();
        
        if (id.length === 0) {
            return { valid: false, message: 'ID Number is required.', type: 'error' };
        }
        
        // Old format: 9 digits + 1 letter at the end
        if (id.length === 10) {
            const digits = id.substring(0, 9);
            const letter = id.substring(9, 10);
            
            if (/^\d{9}$/.test(digits) && /^[A-Za-z]$/.test(letter)) {
                return { valid: true, message: 'Valid (9 digits + letter)', type: 'valid' };
            }
            
            if (/^\d{9}$/.test(digits) && !/^[A-Za-z]$/.test(letter)) {
                return { valid: false, message: 'Last character must be a letter (e.g., 123456789V)', type: 'error' };
            }
            
            if (!/^\d{9}$/.test(digits)) {
                return { valid: false, message: 'First 9 characters must be digits (e.g., 123456789V)', type: 'error' };
            }
        }
        
        // New format: Exactly 12 digits
        if (id.length === 12) {
            if (/^\d{12}$/.test(id)) {
                return { valid: true, message: 'Valid (12 digits)', type: 'valid' };
            } else {
                return { valid: false, message: '12 digits only, no letters (e.g., 123456789012)', type: 'error' };
            }
        }
        
        // Invalid lengths
        if (id.length === 9) {
            if (/^\d{9}$/.test(id)) {
                return { valid: false, message: 'Add a letter at the end (e.g., 123456789V)', type: 'error' };
            }
        }
        
        if (id.length === 11) {
            return { valid: false, message: 'ID must be 10 characters (old) or 12 characters (new)', type: 'error' };
        }
        
        if (id.length > 12) {
            return { valid: false, message: 'ID cannot exceed 12 characters', type: 'error' };
        }
        
        if (id.length > 0 && id.length < 9) {
            return { valid: false, message: 'ID must be 10 characters (old) or 12 characters (new)', type: 'error' };
        }
        
        return { valid: false, message: 'Use format: 123456789V (old) or 123456789012 (new)', type: 'error' };
    }

    // ID FORMAT HINT (Only shows one message at a time)
    function showFormatHint(message, type) {
        let hint = document.getElementById('id-format-hint');
        if (!hint) {
            hint = document.createElement('div');
            hint.id = 'id-format-hint';
            hint.className = 'id-format-hint';
            idNum.parentNode.appendChild(hint);
        }
        
        // Clear the error message when showing hint
        clearError(idNumErr);
        
        hint.textContent = message;
        hint.style.display = 'block';
        
        if (type === 'valid') {
            hint.style.color = '#28a745';
            hint.className = 'id-format-hint show valid';
        } else if (type === 'error') {
            hint.style.color = '#dc3545';
            hint.className = 'id-format-hint show error';
        } else {
            hint.style.color = '#888';
            hint.className = 'id-format-hint show hint';
        }
    }

    function clearFormatHint() {
        let hint = document.getElementById('id-format-hint');
        if (hint) {
            hint.textContent = '';
            hint.style.display = 'none';
            hint.className = 'id-format-hint';
        }
    }

    // ID NUMBER INPUT HANDLER
    idNum.addEventListener('input', function() {
        this.value = this.value.replace(/\s/g, '').toUpperCase();
        if (this.value.length > 12) {
            this.value = this.value.substring(0, 12);
        }
        
        // Clear both error elements
        clearError(idNumErr);
        clearFormatHint();
        
        if (this.value.length === 0) {
            setDefault(this);
            showFormatHint('Format: 123456789V (old) or 123456789012 (new)', 'hint');
            return;
        }
        
        const result = validateIdNumber(this.value);
        
        if (!result.valid) {
            // Only show error in the format hint, not in the error div
            setInvalid(this, idNumErr, result.message);
            // Don't show format hint when there's an error
            return;
        }
        
        setValid(this);
        showFormatHint(result.message, 'valid');
    });

    // NAME 
    name.addEventListener('input', function() {
        if (/\d/.test(this.value)) {
            this.value = this.value.replace(/\d/g, '');
        }
        
        const val = this.value.trim();
        clearError(nameErr);
        
        if (val.length === 0) {
            setDefault(this);
            return;
        }
        if (val.length < 2) {
            setInvalid(this, nameErr, 'Name must be at least 2 characters.');
            return;
        }
        if (!/^[a-zA-Z\s.]+$/.test(val)) {
            setInvalid(this, nameErr, 'Only letters, spaces, and periods allowed.');
            return;
        }
        setValid(this);
    });

    name.addEventListener('keydown', function(e) {
        if ((e.key >= '0' && e.key <= '9') || (e.key >= 'Num0' && e.key <= 'Num9')) {
            e.preventDefault();
        }
    });

    // EMAIL 
    email.addEventListener('input', function() {
        const val = this.value.trim();
        clearError(emailErr);
        
        if (val.length === 0) {
            setDefault(this);
            return;
        }
        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val)) {
            setInvalid(this, emailErr, 'Enter a valid email address.');
            return;
        }
        setValid(this);
    });

    //PASSWORD 
    password.addEventListener('input', function() {
        const val = this.value;
        clearError(passErr);
        
        if (val.length === 0) {
            setDefault(this);
            updateStrength(val);
            return;
        }
        if (val.length < 8) {
            setInvalid(this, passErr, 'Password must be at least 8 characters.');
            updateStrength(val);
            return;
        }
        setValid(this);
        updateStrength(val);
        
        if (confirmPass.value.length > 0) {
            validateConfirm();
        }
    });

    // CONFIRM PASSWORD 
    function validateConfirm() {
        const val = confirmPass.value;
        clearError(confirmErr);
        
        if (val.length === 0) {
            setDefault(confirmPass);
            return;
        }
        if (val !== password.value) {
            setInvalid(confirmPass, confirmErr, 'Passwords do not match.');
            return;
        }
        setValid(confirmPass);
    }

    confirmPass.addEventListener('input', validateConfirm);

    //  AGE 
    age.addEventListener('input', function() {
        const val = this.value.trim();
        clearError(ageErr);
        
        if (val.length === 0) {
            setDefault(this);
            return;
        }
        const num = parseInt(val);
        if (isNaN(num)) {
            setInvalid(this, ageErr, 'Enter a valid age.');
            return;
        }
        if (num < 18) {
            setInvalid(this, ageErr, 'You must be at least 18 years old.');
            return;
        }
        if (num > 100) {
            setInvalid(this, ageErr, 'Age cannot exceed 100.');
            return;
        }
        setValid(this);
    });

    //  GENDER
    gender.addEventListener('change', function() {
        const val = this.value;
        clearError(genderErr);
        
        if (val === '') {
            setDefault(this);
            return;
        }
        setValid(this);
    });

    //  ADDRESS
    address.addEventListener('input', function() {
        const val = this.value.trim();
        clearError(addressErr);
        
        if (val.length === 0) {
            setDefault(this);
            return;
        }
        if (val.length < 5) {
            setInvalid(this, addressErr, 'Address must be at least 5 characters.');
            return;
        }
        if (val.length > 500) {
            setInvalid(this, addressErr, 'Address is too long.');
            return;
        }
        setValid(this);
    });

    //EYE TOGGLE
    document.getElementById('togglePass').addEventListener('click', function() {
        const isPass = password.type === 'password';
        password.type = isPass ? 'text' : 'password';
        passIcon.src = isPass ? EYE_CLOSED : EYE_OPEN;
        passIcon.alt = isPass ? 'Hide password' : 'Show password';
    });

    document.getElementById('toggleConfirm').addEventListener('click', function() {
        const isPass = confirmPass.type === 'password';
        confirmPass.type = isPass ? 'text' : 'password';
        confirmIcon.src = isPass ? EYE_CLOSED : EYE_OPEN;
        confirmIcon.alt = isPass ? 'Hide password' : 'Show password';
    });

    // FORM SUBMIT
    form.addEventListener('submit', function(e) {
        name.dispatchEvent(new Event('input'));
        idNum.dispatchEvent(new Event('input'));
        email.dispatchEvent(new Event('input'));
        password.dispatchEvent(new Event('input'));
        confirmPass.dispatchEvent(new Event('input'));
        age.dispatchEvent(new Event('input'));
        gender.dispatchEvent(new Event('change'));
        address.dispatchEvent(new Event('input'));

        const invalidFields = document.querySelectorAll('.form-control.invalid');
        const emptyFields = document.querySelectorAll('.form-control:not(.valid):not(.invalid)');
        
        if (invalidFields.length > 0 || emptyFields.length > 0) {
            e.preventDefault();
            const firstInvalid = document.querySelector('.form-control.invalid');
            if (firstInvalid) {
                firstInvalid.focus();
            }
        }
    });

    console.log('All event listeners attached!');
});