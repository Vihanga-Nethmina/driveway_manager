<?php
require_once "../../config/db.php";
require_once "../../config/auth.php";
require_role('admin');
require_once "../../includes/admin_header.php";

$user_id = (int)$_SESSION['user']['id'];

// Mark all as read if requested
if (isset($_GET['mark_read'])) {
    $pdo->prepare("UPDATE notifications SET is_read=1 WHERE user_id=?")->execute([$user_id]);
    header("Location: notifications.php");
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM notifications WHERE user_id=? ORDER BY id DESC");
$stmt->execute([$user_id]);
$notifications = $stmt->fetchAll();

$unread = $pdo->prepare("SELECT COUNT(*) as count FROM notifications WHERE user_id=? AND is_read=0");
$unread->execute([$user_id]);
$unread_count = $unread->fetch()['count'];
?>

<div class="d-flex justify-content-between mb-3">
    <h4><i class="bi bi-bell"></i> Notifications (<?= $unread_count ?> unread)</h4>
    <?php if ($unread_count > 0): ?>
        <a href="?mark_read=1" class="btn btn-sm btn-primary">Mark All as Read</a>
    <?php endif; ?>
</div>

<?php if (count($notifications) === 0): ?>
    <div class="alert alert-info">No notifications yet.</div>
<?php else: ?>
    <div class="list-group">
        <?php foreach($notifications as $n): ?>
        <div class="list-group-item <?= $n['is_read'] ? '' : 'list-group-item-primary' ?>">
            <div class="d-flex w-100 justify-content-between">
                <h6 class="mb-1">
                    <?php if ($n['type'] === 'payment'): ?>
                        <i class="bi bi-credit-card text-success"></i>
                    <?php elseif ($n['type'] === 'booking'): ?>
                        <i class="bi bi-calendar-check text-info"></i>
                    <?php else: ?>
                        <i class="bi bi-info-circle"></i>
                    <?php endif; ?>
                    <?= htmlspecialchars($n['message']) ?>
                </h6>
                <small><?= htmlspecialchars($n['created_at']) ?></small>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php require_once "../../includes/footer.php"; ?>
