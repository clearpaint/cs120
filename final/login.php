<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Login</title>
  <style>
    /* Your CSS styling here */
  </style>
</head>
<body>
  <form action="authenticate.php" method="post">
    <h2>Login</h2>
    <?php if (isset($_GET['error'])): ?>
      <p style="color:red;"><?php echo htmlspecialchars($_GET['error']); ?></p>
    <?php endif; ?>
    <label for="username">Username:</label>
    <input type="text" id="username" name="username" required>

    <label for="password">Password:</label>
    <input type="password" id="password" name="password" required>

    <button type="submit">Login</button>
  </form>
</body>
</html>
