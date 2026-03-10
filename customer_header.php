<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/icons.php";
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Driveway Manager — Premium Car Rental</title>
  <meta name="description" content="Book premium cars at the best prices with Driveway Manager.">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/style.css">
  <style>
    :root {
      --primary:#7c3aed; --primary-light:#a78bfa; --accent:#06b6d4;
      --dark:#0f0f1a; --dark-2:#16162a; --dark-3:#1e1e35;
      --border:rgba(255,255,255,0.08);
      --text-primary:#f1f5f9; --text-secondary:#94a3b8; --text-muted:#64748b;
    }
    *, *::before, *::after { box-sizing:border-box; font-family:'Inter',sans-serif; }
    body { background:var(--dark); color:var(--text-primary); overflow-x:hidden; }

    /* ── SVG icon util ────────────────────────── */
    .nav-svg { display:inline-flex; align-items:center; justify-content:center; flex-shrink:0; }
    .nav-svg svg { display:block; }

    /* ── Top Info Bar ─────────────────────────── */
    .top-bar {
      background:var(--dark-2); border-bottom:1px solid var(--border);
      padding:9px 0; font-size:12.5px; color:var(--text-muted);
    }
    .top-bar .contact-items { display:flex; align-items:center; gap:24px; }
    .top-bar .contact-item {
      display:flex; align-items:center; gap:6px;
    }
    .top-bar .contact-item svg { color:var(--accent); }
    .top-bar .user-chip {
      display:flex; align-items:center; gap:8px;
      background:rgba(124,58,237,.1); border:1px solid rgba(124,58,237,.2);
      padding:4px 12px 4px 8px; border-radius:999px;
      color:var(--primary-light); font-weight:500; font-size:12px;
    }
    .top-bar .user-avatar-sm {
      width:22px; height:22px; border-radius:50%;
      background:linear-gradient(135deg,#7c3aed,#06b6d4);
      display:flex; align-items:center; justify-content:center; color:white;
    }

    /* ── Navbar ───────────────────────────────── */
    .navbar-custom {
      background:rgba(15,15,26,.92) !important;
      backdrop-filter:blur(20px); -webkit-backdrop-filter:blur(20px);
      border-bottom:1px solid var(--border); padding:16px 0; transition:all .35s ease;
    }
    .navbar-custom.scrolled {
      padding:10px 0; background:rgba(15,15,26,.98) !important;
      box-shadow:0 8px 32px rgba(0,0,0,.4);
    }
    .navbar-brand {
      font-size:24px !important; font-weight:900 !important; letter-spacing:-.5px;
      background:linear-gradient(135deg,#a78bfa,#67e8f9) !important;
      -webkit-background-clip:text !important; -webkit-text-fill-color:transparent !important;
      display:flex !important; align-items:center; gap:10px; transition:all .3s;
    }
    .brand-icon-nav {
      width:38px; height:38px; border-radius:12px;
      background:linear-gradient(135deg,#7c3aed,#06b6d4);
      display:flex; align-items:center; justify-content:center;
      color:white; flex-shrink:0;
      box-shadow:0 4px 16px rgba(124,58,237,.4);
    }
    .navbar-brand:hover { transform:scale(1.02); }

    .nav-link-custom {
      color:var(--text-secondary) !important; font-weight:500; font-size:14px;
      padding:9px 14px !important; border-radius:10px; transition:all .25s;
      display:flex; align-items:center; gap:7px; text-decoration:none;
    }
    .nav-link-custom:hover { color:var(--text-primary) !important; background:rgba(124,58,237,.12); }
    .nav-link-custom.active { color:var(--primary-light) !important; background:rgba(124,58,237,.15); }

    /* CTA logout button */
    .btn-nav-logout {
      background:linear-gradient(135deg,#7c3aed 0%,#06b6d4 100%);
      border:none; color:white !important; padding:10px 22px;
      border-radius:12px; font-weight:600; font-size:14px; transition:all .3s;
      box-shadow:0 4px 16px rgba(124,58,237,.35);
      display:flex; align-items:center; gap:7px; text-decoration:none;
    }
    .btn-nav-logout:hover { transform:translateY(-2px); box-shadow:0 8px 28px rgba(124,58,237,.5); color:white !important; }

    /* Hamburger */
    .navbar-toggler { border:1px solid var(--border) !important; padding:8px 12px !important; border-radius:10px !important; }
    .navbar-toggler-icon { filter:invert(.7) !important; }

    /* ── Hero ─────────────────────────────────── */
    .hero-section {
      min-height:500px; display:flex; align-items:center;
      justify-content:center; text-align:center;
      border-radius:28px; margin:24px 0 48px;
      position:relative; overflow:hidden;
      background:
        linear-gradient(135deg,rgba(124,58,237,.75) 0%,rgba(6,182,212,.5) 100%),
        url("<?= BASE_URL ?>/assets/car-keys.jpg") center/cover no-repeat;
      box-shadow:0 24px 80px rgba(0,0,0,.5);
    }
    .hero-section::before {
      content:''; position:absolute; inset:0;
      background:
        radial-gradient(ellipse at 20% 50%,rgba(124,58,237,.3) 0%,transparent 60%),
        radial-gradient(ellipse at 80% 50%,rgba(6,182,212,.2) 0%,transparent 60%);
    }
    .hero-section .container { position:relative; z-index:1; }
    .hero-section h1 {
      font-weight:900; font-size:clamp(32px,5vw,62px);
      letter-spacing:-1px; text-shadow:0 4px 24px rgba(0,0,0,.4);
      animation:fadeSlideUp .9s ease both;
    }
    .hero-section .lead { font-size:clamp(15px,2vw,20px); opacity:.9; animation:fadeSlideUp 1.1s ease both; }

    /* ── Feature Boxes ────────────────────────── */
    .feature-box {
      background:rgba(255,255,255,.04); border:1px solid var(--border);
      border-radius:20px; padding:32px 20px; height:100%; text-align:center;
      transition:all .35s cubic-bezier(.4,0,.2,1);
    }
    .feature-box:hover { transform:translateY(-8px); border-color:rgba(124,58,237,.4); background:rgba(124,58,237,.07); box-shadow:0 20px 50px rgba(0,0,0,.3); }
    .feature-box h5 { font-weight:700; color:var(--text-primary); margin-bottom:8px; font-size:16px; }
    .feature-box p  { color:var(--text-secondary); font-size:13.5px; line-height:1.6; margin:0; }

    /* ── Section Title ────────────────────────── */
    .section-title { font-size:clamp(22px,3vw,36px); font-weight:800; color:var(--text-primary); margin-bottom:14px; text-align:center; }
    .section-title::after { content:''; display:block; width:64px; height:3px; background:linear-gradient(135deg,#7c3aed,#06b6d4); border-radius:99px; margin:12px auto 0; }

    /* ── Card Modern ──────────────────────────── */
    .card-modern {
      background:rgba(255,255,255,.04); border:1px solid var(--border);
      border-radius:20px; overflow:hidden;
      transition:all .35s cubic-bezier(.4,0,.2,1); box-shadow:0 8px 32px rgba(0,0,0,.2);
    }
    .card-modern:hover { transform:translateY(-6px); border-color:rgba(124,58,237,.35); box-shadow:0 20px 50px rgba(0,0,0,.35); }

    /* ── Animate ──────────────────────────────── */
    .animate-on-scroll { opacity:0; transform:translateY(24px); transition:all .65s cubic-bezier(.4,0,.2,1); }
    .animate-on-scroll.visible { opacity:1; transform:translateY(0); }
    @keyframes fadeSlideUp { from{opacity:0;transform:translateY(28px);}to{opacity:1;transform:translateY(0);} }
  </style>
</head>
<body>

<!-- Top Bar -->
<div class="top-bar">
  <div class="container">
    <div class="d-flex justify-content-between align-items-center">
      <div class="contact-items">
        <span class="contact-item"><?= svgi('phone', '12px') ?> +1 234 567 890</span>
        <span class="contact-item"><?= svgi('email', '12px') ?> info@driveway.com</span>
      </div>
      <div class="user-chip">
        <div class="user-avatar-sm"><?= svgi('person', '10px') ?></div>
        <?= htmlspecialchars($_SESSION['user']['name']) ?>
      </div>
    </div>
  </div>
</div>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-custom sticky-top">
  <div class="container">
    <a class="navbar-brand" href="<?= BASE_URL ?>/dashboard.php">
      <div class="brand-icon-nav"><?= svgi('car', '18px') ?></div>
      DRIVEWAY
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto align-items-center gap-1">
        <li class="nav-item">
          <a class="nav-link-custom" href="<?= BASE_URL ?>/dashboard.php">
            <?= svgi('home', '14px') ?> Home
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link-custom" href="<?= BASE_URL ?>/booking/vehicles.php">
            <?= svgi('car-side', '14px') ?> Vehicles
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link-custom" href="<?= BASE_URL ?>/booking/my_bookings.php">
            <?= svgi('booking', '14px') ?> Bookings
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link-custom" href="<?= BASE_URL ?>/dashboard.php#about">
            <?= svgi('info', '14px') ?> About
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link-custom" href="<?= BASE_URL ?>/customer/settings.php">
            <?= svgi('settings', '14px') ?> Settings
          </a>
        </li>
        <li class="nav-item ms-2">
          <a class="btn-nav-logout" href="<?= BASE_URL ?>/logout.php">
            <?= svgi('logout', '14px') ?> Logout
          </a>
        </li>
      </ul>
    </div>
  </div>
</nav>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= BASE_URL ?>/assets/main.js"></script>
<script>
window.addEventListener('scroll', function() {
  document.querySelector('.navbar-custom').classList.toggle('scrolled', window.scrollY > 50);
  document.querySelectorAll('.animate-on-scroll').forEach(el => {
    if (el.getBoundingClientRect().top < window.innerHeight - 80) el.classList.add('visible');
  });
});
document.querySelectorAll('.nav-link-custom').forEach(link => {
  if (window.location.href.includes(link.getAttribute('href').split('#')[0])) link.classList.add('active');
});
</script>

<div class="container py-4">
