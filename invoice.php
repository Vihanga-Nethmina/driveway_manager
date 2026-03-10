<?php
require_once "../../config/db.php";
require_once "../../config/auth.php";
require_login();

$booking_id = (int)($_GET['booking_id'] ?? 0);
$user_id = (int)$_SESSION['user']['id'];

// Get booking details
$stmt = $pdo->prepare("
    SELECT b.*, v.brand, v.model, v.year, u.name as customer_name, u.email,
           p.payment_date, p.card_number, p.card_holder
    FROM bookings b
    JOIN vehicles v ON b.vehicle_id = v.id
    JOIN users u ON b.user_id = u.id
    LEFT JOIN payments p ON b.id = p.booking_id
    WHERE b.id=?
");
$stmt->execute([$booking_id]);
$booking = $stmt->fetch();

if (!$booking) {
    die("Booking not found");
}

// Check if user is admin or owner of booking
if ($_SESSION['user']['role'] !== 'admin' && $booking['user_id'] != $user_id) {
    die("Access denied");
}

$days = (strtotime($booking['return_date']) - strtotime($booking['pickup_date'])) / (60*60*24);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Invoice - Booking #<?= $booking_id ?></title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; }
        .invoice-header { text-align: center; margin-bottom: 30px; border-bottom: 3px solid #667eea; padding-bottom: 20px; }
        .invoice-header h1 { color: #667eea; margin: 0; }
        .invoice-info { display: flex; justify-content: space-between; margin: 30px 0; }
        .info-box { width: 45%; }
        .info-box h3 { color: #667eea; margin-bottom: 10px; }
        .info-box p { margin: 5px 0; }
        table { width: 100%; border-collapse: collapse; margin: 30px 0; }
        th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
        th { background: #667eea; color: white; }
        .total-row { background: #f8f9fa; font-weight: bold; font-size: 18px; }
        .footer { margin-top: 50px; text-align: center; color: #666; border-top: 2px solid #ddd; padding-top: 20px; }
        .paid-stamp { color: green; font-size: 24px; font-weight: bold; text-align: center; margin: 20px 0; }
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

    <div class="invoice-header">
        <h1>INVOICE</h1>
        <p>Driveway Manager - Vehicle Rental Service</p>
        <p>Invoice #<?= str_pad($booking_id, 6, '0', STR_PAD_LEFT) ?></p>
        <p>Date: <?= date('Y-m-d') ?></p>
    </div>

    <?php if ($booking['payment_date']): ?>
        <div class="paid-stamp">✓ PAID</div>
    <?php endif; ?>

    <div class="invoice-info">
        <div class="info-box">
            <h3>Customer Information</h3>
            <p><strong>Name:</strong> <?= htmlspecialchars($booking['customer_name']) ?></p>
            <p><strong>Email:</strong> <?= htmlspecialchars($booking['email']) ?></p>
            <p><strong>Booking Date:</strong> <?= htmlspecialchars($booking['created_at']) ?></p>
        </div>
        <div class="info-box">
            <h3>Vehicle Information</h3>
            <p><strong>Vehicle:</strong> <?= htmlspecialchars($booking['brand']) ?> <?= htmlspecialchars($booking['model']) ?></p>
            <p><strong>Year:</strong> <?= htmlspecialchars($booking['year']) ?></p>
            <p><strong>Status:</strong> <?= htmlspecialchars($booking['status']) ?></p>
        </div>
    </div>

    <table>
        <tr>
            <th>Description</th>
            <th>Pickup Date</th>
            <th>Return Date</th>
            <th>Days</th>
            <th>Rate/Day</th>
            <th>Amount</th>
        </tr>
        <tr>
            <td>Vehicle Rental</td>
            <td><?= htmlspecialchars($booking['pickup_date']) ?></td>
            <td><?= htmlspecialchars($booking['return_date']) ?></td>
            <td><?= $days ?></td>
            <td>$<?= number_format($booking['total_cost'] / $days, 2) ?></td>
            <td>$<?= number_format($booking['total_cost'], 2) ?></td>
        </tr>
        <tr class="total-row">
            <td colspan="5" style="text-align: right;">TOTAL AMOUNT:</td>
            <td>$<?= number_format($booking['total_cost'], 2) ?></td>
        </tr>
    </table>

    <?php if ($booking['payment_date']): ?>
    <div style="background: #d4edda; padding: 15px; border-radius: 5px; margin: 20px 0;">
        <h3 style="color: #155724; margin: 0 0 10px 0;">Payment Information</h3>
        <p><strong>Payment Date:</strong> <?= htmlspecialchars($booking['payment_date']) ?></p>
        <p><strong>Card Holder:</strong> <?= htmlspecialchars($booking['card_holder']) ?></p>
        <p><strong>Card Number:</strong> **** **** **** <?= htmlspecialchars($booking['card_number']) ?></p>
        <p><strong>Status:</strong> <span style="color: green;">PAID</span></p>
    </div>
    <?php else: ?>
    <div style="background: #fff3cd; padding: 15px; border-radius: 5px; margin: 20px 0;">
        <p style="color: #856404; margin: 0;"><strong>Payment Status:</strong> UNPAID</p>
    </div>
    <?php endif; ?>

    <div class="footer">
        <p><strong>Thank you for choosing Driveway Manager!</strong></p>
        <p>For any queries, please contact our support team.</p>
        <p>This is a computer-generated invoice.</p>
    </div>
</body>
</html>
