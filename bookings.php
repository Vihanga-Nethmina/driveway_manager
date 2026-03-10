<?php
require_once "../../config/db.php";
require_once "../../config/auth.php";
require_role('admin');
require_once "../../includes/admin_header.php";

$stmt = $pdo->query("
  SELECT b.*, u.name AS customer_name, u.email, v.brand, v.model,
         (SELECT COUNT(*) FROM payments WHERE booking_id = b.id) as is_paid
  FROM bookings b
  JOIN users u ON b.user_id = u.id
  JOIN vehicles v ON b.vehicle_id = v.id
  ORDER BY b.id DESC
");
$bookings = $stmt->fetchAll();
?>

<h4>All Bookings (Admin)</h4>

<table class="table table-bordered bg-white">
  <tr>
    <th>ID</th>
    <th>Customer</th>
    <th>Vehicle</th>
    <th>Pickup</th>
    <th>Return</th>
    <th>Total</th>
    <th>Status</th>
    <th>Payment</th>
  </tr>

  <?php foreach($bookings as $b): ?>
  <tr>
    <td><?= (int)$b['id'] ?></td>
    <td><?= htmlspecialchars($b['customer_name']) ?> <br><small><?= htmlspecialchars($b['email']) ?></small></td>
    <td><?= htmlspecialchars($b['brand']) ?> <?= htmlspecialchars($b['model']) ?></td>
    <td><?= htmlspecialchars($b['pickup_date']) ?></td>
    <td><?= htmlspecialchars($b['return_date']) ?></td>
    <td>$<?= htmlspecialchars($b['total_cost']) ?></td>
    <td><?= htmlspecialchars($b['status']) ?></td>
    <td>
      <?php if ($b['is_paid'] > 0): ?>
        <span class="badge bg-success"><i class="bi bi-check-circle"></i> Paid</span>
      <?php else: ?>
        <span class="badge bg-warning"><i class="bi bi-exclamation-circle"></i> Unpaid</span>
      <?php endif; ?>
    </td>
  </tr>
  <?php endforeach; ?>
</table>

<?php require_once "../../includes/footer.php"; ?>
