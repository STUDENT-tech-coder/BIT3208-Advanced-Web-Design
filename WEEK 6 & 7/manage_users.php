<?php
require_once 'includes/config.php';
requireLogin();
requireSuperAdmin();

$message  = "";
$msg_type = "success";

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    if ($id === (int)$_SESSION['user_id']) {
        $message = "You cannot delete your own account.";
        $msg_type = "error";
    } else {
        if ($conn->query("DELETE FROM users WHERE id=$id")) {
            $message = "✓ User deleted.";
        } else {
            $message = "Error deleting user.";
            $msg_type = "error";
        }
    }
}

if (isset($_POST['change_role'])) {
    $id   = (int)$_POST['user_id'];
    $role = $_POST['role'] === 'superadmin' ? 'superadmin' : 'admin';
    $stmt = $conn->prepare("UPDATE users SET role=? WHERE id=?");
    $stmt->bind_param('si', $role, $id);
    if ($stmt->execute()) {
        $message = "✓ Role updated.";
    } else {
        $message = "Error updating role.";
        $msg_type = "error";
    }
}

$users = $conn->query("SELECT * FROM users ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <title>Manage Users — Student MS</title>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;900&family=IBM+Plex+Mono:wght@400;500&family=Manrope:wght@400;500;600&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="css/style.css"/>
</head>
<body>

<header>
  <div class="header-inner">
    <div class="brand">
      <div class="brand-name">Student<span>MS</span></div>
      <div class="brand-sub">Manage Users — Superadmin Panel</div>
    </div>
    <div style="display:flex;align-items:center;gap:1.5rem;">
      <span class="role-badge role-superadmin">SUPERADMIN</span>
      <a href="index.php"   class="header-nav-link">Dashboard</a>
      <a href="logout.php"  class="header-nav-link">Logout</a>
    </div>
  </div>
</header>

<main>
  <div class="card">
    <div class="form-title">👑 User Management</div>
    <div class="form-subtitle">Only superadmin can view, edit roles, or delete users</div>

    <?php if ($message): ?>
      <div class="alert alert-<?= $msg_type ?>"><?= $message ?></div>
    <?php endif; ?>

    <div class="alert alert-info">
      ⚠️ Deleting a user is permanent. You cannot delete your own account.
    </div>

    <div style="overflow-x:auto;">
      <table>
        <thead>
          <tr>
            <th>#</th><th>Username</th><th>Email</th>
            <th>Role</th><th>Registered</th><th>Change Role</th><th>Delete</th>
          </tr>
        </thead>
        <tbody>
          <?php $i=1; while($row = $users->fetch_assoc()): ?>
          <tr>
            <td class="mono"><?= $i++ ?></td>
            <td style="font-weight:600"><?= htmlspecialchars($row['username']) ?></td>
            <td class="mono"><?= htmlspecialchars($row['email']) ?></td>
            <td>
              <span class="role-badge role-<?= $row['role'] ?>">
                <?= strtoupper($row['role']) ?>
              </span>
            </td>
            <td class="mono" style="font-size:0.78rem">
              <?= date('d M Y', strtotime($row['created_at'])) ?>
            </td>
            <td>
              <?php if ($row['id'] != $_SESSION['user_id']): ?>
              <form method="POST" style="display:flex;gap:0.4rem;align-items:center;">
                <input type="hidden" name="user_id" value="<?= $row['id'] ?>"/>
                <select name="role" style="padding:0.3rem 0.5rem;font-size:0.8rem;border-radius:4px;border:1px solid #d4cfc6;font-family:monospace;">
                  <option value="admin"      <?= $row['role']==='admin'      ?'selected':'' ?>>Admin</option>
                  <option value="superadmin" <?= $row['role']==='superadmin' ?'selected':'' ?>>Superadmin</option>
                </select>
                <button type="submit" name="change_role" class="act-btn act-edit">Save</button>
              </form>
              <?php else: ?>
                <span style="font-size:0.78rem;color:#7a7068;font-family:monospace">Your account</span>
              <?php endif; ?>
            </td>
            <td>
              <?php if ($row['id'] != $_SESSION['user_id']): ?>
                <a href="?delete=<?= $row['id'] ?>"
                   onclick="return confirm('Delete <?= htmlspecialchars($row['username']) ?>?')">
                  <button class="act-btn act-del">Delete</button>
                </a>
              <?php else: ?>
                <span style="font-size:0.78rem;color:#7a7068">—</span>
              <?php endif; ?>
            </td>
          </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>
</main>

</body>
</html>