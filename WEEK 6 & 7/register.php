<?php
require_once 'includes/config.php';

$error = ""; $success = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = htmlspecialchars(trim($_POST['username']));
    $email    = htmlspecialchars(trim($_POST['email']));
    $password = trim($_POST['password']);
    $confirm  = trim($_POST['confirm']);

    if (empty($username)||empty($email)||empty($password)) {
        $error = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email address.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters.";
    } elseif ($password !== $confirm) {
        $error = "Passwords do not match.";
    } else {
        $stmt = $conn->prepare("SELECT id FROM users WHERE username=? OR email=?");
        $stmt->bind_param('ss', $username, $email);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $error = "Username or email already exists.";
        } else {
            $role  = 'admin';
            $stmt2 = $conn->prepare("INSERT INTO users (username,email,password,role) VALUES (?,?,?,?)");
            $stmt2->bind_param('ssss', $username, $email, $password, $role);
            if ($stmt2->execute()) {
                $success = "Account created! You can now login.";
            } else {
                $error = "Registration failed.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <title>Register — Student MS</title>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;900&family=IBM+Plex+Mono:wght@400;500&family=Manrope:wght@400;500;600&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="css/style.css"/>
</head>
<body class="auth-body">
<div class="auth-card">
  <div class="auth-brand">
    <div class="auth-logo">Student<span>MS</span></div>
    <div class="auth-sub">Create Account</div>
  </div>

  <h2 class="auth-title">Register</h2>

  <?php if ($error):   ?><div class="alert alert-error"><?= $error ?></div><?php endif; ?>
  <?php if ($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>

  <form method="POST">
    <div class="field">
      <label>Username</label>
      <input type="text" name="username" placeholder="Choose a username"/>
    </div>
    <div class="field">
      <label>Email</label>
      <input type="email" name="email" placeholder="your@email.com"/>
    </div>
    <div class="field">
      <label>Password</label>
      <input type="password" name="password" placeholder="Min 6 characters"/>
    </div>
    <div class="field">
      <label>Confirm Password</label>
      <input type="password" name="confirm" placeholder="Repeat password"/>
    </div>
    <button type="submit" class="btn-auth">Create Account</button>
  </form>

  <a href="login.php" class="auth-link">Already have an account? Login →</a>
</div>
</body>
</html>