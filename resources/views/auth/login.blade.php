<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>RSUDEC Login</title>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Inter', sans-serif;
      height: 100vh;
      display: flex;
      background: #f8fafc;
      overflow: hidden;
    }

    .login-container {
      display: flex;
      width: 100%;
      height: 100vh;
    }

    /* Left Panel (gambar) */
    .left-panel {
      flex: 1;
      background: url("{{ asset('img/RSUDEC.jpeg') }}") no-repeat center center;
      background-size: cover;
    }

    /* Right Panel (form) */
    .right-panel {
      /* flex: 1; */
      background: white;
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      padding: 3rem 4rem;
      position: relative;
    }

    .logo-container {
      position: absolute;
      top: 2rem;
      left: 2rem;
      display: flex;
      align-items: center;
      gap: 0.75rem;
    }

    .logo-container img {
      max-height: 140px; 
      width: auto;
    }

    .login-content {
      max-width: 400px;
      width: 100%;
    }

    .welcome-title {
      font-size: 2.5rem;
      font-weight: 700;
      color: #1e293b;
      margin-bottom: 0.5rem;
    }

    .welcome-subtitle {
      font-size: 1.1rem;
      color: #64748b;
      margin-bottom: 3rem;
    }

    .form-group {
      margin-bottom: 1.5rem;
    }

    .form-input {
      width: 100%;
      padding: 1rem 1.25rem;
      border: 2px solid #e2e8f0;
      border-radius: 12px;
      font-size: 1rem;
      background: #f8fafc;
      transition: all 0.3s ease;
    }

    .form-input:focus {
      outline: none;
      border-color: #8b5cf6;
      background: white;
      box-shadow: 0 0 0 4px rgba(139, 92, 246, 0.1);
    }

    .password-input {
      position: relative;
    }

    .password-toggle {
      position: absolute;
      right: 1rem;
      top: 50%;
      transform: translateY(-50%);
      background: none;
      border: none;
      color: #94a3b8;
      cursor: pointer;
    }

    .form-options {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 2rem;
    }

    .remember-me {
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }

    .login-button {
      width: 100%;
      padding: 1rem 1.5rem;
      background: linear-gradient(135deg, #8b5cf6, #a855f7);
      color: white;
      border: none;
      border-radius: 12px;
      font-size: 1rem;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
    }

    .signup-link {
      text-align: center;
      margin-top: 1rem;
    }

    .signup-link a {
      color: #8b5cf6;
      text-decoration: none;
      font-weight: 600;
    }

    /* Responsiveness */
    @media (max-width: 768px) {
      .login-container {
        flex-direction: column;
      }

      .left-panel {
        display: none; /* gambar hilang di layar kecil */
      }

      .right-panel {
        flex: none;
        width: 100%;
        height: auto;
        min-height: 100vh;
        padding: 2rem 1.5rem;
      }

      .logo-container {
        position: static;
        margin-bottom: 2rem;
      }

      .welcome-title {
        font-size: 2rem;
      }
    }
  </style>
</head>
<body>
  <div class="login-container">
    <!-- Left Panel (gambar) -->
    <div class="left-panel"></div>

    <!-- Right Panel (form login) -->
    <div class="right-panel">
      <div class="logo-container">
        <img src="{{ asset('img/logoFull.png') }}" alt="Logo RSUDEC">
      </div>

      <div class="login-content">
        <h1 class="welcome-title">Hello,<br>Welcome to</h1>
        <p class="welcome-subtitle">Sistem Informasi Pengajuan Perencanaan (SIGAP)</p>

        <form action="{{ route('login') }}" method="post">
          @csrf
          <div class="form-group">
            <input type="email" class="form-input" name="email" placeholder="blabla@gmail.com" required>
          </div>

          <div class="form-group password-input">
            <input type="password" class="form-input" name="password" placeholder="••••••••" id="password" required>
            <button type="button" class="password-toggle" onclick="togglePassword()">
              <i class="fas fa-eye" id="password-icon"></i>
            </button>
          </div>

          <div class="form-options">
            <label class="remember-me">
              <input type="checkbox" name="remember">
              <span>Remember me</span>
            </label>
            <a href="{{ route('password.request') }}">Forgot Password?</a>
          </div>

          <button type="submit" class="login-button">Sign In</button>
        </form>

        <div class="signup-link">
          Don't have an account? <a href="{{ route('register') }}">Sign Up</a>
        </div>
      </div>
    </div>
  </div>

  <script>
    function togglePassword() {
      const passwordInput = document.getElementById('password');
      const passwordIcon = document.getElementById('password-icon');
      if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        passwordIcon.className = 'fas fa-eye-slash';
      } else {
        passwordInput.type = 'password';
        passwordIcon.className = 'fas fa-eye';
      }
    }
  </script>
</body>
</html>
