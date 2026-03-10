<?php
require_once "../../config/db.php";
require_once "../../config/auth.php";
require_role('customer');

$booking_id = (int)($_GET['id'] ?? 0);
$user_id = (int)$_SESSION['user']['id'];

$stmt = $pdo->prepare("UPDATE bookings SET status='cancelled' WHERE id=? AND user_id=?");
$stmt->execute([$booking_id, $user_id]);

header("Location: my_bookings.php");
exit;
