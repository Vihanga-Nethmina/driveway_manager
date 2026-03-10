<?php
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../config/auth.php";

$error = "";
$success = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? "");
    $email = trim($_POST['email'] ?? "");
    $password = $_POST['password'] ?? "";

    if ($name === "" || $email === "" || $password === "") {
        $error = "All fields are required.";
    } else {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = "This email is already registered.";
        } else {
            $hash = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'customer')");
            $stmt->execute([$name, $email, $hash]);
            $success = "Registration successful! You can now login.";
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Register — Driveway Manager</title>
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
      padding: 32px 20px;
      overflow: hidden;
      position: relative;
    }

    .bg-orbs { position: fixed; inset: 0; pointer-events: none; z-index: 0; overflow: hidden; }
    .orb { position: absolute; border-radius: 50%; filter: blur(80px); opacity: 0.18; animation: float 8s ease-in-out infinite; }
    .orb-1 { width: 500px; height: 500px; background: radial-gradient(circle, #9333ea, transparent); top: -150px; right: -100px; animation-delay: -2s; }
    .orb-2 { width: 400px; height: 400px; background: radial-gradient(circle, #06b6d4, transparent); bottom: -100px; left: -100px; animation-delay: -5s; }

    @keyframes float {
      0%, 100% { transform: translateY(0) scale(1); }
      50%       { transform: translateY(-25px) scale(1.05); }
    }

    body::before {
      content: '';
      position: fixed; inset: 0;
      background-image:
        linear-gradient(rgba(124,58,237,0.04) 1px, transparent 1px),
        linear-gradient(90deg, rgba(124,58,237,0.04) 1px, transparent 1px);
      background-size: 50px 50px;
      z-index: 0;
    }

    .register-wrapper {
      position: relative; z-index: 1;
      width: 100%; max-width: 460px;
      animation: slideUp 0.6s cubic-bezier(0.4,0,0.2,1) both;
    }

    @keyframes slideUp {
      from { opacity: 0; transform: translateY(32px); }
      to   { opacity: 1; transform: translateY(0); }
    }

    .register-card {
      background: rgba(22,22,42,0.85);
      backdrop-filter: blur(24px);
      -webkit-backdrop-filter: blur(24px);
      border: 1px solid var(--border);
      border-radius: 28px;
      overflow: hidden;
      box-shadow: 0 32px 80px rgba(0,0,0,0.6), 0 0 60px rgba(124,58,237,0.12);
    }

    .register-brand {
      padding: 36px 36px 28px;
      text-align: center;
    }

    .brand-icon {
      width: 72px; height: 72px;
      border-radius: 22px;
      background: linear-gradient(135deg, #7c3aed, #06b6d4);
      display: flex; align-items: center; justify-content: center;
      margin: 0 auto 18px;
      font-size: 30px; color: white;
      box-shadow: 0 8px 32px rgba(124,58,237,0.45);
    }

    .brand-name {
      font-size: 26px; font-weight: 900; letter-spacing: -0.5px;
      background: linear-gradient(135deg, #a78bfa, #67e8f9);
      -webkit-background-clip: text; -webkit-text-fill-color: transparent;
    }

    .brand-tagline { color: var(--text-muted); font-size: 13px; font-weight: 500; margin-top: 5px; }

    .login-divider { height: 1px; background: linear-gradient(90deg, transparent, var(--border), transparent); }

    .register-body { padding: 32px 36px 36px; }

    .form-heading { font-size: 20px; font-weight: 700; color: var(--text-primary); margin-bottom: 5px; }
    .form-subheading { color: var(--text-muted); font-size: 13px; margin-bottom: 26px; }

    .input-group-modern { position: relative; margin-bottom: 18px; }
    .input-group-modern label {
      display: block; font-size: 12px; font-weight: 600;
      color: var(--text-secondary); text-transform: uppercase;
      letter-spacing: 0.6px; margin-bottom: 7px;
    }
    .input-wrapper { position: relative; }
    .input-wrapper .input-icon {
      position: absolute; left: 16px; top: 50%; transform: translateY(-50%);
      color: var(--text-muted); font-size: 16px; transition: color 0.3s; pointer-events: none;
    }
    .input-wrapper:focus-within .input-icon { color: var(--primary-light); }
    .input-wrapper input {
      width: 100%;
      background: rgba(255,255,255,0.05);
      border: 1px solid var(--border);
      border-radius: 14px;
      color: var(--text-primary);
      font-size: 14px; font-family: 'Inter', sans-serif;
      padding: 13px 16px 13px 46px;
      transition: all 0.3s; outline: none;
    }
    .input-wrapper input::placeholder { color: var(--text-muted); }
    .input-wrapper input:focus {
      background: rgba(124,58,237,0.08);
      border-color: var(--primary);
      box-shadow: 0 0 0 3px rgba(124,58,237,0.18);
    }

    /* Strength Indicator */
    .password-strength {
      display: flex; gap: 4px; margin-top: 8px;
    }
    .strength-bar {
      height: 3px; flex: 1; border-radius: 99px;
      background: rgba(255,255,255,0.1);
      transition: background 0.3s;
    }

    .btn-register {
      width: 100%; padding: 15px; border: none; border-radius: 14px;
      background: linear-gradient(135deg, #7c3aed 0%, #06b6d4 100%);
      color: white; font-size: 15px; font-weight: 700;
      font-family: 'Inter', sans-serif; cursor: pointer;
      transition: all 0.3s cubic-bezier(0.4,0,0.2,1);
      position: relative; overflow: hidden;
      box-shadow: 0 6px 24px rgba(124,58,237,0.4);
      margin-top: 6px; letter-spacing: 0.3px;
    }
    .btn-register::before {
      content: ''; position: absolute; inset: 0;
      background: linear-gradient(135deg, #9333ea, #0ea5e9);
      opacity: 0; transition: opacity 0.3s;
    }
    .btn-register:hover { transform: translateY(-2px); box-shadow: 0 10px 40px rgba(124,58,237,0.55); }
    .btn-register:hover::before { opacity: 1; }
    .btn-register span { position: relative; z-index: 1; }

    .alert-modern {
      border-radius: 12px; padding: 12px 16px;
      font-size: 13px; font-weight: 500; margin-bottom: 18px;
      display: flex; align-items: center; gap: 10px;
    }
    .alert-danger-modern  { background: rgba(239,68,68,0.1); border: 1px solid rgba(239,68,68,0.3); color: #f87171; }
    .alert-success-modern { background: rgba(16,185,129,0.1); border: 1px solid rgba(16,185,129,0.3); color: #34d399; }

    .register-footer { text-align: center; margin-top: 22px; color: var(--text-muted); font-size: 13px; }
    .register-footer a { color: var(--primary-light); text-decoration: none; font-weight: 600; transition: color 0.2s; }
    .register-footer a:hover { color: #c4b5fd; text-decoration: underline; }

    .features-strip {
      display: flex; justify-content: center; gap: 24px;
      padding: 18px 36px 28px; border-top: 1px solid var(--border);
    }
    .feature-pill { display: flex; align-items: center; gap: 6px; font-size: 12px; color: var(--text-muted); font-weight: 500; }
    .feature-pill i { color: var(--accent); font-size: 13px; }
  </style>
</head>
<body>
  <div class="bg-orbs">
    <div class="orb orb-1"></div>
    <div class="orb orb-2"></div>
  </div>

  <div class="register-wrapper">
    <div class="register-card">
      <div class="register-brand">
        <div class="brand-icon"><i class="fas fa-car"></i></div>
        <div class="brand-name">DRIVEWAY</div>
        <div class="brand-tagline">Premium Car Rental Management</div>
      </div>

      <div class="login-divider"></div>

      <div class="register-body">
        <div class="form-heading">Create your account ✨</div>
        <div class="form-subheading">Join thousands of happy customers today</div>

        <?php if ($error): ?>
        <div class="alert-modern alert-danger-modern">
          <i class="bi bi-exclamation-triangle-fill"></i>
          <?= htmlspecialchars($error) ?>
        </div>
        <?php endif; ?>
        <?php if ($success): ?>
        <div class="alert-modern alert-success-modern">
          <i class="bi bi-check-circle-fill"></i>
          <?= htmlspecialchars($success) ?>
        </div>
        <?php endif; ?>

        <form method="post">
          <div class="input-group-modern">
            <label>Full Name</label>
            <div class="input-wrapper">
              <i class="bi bi-person input-icon"></i>
              <input type="text" name="name" placeholder="John Doe" required autocomplete="name">
            </div>
          </div>

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
              <input type="password" name="password" id="pwdInput" placeholder="Create a strong password" required autocomplete="new-password">
            </div>
            <div class="password-strength" id="strengthBars">
              <div class="strength-bar" id="bar1"></div>
              <div class="strength-bar" id="bar2"></div>
              <div class="strength-bar" id="bar3"></div>
              <div class="strength-bar" id="bar4"></div>
            </div>
          </div>

          <button type="submit" class="btn-register">
            <span><i class="bi bi-person-plus me-2"></i>Create Free Account</span>
          </button>
        </form>

        <div class="register-footer">
          Already have an account? <a href="login.php">Sign in</a>
        </div>
      </div>

      <div class="features-strip">
        <div class="feature-pill"><i class="fas fa-shield-alt"></i> Secure</div>
        <div class="feature-pill"><i class="fas fa-bolt"></i> Fast</div>
        <div class="feature-pill"><i class="fas fa-star"></i> Free</div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Password strength indicator
    const pwdInput = document.getElementById('pwdInput');
    const bars = [bar1, bar2, bar3, bar4];
    const colors = ['#ef4444','#f59e0b','#10b981','#7c3aed'];

    pwdInput.addEventListener('input', function() {
      const val = this.value;
      let strength = 0;
      if (val.length >= 6) strength++;
      if (val.length >= 10) strength++;
      if (/[A-Z]/.test(val) && /[0-9]/.test(val)) strength++;
      if (/[^A-Za-z0-9]/.test(val)) strength++;

      bars.forEach((bar, i) => {
        bar.style.background = i < strength ? colors[strength - 1] : 'rgba(255,255,255,0.1)';
      });
    });
  </script>
</body>
</html>
