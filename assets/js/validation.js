// Regex Patterns
const patterns = {
    username: /^[a-zA-Z0-9_]{3,20}$/,
    password: /^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d@$!%*#?&]{6,}$/,
    email: /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/,
    name: /^[a-zA-Z\s]{2,50}$/,
    isbn: /^(?:\d{3}-)?\d{1,5}-\d{1,7}-\d{1,7}-\d{1,7}$|^\d{10}$|^\d{13}$/,
    phone: /^[6-9]\d{9}$/,
    number: /^\d+$/,
    date: /^\d{4}-\d{2}-\d{2}$/
};

// Validation Functions
class Validator {
    static validateField(field, pattern, message) {
        const value = field.value.trim();
        const feedback = field.nextElementSibling;
        
        if (!value) {
            this.showError(field, feedback, "This field is required");
            return false;
        }
        
        if (!pattern.test(value)) {
            this.showError(field, feedback, message);
            return false;
        }
        
        this.showSuccess(field, feedback);
        return true;
    }
    
    static showError(field, feedback, message) {
        field.classList.remove('is-valid');
        field.classList.add('is-invalid');
        feedback.textContent = message;
        feedback.classList.add('show');
    }
    
    static showSuccess(field, feedback) {
        field.classList.remove('is-invalid');
        field.classList.add('is-valid');
        feedback.classList.remove('show');
    }
    
    static validateEmail(email) {
        return patterns.email.test(email);
    }
    
    static validatePassword(password) {
        return patterns.password.test(password);
    }
    
    static validatePhone(phone) {
        return patterns.phone.test(phone);
    }
    
    static validateForm(formData, rules) {
        let isValid = true;
        
        for (const [fieldName, rule] of Object.entries(rules)) {
            const field = formData[fieldName];
            if (!this.validateField(field, rule.pattern, rule.message)) {
                isValid = false;
            }
        }
        
        return isValid;
    }
}

// Specific Validations
const authRules = {
    username: {
        pattern: patterns.username,
        message: "Username: 3-20 chars, letters, numbers, underscore only"
    },
    password: {
        pattern: patterns.password,
        message: "Password: min 6 chars with at least 1 letter and 1 number"
    },
    email: {
        pattern: patterns.email,
        message: "Enter a valid email address"
    },
    full_name: {
        pattern: patterns.name,
        message: "Name: 2-50 letters and spaces only"
    }
};

const bookRules = {
    title: {
        pattern: patterns.name,
        message: "Title: 2-50 characters required"
    },
    author: {
        pattern: patterns.name,
        message: "Author name: 2-50 characters required"
    },
    isbn: {
        pattern: patterns.isbn,
        message: "Enter valid ISBN (10 or 13 digits, with optional hyphens)"
    },
    copies: {
        pattern: patterns.number,
        message: "Enter valid number of copies"
    }
};

// Real-time Validation
document.addEventListener('DOMContentLoaded', function() {
    // Real-time username validation
    const usernameInput = document.getElementById('username');
    if (usernameInput) {
        usernameInput.addEventListener('input', function() {
            Validator.validateField(this, patterns.username, 
                "Username: 3-20 chars, letters, numbers, underscore only");
        });
    }
    
    // Real-time email validation
    const emailInput = document.getElementById('email');
    if (emailInput) {
        emailInput.addEventListener('input', function() {
            Validator.validateField(this, patterns.email, 
                "Enter a valid email address");
        });
    }
    
    // Real-time password validation
    const passwordInput = document.getElementById('password');
    if (passwordInput) {
        passwordInput.addEventListener('input', function() {
            Validator.validateField(this, patterns.password, 
                "Password: min 6 chars with at least 1 letter and 1 number");
        });
    }
    
    // Password confirmation
    const confirmPasswordInput = document.getElementById('confirm_password');
    if (confirmPasswordInput) {
        confirmPasswordInput.addEventListener('input', function() {
            const password = document.getElementById('password');
            if (password && password.value !== this.value) {
                Validator.showError(this, this.nextElementSibling, 
                    "Passwords do not match");
            } else {
                Validator.showSuccess(this, this.nextElementSibling);
            }
        });
    }
});