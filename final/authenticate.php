<?php
session_start();

$username = $_POST['username'];
$password = $_POST['password'];

// Replace with your own user authentication logic
if ($username === 'admin' && $password === 'password') {
  $_SESSION['logged_in'] = true;
  header('Location: main.php');
  exit;
} else {
  $error = "Invalid credentials.";
  header('Location: index.php?error=' . urlencode($error));
  exit;
}
?>
