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

$msg = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $service_type = $_POST['service_type'];
    $description = $_POST['description'];
    $service_date = $_POST['service_date'];
    $cost = $_POST['cost'];
    $next_service_date = $_POST['next_service_date'] ?? null;
    
    $stmt = $pdo->prepare("INSERT INTO maintenance (vehicle_id, service_type, description, service_date, cost, next_service_date) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$vehicle_id, $service_type, $description, $service_date, $cost, $next_service_date]);
    
    $msg = "Maintenance record added successfully!";
}

$stmt = $pdo->prepare("SELECT * FROM maintenance WHERE vehicle_id=? ORDER BY service_date DESC");
$stmt->execute([$vehicle_id]);
$records = $stmt->fetchAll();
?>

<div class="d-flex justify-content-between mb-3">
    <h4><i class="bi bi-tools"></i> Maintenance - <?= htmlspecialchars($vehicle['brand']) ?> <?= htmlspecialchars($vehicle['model']) ?></h4>
    <a href="../vehicles/manage.php" class="btn btn-secondary">Back to Vehicles</a>
</div>

<?php if ($msg): ?>
  <div class="alert alert-success"><?= htmlspecialchars($msg) ?></div>
<?php endif; ?>

<div class="row mb-4">
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <i class="bi bi-plus-circle"></i> Add Maintenance Record
            </div>
            <div class="card-body">
                <form method="post">
                    <div class="mb-3">
                        <label class="form-label">Service Type</label>
                        <select name="service_type" class="form-control" required>
                            <option value="">Select Type</option>
                            <option value="Oil Change">Oil Change</option>
                            <option value="Tire Rotation">Tire Rotation</option>
                            <option value="Brake Service">Brake Service</option>
                            <option value="Engine Repair">Engine Repair</option>
                            <option value="General Inspection">General Inspection</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Service Date</label>
                        <input type="date" name="service_date" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Cost ($)</label>
                        <input type="number" step="0.01" name="cost" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Next Service Date (Optional)</label>
                        <input type="date" name="next_service_date" class="form-control">
                    </div>
                    <button class="btn btn-primary"><i class="bi bi-check-circle"></i> Add Record</button>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header bg-info text-white">
                <i class="bi bi-bell"></i> Upcoming Maintenance
            </div>
            <div class="card-body">
                <?php
                $upcoming = $pdo->prepare("SELECT * FROM maintenance WHERE vehicle_id=? AND next_service_date >= CURDATE() ORDER BY next_service_date ASC LIMIT 5");
                $upcoming->execute([$vehicle_id]);
                $upcoming_records = $upcoming->fetchAll();
                ?>
                
                <?php if (count($upcoming_records) === 0): ?>
                    <p class="text-muted">No upcoming maintenance scheduled.</p>
                <?php else: ?>
                    <ul class="list-group">
                        <?php foreach($upcoming_records as $u): ?>
                        <li class="list-group-item">
                            <strong><?= htmlspecialchars($u['service_type']) ?></strong><br>
                            <small class="text-muted">Due: <?= htmlspecialchars($u['next_service_date']) ?></small>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<h5 class="mb-3">Service History</h5>
<table class="table table-bordered bg-white">
    <tr>
        <th>Service Type</th>
        <th>Description</th>
        <th>Service Date</th>
        <th>Cost</th>
        <th>Next Service</th>
    </tr>
    
    <?php if (count($records) === 0): ?>
        <tr><td colspan="5" class="text-center">No maintenance records yet.</td></tr>
    <?php endif; ?>
    
    <?php foreach($records as $r): ?>
    <tr>
        <td><?= htmlspecialchars($r['service_type']) ?></td>
        <td><?= htmlspecialchars($r['description']) ?></td>
        <td><?= htmlspecialchars($r['service_date']) ?></td>
        <td>$<?= htmlspecialchars($r['cost']) ?></td>
        <td><?= $r['next_service_date'] ? htmlspecialchars($r['next_service_date']) : '-' ?></td>
    </tr>
    <?php endforeach; ?>
</table>

<?php require_once "../../includes/footer.php"; ?>
