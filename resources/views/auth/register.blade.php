<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>RSUDEC Register</title>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }

    body {
      font-family: 'Inter', sans-serif;
      height: 100vh;
      display: flex;
      background: #f8fafc;
      overflow: hidden;
    }

    .register-container {
      display: flex;
      width: 100%;
      height: 100vh;
    }

    /* Left Panel → Gambar */
    .left-panel {
      flex: 1;
      background: url("{{ asset('img/RSUDEC.jpeg') }}") no-repeat center center;
      background-size: cover;
    }

    /* Right Panel → Form */
    .right-panel {
      flex: 1;
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
    }

    .logo-container img {
      max-height: 140px;
      width: auto;
    }

    .register-content {
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
      margin-bottom: 2rem;
    }

    .form-group { margin-bottom: 1.2rem; }

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

    .login-link {
      text-align: center;
      margin-top: 1rem;
    }

    .login-link a {
      color: #8b5cf6;
      text-decoration: none;
      font-weight: 600;
    }

    /* Responsive */
    @media (max-width: 768px) {
      .register-container {
        flex-direction: column;
      }
      .left-panel { display: none; }
      .right-panel { width: 100%; min-height: 100vh; }
    }
  </style>
</head>
<body>
  <div class="register-container">
    <!-- Left Panel (gambar) -->
    <div class="left-panel"></div>

    <!-- Right Panel (form) -->
    <div class="right-panel">
      <div class="logo-container">
        <img src="{{ asset('img/logoFull.png') }}" alt="Logo RSUDEC">
      </div>

      <div class="register-content">
        <h1 class="welcome-title">Create Account</h1>
        <p class="welcome-subtitle">Register to start your journey</p>

        <form action="{{ route('register') }}" method="post">
          @csrf
          <div class="form-group">
            <input type="text" name="name" class="form-input @error('name') is-invalid @enderror" 
                   value="{{ old('name') }}" placeholder="Full Name" required>
            @error('name')
              <span class="invalid-feedback" role="alert">{{ $message }}</span>
            @enderror
          </div>

          <div class="form-group">
            <input type="email" name="email" class="form-input @error('email') is-invalid @enderror" 
                   value="{{ old('email') }}" placeholder="Email Address" required>
            @error('email')
              <span class="invalid-feedback" role="alert">{{ $message }}</span>
            @enderror
          </div>

          <div class="form-group">
            <input type="password" name="password" class="form-input @error('password') is-invalid @enderror" 
                   placeholder="Password" required>
            @error('password')
              <span class="invalid-feedback" role="alert">{{ $message }}</span>
            @enderror
          </div>

          <div class="form-group">
            <input type="password" name="password_confirmation" class="form-input" 
                   placeholder="Confirm Password" required>
          </div>

          <button type="submit" class="login-button">Register</button>
        </form>

        <div class="login-link">
          Already have an account? <a href="{{ route('login') }}">Login</a>
        </div>
      </div>
    </div>
  </div>
</body>
</html>