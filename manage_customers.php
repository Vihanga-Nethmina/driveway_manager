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
    $stmt = $pdo->prepare("DELETE FROM users WHERE id=?");
    $stmt->execute([$id]);
    header("Location: manage_customers.php");
    exit;
}

// UPDATE
if ($action === 'edit' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $role = $_POST['role'];
    $password = $_POST['password'] ?? "";

    if ($password) {
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $pdo->prepare("UPDATE users SET name=?, email=?, role=?, password=? WHERE id=?");
        $stmt->execute([$name, $email, $role, $hash, $id]);
    } else {
        $stmt = $pdo->prepare("UPDATE users SET name=?, email=?, role=? WHERE id=?");
        $stmt->execute([$name, $email, $role, $id]);
    }

    $msg = "Customer updated successfully!";
    $action = 'list';
}

// GET CUSTOMER FOR EDIT
$customer = null;
if ($action === 'edit' && $id > 0) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id=?");
    $stmt->execute([$id]);
    $customer = $stmt->fetch();
    if (!$customer) {
        $err = "User not found!";
        $action = 'list';
    }
}

// LIST
$users = [];
if ($action === 'list') {
    $stmt = $pdo->query("SELECT * FROM users ORDER BY id DESC");
    $users = $stmt->fetchAll();
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
<h4>Manage Customers</h4>

<table class="table table-bordered bg-white">
  <tr>
    <th>ID</th>
    <th>Name</th>
    <th>Email</th>
    <th>Role</th>
    <th>Created</th>
    <th>Actions</th>
  </tr>

  <?php foreach($users as $u): ?>
  <tr>
    <td><?= (int)$u['id'] ?></td>
    <td><?= htmlspecialchars($u['name']) ?></td>
    <td><?= htmlspecialchars($u['email']) ?></td>
    <td><?= htmlspecialchars($u['role']) ?></td>
    <td><?= htmlspecialchars($u['created_at']) ?></td>
    <td>
      <a href="?action=edit&id=<?= $u['id'] ?>" class="btn btn-warning btn-sm">Edit</a>
      <a href="?action=delete&id=<?= $u['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this user?')">Delete</a>
    </td>
  </tr>
  <?php endforeach; ?>
</table>

<?php elseif ($action === 'edit' && $customer): ?>
<!-- EDIT VIEW -->
<h4>Edit Customer</h4>

<form method="post" class="bg-white p-3 rounded shadow-sm">
    <div class="mb-2">
        <label>Name</label>
        <input type="text" name="name" value="<?= htmlspecialchars($customer['name']) ?>" class="form-control" required>
    </div>
    <div class="mb-2">
        <label>Email</label>
        <input type="email" name="email" value="<?= htmlspecialchars($customer['email']) ?>" class="form-control" required>
    </div>
    <div class="mb-2">
        <label>Role</label>
        <select name="role" class="form-control">
            <option value="customer" <?= $customer['role']==='customer'?'selected':'' ?>>Customer</option>
            <option value="admin" <?= $customer['role']==='admin'?'selected':'' ?>>Admin</option>
        </select>
    </div>
    <div class="mb-2">
        <label>New Password (leave blank to keep current)</label>
        <input type="password" name="password" class="form-control">
    </div>
    <button class="btn btn-primary">Update</button>
    <a href="?action=list" class="btn btn-secondary">Back</a>
</form>

<?php endif; ?>

<?php require_once "../../includes/footer.php"; ?>
