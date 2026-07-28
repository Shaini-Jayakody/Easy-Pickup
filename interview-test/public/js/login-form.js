/**
 * Login Form Script
 * Handles password visibility toggle
 */

document.addEventListener('DOMContentLoaded', function() {
    const loginPass = document.getElementById('password');
    const loginToggle = document.getElementById('toggleLoginPass');
    const loginIcon = document.getElementById('login-pass-icon');
    
    if (loginToggle && loginPass && loginIcon) {
        // Get paths from data attributes
        const EYE_OPEN = loginToggle.dataset.eyeOpen || '/images/eye-open.svg';
        const EYE_CLOSED = loginToggle.dataset.eyeClosed || '/images/eye-closed.svg';
        
        loginToggle.addEventListener('click', function() {
            const isPass = loginPass.type === 'password';
            loginPass.type = isPass ? 'text' : 'password';
            loginIcon.src = isPass ? EYE_CLOSED : EYE_OPEN;
            loginIcon.alt = isPass ? 'Hide password' : 'Show password';
        });
    }
});