<?php
  error_reporting(E_ALL);
  ini_set('display_errors', 1);
  session_start();

  $config = include 'config.php';
  $ftp_domain = $config['ftp_domain'];

  if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('HTTP/1.1 401 Unauthorized');
    exit;
  }

  $file_name = $_SESSION['file_name'];
  $base_name = pathinfo($file_name, PATHINFO_FILENAME);

  $image_url = $ftp_domain . "/uploads/". $base_name . '_modified_image.jpg';
  $temp_image_path = sys_get_temp_dir() . '/' . basename($image_url);

  if (empty($image_url)) {
    echo "<p>Error: Image path is not set in the session.</p>";
    exit;
  }

  try {
   $image_data = file_get_contents($image_url);
   if ($image_data === false) {
     throw new Exception('Failed to download the image file.');
   }
   file_put_contents($temp_image_path, $image_data);

   if (!class_exists('Imagick')) {
     throw new Exception('Imagick extension is not installed or enabled.');
   }

   $imagick = new Imagick($temp_image_path);
    
    if (!$imagick) {
      echo "<p>Error: Imagick failed to load the image.</p>";
      exit;
    }

    $properties = $imagick->getImageProperties(); 

    $categories = [
      'EXIF' => [],
      'JPEG' => [],
      'IPTC' => [],
      'XMP' => [],
      'Other' => []
    ];

    foreach ($properties as $key => $value) {
      if (strpos($key, 'exif:') === 0) {
        $categories['EXIF'][$key] = $value;
      } elseif (strpos($key, 'jpeg:') === 0) {
        $categories['JPEG'][$key] = $value;
      } elseif (strpos($key, 'iptc:') === 0) {
        $categories['IPTC'][$key] = $value;
      } elseif (strpos($key, 'xmp:') === 0) {
        $categories['XMP'][$key] = $value;
      } else {
        $categories['Other'][$key] = $value;
      }
    }

    echo "<h2>=== Image Metadata ===</h2>";
    foreach ($categories as $category => $properties) {
      if (!empty($properties)) {
        echo "<div class='category-title'><strong>$category Metadata</strong></div>";
        echo "<table>
                <thead>
                  <tr>
                    <th>Property</th>
                    <th>Value</th>
                  </tr>
                </thead>
                <tbody>";
        
        foreach ($properties as $key => $value) {
          $formatted_key = ucfirst(str_replace(['exif:', 'jpeg:', 'iptc:', 'xmp:'], '', $key)); 
          echo "<tr><td>$formatted_key</td><td>$value</td></tr>";
        }

        echo "</tbody>
        </table>";
      }
    }
    $imagick->destroy();
    if (file_exists($temp_image_path)) {
      unlink($temp_image_path);
    }
  } catch (Exception $e) {
    echo "<p>Error using Imagick: " . $e->getMessage() . "</p>";
    if (file_exists($temp_image_path)) {
      unlink($temp_image_path);
    }
  }
?>
