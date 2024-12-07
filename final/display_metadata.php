<?php
if (extension_loaded('imagick')) {
    echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Image Metadata</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f4f4f4;
        }
        h1 {
            text-align: center;
            color: #333;
        }
        .metadata {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table th, table td {
            padding: 10px;
            text-align: left;
            border: 1px solid #ddd;
        }
        table th {
            background-color: #f1f1f1;
            color: #333;
        }
        .category-title {
            background-color: #f1f1f1;
            padding: 10px;
            margin-top: 20px;
            border-radius: 8px;
        }
    </style>
</head>
<body>

<h1>Image Metadata</h1>
<div class='metadata'>
    <h2>=== Image Metadata ===</h2>";

    try {
        $image = new Imagick('/Users/rhong/Downloads/modified_image.JPG');  
        
        $properties = $image->getImageProperties();

        $categories = [
          'EXIF' => [],
          'JPEG' => [],
          'IPTC' => [],
          'XMP' => [],
          'Other' => []
        ];

        // Loop through all properties and categorize them
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

        foreach ($categories as $category => $properties) {
          if (!empty($properties)) {
            echo "<div class='category-title'><strong>$category Metadata</strong></div>";
            echo "<table><thead><tr><th>Property</th><th>Value</th></tr></thead><tbody>";

            foreach ($properties as $key => $value) {
              $formatted_key = ucfirst(str_replace(['exif:', 'jpeg:', 'iptc:', 'xmp:'], '', $key)); 
              echo "<tr><td>$formatted_key</td><td>$value</td></tr>";
            }

            echo "</tbody></table>";
          }
        }
        $image->destroy();
    } catch (Exception $e) {
      echo "<p>Error using Imagick: " . $e->getMessage() . "</p>";
    }

    echo "</div>
</body>
</html>";

} else {
    echo "<p>Imagick is not installed or not enabled.</p>";
}
?>
