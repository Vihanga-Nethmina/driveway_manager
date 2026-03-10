<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/icons.php";

// Get unread notifications count
$notif_count = 0;
if (!empty($_SESSION['user']) && $_SESSION['user']['role'] === 'admin') {
    require_once __DIR__ . "/../config/db.php";
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM notifications WHERE user_id=? AND is_read=0");
    $stmt->execute([$_SESSION['user']['id']]);
    $notif_count = $stmt->fetch()['count'];
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Driveway Manager — Admin</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/style.css">
  <style>
    :root {
      --primary: #7c3aed; --primary-light: #a78bfa;
      --accent: #06b6d4;
      --dark: #0f0f1a; --dark-2: #16162a; --dark-3: #1e1e35; --dark-4: #252542;
      --border: rgba(255,255,255,0.08);
      --text-primary: #f1f5f9; --text-secondary: #94a3b8; --text-muted: #64748b;
    }
    *, *::before, *::after { box-sizing: border-box; }
    html, body { height: 100%; }
    body {
      font-family: 'Inter', sans-serif;
      background: var(--dark); color: var(--text-primary);
      display: flex; min-height: 100vh; overflow-x: hidden;
    }

    /* ── SVG icon util ────────────────────────── */
    .nav-svg { display:inline-flex; align-items:center; justify-content:center; flex-shrink:0; }
    .nav-svg svg { display:block; }

    /* ── Sidebar ──────────────────────────────── */
    .admin-sidebar {
      width:260px; min-height:100vh;
      background:var(--dark-2); border-right:1px solid var(--border);
      display:flex; flex-direction:column;
      position:fixed; top:0; left:0; bottom:0; z-index:100;
      transition:transform .3s ease;
    }

    /* Brand */
    .sidebar-brand {
      padding:28px 20px 24px; border-bottom:1px solid var(--border);
      display:flex; align-items:center; gap:12px;
    }
    .brand-logo {
      width:44px; height:44px; border-radius:14px;
      background:linear-gradient(135deg,#7c3aed,#06b6d4);
      display:flex; align-items:center; justify-content:center;
      color:white; flex-shrink:0;
      box-shadow:0 4px 16px rgba(124,58,237,.4);
    }
    .brand-text .brand-name {
      font-size:18px; font-weight:900; letter-spacing:-0.3px;
      background:linear-gradient(135deg,#a78bfa,#67e8f9);
      -webkit-background-clip:text; -webkit-text-fill-color:transparent; line-height:1.2;
    }
    .brand-text .brand-role {
      font-size:11px; color:var(--text-muted); font-weight:500;
      letter-spacing:.5px; text-transform:uppercase; margin-top:2px;
    }

    /* User block */
    .sidebar-user {
      padding:16px 20px; border-bottom:1px solid var(--border);
      display:flex; align-items:center; gap:12px;
      background:rgba(124,58,237,.05);
    }
    .user-avatar {
      width:38px; height:38px; border-radius:12px;
      background:linear-gradient(135deg,rgba(124,58,237,.3),rgba(6,182,212,.3));
      display:flex; align-items:center; justify-content:center;
      color:var(--primary-light); flex-shrink:0;
      border:1px solid rgba(124,58,237,.3);
    }
    .user-info .user-name {
      font-size:13px; font-weight:600; color:var(--text-primary);
      max-width:140px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;
    }
    .user-info .user-role {
      font-size:11px; font-weight:500;
      background:linear-gradient(135deg,#7c3aed,#06b6d4);
      -webkit-background-clip:text; -webkit-text-fill-color:transparent; margin-top:1px;
    }

    /* Nav */
    .sidebar-nav { flex:1; padding:16px 12px; overflow-y:auto; }
    .nav-section-label {
      font-size:10px; font-weight:700; color:var(--text-muted);
      text-transform:uppercase; letter-spacing:1px; padding:8px 10px 6px; margin-top:8px;
    }
    .sidebar-link {
      display:flex; align-items:center; gap:11px;
      padding:11px 14px; margin:2px 0; border-radius:12px;
      color:var(--text-secondary); text-decoration:none;
      font-size:14px; font-weight:500;
      transition:all .25s cubic-bezier(.4,0,.2,1); position:relative;
    }
    .sidebar-link:hover {
      background:rgba(124,58,237,.12); color:var(--text-primary); transform:translateX(4px);
    }
    .sidebar-link.active { background:rgba(124,58,237,.2); color:var(--primary-light); }
    .sidebar-link.active::before {
      content:''; position:absolute; left:0; top:50%; transform:translateY(-50%);
      width:3px; height:20px;
      background:linear-gradient(135deg,#7c3aed,#06b6d4); border-radius:0 4px 4px 0;
    }
    .notif-badge {
      margin-left:auto; background:#ef4444; color:white;
      font-size:10px; font-weight:700; padding:2px 7px;
      border-radius:999px; animation:pulse 2s ease infinite;
    }
    @keyframes pulse { 0%,100%{transform:scale(1);}50%{transform:scale(1.1);} }
    .sidebar-divider { height:1px; background:var(--border); margin:12px 10px; }

    /* Logout */
    .sidebar-footer { padding:12px; border-top:1px solid var(--border); }
    .logout-link {
      display:flex; align-items:center; gap:11px; padding:11px 14px;
      border-radius:12px; color:rgba(239,68,68,.8); text-decoration:none;
      font-size:14px; font-weight:500; transition:all .25s;
    }
    .logout-link:hover { background:rgba(239,68,68,.1); color:#f87171; }

    /* ── Main content ─────────────────────────── */
    .admin-main { margin-left:260px; flex:1; min-height:100vh; display:flex; flex-direction:column; }
    .admin-topbar {
      background:var(--dark-2); border-bottom:1px solid var(--border);
      padding:0 32px; height:64px;
      display:flex; align-items:center; justify-content:space-between;
      position:sticky; top:0; z-index:50; backdrop-filter:blur(12px);
    }
    .topbar-title { font-size:18px; font-weight:700; color:var(--text-primary); }
    .topbar-right { display:flex; align-items:center; gap:12px; }
    .topbar-chip {
      display:flex; align-items:center; gap:8px;
      background:rgba(124,58,237,.1); border:1px solid rgba(124,58,237,.2);
      padding:8px 14px; border-radius:12px;
      font-size:13px; color:var(--primary-light); font-weight:600;
    }
    .admin-content { padding:32px; flex:1; }

    /* Mobile */
    .sidebar-toggle {
      display:none; background:rgba(124,58,237,.15);
      border:1px solid rgba(124,58,237,.3); color:var(--primary-light);
      padding:8px 12px; border-radius:10px; cursor:pointer;
      align-items:center; gap:8px;
    }
    @media(max-width:768px) {
      .admin-sidebar { transform:translateX(-260px); }
      .admin-sidebar.open { transform:translateX(0); }
      .admin-main { margin-left:0; }
      .sidebar-toggle { display:flex; }
    }

    /* ── Dark theme overrides ─────────────────── */
    .table { color:var(--text-primary); background:var(--dark-2) !important; border-color:var(--border); }
    .table thead { background:var(--dark-3) !important; border-color:var(--border); }
    .table thead tr { background:var(--dark-3) !important; }
    .table th { color:var(--text-primary) !important; font-weight:600; border-color:var(--border); padding:14px 16px; background:var(--dark-3) !important; }
    .table td { border-color:var(--border); padding:12px 16px; background:var(--dark-2) !important; }
    .table tbody tr { transition:background .2s; background:var(--dark-2) !important; }
    .table tbody tr:hover { background:var(--dark-3) !important; }
    .table-bordered { border:1px solid var(--border); border-radius:12px; overflow:hidden; }
    .bg-white { background:var(--dark-2) !important; }
    form.p-3, form.shadow-sm { background:var(--dark-2) !important; border:1px solid var(--border); }
    .form-control, .form-select {
      background:var(--dark-3); border:1px solid var(--border);
      color:var(--text-primary); padding:10px 14px; border-radius:10px;
    }
    .form-control:focus, .form-select:focus {
      background:var(--dark-4); border-color:var(--primary);
      color:var(--text-primary); box-shadow:0 0 0 3px rgba(124,58,237,.15);
    }
    .form-label { color:var(--text-secondary); font-weight:500; font-size:13px; margin-bottom:6px; }
    .alert { border-radius:12px; border:1px solid; }
    .alert-success { background:rgba(16,185,129,.1); border-color:rgba(16,185,129,.3); color:#34d399; }
    .alert-danger { background:rgba(239,68,68,.1); border-color:rgba(239,68,68,.3); color:#f87171; }
    .btn { border-radius:10px; font-weight:600; padding:8px 16px; transition:all .25s; }
    .btn-primary { background:linear-gradient(135deg,#7c3aed,#9333ea); border:none; }
    .btn-primary:hover { transform:translateY(-2px); box-shadow:0 8px 20px rgba(124,58,237,.4); }
    .btn-success { background:linear-gradient(135deg,#10b981,#059669); border:none; }
    .btn-success:hover { transform:translateY(-2px); box-shadow:0 8px 20px rgba(16,185,129,.4); }
    .btn-warning { background:linear-gradient(135deg,#f59e0b,#d97706); border:none; color:white; }
    .btn-warning:hover { transform:translateY(-2px); box-shadow:0 8px 20px rgba(245,158,11,.4); color:white; }
    .btn-danger { background:linear-gradient(135deg,#ef4444,#dc2626); border:none; }
    .btn-danger:hover { transform:translateY(-2px); box-shadow:0 8px 20px rgba(239,68,68,.4); }
    .btn-secondary { background:var(--dark-3); border:1px solid var(--border); color:var(--text-primary); }
    .btn-secondary:hover { background:var(--dark-4); border-color:var(--primary); color:var(--text-primary); }
    .btn-info { background:linear-gradient(135deg,#06b6d4,#0891b2); border:none; }
    .btn-info:hover { transform:translateY(-2px); box-shadow:0 8px 20px rgba(6,182,212,.4); }
    .badge { font-weight:600; padding:5px 10px; border-radius:8px; }
    .shadow-sm { box-shadow:0 4px 16px rgba(0,0,0,.3) !important; }
    .rounded { border-radius:10px !important; }
    .img-thumbnail { background:var(--dark-3); border:1px solid var(--border); padding:4px; }
    h4, h5, h6 { color:var(--text-primary); font-weight:700; }
    small { color:var(--text-muted); }
    .card { background:var(--dark-2); border:1px solid var(--border); color:var(--text-primary); }
    .card-header { background:var(--dark-3); border-bottom:1px solid var(--border); color:var(--text-primary); font-weight:600; }
    .card-body { background:var(--dark-2); }
    .list-group-item { background:var(--dark-2); border:1px solid var(--border); color:var(--text-primary); }
    .list-group-item:hover { background:var(--dark-3); }
    .list-group-item-primary { background:rgba(124,58,237,.15); border-color:rgba(124,58,237,.3); }
    .list-group-item-danger { background:rgba(239,68,68,.1); border-color:rgba(239,68,68,.3); }
    .list-group-item-warning { background:rgba(245,158,11,.1); border-color:rgba(245,158,11,.3); }
    .nav-tabs { border-bottom:1px solid var(--border); }
    .nav-tabs .nav-link { color:var(--text-secondary); border:none; }
    .nav-tabs .nav-link:hover { color:var(--text-primary); background:rgba(124,58,237,.1); }
    .nav-tabs .nav-link.active { color:var(--primary-light); background:rgba(124,58,237,.15); border-bottom:2px solid var(--primary); }
    .text-muted { color:var(--text-muted) !important; }
  </style>
</head>
<body>

<!-- Admin Sidebar -->
<div class="admin-sidebar" id="adminSidebar">

  <!-- Brand -->
  <div class="sidebar-brand">
    <div class="brand-logo"><?= svgi('car', '22px') ?></div>
    <div class="brand-text">
      <div class="brand-name">DRIVEWAY</div>
      <div class="brand-role">Admin Panel</div>
    </div>
  </div>

  <!-- User Info -->
  <div class="sidebar-user">
    <div class="user-avatar"><?= svgi('person', '18px') ?></div>
    <div class="user-info">
      <div class="user-name"><?= htmlspecialchars($_SESSION['user']['name']) ?></div>
      <div class="user-role">Administrator</div>
    </div>
  </div>

  <!-- Navigation -->
  <div class="sidebar-nav">
    <div class="nav-section-label">Overview</div>
    <a class="sidebar-link" href="<?= BASE_URL ?>/dashboard.php">
      <?= svgi('dashboard', '17px') ?> Dashboard
    </a>

    <div class="nav-section-label">Management</div>
    <a class="sidebar-link" href="<?= BASE_URL ?>/vehicles/manage.php">
      <?= svgi('car', '17px') ?> Vehicles
    </a>
    <a class="sidebar-link" href="<?= BASE_URL ?>/admin/bookings.php">
      <?= svgi('booking', '17px') ?> Bookings
    </a>
    <a class="sidebar-link" href="<?= BASE_URL ?>/admin/manage_customers.php">
      <?= svgi('users', '17px') ?> Customers
    </a>

    <div class="nav-section-label">Maintenance</div>
    <a class="sidebar-link" href="<?= BASE_URL ?>/admin/maintenance_reminders.php">
      <?= svgi('tools', '17px') ?> Maintenance
    </a>
    <a class="sidebar-link" href="<?= BASE_URL ?>/admin/vehicle_history.php">
      <?= svgi('history', '17px') ?> Vehicle History
    </a>
    <a class="sidebar-link" href="<?= BASE_URL ?>/admin/vehicle_report.php">
      <?= svgi('chart', '17px') ?> Reports
    </a>

    <div class="sidebar-divider"></div>

    <a class="sidebar-link" href="<?= BASE_URL ?>/admin/notifications.php">
      <?= svgi('bell', '17px') ?> Notifications
      <?php if ($notif_count > 0): ?>
        <span class="notif-badge"><?= $notif_count ?></span>
      <?php endif; ?>
    </a>
    <a class="sidebar-link" href="<?= BASE_URL ?>/customer/settings.php">
      <?= svgi('gear', '17px') ?> Settings
    </a>
  </div>

  <!-- Logout -->
  <div class="sidebar-footer">
    <a class="logout-link" href="<?= BASE_URL ?>/logout.php">
      <?= svgi('logout', '17px') ?> Sign Out
    </a>
  </div>
</div>

<!-- Main Content Wrapper -->
<div class="admin-main">
  <!-- Top Bar -->
  <div class="admin-topbar">
    <div class="d-flex align-items-center gap-3">
      <button class="sidebar-toggle" onclick="toggleSidebar()">
        <?= svgi('menu', '18px') ?>
      </button>
      <div class="topbar-title">Admin Dashboard</div>
    </div>
    <div class="topbar-right">
      <?php if ($notif_count > 0): ?>
      <a href="<?= BASE_URL ?>/admin/notifications.php" class="topbar-chip"
         style="color:#f87171;border-color:rgba(239,68,68,.3);background:rgba(239,68,68,.1);">
        <?= svgi('bell', '14px') ?> <?= $notif_count ?> new
      </a>
      <?php endif; ?>
      <div class="topbar-chip">
        <?= svgi('dot', '8px') ?>&nbsp;
        <?= htmlspecialchars($_SESSION['user']['name']) ?>
      </div>
    </div>
  </div>

  <!-- Page Content -->
  <div class="admin-content">

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
function toggleSidebar() {
  document.getElementById('adminSidebar').classList.toggle('open');
}
document.querySelectorAll('.sidebar-link').forEach(link => {
  if (link.href === window.location.href ||
      window.location.href.includes(link.getAttribute('href'))) {
    link.classList.add('active');
  }
});
</script>
