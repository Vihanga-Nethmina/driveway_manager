<?php
require_once "../../config/db.php";
require_once "../../config/auth.php";
require_login();

if ($_SESSION['user']['role'] === 'admin') {
    require_once "../../includes/admin_header.php";
} else {
    require_once "../../includes/customer_header.php";
}

$user_id = (int)$_SESSION['user']['id'];
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

$msg = "";
$err = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? "";
    
    if ($action === 'update_profile') {
        $name = trim($_POST['name']);
        $email = trim($_POST['email']);
        
        $stmt = $pdo->prepare("UPDATE users SET name=?, email=? WHERE id=?");
        $stmt->execute([$name, $email, $user_id]);
        $_SESSION['user']['name'] = $name;
        $_SESSION['user']['email'] = $email;
        
        $msg = "Profile updated successfully!";
    }
    
    if ($action === 'change_password') {
        $current = $_POST['current_password'] ?? "";
        $new = $_POST['new_password'] ?? "";
        $confirm = $_POST['confirm_password'] ?? "";
        
        if (password_verify($current, $user['password'])) {
            if ($new === $confirm) {
                $hash = password_hash($new, PASSWORD_BCRYPT);
                $stmt = $pdo->prepare("UPDATE users SET password=? WHERE id=?");
                $stmt->execute([$hash, $user_id]);
                $msg = "Password changed successfully!";
            } else {
                $err = "New passwords do not match!";
            }
        } else {
            $err = "Current password is incorrect!";
        }
    }
    
    if ($action === 'toggle_dark_mode') {
        $dark_mode = $_POST['dark_mode'] === '1' ? 1 : 0;
        setcookie('dark_mode', $dark_mode, time() + (86400 * 365), '/');
        $msg = "Dark mode " . ($dark_mode ? "enabled" : "disabled") . "!";
    }
}

$dark_mode = isset($_COOKIE['dark_mode']) ? (int)$_COOKIE['dark_mode'] : 0;
?>

<h4 class="mb-4"><i class="bi bi-gear"></i> Settings</h4>

<?php if ($msg): ?>
  <div class="alert alert-success"><?= htmlspecialchars($msg) ?></div>
<?php endif; ?>
<?php if ($err): ?>
  <div class="alert alert-danger"><?= htmlspecialchars($err) ?></div>
<?php endif; ?>

<div class="row">
    <div class="col-md-6 mb-4">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <i class="bi bi-person-circle"></i> Update Profile
            </div>
            <div class="card-body">
                <form method="post">
                    <input type="hidden" name="action" value="update_profile">
                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" class="form-control" required>
                    </div>
                    <button class="btn btn-primary"><i class="bi bi-check-circle"></i> Update Profile</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-6 mb-4">
        <div class="card shadow-sm">
            <div class="card-header bg-warning text-dark">
                <i class="bi bi-key"></i> Change Password
            </div>
            <div class="card-body">
                <form method="post">
                    <input type="hidden" name="action" value="change_password">
                    <div class="mb-3">
                        <label class="form-label">Current Password</label>
                        <input type="password" name="current_password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">New Password</label>
                        <input type="password" name="new_password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Confirm New Password</label>
                        <input type="password" name="confirm_password" class="form-control" required>
                    </div>
                    <button class="btn btn-warning"><i class="bi bi-shield-lock"></i> Change Password</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-6 mb-4">
        <div class="card shadow-sm">
            <div class="card-header bg-dark text-white">
                <i class="bi bi-moon-stars"></i> Appearance
            </div>
            <div class="card-body">
                <form method="post">
                    <input type="hidden" name="action" value="toggle_dark_mode">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="dark_mode" value="1" id="darkModeSwitch" <?= $dark_mode ? 'checked' : '' ?> onchange="this.form.submit()">
                        <label class="form-check-label" for="darkModeSwitch">
                            <i class="bi bi-moon"></i> Dark Mode
                        </label>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once "../../includes/footer.php"; ?>
