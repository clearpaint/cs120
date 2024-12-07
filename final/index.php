<?php
  session_start();
  if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true && $_SESSION['user_id']) {
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
<div class="header">Image Tagging App</div>
<div class="container">
<form method="post">
    <h2>Login</h2>
    <?php if (isset($_GET['error'])): ?>
      <p class="error"><?php echo htmlspecialchars($_GET['error']); ?></p>
    <?php endif; ?>
    <?php
      require 'db.php';
      if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $email = htmlspecialchars($_POST['email']);
        $password = $_POST['password'];

        try {
          $stmt = $conn->prepare("SELECT * FROM users WHERE email = :email");
          $stmt->bindParam(':email', $email);
          $stmt->execute();
          $user = $stmt->fetch(PDO::FETCH_ASSOC);

          if ($user && password_verify($password, $user['password'])) {
              $_SESSION['user_id'] = $user['id'];
              $_SESSION['username'] = $user['username'];
              $_SESSION['logged_in'] = true;
              header('Location: main.php');
              exit;
          } else {
              echo "Invalid email or password.";
          }
        } catch (PDOException $e) {
          echo "Error: " . $e->getMessage();
        }
      }
    ?>
    <label for="email">Email:</label>
    <input type="text" id="email" name="email" required>

    <label for="password">Password:</label>
    <input type="password" id="password" name="password" required>

    <div id="auth-actions">
      <button type="submit">Login</button>
      <button type="button" id="registerRedirectBtn">Register</button>
    </div>
  </form>
</div>
</body>
<script>
  // Redirect to register.php when the Register button is clicked
  document.getElementById('registerRedirectBtn').addEventListener('click', () => {
    window.location.href = 'register.php';
  });
</script>
</html>
