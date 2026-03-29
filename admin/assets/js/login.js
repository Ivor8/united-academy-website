// Login Page JavaScript
(function() {
    'use strict';
    
    // Toggle password visibility
    const togglePassword = document.getElementById('togglePassword');
    const passwordInput = document.getElementById('password');
    
    if (togglePassword && passwordInput) {
        togglePassword.addEventListener('click', function() {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            this.querySelector('i').classList.toggle('fa-eye');
            this.querySelector('i').classList.toggle('fa-eye-slash');
        });
    }
    
    // Form validation
    const loginForm = document.getElementById('loginForm');
    
    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            const email = document.getElementById('email').value.trim();
            const password = document.getElementById('password').value;
            
            if (!email) {
                e.preventDefault();
                showError('Please enter your email address');
                return false;
            }
            
            if (!password) {
                e.preventDefault();
                showError('Please enter your password');
                return false;
            }
            
            // Show loading state
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Logging in...';
            submitBtn.disabled = true;
            
            // Allow form to submit, loading state will be cleared by page reload
        });
    }
    
    function showError(message) {
        // Remove any existing alerts
        const existingAlert = document.querySelector('.alert');
        if (existingAlert) existingAlert.remove();
        
        // Create alert
        const alert = document.createElement('div');
        alert.className = 'alert alert-error animate-shake';
        alert.innerHTML = `
            <i class="fas fa-exclamation-circle"></i>
            <span>${message}</span>
        `;
        
        // Insert after header
        const loginHeader = document.querySelector('.login-header');
        loginHeader.insertAdjacentElement('afterend', alert);
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            alert.remove();
        }, 5000);
    }
    
    // Check for saved credentials
    if (localStorage.getItem('rememberedEmail')) {
        document.getElementById('email').value = localStorage.getItem('rememberedEmail');
        document.getElementById('remember').checked = true;
    }
    
    // Save credentials if remember me is checked
    document.getElementById('remember')?.addEventListener('change', function(e) {
        if (!e.target.checked) {
            localStorage.removeItem('rememberedEmail');
        }
    });
    
    // Add floating label effect
    const inputs = document.querySelectorAll('.form-group input');
    inputs.forEach(input => {
        input.addEventListener('focus', function() {
            this.parentElement.classList.add('focused');
        });
        
        input.addEventListener('blur', function() {
            if (!this.value) {
                this.parentElement.classList.remove('focused');
            }
        });
        
        if (input.value) {
            input.parentElement.classList.add('focused');
        }
    });
})();