<?php
require_once 'includes/config.php';

if (isLoggedIn()) {
    header("Location: index.php");
    exit();
}

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = htmlspecialchars(trim($_POST['username']));
    $password = trim($_POST['password']);

    if (empty($username) || empty($password)) {
        $error = "Please enter both username and password.";
    } else {
        $stmt = $conn->prepare("SELECT id, username, password, role FROM users WHERE username = ?");
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();

        if ($user && $password === $user['password']) {
            $_SESSION['user']    = $user['username'];
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role']    = $user['role'];
            session_regenerate_id(true);
            header("Location: index.php");
            exit();
        } else {
            $error = "Invalid username or password.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Login — Student MS</title>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;900&family=IBM+Plex+Mono:wght@400;500&family=Manrope:wght@400;500;600&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="css/style.css"/>
</head>
<body class="auth-body">
<div class="auth-card">

  <div class="auth-brand">
    <div class="auth-logo">Student<span>MS</span></div>
    <div class="auth-sub">Academic Records System</div>
  </div>

  <h2 class="auth-title">Sign In</h2>

  <?php if ($error): ?>
    <div class="alert alert-error"><?= $error ?></div>
  <?php endif; ?>

  <form method="POST" action="">
    <div class="field">
      <label>Username</label>
      <input type="text" name="username" placeholder="Enter username" required/>
    </div>
    <div class="field">
      <label>Password</label>
      <input type="password" name="password" placeholder="Enter password" required/>
    </div>
    <button type="submit" class="btn-auth">Login</button>
  </form>

  <a href="register.php" class="auth-link">No account? Register →</a>
</div>
</body>
</html>