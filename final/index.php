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
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="css/style.css">
<style>
* {
  box-sizing: border-box;
  margin: 0;
  padding: 0;
}
body {
  font-family: Arial, sans-serif;
  background: #000;
  width: 100%;
}
.header {
  background: #111;
  color: #fff;
  padding: 1em;
  text-align: center;
  width: 100%;
  position: fixed;
  top: 0;
  left: 0;
  z-index: 999;
}
.container {
  background: #222;
  color: #fff;
  width: 100%;
  max-width: 400px;
  margin: 100px auto 0 auto;
  padding: 1em;
}
h2 {
  text-align: center;
  margin-bottom: 1em;
  font-size: 1.4em;
  color: #fff;
}
form {
  display: flex;
  flex-direction: column;
  gap: 1em;
}
label {
  font-size: 1em;
  margin-bottom: 0.5em;
  color: #fff;
}
input[type="text"],
input[type="password"] {
  width: 100%;
  padding: 0.5em;
  font-size: 1em;
  border: 1px solid #555;
  border-radius: 4px;
  background: #333;
  color: #fff;
}
button {
  background: #2c89e5;
  color: #fff;
  border: none;
  padding: 0.75em;
  font-size: 1em;
  border-radius: 5px;
  cursor: pointer;
  text-align: center;
}
button:hover {
  background: #1f6bb2;
}
.error {
  background: #bb0000;
  color: #fff;
  padding: 0.5em;
  margin-bottom: 1em;
  text-align: center;
  border-radius: 4px;
}
@media (max-width: 600px) {
  .container {
    width: 90%;
    margin-top: 80px;
  }
  h2 {
    font-size: 1.2em;
  }
  label {
    font-size: 0.95em;
  }
  button {
    font-size: 1em;
  }
}
</style>
</head>
<body>
<div class="header">Image Tagging App</div>
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
