<?php
require_once "../../config/db.php";
require_once "../../config/auth.php";
require_role('customer');
require_once "../../includes/customer_header.php";

$booking_id = (int)($_GET['id'] ?? 0);
$user_id = (int)$_SESSION['user']['id'];

$stmt = $pdo->prepare("
    SELECT b.*, v.brand, v.model 
    FROM bookings b
    JOIN vehicles v ON b.vehicle_id = v.id
    WHERE b.id=? AND b.user_id=?
");
$stmt->execute([$booking_id, $user_id]);
$booking = $stmt->fetch();

if (!$booking) {
    echo "<div class='alert alert-danger'>Booking not found</div>";
    require_once "../../includes/footer.php";
    exit;
}

$msg = "";
$err = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $card_number = $_POST['card_number'] ?? "";
    $card_holder = $_POST['card_holder'] ?? "";
    $expiry = $_POST['expiry'] ?? "";
    $cvv = $_POST['cvv'] ?? "";
    
    if ($card_number && $card_holder && $expiry && $cvv) {
        // Insert payment
        $stmt = $pdo->prepare("INSERT INTO payments (booking_id, amount, card_number, card_holder) VALUES (?, ?, ?, ?)");
        $stmt->execute([$booking_id, $booking['total_cost'], substr($card_number, -4), $card_holder]);
        
        // Create notification for admin
        $admin_stmt = $pdo->query("SELECT id FROM users WHERE role='admin'");
        while ($admin = $admin_stmt->fetch()) {
            $notif_msg = "New payment received: " . $booking['brand'] . " " . $booking['model'] . " - $" . $booking['total_cost'];
            $notif = $pdo->prepare("INSERT INTO notifications (user_id, message, type) VALUES (?, ?, 'payment')");
            $notif->execute([$admin['id'], $notif_msg]);
        }
        
        $msg = "Payment successful! Thank you.";
    } else {
        $err = "All fields are required!";
    }
}
?>

<h4 class="mb-4"><i class="bi bi-credit-card"></i> Payment</h4>

<?php if ($msg): ?>
  <div class="alert alert-success"><?= htmlspecialchars($msg) ?></div>
  <a href="my_bookings.php" class="btn btn-primary">Back to My Bookings</a>
<?php elseif ($err): ?>
  <div class="alert alert-danger"><?= htmlspecialchars($err) ?></div>
<?php endif; ?>

<?php if (!$msg): ?>
<div class="row">
    <div class="col-md-6">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-info text-white">
                <i class="bi bi-info-circle"></i> Booking Details
            </div>
            <div class="card-body">
                <p><strong>Vehicle:</strong> <?= htmlspecialchars($booking['brand']) ?> <?= htmlspecialchars($booking['model']) ?></p>
                <p><strong>Pickup:</strong> <?= htmlspecialchars($booking['pickup_date']) ?></p>
                <p><strong>Return:</strong> <?= htmlspecialchars($booking['return_date']) ?></p>
                <h5 class="text-success"><strong>Total Amount: $<?= htmlspecialchars($booking['total_cost']) ?></strong></h5>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header bg-success text-white">
                <i class="bi bi-credit-card-2-front"></i> Payment Details
            </div>
            <div class="card-body">
                <form method="post">
                    <div class="mb-3">
                        <label class="form-label">Card Number</label>
                        <input type="text" name="card_number" class="form-control" placeholder="1234 5678 9012 3456" maxlength="19" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Card Holder Name</label>
                        <input type="text" name="card_holder" class="form-control" placeholder="John Doe" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Expiry Date</label>
                            <input type="text" name="expiry" class="form-control" placeholder="MM/YY" maxlength="5" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">CVV</label>
                            <input type="text" name="cvv" class="form-control" placeholder="123" maxlength="3" required>
                        </div>
                    </div>
                    <button class="btn btn-success w-100"><i class="bi bi-check-circle"></i> Pay $<?= htmlspecialchars($booking['total_cost']) ?></button>
                    <a href="my_bookings.php" class="btn btn-secondary w-100 mt-2">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php require_once "../../includes/footer.php"; ?>
