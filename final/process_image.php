<?php
session_start();

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
  echo json_encode(['error' => 'Unauthorized access']);
  exit;
}

$threshold = isset($_POST['threshold']) ? (float)$_POST['threshold'] : 50;
$debug = [];
$tags = [];

$inputOption = $_POST['inputOption'];

if ($inputOption === 'upload' && isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
  // Handle uploaded file
  $fileTmpPath = $_FILES['file']['tmp_name'];
  $fileName = basename($_FILES['file']['name']);
  $fileType = $_FILES['file']['type'];

  // Validate file type (allowing common image types)
  $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
  if (!in_array($fileType, $allowedTypes)) {
    $debug[] = 'Invalid file type.';
    echo json_encode(['error' => 'Invalid file type', 'debug' => $debug]);
    exit;
  }

  // Move the file to a temporary directory
  $uploadFileDir = './uploads/';
  $destPath = $uploadFileDir . uniqid() . '_' . $fileName;

  if (!move_uploaded_file($fileTmpPath, $destPath)) {
    $debug[] = 'Failed to move uploaded file.';
    echo json_encode(['error' => 'Failed to move uploaded file', 'debug' => $debug]);
    exit;
  }

  $debug[] = "File uploaded successfully: $destPath";

  // Imagga API credentials
  $api_key = 'acc_74179c62baa1fe3';
  $api_secret = 'b5d57bf2b166c425951dda16671004';

  // Prepare the image for the API call
  $imageData = base64_encode(file_get_contents($destPath));

  // Call the Imagga API
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, 'https://api.imagga.com/v2/tags');
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_USERPWD, "$api_key:$api_secret");
  curl_setopt($ch, CURLOPT_POST, true);
  curl_setopt($ch, CURLOPT_POSTFIELDS, [
    'image_base64' => $imageData,
  ]);

  $response = curl_exec($ch);
  $curl_error = curl_error($ch);
  curl_close($ch);

  if ($response) {
    $debug[] = "API call successful";
    $data = json_decode($response, true);
  } else {
    $debug[] = "API call failed: $curl_error";
    echo json_encode(['error' => 'API call failed', 'debug' => $debug]);
    exit;
  }

  // Store the image path in the session for later use
  $_SESSION['image_path'] = $destPath;
  $_SESSION['image_type'] = $fileType;

  // Prepare image preview
  $imagePreview = 'data:' . $fileType . ';base64,' . base64_encode(file_get_contents($destPath));
} elseif ($inputOption === 'url' && isset($_POST['imageUrl'])) {
  // Handle image URL
  $imageUrl = filter_var($_POST['imageUrl'], FILTER_VALIDATE_URL);

  if (!$imageUrl) {
    $debug[] = 'Invalid image URL.';
    echo json_encode(['error' => 'Invalid image URL', 'debug' => $debug]);
    exit;
  }

  // Imagga API credentials
  $api_key = 'acc_74179c62baa1fe3';
  $api_secret = '72b5d57bf2b166c425951dda16671004';

  // Call the Imagga API with the image URL
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, 'https://api.imagga.com/v2/tags?image_url=' . urlencode($imageUrl));
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_USERPWD, "$api_key:$api_secret");

  $response = curl_exec($ch);
  $curl_error = curl_error($ch);
  curl_close($ch);

  if ($response) {
    $debug[] = "API call successful";
    $data = json_decode($response, true);
  } else {
    $debug[] = "API call failed: $curl_error";
    echo json_encode(['error' => 'API call failed', 'debug' => $debug]);
    exit;
  }

  // Download the image from the URL
  $imageContent = file_get_contents($imageUrl);
  if ($imageContent === false) {
    $debug[] = 'Failed to download image from URL.';
    echo json_encode(['error' => 'Failed to download image from URL', 'debug' => $debug]);
    exit;
  }

  // Determine the image type
  $finfo = new finfo(FILEINFO_MIME_TYPE);
  $fileType = $finfo->buffer($imageContent);
  $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
  if (!in_array($fileType, $allowedTypes)) {
    $debug[] = 'Invalid image type from URL.';
    echo json_encode(['error' => 'Invalid image type from URL', 'debug' => $debug]);
    exit;
  }

  // Save the image to a temporary file
  $uploadFileDir = './uploads/';
  $destPath = $uploadFileDir . uniqid() . '_downloaded_image';
  file_put_contents($destPath, $imageContent);

  $debug[] = "Image downloaded successfully: $destPath";

  // Store the image path in the session for later use
  $_SESSION['image_path'] = $destPath;
  $_SESSION['image_type'] = $fileType;

  // Prepare image preview
  $imagePreview = 'data:' . $fileType . ';base64,' . base64_encode($imageContent);
} else {
  $debug[] = 'No file uploaded or invalid input option.';
  echo json_encode(['error' => 'No file uploaded or invalid input option', 'debug' => $debug]);
  exit;
}

// Filter tags based on confidence threshold
if (isset($data['result']['tags'])) {
  foreach ($data['result']['tags'] as $tag) {
    if ($tag['confidence'] >= $threshold) {
      $tags[] = [
        'tag' => $tag['tag']['en'],
        'confidence' => $tag['confidence']
      ];
    }
  }
} else {
  $debug[] = "No tags found in API response.";
  echo json_encode(['error' => 'Failed to get tags from API', 'debug' => $debug]);
  exit;
}

echo json_encode([
  'tags' => $tags,
  'debug' => $debug,
  'imagePreview' => $imagePreview
]);
?>
