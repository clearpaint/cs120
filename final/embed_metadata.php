<?php
  error_reporting(E_ALL);
  ini_set('display_errors', 1);
  session_start();

  $config = include 'config.php';
  $ftp_server = $config['ftp_server'];
  $ftp_port = $config['ftp_port'];
  $ftp_user = $config['ftp_user'];
  $ftp_pass = $config['ftp_pass'];
  $ftp_path = $config['ftp_path'];

  if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('HTTP/1.1 401 Unauthorized');
    exit;
  }

  $selected_tags = isset($_POST['tags']) ? $_POST['tags'] : [];

  $file_url = $_SESSION['image_path'];
  $file_name = $_SESSION['file_name'];
  $base_name = pathinfo($file_name, PATHINFO_FILENAME);

  $remote_file = $ftp_path . $base_name . '_modified_image.jpg';

  try {
    if (!class_exists('Imagick')) {
      throw new Exception('Imagick extension is not installed or enabled.');
    }

    $temp_image_path = sys_get_temp_dir() . '/' . basename($file_url);
    $image_data = file_get_contents($file_url);
    if ($image_data === false) {
      throw new Exception('Failed to download the remote image file.');
    }
    file_put_contents($temp_image_path, $image_data);

    $imagick = new Imagick($temp_image_path);

    $tags_string = implode(', ', $selected_tags);
    // Imagick only supports writing comments to jpegs, modifying exif data does not persist
    // https://github.com/Imagick/imagick/issues/124
    // https://stackoverflow.com/questions/5384962/writing-exif-data-in-php
    $imagick->setImageProperty('comment', $tags_string);

    // Debug: Output all properties
    foreach ($imagick->getImageProperties() as $key => $value) {
      error_log("Key: $key, Value: $value");
    }

    $imagick->setImageFormat('jpeg');

    $temp_output_path = sys_get_temp_dir() . '/output_image.jpg';
    $imagick->writeImage($temp_output_path);

    $image_type = mime_content_type($temp_output_path);
    header('Content-Type: ' . $image_type);
    header('Content-Disposition: attachment; filename="' . $file_name . '_modified_image.jpg"');
    readfile($temp_output_path);

    $temp_output_path = sys_get_temp_dir() . '/output_image.jpg';
    $imagick->writeImage($temp_output_path);

    $ftp_conn = ftp_connect($ftp_server, $ftp_port);
    if ($ftp_conn) {
        $debug[] = "Connected to FTP server: $ftp_server:$ftp_port";
    } else {
        echo json_encode(['message' => 'Could not connect to FTP server', 'debug' => $debug]);
        exit;
    }

    $login = ftp_login($ftp_conn, $ftp_user, $ftp_pass);
    if ($login) {
        $debug[] = "FTP login successful for user: $ftp_user";
        ftp_pasv($ftp_conn, true); // Enable passive mode
    } else {
        $ftp_response = ftp_raw($ftp_conn, 'NOOP');
        $debug[] = "FTP login failed for user: $ftp_user";
        $debug[] = "FTP Server Response: " . implode(" | ", $ftp_response);
        ftp_close($ftp_conn);
        echo json_encode(['message' => 'FTP login failed', 'debug' => $debug]);
        exit;
    }

    if (ftp_put($ftp_conn, $remote_file, $temp_output_path, FTP_BINARY)) {
      $debug[] = "File uploaded successfully to: $remote_file";
    }

    if (file_exists($temp_image_path)) {
      unlink($temp_image_path);
    }

    if (file_exists($temp_output_path)) {
      unlink($temp_output_path);
    }

    if (!file_exists($temp_output_path)) {
        echo "<p>Error: Image file does not exist at $temp_output_path</p>";
        exit;
    }

  } catch (Exception $e) {
    if (isset($imagick)) {
      $imagick->destroy();
      $imagick->clear();
    }
    if (isset($temp_image_path) && file_exists($temp_image_path)) {
      unlink($temp_image_path);
    }
    echo 'Error processing image: ' . htmlspecialchars($e->getMessage());
  }
?>