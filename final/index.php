<?php
session_start();
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
  header('Location: main.php');
  exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Login</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="header">My Application</div>
<div class="container">
  <form action="authenticate.php" method="post">
    <h2>Login</h2>
    <?php if (isset($_GET['error'])): ?>
      <p class="error"><?php echo htmlspecialchars($_GET['error']); ?></p>
    <?php endif; ?>
    <label for="username">Username:</label>
    <input type="text" id="username" name="username" required>

    <label for="password">Password:</label>
    <input type="password" id="password" name="password" required>

    <button type="submit">Login</button>
  </form>
</div>
<script src="js/app.js"></script>
</body>
</html>
