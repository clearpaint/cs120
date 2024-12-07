<?php
  $config = include 'config.php';

  $server = $config['db_host'];
  $userid = $config['db_user'];
  $pw = $config['db_pwd'];
  $db_name = $config['db_name'];

  try {
    $conn = new PDO("mysql:host=$server;dbname=$db_name", $userid, $pw);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  } catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
  }
?>
