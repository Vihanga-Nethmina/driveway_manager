<?php
require_once __DIR__ . "/../config/auth.php";
require_once __DIR__ . "/../includes/icons.php";
require_login();

$user = $_SESSION['user'] ?? null;

if (!is_array($user) || !isset($user['role'])) {
    session_destroy();
    header("Location: login.php");
    exit;
}


/* ═══════════════════════════════════════════════
   ADMIN DASHBOARD
═══════════════════════════════════════════════ */
if ($user['role'] === 'admin') {
    require_once __DIR__ . "/../includes/admin_header.php";
    ?>

    <style>
    /* ── SVG icon sizing ─────────────────────── */
    .svg-icon { display:inline-flex; align-items:center; justify-content:center; line-height:1; }
    .svg-icon svg { width:1em; height:1em; fill:currentColor; display:block; }
    .ac-icon .svg-icon svg { width:24px; height:24px; }
    .ac-cta .svg-icon svg { width:14px; height:14px; }

    /* ── Force dark theme ─────────────────────── */
    * { box-sizing: border-box; }
    .dash-page { width:100%; padding:0; }

    /* ── hero banner ──────────────────────────── */
    .dash-hero {
      background:linear-gradient(135deg,rgba(124,58,237,.14) 0%,rgba(6,182,212,.07) 100%);
      border:1px solid rgba(255,255,255,.07);border-radius:20px;
      padding:40px 44px;margin-bottom:40px;position:relative;overflow:hidden;
    }
    .dash-hero::before{
      content:'';position:absolute;top:-80px;right:-80px;width:280px;height:280px;
      border-radius:50%;background:radial-gradient(circle,rgba(124,58,237,.2) 0%,transparent 65%);
      pointer-events:none;
    }
    .dash-hero::after{
      content:'';position:absolute;bottom:-60px;left:25%;width:200px;height:200px;
      border-radius:50%;background:radial-gradient(circle,rgba(6,182,212,.12) 0%,transparent 65%);
      pointer-events:none;
    }
    .dash-hero-inner{position:relative;z-index:1;}
    .dash-eyebrow{
      font-size:11px;font-weight:700;letter-spacing:2px;text-transform:uppercase;
      color:#7c3aed;margin-bottom:10px;display:flex;align-items:center;gap:10px;
    }
    .dash-eyebrow::before{
      content:'';display:inline-block;width:22px;height:2px;
      background:linear-gradient(90deg,#7c3aed,#06b6d4);border-radius:2px;
    }
    .dash-title{
      font-size:clamp(24px,3vw,36px);font-weight:900;color:#f1f5f9;
      margin:0 0 6px;letter-spacing:-.5px;line-height:1.2;
    }
    .dash-title span{background:linear-gradient(135deg,#a78bfa,#67e8f9);-webkit-background-clip:text;-webkit-text-fill-color:transparent;}
    .dash-subtitle{color:#64748b;font-size:14px;margin:0;}
    .dash-status{
      display:inline-flex;align-items:center;gap:7px;
      background:rgba(16,185,129,.1);border:1px solid rgba(16,185,129,.22);
      color:#34d399;font-size:12px;font-weight:600;padding:5px 13px;border-radius:999px;margin-top:18px;
    }
    .dash-status .dot{
      width:6px;height:6px;border-radius:50%;
      background:#10b981;box-shadow:0 0 6px #10b981;animation:stBlink 2s ease infinite;
    }
    @keyframes stBlink{0%,100%{opacity:1;}50%{opacity:.35;}}

    /* ── section label ────────────────────────── */
    .sec-label{
      font-size:10.5px;font-weight:700;letter-spacing:2.5px;text-transform:uppercase;
      color:#475569;margin:0 0 18px;display:flex;align-items:center;gap:10px;
    }
    .sec-label::after{content:'';flex:1;height:1px;background:rgba(255,255,255,.05);}

    /* ── action cards ────────────────────────── */
    .ac{
      display:flex;flex-direction:column;background:#16182a;
      border:1px solid rgba(255,255,255,.08);border-radius:18px;
      padding:28px 24px;height:100%;text-decoration:none;
      transition:all .35s cubic-bezier(.4,0,.2,1);position:relative;overflow:hidden;
    }
    .ac::before{
      content:'';position:absolute;top:0;left:0;right:0;height:2px;
      border-radius:18px 18px 0 0;transition:opacity .3s;opacity:.65;
    }
    .ac:hover{transform:translateY(-6px);border-color:rgba(255,255,255,.14);box-shadow:0 24px 64px rgba(0,0,0,.45);}
    .ac:hover::before{opacity:1;}
    .ac-icon{
      width:52px;height:52px;border-radius:14px;display:flex;align-items:center;
      justify-content:center;color:#fff;margin-bottom:20px;flex-shrink:0;
    }
    .ac-tag{font-size:10.5px;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;margin-bottom:5px;}
    .ac-name{font-size:18px;font-weight:800;color:#f1f5f9;margin-bottom:8px;letter-spacing:-.3px;}
    .ac-desc{font-size:13px;color:#64748b;line-height:1.65;flex:1;}
    .ac-cta{display:inline-flex;align-items:center;gap:7px;font-size:12.5px;font-weight:700;margin-top:20px;letter-spacing:.3px;transition:gap .25s;}
    .ac:hover .ac-cta{gap:11px;}

    .ac-v::before{background:linear-gradient(90deg,#7c3aed,#9333ea);}
    .ac-v .ac-tag,.ac-v .ac-cta{color:#a78bfa;}
    .ac-v .ac-icon{background:linear-gradient(135deg,#7c3aed,#9333ea);box-shadow:0 8px 24px rgba(124,58,237,.4);}
    .ac-v:hover{background:#191430;}
    .ac-g::before{background:linear-gradient(90deg,#10b981,#059669);}
    .ac-g .ac-tag,.ac-g .ac-cta{color:#34d399;}
    .ac-g .ac-icon{background:linear-gradient(135deg,#10b981,#059669);box-shadow:0 8px 24px rgba(16,185,129,.4);}
    .ac-g:hover{background:#0f1e1a;}
    .ac-c::before{background:linear-gradient(90deg,#06b6d4,#0891b2);}
    .ac-c .ac-tag,.ac-c .ac-cta{color:#67e8f9;}
    .ac-c .ac-icon{background:linear-gradient(135deg,#06b6d4,#0891b2);box-shadow:0 8px 24px rgba(6,182,212,.4);}
    .ac-c:hover{background:#0f1e22;}
    .ac-a::before{background:linear-gradient(90deg,#f59e0b,#d97706);}
    .ac-a .ac-tag,.ac-a .ac-cta{color:#fcd34d;}
    .ac-a .ac-icon{background:linear-gradient(135deg,#f59e0b,#d97706);box-shadow:0 8px 24px rgba(245,158,11,.4);}
    .ac-a:hover{background:#1c1800;}
    .ac-r::before{background:linear-gradient(90deg,#f43f5e,#e11d48);}
    .ac-r .ac-tag,.ac-r .ac-cta{color:#fb7185;}
    .ac-r .ac-icon{background:linear-gradient(135deg,#f43f5e,#e11d48);box-shadow:0 8px 24px rgba(244,63,94,.35);}
    .ac-r:hover{background:#1c0d12;}

    .card-anim{opacity:0;transform:translateY(16px);animation:cIn .5s cubic-bezier(.4,0,.2,1) forwards;}
    @keyframes cIn{to{opacity:1;transform:translateY(0);}}
    </style>

    <div class="dash-page">
      <!-- Hero -->
      <div class="dash-hero card-anim" style="animation-delay:0s">
        <div class="dash-hero-inner">
          <div class="dash-eyebrow">Admin Dashboard</div>
          <h1 class="dash-title">Welcome back, <span><?= htmlspecialchars($_SESSION['user']['name']) ?></span></h1>
          <p class="dash-subtitle">Full control over your fleet, reservations and customers.</p>
          <div class="dash-status"><span class="dot"></span>All systems operational</div>
        </div>
      </div>

      <!-- Core Management -->
      <div class="sec-label card-anim" style="animation-delay:.06s">Core Management</div>
      <div class="row g-3 mb-4">
        <div class="col-lg-4 col-md-6 card-anim" style="animation-delay:.1s">
          <a href="/driveway_manager/public/vehicles/manage.php" class="ac ac-v">
            <div class="ac-icon"><?= svgi('car') ?></div>
            <div class="ac-tag">Fleet</div>
            <div class="ac-name">Vehicles</div>
            <div class="ac-desc">Add, edit or remove vehicles. Set pricing, availability and specs.</div>
            <div class="ac-cta">Manage Fleet <?= svgi('arrow') ?></div>
          </a>
        </div>
        <div class="col-lg-4 col-md-6 card-anim" style="animation-delay:.15s">
          <a href="/driveway_manager/public/admin/bookings.php" class="ac ac-g">
            <div class="ac-icon"><?= svgi('booking') ?></div>
            <div class="ac-tag">Reservations</div>
            <div class="ac-name">Bookings</div>
            <div class="ac-desc">View and manage all customer reservations, approve or cancel pending ones.</div>
            <div class="ac-cta">View All <?= svgi('arrow') ?></div>
          </a>
        </div>
        <div class="col-lg-4 col-md-6 card-anim" style="animation-delay:.2s">
          <a href="/driveway_manager/public/admin/manage_customers.php" class="ac ac-c">
            <div class="ac-icon"><?= svgi('users') ?></div>
            <div class="ac-tag">Accounts</div>
            <div class="ac-name">Customers</div>
            <div class="ac-desc">Browse registered users, view profiles and manage account access.</div>
            <div class="ac-cta">Manage Users <?= svgi('arrow') ?></div>
          </a>
        </div>
      </div>

      <!-- Maintenance & Reports -->
      <div class="sec-label card-anim" style="animation-delay:.24s">Maintenance &amp; Reporting</div>
      <div class="row g-3 mb-2">
        <div class="col-lg-4 col-md-6 card-anim" style="animation-delay:.28s">
          <a href="/driveway_manager/public/admin/maintenance_reminders.php" class="ac ac-a">
            <div class="ac-icon"><?= svgi('tools') ?></div>
            <div class="ac-tag">Service</div>
            <div class="ac-name">Maintenance</div>
            <div class="ac-desc">Track scheduled service, reminders and vehicle health status.</div>
            <div class="ac-cta">Open <?= svgi('arrow') ?></div>
          </a>
        </div>
        <div class="col-lg-4 col-md-6 card-anim" style="animation-delay:.32s">
          <a href="/driveway_manager/public/admin/vehicle_history.php" class="ac ac-v">
            <div class="ac-icon"><?= svgi('history') ?></div>
            <div class="ac-tag">Logs</div>
            <div class="ac-name">Vehicle History</div>
            <div class="ac-desc">Full rental and service history for every vehicle in the fleet.</div>
            <div class="ac-cta">View Logs <?= svgi('arrow') ?></div>
          </a>
        </div>
        <div class="col-lg-4 col-md-6 card-anim" style="animation-delay:.36s">
          <a href="/driveway_manager/public/admin/vehicle_report.php" class="ac ac-r">
            <div class="ac-icon"><?= svgi('chart') ?></div>
            <div class="ac-tag">Analytics</div>
            <div class="ac-name">Reports</div>
            <div class="ac-desc">Revenue breakdowns, utilisation rates and high-level insights.</div>
            <div class="ac-cta">View Reports <?= svgi('arrow') ?></div>
          </a>
        </div>
      </div>
    </div>

    <?php

/* ═══════════════════════════════════════════════
   CUSTOMER DASHBOARD
═══════════════════════════════════════════════ */
} else {
    require_once __DIR__ . "/../includes/customer_header.php";
    echo '</div><!-- /container (dashboard breaks out) -->';
    ?>

    <style>
    .svg-icon{display:inline-flex;align-items:center;justify-content:center;line-height:1;}
    .svg-icon svg{width:1em;height:1em;fill:currentColor;display:block;}
    .qa-ico .svg-icon svg{width:24px;height:24px;}
    .qa-arr .svg-icon svg{width:14px;height:14px;}
    .fi .svg-icon svg{width:22px;height:22px;}
    .btn-wp .svg-icon svg,.btn-gh .svg-icon svg{width:16px;height:16px;}
    .ch-eyebrow .svg-icon svg{width:12px;height:12px;}

    /* ── hero full-width ─────────────────────── */
    .ch-hero{
      width:100%;min-height:580px;display:flex;align-items:center;
      position:relative;overflow:hidden;box-shadow:0 30px 90px rgba(0,0,0,.55);
    }
    .ch-hero-bg{
      position:absolute;inset:0;
      background:url("/driveway_manager/public/assets/car-keys.jpg") center/cover no-repeat;z-index:0;
    }
    .ch-hero-overlay{
      position:absolute;inset:0;
      background:linear-gradient(120deg,rgba(15,10,40,.80) 0%,rgba(80,20,160,.55) 50%,rgba(6,60,80,.45) 100%);z-index:1;
    }
    .ch-hero-glow{
      position:absolute;inset:0;z-index:2;pointer-events:none;
      background:radial-gradient(ellipse at 10% 60%,rgba(124,58,237,.45) 0%,transparent 45%),
                 radial-gradient(ellipse at 90% 40%,rgba(6,182,212,.25) 0%,transparent 45%);
    }
    .ch-hero::after{
      content:'';position:absolute;right:-80px;top:50%;
      transform:translateY(-50%);width:500px;height:500px;
      border-radius:50%;border:1px solid rgba(255,255,255,.05);z-index:2;pointer-events:none;
    }
    .ch-hero-inner{position:relative;z-index:3;padding:72px 56px;max-width:740px;}
    .ch-eyebrow{
      font-size:11px;font-weight:700;letter-spacing:2.5px;text-transform:uppercase;
      color:rgba(255,255,255,.6);margin-bottom:14px;display:flex;align-items:center;gap:10px;
    }
    .ch-eyebrow span{display:inline-block;width:28px;height:1px;background:rgba(255,255,255,.4);}
    .ch-hero h1{
      font-size:clamp(30px,5vw,54px);font-weight:900;color:#fff;letter-spacing:-1.5px;
      line-height:1.08;margin-bottom:14px;text-shadow:0 4px 30px rgba(0,0,0,.3);
      animation:fuUp .8s ease both;
    }
    .ch-hero p{
      font-size:15px;color:rgba(255,255,255,.75);max-width:460px;line-height:1.7;
      margin-bottom:32px;animation:fuUp 1s ease both;
    }
    .ch-btns{display:flex;flex-wrap:wrap;gap:12px;animation:fuUp 1.2s ease both;}
    @keyframes fuUp{from{opacity:0;transform:translateY(22px);}to{opacity:1;transform:translateY(0);}}

    .btn-wp{
      display:inline-flex;align-items:center;gap:9px;background:#fff;color:#7c3aed;
      font-weight:700;font-size:14px;padding:13px 28px;border-radius:14px;
      text-decoration:none;box-shadow:0 8px 32px rgba(0,0,0,.3);transition:all .3s;
    }
    .btn-wp:hover{transform:translateY(-3px);box-shadow:0 16px 48px rgba(0,0,0,.4);color:#5b21b6;}
    .btn-gh{
      display:inline-flex;align-items:center;gap:9px;
      background:rgba(255,255,255,.12);border:1px solid rgba(255,255,255,.25);
      color:#fff;font-weight:600;font-size:14px;padding:13px 28px;border-radius:14px;
      text-decoration:none;backdrop-filter:blur(8px);transition:all .3s;
    }
    .btn-gh:hover{background:rgba(255,255,255,.2);transform:translateY(-3px);color:#fff;}

    /* ── body ────────────────────────────────── */
    .ch-body{max-width:1200px;margin:0 auto;padding:52px 24px 0;}

    /* ── feature strip ───────────────────────── */
    .feat-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:56px;}
    @media(max-width:768px){.feat-grid{grid-template-columns:repeat(2,1fr);}}
    .feat-cell{
      background:#16182a;border:1px solid rgba(255,255,255,.08);border-radius:16px;
      padding:26px 18px;text-align:center;transition:all .3s cubic-bezier(.4,0,.2,1);
      opacity:0;transform:translateY(14px);animation:cA .55s ease forwards;
    }
    @keyframes cA{to{opacity:1;transform:translateY(0);}}
    .feat-cell:hover{transform:translateY(-6px);border-color:rgba(124,58,237,.3);background:#191430;box-shadow:0 20px 50px rgba(0,0,0,.35);}
    .feat-cell .fi{
      width:48px;height:48px;border-radius:13px;display:flex;align-items:center;
      justify-content:center;margin:0 auto 14px;
    }
    .feat-cell h6{font-size:14px;font-weight:700;color:#f1f5f9;margin-bottom:5px;}
    .feat-cell p{font-size:12.5px;color:#64748b;line-height:1.55;margin:0;}

    /* ── quick actions ───────────────────────── */
    .qa-title{font-size:22px;font-weight:800;color:#f1f5f9;margin-bottom:5px;letter-spacing:-.4px;}
    .qa-sub{font-size:13.5px;color:#64748b;margin-bottom:26px;}
    .qa-card{
      background:#16182a;border:1px solid rgba(255,255,255,.08);border-radius:18px;
      padding:22px 24px;display:flex;align-items:center;gap:18px;text-decoration:none;
      transition:all .3s cubic-bezier(.4,0,.2,1);
      opacity:0;transform:translateX(-10px);animation:sIn .5s ease forwards;
    }
    @keyframes sIn{to{opacity:1;transform:translateX(0);}}
    .qa-card:hover{transform:translateY(-4px);border-color:rgba(255,255,255,.13);box-shadow:0 20px 50px rgba(0,0,0,.35);}
    .qa-ico{
      width:54px;height:54px;border-radius:15px;display:flex;align-items:center;
      justify-content:center;color:#fff;flex-shrink:0;
    }
    .qa-card .qt{font-size:15px;font-weight:700;color:#f1f5f9;margin-bottom:3px;}
    .qa-card .qs{font-size:12.5px;color:#64748b;}
    .qa-arr{
      margin-left:auto;width:36px;height:36px;border-radius:10px;
      background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.08);
      display:flex;align-items:center;justify-content:center;
      color:#64748b;flex-shrink:0;transition:all .25s;
    }
    .qa-card:hover .qa-arr{background:rgba(124,58,237,.2);border-color:rgba(124,58,237,.3);color:#a78bfa;transform:translateX(3px);}
    </style>

    <!-- Hero (full width) -->
    <div class="ch-hero">
      <div class="ch-hero-bg"></div>
      <div class="ch-hero-overlay"></div>
      <div class="ch-hero-glow"></div>
      <div class="ch-hero-inner">
        <div class="ch-eyebrow"><span></span> Premium Car Rental</div>
        <h1>Drive the car<br>you deserve.</h1>
        <p>Browse our curated fleet of premium vehicles and book your perfect ride in minutes.</p>
        <div class="ch-btns">
          <a href="/driveway_manager/public/booking/vehicles.php" class="btn-wp">
            <?= svgi('search') ?> Browse Vehicles
          </a>
          <a href="/driveway_manager/public/booking/my_bookings.php" class="btn-gh">
            <?= svgi('booking') ?> My Bookings
          </a>
        </div>
      </div>
    </div>

    <!-- Body -->
    <div class="ch-body">

      <!-- Feature Strip -->
      <div class="feat-grid">
        <div class="feat-cell" style="animation-delay:.05s">
          <div class="fi" style="background:rgba(124,58,237,.15);color:#a78bfa;"><?= svgi('shield') ?></div>
          <h6>Safe &amp; Insured</h6>
          <p>All vehicles fully insured and regularly serviced</p>
        </div>
        <div class="feat-cell" style="animation-delay:.12s">
          <div class="fi" style="background:rgba(16,185,129,.12);color:#34d399;"><?= svgi('tag') ?></div>
          <h6>Best Prices</h6>
          <p>Competitive daily rates, zero hidden charges</p>
        </div>
        <div class="feat-cell" style="animation-delay:.19s">
          <div class="fi" style="background:rgba(6,182,212,.12);color:#67e8f9;"><?= svgi('headset') ?></div>
          <h6>24/7 Support</h6>
          <p>Our team is always here whenever you need us</p>
        </div>
        <div class="feat-cell" style="animation-delay:.26s">
          <div class="fi" style="background:rgba(245,158,11,.12);color:#fcd34d;"><?= svgi('location') ?></div>
          <h6>Easy Pickup</h6>
          <p>Convenient locations across the city</p>
        </div>
      </div>

      <!-- Quick Actions -->
      <div id="about" class="mb-5">
        <div class="qa-title">Quick Actions</div>
        <div class="qa-sub">Everything you need, right here.</div>
        <div class="row g-3">
          <div class="col-md-6">
            <a href="/driveway_manager/public/booking/vehicles.php" class="qa-card" style="animation-delay:.06s">
              <div class="qa-ico" style="background:linear-gradient(135deg,#7c3aed,#9333ea);box-shadow:0 8px 24px rgba(124,58,237,.35);">
                <?= svgi('car') ?>
              </div>
              <div>
                <div class="qt">Browse Vehicles</div>
                <div class="qs">Find your perfect rental car</div>
              </div>
              <div class="qa-arr"><?= svgi('arrow') ?></div>
            </a>
          </div>
          <div class="col-md-6">
            <a href="/driveway_manager/public/booking/my_bookings.php" class="qa-card" style="animation-delay:.14s">
              <div class="qa-ico" style="background:linear-gradient(135deg,#10b981,#059669);box-shadow:0 8px 24px rgba(16,185,129,.35);">
                <?= svgi('booking') ?>
              </div>
              <div>
                <div class="qt">My Bookings</div>
                <div class="qs">View and manage your reservations</div>
              </div>
              <div class="qa-arr"><?= svgi('arrow') ?></div>
            </a>
          </div>
          <div class="col-md-6">
            <a href="/driveway_manager/public/customer/settings.php" class="qa-card" style="animation-delay:.22s">
              <div class="qa-ico" style="background:linear-gradient(135deg,#06b6d4,#0891b2);box-shadow:0 8px 24px rgba(6,182,212,.35);">
                <?= svgi('settings') ?>
              </div>
              <div>
                <div class="qt">Account Settings</div>
                <div class="qs">Update your profile and preferences</div>
              </div>
              <div class="qa-arr"><?= svgi('arrow') ?></div>
            </a>
          </div>
          <div class="col-md-6">
            <a href="/driveway_manager/public/logout.php" class="qa-card" style="animation-delay:.3s">
              <div class="qa-ico" style="background:linear-gradient(135deg,#f43f5e,#e11d48);box-shadow:0 8px 24px rgba(244,63,94,.3);">
                <?= svgi('logout') ?>
              </div>
              <div>
                <div class="qt">Sign Out</div>
                <div class="qs">Securely log out of your account</div>
              </div>
              <div class="qa-arr"><?= svgi('arrow') ?></div>
            </a>
          </div>
        </div>
      </div>

    </div><!-- /ch-body -->

    <!-- Re-open for footer's </div> -->
    <div>

    <?php
}

require_once __DIR__ . "/../includes/footer.php";
?>
