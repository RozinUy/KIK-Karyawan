<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Register</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- Bootstrap 5 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

  <style>
    :root {
      --primary-color: #0d6efd;
      --secondary-color: #6c757d;
      --light-bg: #f8f9fa;
      --white: #ffffff;
      --text-color: #212529;
      --border-color: #dee2e6;
      --success-color: #198754;
    }
    
    body {
      background-color: var(--light-bg);
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
      padding: 20px;
      margin: 0;
    }

    .register-card {
      position: relative;
      width: 100%;
      max-width: 480px;
      background-color: var(--white);
      border-radius: 12px;
      box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
      overflow: hidden;
      border: 1px solid var(--border-color);
    }

    .card-header {
      background-color: var(--success-color);
      color: white;
      padding: 30px 20px 20px;
      text-align: center;
      position: relative;
      margin-bottom: 20px;
    }

    .user-icon {
      width: 80px;
      height: 80px;
      background-color: white;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 15px;
      color: var(--success-color);
      font-size: 36px;
      border: 5px solid rgba(255, 255, 255, 0.2);
    }

    .register-title {
      font-weight: 600;
      font-size: 24px;
      margin: 0;
    }

    .card-body {
      padding: 0 30px 30px;
    }

    .form-group {
      margin-bottom: 20px;
    }

    .form-label {
      font-weight: 500;
      color: var(--text-color);
      margin-bottom: 8px;
      display: block;
    }

    .form-control {
      border-radius: 8px;
      padding: 12px 15px;
      border: 1px solid var(--border-color);
      font-size: 16px;
      width: 100%;
      box-sizing: border-box;
    }

    .form-control:focus {
      border-color: var(--primary-color);
      box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
      outline: none;
    }

    .password-wrapper {
      position: relative;
    }

    .password-toggle {
      position: absolute;
      right: 15px;
      top: 50%;
      transform: translateY(-50%);
      background: none;
      border: none;
      color: var(--secondary-color);
      cursor: pointer;
      font-size: 18px;
      padding: 5px;
    }

    .register-button {
      background-color: var(--success-color);
      color: white;
      border: none;
      border-radius: 8px;
      padding: 14px;
      font-size: 16px;
      font-weight: 600;
      width: 100%;
      cursor: pointer;
      transition: background-color 0.2s;
      margin-top: 10px;
      margin-bottom: 15px;
    }

    .register-button:hover {
      background-color: #157347;
    }

    .login-link-container {
      text-align: center;
      margin-top: 20px;
      color: var(--text-color);
      font-size: 15px;
    }

    .login-link {
      color: var(--success-color);
      text-decoration: none;
      font-weight: 500;
    }

    .login-link:hover {
      text-decoration: underline;
    }

    .divider {
      display: flex;
      align-items: center;
      text-align: center;
      margin: 20px 0;
      color: var(--secondary-color);
    }

    .divider::before,
    .divider::after {
      content: '';
      flex: 1;
      border-bottom: 1px solid var(--border-color);
    }

    .divider:not(:empty)::before {
      margin-right: 15px;
    }

    .divider:not(:empty)::after {
      margin-left: 15px;
    }

    .terms-container {
      display: flex;
      align-items: flex-start;
      margin: 20px 0;
    }

    .terms-container input {
      margin-right: 10px;
      margin-top: 3px;
      width: 18px;
      height: 18px;
      flex-shrink: 0;
    }

    .terms-container label {
      color: var(--text-color);
      font-size: 14px;
      line-height: 1.4;
    }

    .terms-link {
      color: var(--success-color);
      text-decoration: none;
    }

    .terms-link:hover {
      text-decoration: underline;
    }

    .password-strength {
      margin-top: 8px;
      font-size: 14px;
    }

    .strength-weak {
      color: #dc3545;
    }

    .strength-medium {
      color: #ffc107;
    }

    .strength-strong {
      color: var(--success-color);
    }

    .error-message {
      color: #dc3545;
      font-size: 14px;
      margin-top: 5px;
      display: none;
    }

    .success-message {
      color: var(--success-color);
      font-size: 14px;
      margin-top: 5px;
      display: none;
    }

    /* Responsive adjustments */
    @media (max-width: 576px) {
      .card-body {
        padding: 0 20px 25px;
      }
      
      .card-header {
        padding: 25px 15px 15px;
      }
      
      .user-icon {
        width: 70px;
        height: 70px;
        font-size: 30px;
      }
      
      body {
        padding: 15px;
      }
      
      .register-card {
        max-width: 100%;
      }
    }

    /* Form validation styles */
    .form-group.invalid .form-control {
      border-color: #dc3545;
    }

    .form-group.valid .form-control {
      border-color: var(--success-color);
    }
  </style>
</head>
<body>

  <div class="register-card">
    <div class="card-header">
      <div class="user-icon">
        <i class="bi bi-person-plus-fill"></i>
      </div>
      <h1 class="register-title">Register</h1>
    </div>

    <div class="card-body">
      <form id="registerForm">
        <div class="form-group" id="nameGroup">
          <label class="form-label">Full Name</label>
          <input type="text" class="form-control" placeholder="Enter your full name" required id="nameInput">
          <div class="error-message" id="nameError">Name must be at least 3 characters</div>
        </div>

        <div class="form-group" id="emailGroup">
          <label class="form-label">Email</label>
          <input type="email" class="form-control" placeholder="Enter email" required id="emailInput">
          <div class="error-message" id="emailError">Please enter a valid email address</div>
        </div>

        <div class="form-group" id="passwordGroup">
          <label class="form-label">Password</label>
          <div class="password-wrapper">
            <input type="password" class="form-control" id="passwordInput" placeholder="Enter password" required>
            <button type="button" class="password-toggle" id="togglePassword">
              <i class="bi bi-eye"></i>
            </button>
          </div>
          <div class="password-strength" id="passwordStrength"></div>
          <div class="error-message" id="passwordError">Password must be at least 8 characters with uppercase, lowercase, and number</div>
        </div>

        <div class="form-group" id="confirmPasswordGroup">
          <label class="form-label">Confirm Password</label>
          <div class="password-wrapper">
            <input type="password" class="form-control" id="confirmPasswordInput" placeholder="Confirm password" required>
            <button type="button" class="password-toggle" id="toggleConfirmPassword">
              <i class="bi bi-eye"></i>
            </button>
          </div>
          <div class="error-message" id="confirmPasswordError">Passwords do not match</div>
          <div class="success-message" id="confirmPasswordSuccess">Passwords match</div>
        </div>

        <div class="terms-container">
          <input type="checkbox" id="termsAgreement" required>
          <label for="termsAgreement">
            I agree to the <a href="#" class="terms-link">Terms of Service</a> and <a href="#" class="terms-link">Privacy Policy</a>
          </label>
        </div>
        <div class="error-message" id="termsError">You must agree to the terms and conditions</div>

        <button type="submit" class="register-button">Create Account</button>

        <div class="divider"></div>

        <div class="login-link-container">
          Sudah punya akun?
          <a href="login.php" class="login-link">Login</a>
        </div>
      </form>
    </div>
  </div>

  <!-- Bootstrap 5 JS Bundle with Popper -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  
  <script>
    // Toggle password visibility
    document.getElementById('togglePassword').addEventListener('click', function() {
      const passwordInput = document.getElementById('passwordInput');
      const icon = this.querySelector('i');
      
      if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        icon.classList.remove('bi-eye');
        icon.classList.add('bi-eye-slash');
      } else {
        passwordInput.type = 'password';
        icon.classList.remove('bi-eye-slash');
        icon.classList.add('bi-eye');
      }
    });
    
    // Toggle confirm password visibility
    document.getElementById('toggleConfirmPassword').addEventListener('click', function() {
      const confirmPasswordInput = document.getElementById('confirmPasswordInput');
      const icon = this.querySelector('i');
      
      if (confirmPasswordInput.type === 'password') {
        confirmPasswordInput.type = 'text';
        icon.classList.remove('bi-eye');
        icon.classList.add('bi-eye-slash');
      } else {
        confirmPasswordInput.type = 'password';
        icon.classList.remove('bi-eye-slash');
        icon.classList.add('bi-eye');
      }
    });
    
    // Form elements
    const nameInput = document.getElementById('nameInput');
    const emailInput = document.getElementById('emailInput');
    const passwordInput = document.getElementById('passwordInput');
    const confirmPasswordInput = document.getElementById('confirmPasswordInput');
    const termsCheckbox = document.getElementById('termsAgreement');
    
    // Error elements
    const nameError = document.getElementById('nameError');
    const emailError = document.getElementById('emailError');
    const passwordError = document.getElementById('passwordError');
    const confirmPasswordError = document.getElementById('confirmPasswordError');
    const confirmPasswordSuccess = document.getElementById('confirmPasswordSuccess');
    const termsError = document.getElementById('termsError');
    const passwordStrength = document.getElementById('passwordStrength');
    
    // Form groups
    const nameGroup = document.getElementById('nameGroup');
    const emailGroup = document.getElementById('emailGroup');
    const passwordGroup = document.getElementById('passwordGroup');
    const confirmPasswordGroup = document.getElementById('confirmPasswordGroup');
    
    // Validation functions
    function validateName(name) {
      return name.length >= 3;
    }
    
    function validateEmail(email) {
      const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      return re.test(email);
    }
    
    function validatePassword(password) {
      // At least 8 characters, 1 uppercase, 1 lowercase, 1 number
      const re = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/;
      return re.test(password);
    }
    
    function checkPasswordStrength(password) {
      if (password.length === 0) return '';
      
      let strength = 0;
      let text = '';
      let className = '';
      
      // Length check
      if (password.length >= 8) strength++;
      
      // Contains lowercase
      if (/[a-z]/.test(password)) strength++;
      
      // Contains uppercase
      if (/[A-Z]/.test(password)) strength++;
      
      // Contains number
      if (/\d/.test(password)) strength++;
      
      // Contains special character
      if (/[^A-Za-z0-9]/.test(password)) strength++;
      
      switch(strength) {
        case 0:
        case 1:
          text = 'Weak password';
          className = 'strength-weak';
          break;
        case 2:
        case 3:
          text = 'Medium password';
          className = 'strength-medium';
          break;
        case 4:
        case 5:
          text = 'Strong password';
          className = 'strength-strong';
          break;
      }
      
      return `<span class="${className}">${text}</span>`;
    }
    
    // Real-time validation
    nameInput.addEventListener('blur', function() {
      if (!validateName(this.value) && this.value !== '') {
        nameError.style.display = 'block';
        nameGroup.classList.add('invalid');
        nameGroup.classList.remove('valid');
      } else if (this.value !== '') {
        nameError.style.display = 'none';
        nameGroup.classList.remove('invalid');
        nameGroup.classList.add('valid');
      } else {
        nameError.style.display = 'none';
        nameGroup.classList.remove('invalid', 'valid');
      }
    });
    
    emailInput.addEventListener('blur', function() {
      if (!validateEmail(this.value) && this.value !== '') {
        emailError.style.display = 'block';
        emailGroup.classList.add('invalid');
        emailGroup.classList.remove('valid');
      } else if (this.value !== '') {
        emailError.style.display = 'none';
        emailGroup.classList.remove('invalid');
        emailGroup.classList.add('valid');
      } else {
        emailError.style.display = 'none';
        emailGroup.classList.remove('invalid', 'valid');
      }
    });
    
    passwordInput.addEventListener('input', function() {
      const password = this.value;
      const strengthHtml = checkPasswordStrength(password);
      passwordStrength.innerHTML = strengthHtml;
      
      if (!validatePassword(password) && password !== '') {
        passwordError.style.display = 'block';
        passwordGroup.classList.add('invalid');
        passwordGroup.classList.remove('valid');
      } else if (password !== '') {
        passwordError.style.display = 'none';
        passwordGroup.classList.remove('invalid');
        passwordGroup.classList.add('valid');
      } else {
        passwordError.style.display = 'none';
        passwordStrength.innerHTML = '';
        passwordGroup.classList.remove('invalid', 'valid');
      }
      
      // Check if passwords match
      const confirmPassword = confirmPasswordInput.value;
      if (confirmPassword !== '') {
        if (password !== confirmPassword) {
          confirmPasswordError.style.display = 'block';
          confirmPasswordSuccess.style.display = 'none';
          confirmPasswordGroup.classList.add('invalid');
          confirmPasswordGroup.classList.remove('valid');
        } else {
          confirmPasswordError.style.display = 'none';
          confirmPasswordSuccess.style.display = 'block';
          confirmPasswordGroup.classList.remove('invalid');
          confirmPasswordGroup.classList.add('valid');
        }
      }
    });
    
    confirmPasswordInput.addEventListener('blur', function() {
      const password = passwordInput.value;
      const confirmPassword = this.value;
      
      if (password !== confirmPassword && confirmPassword !== '') {
        confirmPasswordError.style.display = 'block';
        confirmPasswordSuccess.style.display = 'none';
        confirmPasswordGroup.classList.add('invalid');
        confirmPasswordGroup.classList.remove('valid');
      } else if (confirmPassword !== '') {
        confirmPasswordError.style.display = 'none';
        confirmPasswordSuccess.style.display = 'block';
        confirmPasswordGroup.classList.remove('invalid');
        confirmPasswordGroup.classList.add('valid');
      } else {
        confirmPasswordError.style.display = 'none';
        confirmPasswordSuccess.style.display = 'none';
        confirmPasswordGroup.classList.remove('invalid', 'valid');
      }
    });
    
    termsCheckbox.addEventListener('change', function() {
      termsError.style.display = this.checked ? 'none' : 'block';
    });
    
    // Form submission
    document.getElementById('registerForm').addEventListener('submit', function(e) {
      e.preventDefault();
      
      const name = nameInput.value;
      const email = emailInput.value;
      const password = passwordInput.value;
      const confirmPassword = confirmPasswordInput.value;
      const termsAgreed = termsCheckbox.checked;
      
      // Reset all errors
      nameError.style.display = 'none';
      emailError.style.display = 'none';
      passwordError.style.display = 'none';
      confirmPasswordError.style.display = 'none';
      confirmPasswordSuccess.style.display = 'none';
      termsError.style.display = 'none';
      
      // Remove all validation classes
      nameGroup.classList.remove('invalid', 'valid');
      emailGroup.classList.remove('invalid', 'valid');
      passwordGroup.classList.remove('invalid', 'valid');
      confirmPasswordGroup.classList.remove('invalid', 'valid');
      
      let isValid = true;
      
      // Validate name
      if (!validateName(name)) {
        nameError.style.display = 'block';
        nameGroup.classList.add('invalid');
        isValid = false;
      } else {
        nameGroup.classList.add('valid');
      }
      
      // Validate email
      if (!validateEmail(email)) {
        emailError.style.display = 'block';
        emailGroup.classList.add('invalid');
        isValid = false;
      } else {
        emailGroup.classList.add('valid');
      }
      
      // Validate password
      if (!validatePassword(password)) {
        passwordError.style.display = 'block';
        passwordGroup.classList.add('invalid');
        isValid = false;
      } else {
        passwordGroup.classList.add('valid');
      }
      
      // Validate password confirmation
      if (password !== confirmPassword) {
        confirmPasswordError.style.display = 'block';
        confirmPasswordGroup.classList.add('invalid');
        isValid = false;
      } else if (confirmPassword !== '') {
        confirmPasswordSuccess.style.display = 'block';
        confirmPasswordGroup.classList.add('valid');
      }
      
      // Validate terms agreement
      if (!termsAgreed) {
        termsError.style.display = 'block';
        isValid = false;
      }
      
      if (!isValid) {
        return;
      }
      
      // Simulate registration process
      const submitBtn = this.querySelector('button[type="submit"]');
      const originalText = submitBtn.textContent;
      
      submitBtn.textContent = 'Creating Account...';
      submitBtn.disabled = true;
      
      setTimeout(() => {
        alert(`Registration successful!\n\nWelcome ${name}! Your account has been created.\nEmail: ${email}`);
        submitBtn.textContent = originalText;
        submitBtn.disabled = false;
        
        // Redirect to login page after successful registration
        setTimeout(() => {
          window.location.href = 'login.php';
        }, 1000);
      }, 2000);
    });
  </script>
</body>
</html>