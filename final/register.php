<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Register</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
  <div class="header">Image Tagging App</div>
  <div class="container">
  <form method="post">
      <h2>Register</h2>
      <?php
        require 'db.php';

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
          $username = htmlspecialchars($_POST['username']);
          $email = htmlspecialchars($_POST['email']);
          $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

          try {
              $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (:username, :email, :password)");
              $stmt->bindParam(':username', $username);
              $stmt->bindParam(':email', $email);
              $stmt->bindParam(':password', $password);
              $stmt->execute();

              echo "Registration successful! Redirecting to login in <span id='countdown'>5</span> second(s).<p>";
              echo "<script>
                      let countdown = 5;
                      const interval = setInterval(() => {
                        countdown--;
                        document.getElementById('countdown').textContent = countdown;
                        if (countdown === 0) {
                          clearInterval(interval);
                          window.location.href = 'index.php';
                        }
                      }, 1000);
                    </script>";
          } catch (PDOException $e) {
            if ($e->getCode() == 23000) { 
              echo "Username or email already exists.";
            } else {
              echo "Error: " . $e->getMessage();
            }
          }
        }
      ?>
      <label for="email">Email:</label>
      <input type="text" id="email" name="email" required>

      <label for="email">Username:</label>
      <input type="text" id="username" name="username" required>

      <label for="password">Password:</label>
      <input type="password" id="password" name="password" required>

      <div id="auth-actions">
        <button type="submit">Register</button>
        <button type="button" id="loginRedirectBtn">Login</button>
      </div>
    </form>
  </div>
</body>
<script>
  // Redirect to index.php when the Login button is clicked
  document.getElementById('loginRedirectBtn').addEventListener('click', () => {
    window.location.href = 'index.php';
  });
</script>
</html>
