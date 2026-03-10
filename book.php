<?php
require_once "../../config/db.php";
require_once "../../config/auth.php";
require_role('customer');
require_once "../../includes/customer_header.php";

$id = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare("SELECT * FROM vehicles WHERE id=?");
$stmt->execute([$id]);
$vehicle = $stmt->fetch();

if (!$vehicle) {
    echo "<div class='alert alert-danger'>Vehicle not found</div>";
    require_once "../../includes/footer.php";
    exit;
}

$pickupPrefill = $_GET['pickup'] ?? "";
$returnPrefill = $_GET['return'] ?? "";

$msg = "";
$err = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pickup = $_POST['pickup'] ?? "";
    $return = $_POST['return'] ?? "";

    $days = (strtotime($return) - strtotime($pickup)) / (60*60*24);

    if ($days <= 0) {
        $err = "Invalid date range.";
    } else {
        // Check double booking
        $check = $pdo->prepare("
            SELECT id FROM bookings
            WHERE vehicle_id=?
              AND status='confirmed'
              AND pickup_date <= ?
              AND return_date >= ?
        ");
        $check->execute([$id, $return, $pickup]);

        if ($check->fetch()) {
            $err = "Vehicle already booked for these dates.";
        } else {
            $total = $days * (float)$vehicle['price_per_day'];
            $user_id = (int)$_SESSION['user']['id'];

            $ins = $pdo->prepare("
                INSERT INTO bookings (user_id, vehicle_id, pickup_date, return_date, total_cost)
                VALUES (?, ?, ?, ?, ?)
            ");
            $ins->execute([$user_id, $id, $pickup, $return, $total]);

            // Create notification for admin
            $admin_stmt = $pdo->query("SELECT id FROM users WHERE role='admin'");
            while ($admin = $admin_stmt->fetch()) {
                $notif_msg = "New booking: " . $vehicle['brand'] . " " . $vehicle['model'] . " from " . $pickup . " to " . $return;
                $notif = $pdo->prepare("INSERT INTO notifications (user_id, message, type) VALUES (?, ?, 'booking')");
                $notif->execute([$admin['id'], $notif_msg]);
            }

            $msg = "Booking successful! Total Cost: $" . number_format($total, 2);
        }
    }
}
?>

<style>
.book-container {
  background: #16182a;
  border: 1px solid rgba(255,255,255,0.08);
  border-radius: 18px;
  padding: 32px;
  max-width: 600px;
  margin: 0 auto;
}
.book-vehicle-info {
  background: rgba(124,58,237,0.1);
  border: 1px solid rgba(124,58,237,0.2);
  border-radius: 14px;
  padding: 20px;
  margin-bottom: 24px;
}
.book-vehicle-info h5 {
  font-size: 18px;
  font-weight: 800;
  color: #f1f5f9;
  margin-bottom: 8px;
}
.book-vehicle-info p {
  font-size: 14px;
  color: #94a3b8;
  margin: 0;
}
.book-price {
  font-size: 20px;
  font-weight: 900;
  color: #34d399;
}
</style>

<h4 style="margin-bottom: 24px;"><i class="bi bi-calendar-check"></i> Book Vehicle</h4>

<div class="book-container">
    <div class="book-vehicle-info">
        <h5><?= htmlspecialchars($vehicle['brand']) ?> <?= htmlspecialchars($vehicle['model']) ?></h5>
        <p class="book-price">$<?= htmlspecialchars($vehicle['price_per_day']) ?> <span style="font-size:14px;color:#64748b;font-weight:500;">/day</span></p>
    </div>

    <?php if ($err): ?>
      <div class="alert alert-danger"><?= htmlspecialchars($err) ?></div>
    <?php endif; ?>
    <?php if ($msg): ?>
      <div class="alert alert-success"><?= htmlspecialchars($msg) ?></div>
      <a class="btn btn-primary" href="my_bookings.php">Go to My Bookings</a>
    <?php endif; ?>

    <form method="post">
        <div class="mb-2">
            <label>Pickup Date</label>
            <input type="date" name="pickup" class="form-control" value="<?= htmlspecialchars($pickupPrefill) ?>" required>
        </div>

        <div class="mb-2">
            <label>Return Date</label>
            <input type="date" name="return" class="form-control" value="<?= htmlspecialchars($returnPrefill) ?>" required>
        </div>

        <button class="btn btn-success">Confirm Booking</button>
        <a href="vehicles.php" class="btn btn-secondary">Back</a>
    </form>
</div>

<?php require_once "../../includes/footer.php"; ?>
