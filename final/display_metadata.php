<?php
  error_reporting(E_ALL);
  ini_set('display_errors', 1);
  session_start();

  if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('HTTP/1.1 401 Unauthorized');
    exit;
  }

  $file_url = isset($_SESSION['temp_output_path']) ? $_SESSION['temp_output_path'] : '';

  if (empty($file_url)) {
    echo "<p>Error: Image path is not set in the session.</p>";
    exit;
  }

  if (!file_exists($file_url)) {
    echo "<p>Error: Image file does not exist at $file_url</p>";
    exit;
  }

  try {
    if (!class_exists('Imagick')) {
      throw new Exception('Imagick extension is not installed or not enabled.');
    }

    $imagick = new Imagick($file_url);
    
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
    $temp_output_path = sys_get_temp_dir() . '/output_image.jpg';
    unlink($temp_output_path);
  } catch (Exception $e) {
    echo "<p>Error using Imagick: " . $e->getMessage() . "</p>";
  }
?>
