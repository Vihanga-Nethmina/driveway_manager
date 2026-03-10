<?php
require_once "../../config/db.php";
require_once "../../config/auth.php";
require_role('admin');

$vehicle_id = (int)($_GET['vehicle_id'] ?? 0);

$vehicle_stmt = $pdo->prepare("SELECT * FROM vehicles WHERE id=?");
$vehicle_stmt->execute([$vehicle_id]);
$vehicle = $vehicle_stmt->fetch();

if (!$vehicle) {
    die("Vehicle not found");
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

$net_profit = $total_revenue - $total_maintenance_cost;
?>
<!DOCTYPE html>
<html>
<head>
    <title>Vehicle Report - <?= htmlspecialchars($vehicle['brand']) ?> <?= htmlspecialchars($vehicle['model']) ?></title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #333; padding-bottom: 20px; }
        .stats { display: flex; justify-content: space-around; margin: 30px 0; }
        .stat-box { text-align: center; padding: 20px; border: 1px solid #ddd; border-radius: 5px; }
        .stat-box h3 { margin: 0; color: #667eea; }
        .stat-box p { margin: 5px 0; font-size: 24px; font-weight: bold; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background: #667eea; color: white; }
        .section-title { margin-top: 40px; color: #667eea; border-bottom: 2px solid #667eea; padding-bottom: 5px; }
        @media print {
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="no-print" style="text-align: right; margin-bottom: 20px;">
        <button onclick="window.print()" style="padding: 10px 20px; background: #667eea; color: white; border: none; border-radius: 5px; cursor: pointer;">Download PDF</button>
        <button onclick="window.close()" style="padding: 10px 20px; background: #6c757d; color: white; border: none; border-radius: 5px; cursor: pointer;">Close</button>
    </div>

    <div class="header">
        <h1>Vehicle Report</h1>
        <h2><?= htmlspecialchars($vehicle['brand']) ?> <?= htmlspecialchars($vehicle['model']) ?> (<?= $vehicle['year'] ?>)</h2>
        <p>Report Generated: <?= date('Y-m-d H:i:s') ?></p>
    </div>

    <div class="stats">
        <div class="stat-box">
            <h3>Total Bookings</h3>
            <p><?= $total_bookings ?></p>
        </div>
        <div class="stat-box">
            <h3>Total Revenue</h3>
            <p>$<?= number_format($total_revenue, 2) ?></p>
        </div>
        <div class="stat-box">
            <h3>Maintenance Cost</h3>
            <p>$<?= number_format($total_maintenance_cost, 2) ?></p>
        </div>
        <div class="stat-box">
            <h3>Net Profit</h3>
            <p style="color: <?= $net_profit >= 0 ? 'green' : 'red' ?>">$<?= number_format($net_profit, 2) ?></p>
        </div>
    </div>

    <h3 class="section-title">Booking History</h3>
    <table>
        <tr>
            <th>Customer</th>
            <th>Pickup Date</th>
            <th>Return Date</th>
            <th>Total Cost</th>
            <th>Status</th>
            <th>Payment</th>
        </tr>
        <?php if (count($booking_records) === 0): ?>
            <tr><td colspan="6" style="text-align: center;">No booking history.</td></tr>
        <?php endif; ?>
        <?php foreach($booking_records as $b): ?>
        <tr>
            <td><?= htmlspecialchars($b['customer_name']) ?></td>
            <td><?= htmlspecialchars($b['pickup_date']) ?></td>
            <td><?= htmlspecialchars($b['return_date']) ?></td>
            <td>$<?= htmlspecialchars($b['total_cost']) ?></td>
            <td><?= htmlspecialchars($b['status']) ?></td>
            <td><?= $b['is_paid'] > 0 ? 'Paid' : 'Unpaid' ?></td>
        </tr>
        <?php endforeach; ?>
    </table>

    <h3 class="section-title">Maintenance History</h3>
    <table>
        <tr>
            <th>Service Type</th>
            <th>Description</th>
            <th>Service Date</th>
            <th>Cost</th>
            <th>Next Service</th>
        </tr>
        <?php if (count($maintenance_records) === 0): ?>
            <tr><td colspan="5" style="text-align: center;">No maintenance history.</td></tr>
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
    </table>

    <div style="margin-top: 50px; text-align: center; color: #666;">
        <p>Driveway Manager - Vehicle Rental Management System</p>
        <p>Generated by Admin Panel</p>
    </div>
</body>
</html>
