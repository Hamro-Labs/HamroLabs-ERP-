<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include('admin/config/dbcon.php');

// If already logged in
if (isset($_SESSION['auth']) && $_SESSION['auth'] === true) {
    header("Location: index.php");
    exit;
}

$pageTitle = "Sign Up";
include('Includes/header.php');

// Get current theme
$theme = isset($_COOKIE['theme']) ? $_COOKIE['theme'] : 'light';
?>

<!-- Sign Up Content -->
<style>
    :root {
        --primary-gradient: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);
        --input-focus-shadow: 0 0 0 4px rgba(99, 102, 241, 0.15);
        --error-color: #ef4444;
        --success-color: #10b981;
    }

    .signup-wrapper {
        background: var(--bg-secondary);
        min-height: calc(100vh - 80px);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 40px 20px;
    }

    .signup-container {
        background: var(--bg-elevated);
        max-width: 900px;
        width: 100%;
        padding: 45px;
        border-radius: 24px;
        box-shadow: 0 20px 50px var(--shadow-color);
        border: 1px solid var(--border-color);
        position: relative;
        overflow: hidden;
    }

    .signup-container::after {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 5px;
        background: var(--primary-gradient);
    }

    .signup-header {
        text-align: center;
        margin-bottom: 35px;
    }

    .signup-icon-box {
        width: 80px;
        height: 80px;
        background: var(--primary-gradient);
        border-radius: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 25px;
        box-shadow: 0 10px 20px rgba(99, 102, 241, 0.3);
    }

    .signup-icon-box i {
        font-size: 35px;
        color: white;
    }

    .signup-header h3 {
        font-size: 2rem;
        font-weight: 800;
        background: var(--primary-gradient);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        margin-bottom: 10px;
    }

    .signup-header p {
        color: var(--text-muted);
        font-size: 1rem;
        font-weight: 500;
    }

    .form-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
        margin-bottom: 20px;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-group label {
        display: block;
        margin-bottom: 10px;
        font-weight: 600;
        color: var(--text-secondary);
        font-size: 0.9rem;
    }

    .form-group label .required {
        color: var(--error-color);
        margin-left: 3px;
    }

    .input-wrapper {
        position: relative;
    }

    .form-control, .form-select {
        width: 100%;
        padding: 14px 18px 14px 50px;
        border-radius: 12px;
        border: 1.5px solid var(--border-color);
        background: var(--bg-primary);
        color: var(--text-primary);
        font-size: 1rem;
        transition: all 0.3s ease;
    }

    .form-control:focus, .form-select:focus {
        outline: none;
        border-color: #6366f1;
        box-shadow: var(--input-focus-shadow);
    }

    .form-control.error, .form-select.error {
        border-color: var(--error-color);
    }

    .form-control.success, .form-select.success {
        border-color: var(--success-color);
    }

    .input-icon {
        position: absolute;
        left: 18px;
        top: 50%;
        transform: translateY(-50%);
        font-size: 1.25rem;
        color: var(--text-muted);
        transition: color 0.3s ease;
        z-index: 1;
    }

    .form-control:focus + .input-icon,
    .form-select:focus + .input-icon {
        color: #6366f1;
    }

    .toggle-password {
        position: absolute;
        right: 18px;
        top: 50%;
        transform: translateY(-50%);
        cursor: pointer;
        color: var(--text-muted);
        font-size: 1.1rem;
        transition: color 0.3s ease;
        z-index: 2;
    }

    .toggle-password:hover {
        color: #6366f1;
    }

    .error-message {
        color: var(--error-color);
        font-size: 0.85rem;
        margin-top: 6px;
        display: none;
        font-weight: 500;
    }

    .error-message.show {
        display: block;
    }

    .password-strength {
        margin-top: 8px;
        height: 4px;
        background: var(--border-color);
        border-radius: 2px;
        overflow: hidden;
        display: none;
    }

    .password-strength.show {
        display: block;
    }

    .password-strength-bar {
        height: 100%;
        width: 0;
        transition: all 0.3s ease;
        border-radius: 2px;
    }

    .password-strength-text {
        font-size: 0.8rem;
        margin-top: 4px;
        font-weight: 600;
        display: none;
    }

    .password-strength-text.show {
        display: block;
    }

    .btn-primary {
        width: 100%;
        padding: 16px;
        font-size: 1.1rem;
        font-weight: 700;
        border-radius: 12px;
        background: var(--primary-gradient);
        border: none;
        color: white;
        transition: all 0.3s ease;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        box-shadow: 0 10px 15px -3px rgba(99, 102, 241, 0.3);
        margin-top: 25px;
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 20px 25px -5px rgba(99, 102, 241, 0.4);
    }

    .btn-primary:disabled {
        opacity: 0.6;
        cursor: not-allowed;
        transform: none;
    }

    .divider {
        display: flex;
        align-items: center;
        margin: 30px 0;
    }

    .divider::before, .divider::after {
        content: '';
        flex: 1;
        height: 1px;
        background: var(--border-color);
    }

    .divider span {
        padding: 0 15px;
        color: var(--text-muted);
        font-size: 0.85rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .signup-footer {
        text-align: center;
    }

    .signup-footer p {
        color: var(--text-muted);
        margin-bottom: 15px;
        font-size: 0.95rem;
    }

    .login-btn-link {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 12px 30px;
        border-radius: 30px;
        background: var(--bg-secondary);
        color: var(--text-primary);
        text-decoration: none;
        font-weight: 600;
        transition: all 0.3s ease;
        border: 1px solid var(--border-color);
    }

    .login-btn-link:hover {
        background: var(--primary-gradient);
        color: white;
        border-color: transparent;
        transform: translateY(-1px);
    }

    @media (max-width: 768px) {
        .signup-container {
            padding: 30px 20px;
        }

        .form-row {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="signup-wrapper">
    <div class="signup-container" id="signupCard">
        <div class="signup-header">
            <div class="signup-icon-box">
                <i class="bi bi-person-plus-fill"></i>
            </div>
            <h3>Create Account</h3>
            <p>Join Birgunj Institute of Technology</p>
        </div>

        <div id="alertBox">
            <?php
            if (isset($_SESSION['message'])) {
                $message_type = isset($_SESSION['message_type']) ? $_SESSION['message_type'] : 'info';
                $alert_class = $message_type === 'error' ? 'alert-danger' : 'alert-success';
                echo '<div class="alert ' . $alert_class . ' alert-dismissible fade show" role="alert">';
                echo $_SESSION['message'];
                echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
                echo '</div>';
                unset($_SESSION['message']);
                unset($_SESSION['message_type']);
            }
            ?>
        </div>

        <form action="signupcode.php" method="POST" id="signupForm" novalidate>
            <div class="form-row">
                <div class="form-group">
                    <label for="name">Full Name<span class="required">*</span></label>
                    <div class="input-wrapper">
                        <input type="text" name="name" class="form-control" id="name" 
                               placeholder="Enter your full name" required>
                        <i class="bi bi-person-fill input-icon"></i>
                    </div>
                    <div class="error-message" id="nameError"></div>
                </div>

                <div class="form-group">
                    <label for="email">Email Address<span class="required">*</span></label>
                    <div class="input-wrapper">
                        <input type="email" name="email" class="form-control" id="email" 
                               placeholder="name@example.com" required>
                        <i class="bi bi-envelope-at-fill input-icon"></i>
                    </div>
                    <div class="error-message" id="emailError"></div>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="contact">Contact Number<span class="required">*</span></label>
                    <div class="input-wrapper">
                        <input type="tel" name="contact" class="form-control" id="contact" 
                               placeholder="98XXXXXXXX" required>
                        <i class="bi bi-phone-fill input-icon"></i>
                    </div>
                    <div class="error-message" id="contactError"></div>
                </div>

                <div class="form-group">
                    <label for="address">Address<span class="required">*</span></label>
                    <div class="input-wrapper">
                        <input type="text" name="address" class="form-control" id="address" 
                               placeholder="City, District" required>
                        <i class="bi bi-geo-alt-fill input-icon"></i>
                    </div>
                    <div class="error-message" id="addressError"></div>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="faculty">Faculty<span class="required">*</span></label>
                    <div class="input-wrapper">
                        <select name="faculty" class="form-select" id="faculty" required>
                            <option value="" selected disabled>Select Faculty</option>
                            <option value="DCOM">Computer Engineering</option>
                            <option value="Civil">Civil Engineering</option>
                            <option value="DEEx">Electrical Engineering</option>
                            <option value="Electronics">Electronics Engineering</option>
                            <option value="Architecture">Architecture</option>
                        </select>
                        <i class="bi bi-mortarboard-fill input-icon"></i>
                    </div>
                    <div class="error-message" id="facultyError"></div>
                </div>

                <div class="form-group">
                    <label for="year_part">Year/Part<span class="required">*</span></label>
                    <div class="input-wrapper">
                        <select name="year_part" class="form-select" id="year_part" required>
                            <option value="" selected disabled>Select Year/Part</option>
                            <option value="I/I">I/I</option>
                            <option value="I/II">I/II</option>
                            <option value="II/I">II/I</option>
                            <option value="II/II">II/II</option>
                            <option value="III/I">III/I</option>
                            <option value="III/II">III/II</option>
                            <option value="IV/I">IV/I</option>
                            <option value="IV/II">IV/II</option>
                        </select>
                        <i class="bi bi-calendar-check-fill input-icon"></i>
                    </div>
                    <div class="error-message" id="yearError"></div>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="password">Password<span class="required">*</span></label>
                    <div class="input-wrapper">
                        <input type="password" name="password" class="form-control" id="password" 
                               placeholder="••••••••" required>
                        <i class="bi bi-shield-lock-fill input-icon"></i>
                        <i class="bi bi-eye toggle-password" id="togglePass"></i>
                    </div>
                    <div class="password-strength" id="passwordStrength">
                        <div class="password-strength-bar" id="strengthBar"></div>
                    </div>
                    <div class="password-strength-text" id="strengthText"></div>
                    <div class="error-message" id="passwordError"></div>
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirm Password<span class="required">*</span></label>
                    <div class="input-wrapper">
                        <input type="password" name="confirm_password" class="form-control" 
                               id="confirm_password" placeholder="••••••••" required>
                        <i class="bi bi-shield-check-fill input-icon"></i>
                        <i class="bi bi-eye toggle-password" id="toggleConfirmPass"></i>
                    </div>
                    <div class="error-message" id="confirmPasswordError"></div>
                </div>
            </div>

            <button type="submit" name="signup_btn" class="btn-primary" id="signupBtn">
                <span id="btnText">Create Account</span>
                <i class="bi bi-arrow-right-short" style="font-size: 1.4rem;"></i>
            </button>

            <div class="divider">
                <span>Already Registered?</span>
            </div>

            <div class="signup-footer">
                <p>Access your existing account</p>
                <a href="Login.php" class="login-btn-link">
                    <i class="bi bi-box-arrow-in-right"></i>
                    Sign In
                </a>
            </div>
            
            <!-- Hidden input to ensure signup_btn is sent with JS submission -->
            <input type="hidden" name="signup_btn" value="true">
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        // GSAP Animation
        if (typeof gsap !== 'undefined') {
            gsap.from('#signupCard', {
                y: 40,
                opacity: 0,
                duration: 1,
                ease: "expo.out"
            });
        }

        // Form elements
        const form = document.getElementById('signupForm');
        const nameInput = document.getElementById('name');
        const emailInput = document.getElementById('email');
        const contactInput = document.getElementById('contact');
        const addressInput = document.getElementById('address');
        const facultyInput = document.getElementById('faculty');
        const yearInput = document.getElementById('year_part');
        const passwordInput = document.getElementById('password');
        const confirmPasswordInput = document.getElementById('confirm_password');
        
        // Toggle password visibility
        const togglePass = document.getElementById('togglePass');
        const toggleConfirmPass = document.getElementById('toggleConfirmPass');
        
        togglePass.addEventListener('click', () => {
            const type = passwordInput.type === 'password' ? 'text' : 'password';
            passwordInput.type = type;
            togglePass.classList.toggle('bi-eye');
            togglePass.classList.toggle('bi-eye-slash');
        });
        
        toggleConfirmPass.addEventListener('click', () => {
            const type = confirmPasswordInput.type === 'password' ? 'text' : 'password';
            confirmPasswordInput.type = type;
            toggleConfirmPass.classList.toggle('bi-eye');
            toggleConfirmPass.classList.toggle('bi-eye-slash');
        });

        // Password strength checker
        passwordInput.addEventListener('input', () => {
            const password = passwordInput.value;
            const strengthBar = document.getElementById('strengthBar');
            const strengthText = document.getElementById('strengthText');
            const strengthContainer = document.getElementById('passwordStrength');
            
            if (password.length === 0) {
                strengthContainer.classList.remove('show');
                strengthText.classList.remove('show');
                return;
            }
            
            strengthContainer.classList.add('show');
            strengthText.classList.add('show');
            
            let strength = 0;
            if (password.length >= 8) strength++;
            if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength++;
            if (/\d/.test(password)) strength++;
            if (/[^a-zA-Z\d]/.test(password)) strength++;
            
            const colors = ['#ef4444', '#f59e0b', '#eab308', '#10b981'];
            const texts = ['Weak', 'Fair', 'Good', 'Strong'];
            const widths = ['25%', '50%', '75%', '100%'];
            
            strengthBar.style.width = widths[strength] || '0%';
            strengthBar.style.background = colors[strength] || '#ef4444';
            strengthText.textContent = texts[strength] || '';
            strengthText.style.color = colors[strength] || '#ef4444';
        });

        // Real-time validation functions
        function validateName() {
            const value = nameInput.value.trim();
            const errorEl = document.getElementById('nameError');
            
            if (value.length === 0) {
                showError(nameInput, errorEl, 'Full name is required');
                return false;
            } else if (value.length < 3) {
                showError(nameInput, errorEl, 'Name must be at least 3 characters');
                return false;
            } else if (!/^[a-zA-Z\s]+$/.test(value)) {
                showError(nameInput, errorEl, 'Name can only contain letters');
                return false;
            } else {
                showSuccess(nameInput, errorEl);
                return true;
            }
        }

        function validateEmail() {
            const value = emailInput.value.trim();
            const errorEl = document.getElementById('emailError');
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            
            if (value.length === 0) {
                showError(emailInput, errorEl, 'Email address is required');
                return false;
            } else if (!emailRegex.test(value)) {
                showError(emailInput, errorEl, 'Please enter a valid email address');
                return false;
            } else {
                showSuccess(emailInput, errorEl);
                return true;
            }
        }

        function validateContact() {
            const value = contactInput.value.trim();
            const errorEl = document.getElementById('contactError');
            
            if (value.length === 0) {
                showError(contactInput, errorEl, 'Contact number is required');
                return false;
            } else if (!/^9[78]\d{8}$/.test(value)) {
                showError(contactInput, errorEl, 'Enter valid Nepali number (98XXXXXXXX)');
                return false;
            } else {
                showSuccess(contactInput, errorEl);
                return true;
            }
        }

        function validateAddress() {
            const value = addressInput.value.trim();
            const errorEl = document.getElementById('addressError');
            
            if (value.length === 0) {
                showError(addressInput, errorEl, 'Address is required');
                return false;
            } else if (value.length < 5) {
                showError(addressInput, errorEl, 'Please enter a complete address');
                return false;
            } else {
                showSuccess(addressInput, errorEl);
                return true;
            }
        }

        function validateFaculty() {
            const value = facultyInput.value;
            const errorEl = document.getElementById('facultyError');
            
            if (value === '') {
                showError(facultyInput, errorEl, 'Please select your faculty');
                return false;
            } else {
                showSuccess(facultyInput, errorEl);
                return true;
            }
        }

        function validateYear() {
            const value = yearInput.value;
            const errorEl = document.getElementById('yearError');
            
            if (value === '') {
                showError(yearInput, errorEl, 'Please select your year/part');
                return false;
            } else {
                showSuccess(yearInput, errorEl);
                return true;
            }
        }

        function validatePassword() {
            const value = passwordInput.value;
            const errorEl = document.getElementById('passwordError');
            
            if (value.length === 0) {
                showError(passwordInput, errorEl, 'Password is required');
                return false;
            } else if (value.length < 8) {
                showError(passwordInput, errorEl, 'Password must be at least 8 characters');
                return false;
            } else {
                showSuccess(passwordInput, errorEl);
                return true;
            }
        }

        function validateConfirmPassword() {
            const value = confirmPasswordInput.value;
            const errorEl = document.getElementById('confirmPasswordError');
            
            if (value.length === 0) {
                showError(confirmPasswordInput, errorEl, 'Please confirm your password');
                return false;
            } else if (value !== passwordInput.value) {
                showError(confirmPasswordInput, errorEl, 'Passwords do not match');
                return false;
            } else {
                showSuccess(confirmPasswordInput, errorEl);
                return true;
            }
        }

        function showError(input, errorEl, message) {
            input.classList.remove('success');
            input.classList.add('error');
            errorEl.textContent = message;
            errorEl.classList.add('show');
        }

        function showSuccess(input, errorEl) {
            input.classList.remove('error');
            input.classList.add('success');
            errorEl.classList.remove('show');
        }

        // Add event listeners for real-time validation
        nameInput.addEventListener('blur', validateName);
        emailInput.addEventListener('blur', validateEmail);
        contactInput.addEventListener('blur', validateContact);
        addressInput.addEventListener('blur', validateAddress);
        facultyInput.addEventListener('change', validateFaculty);
        yearInput.addEventListener('change', validateYear);
        passwordInput.addEventListener('blur', validatePassword);
        confirmPasswordInput.addEventListener('blur', validateConfirmPassword);
        confirmPasswordInput.addEventListener('input', () => {
            if (confirmPasswordInput.value.length > 0) {
                validateConfirmPassword();
            }
        });

        // Form submission
        form.addEventListener('submit', (e) => {
            e.preventDefault();
            
            // Validate all fields
            const isNameValid = validateName();
            const isEmailValid = validateEmail();
            const isContactValid = validateContact();
            const isAddressValid = validateAddress();
            const isFacultyValid = validateFaculty();
            const isYearValid = validateYear();
            const isPasswordValid = validatePassword();
            const isConfirmPasswordValid = validateConfirmPassword();
            
            const isFormValid = isNameValid && isEmailValid && isContactValid && 
                               isAddressValid && isFacultyValid && isYearValid && 
                               isPasswordValid && isConfirmPasswordValid;
            
            if (isFormValid) {
                const btn = document.getElementById('signupBtn');
                const text = document.getElementById('btnText');
                btn.style.opacity = '0.8';
                btn.style.pointerEvents = 'none';
                text.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Creating Account...';
                
                // Submit the form
                form.submit();
            } else {
                // Scroll to first error
                const firstError = document.querySelector('.form-control.error, .form-select.error');
                if (firstError) {
                    firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    firstError.focus();
                }
            }
        });
    });
</script>

<!-- Bootstrap Icons -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

<?php include "Includes/bottom.php"; ?>
