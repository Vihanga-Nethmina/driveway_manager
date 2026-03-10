<?php
require_once "../../config/db.php";
require_once "../../config/auth.php";
require_role('customer');
require_once "../../includes/customer_header.php";

$pickup  = $_GET['pickup']  ?? "";
$dropoff = $_GET['return'] ?? "";

$vehicles = [];
if ($pickup && $dropoff) {
    $stmt = $pdo->prepare("
        SELECT * FROM vehicles v
        WHERE v.id NOT IN (
            SELECT b.vehicle_id FROM bookings b
            WHERE b.status='confirmed'
              AND b.pickup_date <= ?
              AND b.return_date >= ?
        )
        ORDER BY v.id DESC
    ");
    $stmt->execute([$dropoff, $pickup]);
    $vehicles = $stmt->fetchAll();
} else {
    $stmt = $pdo->query("SELECT * FROM vehicles ORDER BY id DESC");
    $vehicles = $stmt->fetchAll();
}
?>

<!-- Icon imports -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<style>
/* ── Search / Filter Box ─────────────────────────────── */
.search-box {
  background: #16182a;
  border: 1px solid rgba(255,255,255,0.08);
  border-radius: 20px;
  padding: 32px 36px;
  margin-bottom: 36px;
  position: relative;
  overflow: hidden;
}
.search-box::before {
  content: '';
  position: absolute; top: 0; left: 0; right: 0;
  height: 2px;
  background: linear-gradient(90deg, #7c3aed, #06b6d4);
  border-radius: 20px 20px 0 0;
}
.search-box h4 {
  font-size: 18px;
  font-weight: 800;
  color: #f1f5f9;
  margin-bottom: 22px;
  display: flex;
  align-items: center;
  gap: 10px;
}
.search-box h4 i {
  width: 36px; height: 36px;
  border-radius: 10px;
  background: rgba(124,58,237,0.2);
  display: inline-flex;
  align-items: center;
  justify-content: center;
  font-size: 16px;
  color: #a78bfa;
}

/* date inputs */
.search-box .form-label {
  font-size: 11px;
  font-weight: 700;
  letter-spacing: 1px;
  text-transform: uppercase;
  color: #64748b !important;
  margin-bottom: 8px;
  display: flex;
  align-items: center;
  gap: 6px;
}
.search-box .form-control {
  background: #1e1e35 !important;
  border: 1px solid rgba(255,255,255,0.1) !important;
  border-radius: 12px !important;
  color: #f1f5f9 !important;
  padding: 12px 16px !important;
  font-size: 14px;
  font-weight: 500;
  height: 48px;
}
.search-box .form-control:focus {
  border-color: #7c3aed !important;
  box-shadow: 0 0 0 3px rgba(124,58,237,0.2) !important;
  background: rgba(124,58,237,0.06) !important;
}
/* fix date input calendar icon colour */
.search-box .form-control::-webkit-calendar-picker-indicator {
  filter: invert(0.6);
  cursor: pointer;
}

/* search button */
.btn-search {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  height: 48px;
  background: linear-gradient(135deg, #7c3aed, #06b6d4);
  border: none;
  border-radius: 12px;
  color: #fff;
  font-weight: 700;
  font-size: 14px;
  padding: 0 24px;
  width: 100%;
  cursor: pointer;
  transition: all 0.3s;
  box-shadow: 0 6px 20px rgba(124,58,237,0.4);
}
.btn-search:hover {
  transform: translateY(-2px);
  box-shadow: 0 10px 30px rgba(124,58,237,0.55);
  color: #fff;
}

/* ── Section header ──────────────────────────────────── */
.fleet-header {
  display: flex;
  align-items: center;
  gap: 14px;
  margin-bottom: 28px;
}
.fleet-header h4 {
  font-size: 20px;
  font-weight: 800;
  color: #f1f5f9;
  margin: 0;
}
.fleet-count {
  background: rgba(124,58,237,0.18);
  border: 1px solid rgba(124,58,237,0.3);
  color: #a78bfa;
  font-size: 12px;
  font-weight: 700;
  padding: 4px 12px;
  border-radius: 999px;
}

/* ── Vehicle Cards ───────────────────────────────────── */
.vc {
  background: #16182a;
  border: 1px solid rgba(255,255,255,0.08);
  border-radius: 18px;
  overflow: hidden;
  height: 100%;
  display: flex;
  flex-direction: column;
  transition: all 0.35s cubic-bezier(0.4,0,0.2,1);
}
.vc:hover {
  transform: translateY(-8px);
  border-color: rgba(124,58,237,0.4);
  box-shadow: 0 24px 60px rgba(0,0,0,0.5);
}

/* image area */
.vc-img-wrap { position: relative; flex-shrink: 0; }
.vc-img {
  height: 210px;
  width: 100%;
  object-fit: cover;
  display: block;
}
.vc-placeholder {
  height: 210px;
  background: linear-gradient(135deg, #1e1e35 0%, #252542 100%);
  display: flex; align-items: center; justify-content: center;
  font-size: 56px; color: rgba(124,58,237,0.4);
}

/* price badge */
.vc-price {
  position: absolute;
  top: 14px; right: 14px;
  background: linear-gradient(135deg, #7c3aed, #06b6d4);
  color: #fff;
  font-size: 14px;
  font-weight: 800;
  padding: 6px 14px;
  border-radius: 999px;
  box-shadow: 0 4px 16px rgba(124,58,237,0.5);
  letter-spacing: -0.2px;
}

/* body */
.vc-body {
  padding: 20px;
  flex: 1;
  display: flex;
  flex-direction: column;
}
.vc-title {
  font-size: 17px;
  font-weight: 800;
  color: #f1f5f9;
  margin-bottom: 12px;
  letter-spacing: -0.3px;
}
.vc-specs {
  display: flex;
  gap: 16px;
  margin-bottom: 18px;
  flex-wrap: wrap;
}
.vc-spec {
  display: flex;
  align-items: center;
  gap: 5px;
  font-size: 12.5px;
  color: #64748b;
}
.vc-spec i { color: #7c3aed; font-size: 13px; }

/* buttons */
.btn-book {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 7px;
  width: 100%;
  padding: 11px;
  background: linear-gradient(135deg, #7c3aed, #06b6d4);
  border: none;
  border-radius: 12px;
  color: #fff;
  font-weight: 700;
  font-size: 14px;
  text-decoration: none;
  cursor: pointer;
  transition: all 0.3s;
  box-shadow: 0 4px 16px rgba(124,58,237,0.35);
  margin-top: auto;
}
.btn-book:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 28px rgba(124,58,237,0.55);
  color: #fff;
}
.btn-book-disabled {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 7px;
  width: 100%;
  padding: 11px;
  background: rgba(255,255,255,0.05);
  border: 1px solid rgba(255,255,255,0.08);
  border-radius: 12px;
  color: #64748b;
  font-size: 14px;
  font-weight: 600;
  cursor: not-allowed;
  margin-top: auto;
}
</style>

<!-- Search Box -->
<div class="search-box">
  <h4><i class="bi bi-search"></i> Find Your Perfect Vehicle</h4>
  <form method="get" class="row g-3">
    <div class="col-md-5">
      <label class="form-label"><i class="bi bi-calendar-event"></i> Pickup Date</label>
      <input type="date" name="pickup" value="<?= htmlspecialchars($pickup) ?>" class="form-control" required>
    </div>
    <div class="col-md-5">
      <label class="form-label"><i class="bi bi-calendar-check"></i> Return Date</label>
      <input type="date" name="return" value="<?= htmlspecialchars($dropoff) ?>" class="form-control" required>
    </div>
    <div class="col-md-2 d-flex align-items-end">
      <button type="submit" class="btn-search"><i class="bi bi-search"></i> Search</button>
    </div>
  </form>
</div>

<?php if (!$pickup || !$dropoff): ?>
  <div class="alert alert-info mb-4" style="border-radius:12px;">
    <i class="bi bi-info-circle-fill me-2"></i>
    Select pickup and return dates to see available vehicles.
  </div>
<?php endif; ?>

<!-- Fleet Header -->
<div class="fleet-header">
  <h4><?= ($pickup && $dropoff) ? 'Available Vehicles' : 'Our Fleet' ?></h4>
  <span class="fleet-count"><?= count($vehicles) ?> Vehicles</span>
</div>

<!-- Vehicle Grid -->
<div class="row g-4 mb-4">
  <?php if (count($vehicles) === 0): ?>
    <div class="col-12">
      <div class="alert alert-warning" style="border-radius:12px;">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>
        No vehicles available for these dates. Please try different dates.
      </div>
    </div>
  <?php endif; ?>

  <?php foreach ($vehicles as $v): ?>
  <div class="col-md-4">
    <div class="vc">
      <div class="vc-img-wrap">
        <?php if (!empty($v['image'])): ?>
          <img src="/driveway_manager/public/uploads/vehicles/<?= htmlspecialchars($v['image']) ?>" class="vc-img" alt="<?= htmlspecialchars($v['brand']) ?>">
        <?php else: ?>
          <div class="vc-placeholder"><i class="bi bi-car-front"></i></div>
        <?php endif; ?>
        <div class="vc-price">$<?= htmlspecialchars($v['price_per_day']) ?>/day</div>
      </div>

      <div class="vc-body">
        <div class="vc-title"><?= htmlspecialchars($v['brand']) ?> <?= htmlspecialchars($v['model']) ?></div>
        <div class="vc-specs">
          <div class="vc-spec"><i class="bi bi-calendar3"></i> <?= (int)$v['year'] ?></div>
          <div class="vc-spec"><i class="bi bi-gear-fill"></i> Automatic</div>
          <div class="vc-spec"><i class="bi bi-people-fill"></i> 5 Seats</div>
        </div>

        <?php if ($pickup && $dropoff): ?>
          <a class="btn-book"
             href="book.php?id=<?= (int)$v['id'] ?>&pickup=<?= urlencode($pickup) ?>&return=<?= urlencode($dropoff) ?>">
            <i class="bi bi-check-circle-fill"></i> Book Now
          </a>
        <?php else: ?>
          <div class="btn-book-disabled">
            <i class="bi bi-calendar-x"></i> Select Dates First
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<?php require_once "../../includes/footer.php"; ?>
