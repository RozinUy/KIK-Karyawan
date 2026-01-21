<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Login</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- Bootstrap 5 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- Bootstrap Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

  <style>
    body {
      background-color: #1e1e1e;
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .login-card {
      position: relative;
      width: 100%;
      max-width: 400px;
      padding: 3rem 2rem 2rem;
      border-radius: 10px;
    }

    .icon-circle {
      position: absolute;
      top: -45px;
      left: 50%;
      transform: translateX(-50%);
      width: 90px;
      height: 90px;
      background-color: #0d6efd;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      color: white;
      font-size: 40px;
    }
  </style>
</head>
<body>

  <div class="card login-card shadow">
    <div class="icon-circle">
      <i class="bi bi-person"></i>
    </div>

    <h4 class="text-center mb-4">Login</h4>

    <form>
      <div class="mb-3">
        <label class="form-label">Email</label>
        <input type="email" class="form-control" placeholder="Enter email">
      </div>

      <div class="mb-3">
        <label class="form-label">Password</label>
        <input type="password" class="form-control" placeholder="Enter password">
      </div>

      <button type="submit" class="btn btn-primary w-100">Login</button>

      <p class="text-center mt-3">
        Belum punya akun?
        <a href="register.html">Register</a>
      </p>
    </form>
  </div>

</body>
</html>
