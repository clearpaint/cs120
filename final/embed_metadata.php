<?php
session_start();

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
  header('HTTP/1.1 401 Unauthorized');
  exit;
}

if (!isset($_SESSION['image_path']) || !file_exists($_SESSION['image_path'])) {
  header('HTTP/1.1 404 Not Found');
  exit;
}

$selected_tags = isset($_POST['tags']) ? $_POST['tags'] : [];
$image_path = $_SESSION['image_path'];
$image_type = $_SESSION['image_type'];

try {
  if (!class_exists('Imagick')) {
    throw new Exception('Imagick extension is not installed or enabled.');
  }

  $imagick = new Imagick($image_path);

  // Embed the selected tags into the image metadata
  $tags_string = implode(', ', $selected_tags);
  $imagick->setImageProperty('exif:ImageDescription', $tags_string);

  // Output the image
  header('Content-Type: ' . $image_type);
  header('Content-Disposition: attachment; filename="modified_image.' . strtolower($imagick->getImageFormat()) . '"');
  echo $imagick->getImagesBlob();

  // Clean up
  $imagick->clear();
  $imagick->destroy();
  unlink($image_path);
  unset($_SESSION['image_path']);
  unset($_SESSION['image_type']);
} catch (Exception $e) {
  // Clean up
  if (isset($imagick)) {
    $imagick->clear();
    $imagick->destroy();
  }
  if (file_exists($image_path)) {
    unlink($image_path);
  }
  unset($_SESSION['image_path']);
  unset($_SESSION['image_type']);

  // Log the error
  error_log('Error in embed_metadata.php: ' . $e->getMessage());

  // Display error in debug window
  echo 'Error processing image: ' . htmlspecialchars($e->getMessage());
}
?>
