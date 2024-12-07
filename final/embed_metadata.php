<?php
  error_reporting(E_ALL);
  ini_set('display_errors', 1);
  session_start();

  if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('HTTP/1.1 401 Unauthorized');
    exit;
  }

  $selected_tags = isset($_POST['tags']) ? $_POST['tags'] : [];

  // Get the remote file URL from the session
  $file_url = $_SESSION['image_path']; // Remote FTP URL

  try {
    if (!class_exists('Imagick')) {
      throw new Exception('Imagick extension is not installed or enabled.');
    }

    // Download the remote image to a temporary local file
    $temp_image_path = sys_get_temp_dir() . '/' . basename($file_url);
    $image_data = file_get_contents($file_url);
    if ($image_data === false) {
        throw new Exception('Failed to download the remote image file.');
    }
    file_put_contents($temp_image_path, $image_data);

    // Load the image using Imagick
    $imagick = new Imagick($temp_image_path);

    // Embed the selected tags into the image metadata
    $tags_string = implode(', ', $selected_tags);
    $imagick->setImageProperty('exif:ImageDescription', $tags_string);
    $imagick->setImageProperty('exif:Artist', $_SESSION['username']);
    $imagick->setImageProperty('comment', $tags_string);

    // Debug: Output all properties
    foreach ($imagick->getImageProperties() as $key => $value) {
      error_log("Key: $key, Value: $value");
    }

    // Set output format
    $imagick->setImageFormat('jpeg');

    // Save to a temporary output file
    $temp_output_path = sys_get_temp_dir() . '/output_image.jpg';
    $imagick->writeImage($temp_output_path);

    // Output the image to the client
    $image_type = mime_content_type($temp_output_path);
    header('Content-Type: ' . $image_type);
    header('Content-Disposition: attachment; filename="modified_image.jpg"');
    readfile($temp_output_path);

    // Clean up
      unlink($temp_output_path);

  } catch (Exception $e) {
    // Handle errors
    if (isset($imagick)) {
        $imagick->clear();
        $imagick->destroy();
    }
    if (isset($temp_image_path) && file_exists($temp_image_path)) {
        unlink($temp_image_path);
    }
    echo 'Error processing image: ' . htmlspecialchars($e->getMessage());
  }
?>