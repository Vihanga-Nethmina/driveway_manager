<?php
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../config/auth.php";

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST')
 {
    $email = isset($_POST['email']) ? trim($_POST['email']) : "";
$password = isset($_POST['password']) ? $_POST['password'] : "";

    if ($email === "" || $password === "") {
        $error = "Email and password are required.";
    } else {
        $stmt = $pdo->prepare("SELECT id, name, email, password, role FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password'])) {
            $error = "Invalid login credentials.";
        } else {
            unset($user['password']);
            $_SESSION['user'] = $user;
            header("Location: dashboard.php");
            exit;
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Login — Driveway Manager</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
  <style>
    :root {
      --primary: #7c3aed;
      --primary-light: #a78bfa;
      --accent: #06b6d4;
      --dark: #0f0f1a;
      --dark-2: #16162a;
      --dark-3: #1e1e35;
      --border: rgba(255,255,255,0.10);
      --text-primary: #f1f5f9;
      --text-secondary: #94a3b8;
      --text-muted: #64748b;
    }

    * { font-family: 'Inter', sans-serif; box-sizing: border-box; margin: 0; padding: 0; }

    body {
      background: var(--dark);
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      overflow: hidden;
      position: relative;
    }

    /* Animated Background */
    .bg-orbs {
      position: fixed;
      inset: 0;
      pointer-events: none;
      z-index: 0;
      overflow: hidden;
    }

    .orb {
      position: absolute;
      border-radius: 50%;
      filter: blur(80px);
      opacity: 0.18;
      animation: float 8s ease-in-out infinite;
    }

    .orb-1 {
      width: 500px; height: 500px;
      background: radial-gradient(circle, #7c3aed, transparent);
      top: -150px; left: -150px;
      animation-delay: 0s;
    }

    .orb-2 {
      width: 400px; height: 400px;
      background: radial-gradient(circle, #06b6d4, transparent);
      bottom: -100px; right: -100px;
      animation-delay: -3s;
    }

    .orb-3 {
      width: 300px; height: 300px;
      background: radial-gradient(circle, #9333ea, transparent);
      top: 50%; left: 50%;
      transform: translate(-50%, -50%);
      animation-delay: -6s;
    }

    @keyframes float {
      0%, 100% { transform: translateY(0) scale(1); }
      50%       { transform: translateY(-30px) scale(1.05); }
    }

    /* Grid pattern */
    body::before {
      content: '';
      position: fixed;
      inset: 0;
      background-image:
        linear-gradient(rgba(124,58,237,0.04) 1px, transparent 1px),
        linear-gradient(90deg, rgba(124,58,237,0.04) 1px, transparent 1px);
      background-size: 50px 50px;
      z-index: 0;
    }

    /* Login Box */
    .login-wrapper {
      position: relative;
      z-index: 1;
      width: 100%;
      max-width: 440px;
      padding: 20px;
      animation: slideUp 0.6s cubic-bezier(0.4,0,0.2,1) both;
    }

    @keyframes slideUp {
      from { opacity: 0; transform: translateY(32px); }
      to   { opacity: 1; transform: translateY(0); }
    }

    .login-card {
      background: rgba(22,22,42,0.85);
      backdrop-filter: blur(24px);
      -webkit-backdrop-filter: blur(24px);
      border: 1px solid var(--border);
      border-radius: 28px;
      overflow: hidden;
      box-shadow: 0 32px 80px rgba(0,0,0,0.6), 0 0 60px rgba(124,58,237,0.12);
    }

    /* Brand Header */
    .login-brand {
      padding: 40px 36px 32px;
      text-align: center;
      position: relative;
    }

    .brand-icon {
      width: 72px; height: 72px;
      border-radius: 22px;
      background: linear-gradient(135deg, #7c3aed, #06b6d4);
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 20px;
      font-size: 30px;
      color: white;
      box-shadow: 0 8px 32px rgba(124,58,237,0.45);
      animation: pulse-glow 3s ease infinite;
    }

    @keyframes pulse-glow {
      0%, 100% { box-shadow: 0 8px 32px rgba(124,58,237,0.45); }
      50%       { box-shadow: 0 8px 48px rgba(124,58,237,0.7), 0 0 0 8px rgba(124,58,237,0.08); }
    }

    .brand-name {
      font-size: 28px;
      font-weight: 900;
      letter-spacing: -0.5px;
      background: linear-gradient(135deg, #a78bfa, #67e8f9);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
    }

    .brand-tagline {
      color: var(--text-muted);
      font-size: 13px;
      font-weight: 500;
      margin-top: 6px;
      letter-spacing: 0.3px;
    }

    /* Divider */
    .login-divider {
      height: 1px;
      background: linear-gradient(90deg, transparent, var(--border), transparent);
      margin: 0;
    }

    /* Form Area */
    .login-body {
      padding: 36px;
    }

    .form-heading {
      font-size: 20px;
      font-weight: 700;
      color: var(--text-primary);
      margin-bottom: 6px;
    }

    .form-subheading {
      color: var(--text-muted);
      font-size: 13px;
      margin-bottom: 28px;
    }

    /* Input Groups */
    .input-group-modern {
      position: relative;
      margin-bottom: 20px;
    }

    .input-group-modern label {
      display: block;
      font-size: 12px;
      font-weight: 600;
      color: var(--text-secondary);
      text-transform: uppercase;
      letter-spacing: 0.6px;
      margin-bottom: 8px;
    }

    .input-wrapper {
      position: relative;
    }

    .input-wrapper .input-icon {
      position: absolute;
      left: 16px;
      top: 50%;
      transform: translateY(-50%);
      color: var(--text-muted);
      font-size: 16px;
      transition: color 0.3s;
      pointer-events: none;
    }

    .input-wrapper input {
      width: 100%;
      background: rgba(255,255,255,0.05);
      border: 1px solid var(--border);
      border-radius: 14px;
      color: var(--text-primary);
      font-size: 14px;
      font-family: 'Inter', sans-serif;
      padding: 14px 16px 14px 46px;
      transition: all 0.3s;
      outline: none;
    }

    .input-wrapper input::placeholder { color: var(--text-muted); }

    .input-wrapper input:focus {
      background: rgba(124,58,237,0.08);
      border-color: var(--primary);
      box-shadow: 0 0 0 3px rgba(124,58,237,0.18);
    }

    .input-wrapper input:focus + .input-border { width: 100%; }
    .input-wrapper:focus-within .input-icon { color: var(--primary-light); }

    /* Submit Button */
    .btn-login {
      width: 100%;
      padding: 15px;
      border: none;
      border-radius: 14px;
      background: linear-gradient(135deg, #7c3aed 0%, #06b6d4 100%);
      color: white;
      font-size: 15px;
      font-weight: 700;
      font-family: 'Inter', sans-serif;
      cursor: pointer;
      transition: all 0.3s cubic-bezier(0.4,0,0.2,1);
      position: relative;
      overflow: hidden;
      box-shadow: 0 6px 24px rgba(124,58,237,0.4);
      margin-top: 8px;
      letter-spacing: 0.3px;
    }

    .btn-login::before {
      content: '';
      position: absolute;
      inset: 0;
      background: linear-gradient(135deg, #9333ea 0%, #0ea5e9 100%);
      opacity: 0;
      transition: opacity 0.3s;
    }

    .btn-login:hover {
      transform: translateY(-2px);
      box-shadow: 0 10px 40px rgba(124,58,237,0.55);
    }

    .btn-login:hover::before { opacity: 1; }
    .btn-login span { position: relative; z-index: 1; }

    /* Error Alert */
    .alert-modern {
      background: rgba(239,68,68,0.1);
      border: 1px solid rgba(239,68,68,0.3);
      border-radius: 12px;
      color: #f87171;
      padding: 12px 16px;
      font-size: 13px;
      font-weight: 500;
      margin-bottom: 20px;
      display: flex;
      align-items: center;
      gap: 10px;
    }

    /* Footer Link */
    .login-footer {
      text-align: center;
      margin-top: 24px;
      color: var(--text-muted);
      font-size: 13px;
    }

    .login-footer a {
      color: var(--primary-light);
      text-decoration: none;
      font-weight: 600;
      transition: color 0.2s;
    }

    .login-footer a:hover { color: #c4b5fd; text-decoration: underline; }

    /* Features Strip */
    .features-strip {
      display: flex;
      justify-content: center;
      gap: 24px;
      padding: 20px 36px 32px;
      border-top: 1px solid var(--border);
    }

    .feature-pill {
      display: flex;
      align-items: center;
      gap: 6px;
      font-size: 12px;
      color: var(--text-muted);
      font-weight: 500;
    }

    .feature-pill i {
      color: var(--accent);
      font-size: 13px;
    }
  </style>
</head>
<body>
  <!-- Background Orbs -->
  <div class="bg-orbs">
    <div class="orb orb-1"></div>
    <div class="orb orb-2"></div>
    <div class="orb orb-3"></div>
  </div>

  <div class="login-wrapper">
    <div class="login-card">

      <!-- Brand Header -->
      <div class="login-brand">
        <div class="brand-icon">
          <i class="fas fa-car"></i>
        </div>
        <div class="brand-name">DRIVEWAY</div>
        <div class="brand-tagline">Premium Car Rental Management</div>
      </div>

      <div class="login-divider"></div>

      <!-- Form Body -->
      <div class="login-body">
        <div class="form-heading">Welcome back 👋</div>
        <div class="form-subheading">Sign in to your account to continue</div>

        <?php if ($error): ?>
        <div class="alert-modern">
          <i class="bi bi-exclamation-triangle-fill"></i>
          <?= htmlspecialchars($error) ?>
        </div>
        <?php endif; ?>

        <form method="post">
          <div class="input-group-modern">
            <label>Email Address</label>
            <div class="input-wrapper">
              <i class="bi bi-envelope input-icon"></i>
              <input type="email" name="email" placeholder="your@email.com" required autocomplete="email">
            </div>
          </div>

          <div class="input-group-modern">
            <label>Password</label>
            <div class="input-wrapper">
              <i class="bi bi-lock input-icon"></i>
              <input type="password" name="password" placeholder="Enter your password" required autocomplete="current-password">
            </div>
          </div>

          <button type="submit" class="btn-login">
            <span><i class="bi bi-box-arrow-in-right me-2"></i>Sign In</span>
          </button>
        </form>

        <div class="login-footer">
          Don't have an account? <a href="register.php">Create one free</a>
        </div>
      </div>

      <!-- Features Strip -->
      <div class="features-strip">
        <div class="feature-pill"><i class="fas fa-shield-alt"></i> Secure</div>
        <div class="feature-pill"><i class="fas fa-bolt"></i> Fast</div>
        <div class="feature-pill"><i class="fas fa-star"></i> Premium</div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
