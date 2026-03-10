<?php
require_once "../../config/db.php";
require_once "../../config/auth.php";
require_role('admin');
require_once "../../includes/admin_header.php";

$vehicle_id = (int)($_GET['vehicle_id'] ?? 0);

$vehicle_stmt = $pdo->prepare("SELECT * FROM vehicles WHERE id=?");
$vehicle_stmt->execute([$vehicle_id]);
$vehicle = $vehicle_stmt->fetch();

if (!$vehicle) {
    echo "<div class='alert alert-danger'>Vehicle not found</div>";
    require_once "../../includes/footer.php";
    exit;
}

// Get all bookings
$bookings = $pdo->prepare("
    SELECT b.*, u.name as customer_name, u.email,
           (SELECT COUNT(*) FROM payments WHERE booking_id = b.id) as is_paid
    FROM bookings b
    JOIN users u ON b.user_id = u.id
    WHERE b.vehicle_id=?
    ORDER BY b.pickup_date DESC
");
$bookings->execute([$vehicle_id]);
$booking_records = $bookings->fetchAll();

// Get all maintenance
$maintenance = $pdo->prepare("SELECT * FROM maintenance WHERE vehicle_id=? ORDER BY service_date DESC");
$maintenance->execute([$vehicle_id]);
$maintenance_records = $maintenance->fetchAll();

// Calculate stats
$total_bookings = count($booking_records);
$total_revenue = 0;
$total_maintenance_cost = 0;

foreach($booking_records as $b) {
    if ($b['is_paid'] > 0) {
        $total_revenue += $b['total_cost'];
    }
}

foreach($maintenance_records as $m) {
    $total_maintenance_cost += $m['cost'];
}
?>

<div class="d-flex justify-content-between mb-3">
    <h4><i class="bi bi-clock-history"></i> Vehicle History - <?= htmlspecialchars($vehicle['brand']) ?> <?= htmlspecialchars($vehicle['model']) ?></h4>
    <a href="../vehicles/manage.php" class="btn btn-secondary">Back to Vehicles</a>
</div>

<div class="row mb-4">
    <div class="col-md-3">
        <div class="card shadow-sm text-center">
            <div class="card-body">
                <i class="bi bi-calendar-check" style="font-size: 2rem; color: #0d6efd;"></i>
                <h3><?= $total_bookings ?></h3>
                <p class="text-muted mb-0">Total Bookings</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm text-center">
            <div class="card-body">
                <i class="bi bi-cash-coin" style="font-size: 2rem; color: #198754;"></i>
                <h3>$<?= number_format($total_revenue, 2) ?></h3>
                <p class="text-muted mb-0">Total Revenue</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm text-center">
            <div class="card-body">
                <i class="bi bi-tools" style="font-size: 2rem; color: #ffc107;"></i>
                <h3><?= count($maintenance_records) ?></h3>
                <p class="text-muted mb-0">Maintenance Records</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm text-center">
            <div class="card-body">
                <i class="bi bi-wallet2" style="font-size: 2rem; color: #dc3545;"></i>
                <h3>$<?= number_format($total_maintenance_cost, 2) ?></h3>
                <p class="text-muted mb-0">Maintenance Cost</p>
            </div>
        </div>
    </div>
</div>

<ul class="nav nav-tabs mb-3" id="historyTab" role="tablist">
  <li class="nav-item" role="presentation">
    <button class="nav-link active" id="bookings-tab" data-bs-toggle="tab" data-bs-target="#bookings" type="button">
        <i class="bi bi-calendar-check"></i> Booking History
    </button>
  </li>
  <li class="nav-item" role="presentation">
    <button class="nav-link" id="maintenance-tab" data-bs-toggle="tab" data-bs-target="#maintenance" type="button">
        <i class="bi bi-tools"></i> Maintenance History
    </button>
  </li>
</ul>

<div class="tab-content" id="historyTabContent">
  <div class="tab-pane fade show active" id="bookings" role="tabpanel">
    <div class="card shadow-sm">
        <div class="card-body">
            <table class="table table-bordered">
                <thead>
                <tr>
                    <th>Customer</th>
                    <th>Pickup Date</th>
                    <th>Return Date</th>
                    <th>Total Cost</th>
                    <th>Status</th>
                    <th>Payment</th>
                </tr>
                </thead>
                <tbody>
                <?php if (count($booking_records) === 0): ?>
                    <tr><td colspan="6" class="text-center">No booking history.</td></tr>
                <?php endif; ?>
                <?php foreach($booking_records as $b): ?>
                <tr>
                    <td><?= htmlspecialchars($b['customer_name']) ?><br><small><?= htmlspecialchars($b['email']) ?></small></td>
                    <td><?= htmlspecialchars($b['pickup_date']) ?></td>
                    <td><?= htmlspecialchars($b['return_date']) ?></td>
                    <td>$<?= htmlspecialchars($b['total_cost']) ?></td>
                    <td><span class="badge bg-<?= $b['status']==='confirmed'?'success':'secondary' ?>"><?= htmlspecialchars($b['status']) ?></span></td>
                    <td>
                        <?php if ($b['is_paid'] > 0): ?>
                            <span class="badge bg-success">Paid</span>
                        <?php else: ?>
                            <span class="badge bg-warning">Unpaid</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
  </div>
  
  <div class="tab-pane fade" id="maintenance" role="tabpanel">
    <div class="card shadow-sm">
        <div class="card-body">
            <table class="table table-bordered">
                <thead>
                <tr>
                    <th>Service Type</th>
                    <th>Description</th>
                    <th>Service Date</th>
                    <th>Cost</th>
                    <th>Next Service</th>
                </tr>
                </thead>
                <tbody>
                <?php if (count($maintenance_records) === 0): ?>
                    <tr><td colspan="5" class="text-center">No maintenance history.</td></tr>
                <?php endif; ?>
                <?php foreach($maintenance_records as $m): ?>
                <tr>
                    <td><?= htmlspecialchars($m['service_type']) ?></td>
                    <td><?= htmlspecialchars($m['description']) ?></td>
                    <td><?= htmlspecialchars($m['service_date']) ?></td>
                    <td>$<?= htmlspecialchars($m['cost']) ?></td>
                    <td><?= $m['next_service_date'] ? htmlspecialchars($m['next_service_date']) : '-' ?></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<?php require_once "../../includes/footer.php"; ?>
