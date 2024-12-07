<?php
  session_start();
  session_destroy();
  $temp_output_path = sys_get_temp_dir() . '/output_image.jpg';
  unlink($temp_output_path);
  header('Location: index.php');
  exit;
?>
