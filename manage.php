<?php
require_once "../../config/db.php";
require_once "../../config/auth.php";
require_role('admin');
require_once "../../includes/admin_header.php";

$action = $_GET['action'] ?? 'list';
$id = (int)($_GET['id'] ?? 0);
$msg = "";
$err = "";

// DELETE
if ($action === 'delete' && $id > 0) {
    $stmt = $pdo->prepare("DELETE FROM vehicles WHERE id=?");
    $stmt->execute([$id]);
    header("Location: manage.php");
    exit;
}

// CREATE
if ($action === 'create' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $brand = $_POST['brand'];
    $model = $_POST['model'];
    $year = $_POST['year'];
    $price = $_POST['price'];

    $image = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $image = uniqid() . '.' . $ext;
        move_uploaded_file($_FILES['image']['tmp_name'], "../uploads/vehicles/" . $image);
    }

    $stmt = $pdo->prepare("INSERT INTO vehicles (brand, model, year, price_per_day, image) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$brand, $model, $year, $price, $image]);
    
    $msg = "Vehicle added successfully!";
    $action = 'list';
}

// UPDATE
if ($action === 'edit' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $brand = $_POST['brand'];
    $model = $_POST['model'];
    $year = $_POST['year'];
    $price = $_POST['price'];
    $status = $_POST['status'];
    
    $vehicle_stmt = $pdo->prepare("SELECT * FROM vehicles WHERE id=?");
    $vehicle_stmt->execute([$id]);
    $vehicle = $vehicle_stmt->fetch();

    $image = $vehicle['image'];
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        if ($vehicle['image'] && file_exists("../uploads/vehicles/" . $vehicle['image'])) {
            unlink("../uploads/vehicles/" . $vehicle['image']);
        }
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $image = uniqid() . '.' . $ext;
        move_uploaded_file($_FILES['image']['tmp_name'], "../uploads/vehicles/" . $image);
    }

    $stmt = $pdo->prepare("UPDATE vehicles SET brand=?, model=?, year=?, price_per_day=?, status=?, image=? WHERE id=?");
    $stmt->execute([$brand, $model, $year, $price, $status, $image, $id]);
    
    $msg = "Vehicle updated successfully!";
    $action = 'list';
}

// GET VEHICLE FOR EDIT
$vehicle = null;
if ($action === 'edit' && $id > 0) {
    $stmt = $pdo->prepare("SELECT * FROM vehicles WHERE id=?");
    $stmt->execute([$id]);
    $vehicle = $stmt->fetch();
    if (!$vehicle) {
        $err = "Vehicle not found!";
        $action = 'list';
    }
}

// LIST
$vehicles = [];
if ($action === 'list') {
    $stmt = $pdo->query("SELECT * FROM vehicles ORDER BY id DESC");
    $vehicles = $stmt->fetchAll();
}
?>

<?php if ($msg): ?>
  <div class="alert alert-success"><?= htmlspecialchars($msg) ?></div>
<?php endif; ?>
<?php if ($err): ?>
  <div class="alert alert-danger"><?= htmlspecialchars($err) ?></div>
<?php endif; ?>

<?php if ($action === 'list'): ?>
<!-- LIST VIEW -->
<div class="d-flex justify-content-between mb-3">
    <h4>Vehicle Management</h4>
    <a href="?action=create" class="btn btn-success">Add Vehicle</a>
</div>

<table class="table table-bordered">
    <thead>
        <tr>
            <th>ID</th>
            <th>Image</th>
            <th>Brand</th>
            <th>Model</th>
            <th>Year</th>
            <th>Price/Day</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($vehicles as $v): ?>
        <tr>
            <td><?= $v['id'] ?></td>
            <td>
                <?php if (!empty($v['image'])): ?>
                    <img src="<?= BASE_URL ?>/uploads/vehicles/<?= $v['image'] ?>" style="width: 60px; height: 60px; object-fit: cover;" class="rounded">
                <?php else: ?>
                    -
                <?php endif; ?>
            </td>
            <td><?= $v['brand'] ?></td>
            <td><?= $v['model'] ?></td>
            <td><?= $v['year'] ?></td>
            <td>$<?= $v['price_per_day'] ?></td>
            <td><?= $v['status'] ?></td>
            <td>
                <a href="?action=edit&id=<?= $v['id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                <a href="../admin/vehicle_history.php?vehicle_id=<?= $v['id'] ?>" class="btn btn-secondary btn-sm"><i class="bi bi-clock-history"></i> History</a>
                <a href="../admin/vehicle_report.php?vehicle_id=<?= $v['id'] ?>" class="btn btn-success btn-sm" target="_blank"><i class="bi bi-file-earmark-pdf"></i> Report</a>
                <a href="../admin/maintenance.php?vehicle_id=<?= $v['id'] ?>" class="btn btn-info btn-sm"><i class="bi bi-tools"></i> Maintenance</a>
                <a href="?action=delete&id=<?= $v['id'] ?>" class="btn btn-danger btn-sm"
                   onclick="return confirm('Delete this vehicle?')">Delete</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php elseif ($action === 'create'): ?>
<!-- CREATE VIEW -->
<h4>Add Vehicle</h4>

<form method="post" enctype="multipart/form-data" class="p-3 rounded shadow-sm">
    <div class="mb-2">
        <label>Brand</label>
        <input type="text" name="brand" class="form-control" required>
    </div>
    <div class="mb-2">
        <label>Model</label>
        <input type="text" name="model" class="form-control" required>
    </div>
    <div class="mb-2">
        <label>Year</label>
        <input type="number" name="year" class="form-control" required>
    </div>
    <div class="mb-2">
        <label>Price Per Day</label>
        <input type="number" step="0.01" name="price" class="form-control" required>
    </div>
    <div class="mb-2">
        <label>Image</label>
        <input type="file" name="image" class="form-control" accept="image/*">
    </div>
    <button class="btn btn-success">Save</button>
    <a href="?action=list" class="btn btn-secondary">Back</a>
</form>

<?php elseif ($action === 'edit' && $vehicle): ?>
<!-- EDIT VIEW -->
<h4>Edit Vehicle</h4>

<form method="post" enctype="multipart/form-data" class="p-3 rounded shadow-sm">
    <div class="mb-2">
        <label>Brand</label>
        <input type="text" name="brand" value="<?= $vehicle['brand'] ?>" class="form-control">
    </div>
    <div class="mb-2">
        <label>Model</label>
        <input type="text" name="model" value="<?= $vehicle['model'] ?>" class="form-control">
    </div>
    <div class="mb-2">
        <label>Year</label>
        <input type="number" name="year" value="<?= $vehicle['year'] ?>" class="form-control">
    </div>
    <div class="mb-2">
        <label>Price Per Day</label>
        <input type="number" step="0.01" name="price" value="<?= $vehicle['price_per_day'] ?>" class="form-control">
    </div>
    <div class="mb-2">
        <label>Image</label>
        <?php if (!empty($vehicle['image'])): ?>
            <div class="mb-2">
                <img src="<?= BASE_URL ?>/uploads/vehicles/<?= $vehicle['image'] ?>" style="max-width: 200px;" class="img-thumbnail">
            </div>
        <?php endif; ?>
        <input type="file" name="image" class="form-control" accept="image/*">
    </div>
    <div class="mb-2">
        <label>Status</label>
        <select name="status" class="form-control">
            <option value="available" <?= $vehicle['status']==='available'?'selected':'' ?>>Available</option>
            <option value="booked" <?= $vehicle['status']==='booked'?'selected':'' ?>>Booked</option>
            <option value="maintenance" <?= $vehicle['status']==='maintenance'?'selected':'' ?>>Maintenance</option>
        </select>
    </div>
    <button class="btn btn-primary">Update</button>
    <a href="?action=list" class="btn btn-secondary">Back</a>
</form>

<?php endif; ?>

<?php require_once "../../includes/footer.php"; ?>
