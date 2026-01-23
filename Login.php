<?php
session_start();
require_once __DIR__ . '/koneksidb.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email = trim($_POST['email'] ?? '');
  $password = $_POST['password'] ?? '';
  if ($email === '' || $password === '') {
    $error = 'Email dan password wajib diisi.';
  } else {
    $stmt = $conn->prepare('SELECT id, nama, password FROM admin WHERE email = ? LIMIT 1');
    if ($stmt) {
      $stmt->bind_param('s', $email);
      $stmt->execute();
      $res = $stmt->get_result();
      if ($row = $res->fetch_assoc()) {
        $hash = $row['password'];
        if (password_verify($password, $hash) || $password === $hash) {
          $_SESSION['user_id'] = $row['id'];
          $_SESSION['nama'] = $row['nama'] ?? 'Admin';
          $_SESSION['role'] = 'admin';
          header('Location: operator/home.php');
          exit;
        }
      }
      $stmt->close();
    }
    $stmt = $conn->prepare('SELECT id, nama, password FROM user WHERE email = ? LIMIT 1');
    if ($stmt) {
      $stmt->bind_param('s', $email);
      $stmt->execute();
      $res = $stmt->get_result();
      if ($row = $res->fetch_assoc()) {
        $hash = $row['password'];
        if (password_verify($password, $hash) || $password === $hash) {
          $_SESSION['user_id'] = $row['id'];
          $_SESSION['nama'] = $row['nama'] ?? 'Pengguna';
          $_SESSION['role'] = 'user';
          header('Location: pengguna/home.php');
          exit;
        }
      }
      $stmt->close();
    }
    $error = 'Email atau password tidak valid.';
  }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Login</title>
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

    .login-card {
      position: relative;
      width: 100%;
      max-width: 450px;
      background-color: var(--white);
      border-radius: 12px;
      box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
      overflow: hidden;
      border: 1px solid var(--border-color);
    }

    .card-header {
      background-color: var(--primary-color);
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
      color: var(--primary-color);
      font-size: 36px;
      border: 5px solid rgba(255, 255, 255, 0.2);
    }

    .login-title {
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

    .checkbox-container {
      display: flex;
      align-items: center;
      margin-bottom: 20px;
    }

    .checkbox-container input {
      margin-right: 10px;
      width: 18px;
      height: 18px;
    }

    .checkbox-container label {
      color: var(--text-color);
      font-size: 15px;
    }

    .login-button {
      background-color: var(--primary-color);
      color: white;
      border: none;
      border-radius: 8px;
      padding: 14px;
      font-size: 16px;
      font-weight: 600;
      width: 100%;
      cursor: pointer;
      transition: background-color 0.2s;
      margin-bottom: 15px;
    }

    .login-button:hover {
      background-color: #0b5ed7;
    }

    .forgot-password {
      display: block;
      text-align: center;
      color: var(--primary-color);
      text-decoration: none;
      margin-bottom: 20px;
      font-size: 15px;
    }

    .forgot-password:hover {
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

    .register-link-container {
      text-align: center;
      margin-top: 20px;
      color: var(--text-color);
      font-size: 15px;
    }

    .register-link {
      color: var(--primary-color);
      text-decoration: none;
      font-weight: 500;
    }

    .register-link:hover {
      text-decoration: underline;
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
    }

    /* Password toggle eye */
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

    /* Form validation styles */
    .error-message {
      color: #dc3545;
      font-size: 14px;
      margin-top: 5px;
      display: none;
    }
  </style>
</head>
<body>

<?php if ($error): ?>
  <div class="container mb-3" style="max-width: 450px;">
    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
  </div>
<?php endif; ?>
  <div class="login-card">
    <div class="card-header">
      <div class="user-icon">
        <i class="bi bi-person-fill"></i>
      </div>
      <h1 class="login-title">Login</h1>
    </div>

    <div class="card-body">
      <form id="loginForm" method="post" action="">
        <div class="form-group">
          <label class="form-label">Email</label>
          <input type="email" name="email" class="form-control" placeholder="Enter email" required id="emailInput">
          <div class="error-message" id="emailError">Please enter a valid email address</div>
        </div>

        <div class="form-group">
          <label class="form-label">Password</label>
          <div class="password-wrapper">
            <input type="password" name="password" class="form-control" id="passwordInput" placeholder="Enter password" required>
            <button type="button" class="password-toggle" id="togglePassword">
              <i class="bi bi-eye"></i>
            </button>
          </div>
          <div class="error-message" id="passwordError">Password must be at least 6 characters</div>
        </div>

        <div class="checkbox-container">
          <input type="checkbox" id="rememberMe">
          <label for="rememberMe">Remember me</label>
        </div>

        <button type="submit" class="login-button">Login</button>

        <div class="divider"></div>

        <div class="register-link-container">
          Belum punya akun?
          <a href="register.php" class="register-link">Register</a>
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
    
    // Form validation
    const emailInput = document.getElementById('emailInput');
    const passwordInput = document.getElementById('passwordInput');
    const emailError = document.getElementById('emailError');
    const passwordError = document.getElementById('passwordError');
    
    function validateEmail(email) {
      const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      return re.test(email);
    }
    
    function validatePassword(password) {
      return password.length >= 6;
    }
    
    // Real-time validation
    emailInput.addEventListener('blur', function() {
      if (!validateEmail(this.value) && this.value !== '') {
        emailError.style.display = 'block';
      } else {
        emailError.style.display = 'none';
      }
    });
    
    passwordInput.addEventListener('blur', function() {
      if (!validatePassword(this.value) && this.value !== '') {
        passwordError.style.display = 'block';
      } else {
        passwordError.style.display = 'none';
      }
    });
    

  </script>
</body>
</html>