// Auth Form Validation
function validateLoginForm() {
    const username = document.getElementById('username');
    const password = document.getElementById('password');
    
    // Clear previous errors
    document.querySelectorAll('.invalid-feedback').forEach(el => {
        el.classList.remove('show');
    });
    document.querySelectorAll('.is-invalid').forEach(el => {
        el.classList.remove('is-invalid');
    });
    
    let isValid = true;
    
    // Validate username
    if (!username.value.trim()) {
        showError(username, "Username is required");
        isValid = false;
    } else if (!/^[a-zA-Z0-9_]{3,20}$/.test(username.value)) {
        showError(username, "Username: 3-20 chars, letters/numbers/underscore only");
        isValid = false;
    }
    
    // Validate password
    if (!password.value) {
        showError(password, "Password is required");
        isValid = false;
    } else if (password.value.length < 3) {
        showError(password, "Password must be at least 3 characters");
        isValid = false;
    }
    
    return isValid;
}

function validateRegisterForm() {
    const rules = {
        username: /^[a-zA-Z0-9_]{3,20}$/,
        password: /^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d@$!%*#?&]{6,}$/,
        email: /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/,
        full_name: /^[a-zA-Z\s]{2,50}$/,
        phone: /^[6-9]\d{9}$/
    };
    
    const messages = {
        username: "Username: 3-20 chars, letters/numbers/underscore only",
        password: "Password: min 6 chars with at least 1 letter and 1 number",
        email: "Enter a valid email address",
        full_name: "Full name: 2-50 letters and spaces only",
        phone: "Enter a valid 10-digit Indian mobile number"
    };
    
    let isValid = true;
    
    for (const [fieldName, pattern] of Object.entries(rules)) {
        const field = document.getElementById(fieldName);
        if (field) {
            const value = field.value.trim();
            if (!value) {
                showError(field, `${fieldName.replace('_', ' ')} is required`);
                isValid = false;
            } else if (!pattern.test(value)) {
                showError(field, messages[fieldName]);
                isValid = false;
            } else {
                clearError(field);
            }
        }
    }
    
    // Check password confirmation
    const password = document.getElementById('password');
    const confirmPassword = document.getElementById('confirm_password');
    if (password && confirmPassword && password.value !== confirmPassword.value) {
        showError(confirmPassword, "Passwords do not match");
        isValid = false;
    }
    
    return isValid;
}

// Role Selection
document.addEventListener('DOMContentLoaded', function() {
    const roleOptions = document.querySelectorAll('.role-option');
    roleOptions.forEach(option => {
        option.addEventListener('click', function() {
            roleOptions.forEach(o => o.classList.remove('selected'));
            this.classList.add('selected');
            const input = this.querySelector('input');
            if (input) input.checked = true;
        });
    });
});

// Helper Functions
function showError(element, message) {
    element.classList.add('is-invalid');
    element.classList.remove('is-valid');
    
    let feedback = element.nextElementSibling;
    if (!feedback || !feedback.classList.contains('invalid-feedback')) {
        feedback = document.createElement('div');
        feedback.className = 'invalid-feedback';
        element.parentNode.appendChild(feedback);
    }
    
    feedback.textContent = message;
    feedback.classList.add('show');
}

function clearError(element) {
    element.classList.remove('is-invalid');
    element.classList.add('is-valid');
    
    const feedback = element.nextElementSibling;
    if (feedback && feedback.classList.contains('invalid-feedback')) {
        feedback.classList.remove('show');
    }
}