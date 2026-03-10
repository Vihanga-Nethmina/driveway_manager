<?php
require_once "../../config/db.php";
require_once "../../config/auth.php";
require_role('admin');
require_once "../../includes/admin_header.php";

// Get overdue maintenance
$overdue = $pdo->query("
    SELECT m.*, v.brand, v.model 
    FROM maintenance m
    JOIN vehicles v ON m.vehicle_id = v.id
    WHERE m.next_service_date < CURDATE()
    ORDER BY m.next_service_date ASC
");
$overdue_records = $overdue->fetchAll();

// Get upcoming maintenance (next 30 days)
$upcoming = $pdo->query("
    SELECT m.*, v.brand, v.model 
    FROM maintenance m
    JOIN vehicles v ON m.vehicle_id = v.id
    WHERE m.next_service_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)
    ORDER BY m.next_service_date ASC
");
$upcoming_records = $upcoming->fetchAll();
?>

<h4 class="mb-4"><i class="bi bi-bell"></i> Maintenance Reminders</h4>

<div class="row">
    <div class="col-md-6 mb-4">
        <div class="card shadow-sm border-danger">
            <div class="card-header bg-danger text-white">
                <i class="bi bi-exclamation-triangle"></i> Overdue Maintenance (<?= count($overdue_records) ?>)
            </div>
            <div class="card-body">
                <?php if (count($overdue_records) === 0): ?>
                    <p class="text-muted">No overdue maintenance.</p>
                <?php else: ?>
                    <div class="list-group">
                        <?php foreach($overdue_records as $o): ?>
                        <div class="list-group-item list-group-item-danger">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <strong><?= htmlspecialchars($o['brand']) ?> <?= htmlspecialchars($o['model']) ?></strong><br>
                                    <small><?= htmlspecialchars($o['service_type']) ?></small>
                                </div>
                                <div class="text-end">
                                    <span class="badge bg-danger">Overdue</span><br>
                                    <small><?= htmlspecialchars($o['next_service_date']) ?></small>
                                </div>
                            </div>
                            <a href="maintenance.php?vehicle_id=<?= $o['vehicle_id'] ?>" class="btn btn-sm btn-danger mt-2">View Details</a>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="col-md-6 mb-4">
        <div class="card shadow-sm border-warning">
            <div class="card-header bg-warning text-dark">
                <i class="bi bi-calendar-event"></i> Upcoming Maintenance (Next 30 Days)
            </div>
            <div class="card-body">
                <?php if (count($upcoming_records) === 0): ?>
                    <p class="text-muted">No upcoming maintenance in the next 30 days.</p>
                <?php else: ?>
                    <div class="list-group">
                        <?php foreach($upcoming_records as $u): ?>
                        <div class="list-group-item list-group-item-warning">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <strong><?= htmlspecialchars($u['brand']) ?> <?= htmlspecialchars($u['model']) ?></strong><br>
                                    <small><?= htmlspecialchars($u['service_type']) ?></small>
                                </div>
                                <div class="text-end">
                                    <span class="badge bg-warning text-dark">Upcoming</span><br>
                                    <small><?= htmlspecialchars($u['next_service_date']) ?></small>
                                </div>
                            </div>
                            <a href="maintenance.php?vehicle_id=<?= $u['vehicle_id'] ?>" class="btn btn-sm btn-warning mt-2">View Details</a>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once "../../includes/footer.php"; ?>
