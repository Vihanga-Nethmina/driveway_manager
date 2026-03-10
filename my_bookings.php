<?php
require_once "../../config/db.php";
require_once "../../config/auth.php";
require_role('customer');
require_once "../../includes/customer_header.php";

$user_id = $_SESSION['user']['id'];

$stmt = $pdo->prepare("
    SELECT b.*, v.brand, v.model,
           (SELECT COUNT(*) FROM payments WHERE booking_id = b.id) as is_paid
    FROM bookings b
    JOIN vehicles v ON b.vehicle_id = v.id
    WHERE b.user_id = ?
    ORDER BY b.id DESC
");
$stmt->execute([$user_id]);
$bookings = $stmt->fetchAll();
?>

<!-- Icon imports -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<style>
/* ── Page Header ───────────────────────────────────── */
.bk-page-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 28px;
  flex-wrap: wrap;
  gap: 14px;
}
.bk-page-header h2 {
  font-size: 22px;
  font-weight: 900;
  color: #f1f5f9;
  margin: 0;
  display: flex;
  align-items: center;
  gap: 10px;
}
.bk-page-header h2 i {
  width: 40px; height: 40px;
  border-radius: 12px;
  background: rgba(124,58,237,0.2);
  display: inline-flex;
  align-items: center;
  justify-content: center;
  font-size: 18px;
  color: #a78bfa;
}
.bk-count {
  background: rgba(124,58,237,0.15);
  border: 1px solid rgba(124,58,237,0.3);
  color: #a78bfa;
  font-size: 12px;
  font-weight: 700;
  padding: 5px 14px;
  border-radius: 999px;
}

.btn-new-booking {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 10px 22px;
  background: linear-gradient(135deg, #7c3aed, #06b6d4);
  border: none;
  border-radius: 12px;
  color: #fff;
  font-weight: 700;
  font-size: 14px;
  text-decoration: none;
  transition: all 0.3s;
  box-shadow: 0 4px 16px rgba(124,58,237,0.35);
}
.btn-new-booking:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 28px rgba(124,58,237,0.55);
  color: #fff;
}

/* ── Empty State ───────────────────────────────────── */
.bk-empty {
  background: #16182a;
  border: 1px solid rgba(255,255,255,0.08);
  border-radius: 20px;
  padding: 64px 32px;
  text-align: center;
}
.bk-empty-icon {
  width: 72px; height: 72px;
  border-radius: 20px;
  background: rgba(124,58,237,0.12);
  display: flex; align-items: center; justify-content: center;
  font-size: 30px; color: #a78bfa;
  margin: 0 auto 20px;
}
.bk-empty h5 { font-size: 18px; font-weight: 800; color: #f1f5f9; margin-bottom: 8px; }
.bk-empty p  { font-size: 14px; color: #64748b; margin-bottom: 24px; }

/* ── Booking Cards ─────────────────────────────────── */
.bk-card {
  background: #16182a;
  border: 1px solid rgba(255,255,255,0.08);
  border-radius: 18px;
  overflow: hidden;
  margin-bottom: 16px;
  transition: all 0.3s cubic-bezier(0.4,0,0.2,1);
}
.bk-card:hover {
  border-color: rgba(124,58,237,0.3);
  box-shadow: 0 16px 40px rgba(0,0,0,0.35);
  transform: translateY(-2px);
}

/* card top stripe by status */
.bk-card.status-confirmed { border-top: 2px solid #10b981; }
.bk-card.status-pending   { border-top: 2px solid #f59e0b; }
.bk-card.status-cancelled { border-top: 2px solid #ef4444; }

.bk-card-body {
  padding: 22px 26px;
  display: flex;
  align-items: center;
  gap: 20px;
  flex-wrap: wrap;
}

/* vehicle icon */
.bk-car-icon {
  width: 54px; height: 54px;
  border-radius: 15px;
  background: rgba(124,58,237,0.12);
  border: 1px solid rgba(124,58,237,0.2);
  display: flex; align-items: center; justify-content: center;
  font-size: 24px; color: #a78bfa;
  flex-shrink: 0;
}

/* vehicle info */
.bk-info { flex: 1; min-width: 180px; }
.bk-vehicle-name {
  font-size: 16px; font-weight: 800; color: #f1f5f9;
  margin-bottom: 4px; letter-spacing: -0.2px;
}
.bk-id { font-size: 11px; color: #475569; font-weight: 500; }

/* dates */
.bk-dates {
  display: flex;
  gap: 18px;
  flex-wrap: wrap;
  min-width: 200px;
}
.bk-date-item { display: flex; flex-direction: column; gap: 3px; }
.bk-date-label {
  font-size: 10px; font-weight: 700;
  letter-spacing: 1px; text-transform: uppercase; color: #475569;
}
.bk-date-val { font-size: 13px; font-weight: 600; color: #94a3b8; }

/* cost */
.bk-cost {
  font-size: 20px; font-weight: 900; color: #f1f5f9;
  min-width: 90px; text-align: right;
}
.bk-cost span { font-size: 12px; color: #475569; font-weight: 500; display: block; }

/* badges */
.bk-status {
  font-size: 11px; font-weight: 700;
  padding: 5px 12px; border-radius: 999px;
  text-transform: capitalize; letter-spacing: 0.3px;
}
.bk-status.confirmed { background: rgba(16,185,129,0.15); color: #34d399; border: 1px solid rgba(16,185,129,0.25); }
.bk-status.pending   { background: rgba(245,158,11,0.15);  color: #fcd34d; border: 1px solid rgba(245,158,11,0.25); }
.bk-status.cancelled { background: rgba(239,68,68,0.15);   color: #f87171; border: 1px solid rgba(239,68,68,0.25); }

.bk-pay-badge {
  font-size: 11px; font-weight: 700;
  padding: 5px 12px; border-radius: 999px;
}
.bk-pay-badge.paid   { background: rgba(16,185,129,0.12); color: #34d399; border: 1px solid rgba(16,185,129,0.2); }
.bk-pay-badge.unpaid { background: rgba(245,158,11,0.12);  color: #fcd34d; border: 1px solid rgba(245,158,11,0.2); }

/* actions */
.bk-actions { display: flex; gap: 8px; flex-wrap: wrap; align-items: center; }

.btn-bk {
  display: inline-flex; align-items: center; gap: 6px;
  font-size: 12.5px; font-weight: 700;
  padding: 8px 16px; border-radius: 10px;
  text-decoration: none; border: none; cursor: pointer;
  transition: all 0.25s;
}
.btn-bk-pay {
  background: linear-gradient(135deg, #10b981, #059669);
  color: #fff;
  box-shadow: 0 4px 14px rgba(16,185,129,0.3);
}
.btn-bk-pay:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(16,185,129,0.45); color: #fff; }
.btn-bk-inv {
  background: rgba(6,182,212,0.12);
  border: 1px solid rgba(6,182,212,0.25);
  color: #67e8f9;
}
.btn-bk-inv:hover { background: rgba(6,182,212,0.2); color: #67e8f9; }
.btn-bk-cancel {
  background: rgba(239,68,68,0.1);
  border: 1px solid rgba(239,68,68,0.2);
  color: #f87171;
}
.btn-bk-cancel:hover { background: rgba(239,68,68,0.18); color: #f87171; }

/* divider */
.bk-divider { height: 1px; background: rgba(255,255,255,0.05); margin: 0 26px; }

/* status+badges row */
.bk-meta {
  display: flex; align-items: center;
  gap: 10px; flex-wrap: wrap;
}
</style>

<!-- Header -->
<div class="bk-page-header">
  <div class="d-flex align-items-center gap-3">
    <h2><i class="bi bi-calendar2-check-fill"></i> My Bookings</h2>
    <span class="bk-count"><?= count($bookings) ?> Total</span>
  </div>
  <a href="/driveway_manager/public/booking/vehicles.php" class="btn-new-booking">
    <i class="bi bi-plus-circle-fill"></i> New Booking
  </a>
</div>

<?php if (count($bookings) === 0): ?>
  <!-- Empty state -->
  <div class="bk-empty">
    <div class="bk-empty-icon"><i class="bi bi-calendar-x"></i></div>
    <h5>No bookings yet</h5>
    <p>You haven't made any reservations. Browse our fleet and book your first ride.</p>
    <a href="/driveway_manager/public/booking/vehicles.php" class="btn-new-booking">
      <i class="bi bi-car-front-fill"></i> Browse Vehicles
    </a>
  </div>
<?php else: ?>
  <!-- Booking Cards -->
  <?php foreach ($bookings as $b):
    $statusClass = in_array($b['status'], ['confirmed','pending','cancelled']) ? $b['status'] : 'pending';
  ?>
  <div class="bk-card status-<?= $statusClass ?>">
    <div class="bk-card-body">

      <!-- Car icon -->
      <div class="bk-car-icon"><i class="bi bi-car-front-fill"></i></div>

      <!-- Vehicle name + ID -->
      <div class="bk-info">
        <div class="bk-vehicle-name"><?= htmlspecialchars($b['brand']) ?> <?= htmlspecialchars($b['model']) ?></div>
        <div class="bk-id">Booking #<?= (int)$b['id'] ?></div>
      </div>

      <!-- Dates -->
      <div class="bk-dates">
        <div class="bk-date-item">
          <span class="bk-date-label"><i class="bi bi-box-arrow-in-right"></i> Pickup</span>
          <span class="bk-date-val"><?= htmlspecialchars($b['pickup_date']) ?></span>
        </div>
        <div class="bk-date-item">
          <span class="bk-date-label"><i class="bi bi-box-arrow-right"></i> Return</span>
          <span class="bk-date-val"><?= htmlspecialchars($b['return_date']) ?></span>
        </div>
      </div>

      <!-- Cost -->
      <div class="bk-cost">
        $<?= number_format($b['total_cost'], 2) ?>
        <span>Total Cost</span>
      </div>

      <!-- Status + payment badges -->
      <div class="bk-meta">
        <span class="bk-status <?= $statusClass ?>"><?= ucfirst(htmlspecialchars($b['status'])) ?></span>
        <span class="bk-pay-badge <?= $b['is_paid'] > 0 ? 'paid' : 'unpaid' ?>">
          <?= $b['is_paid'] > 0 ? '<i class="bi bi-check-circle-fill"></i> Paid' : '<i class="bi bi-clock"></i> Unpaid' ?>
        </span>
      </div>

      <!-- Action buttons -->
      <div class="bk-actions">
        <?php if ($b['status'] === 'confirmed' && $b['is_paid'] == 0): ?>
          <a class="btn-bk btn-bk-pay" href="payment.php?id=<?= (int)$b['id'] ?>">
            <i class="bi bi-credit-card-fill"></i> Pay Now
          </a>
        <?php endif; ?>
        <?php if ($b['is_paid'] > 0): ?>
          <a class="btn-bk btn-bk-inv" href="invoice.php?booking_id=<?= (int)$b['id'] ?>" target="_blank">
            <i class="bi bi-file-earmark-text-fill"></i> Invoice
          </a>
        <?php endif; ?>
        <?php if ($b['status'] === 'confirmed'): ?>
          <a class="btn-bk btn-bk-cancel"
             href="cancel.php?id=<?= (int)$b['id'] ?>"
             onclick="return confirm('Are you sure you want to cancel this booking?')">
            <i class="bi bi-x-circle-fill"></i> Cancel
          </a>
        <?php endif; ?>
      </div>

    </div>
  </div>
  <?php endforeach; ?>
<?php endif; ?>

<?php require_once "../../includes/footer.php"; ?>