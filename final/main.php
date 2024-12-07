<?php
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
  header('Location: index.php');
  exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Main Interface</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="header">
  My Application
  <a href="logout.php" class="logout">Logout</a>
</div>
<div class="container">
  <?php
    if (isset($_SESSION['username'])) {
      echo "<h1>Welcome, " . htmlspecialchars($_SESSION['username']) . ".</h1>";
    }
  ?>
  <h2>Process Image</h2>
  <form id="processForm" enctype="multipart/form-data">
    <label>Choose an Option:</label>
    <div>
      <input type="radio" id="uploadOption" name="inputOption" value="upload" checked>
      <label for="uploadOption">Upload Image</label>
      <input type="radio" id="urlOption" name="inputOption" value="url">
      <label for="urlOption">Image URL</label>
    </div>

    <div id="uploadSection">
      <label for="fileInput">Upload Image:</label>
      <div id="drop-area">
        <p>Drag & Drop an image or click to select a file</p>
        <input type="file" id="fileInput" name="file" accept="image/*">
      </div>
    </div>

    <div id="urlSection" style="display: none;">
      <label for="imageUrl">Image URL:</label>
      <input type="url" id="imageUrl" name="imageUrl" placeholder="Enter image URL">
    </div>

    <label for="threshold">Confidence Threshold (%):</label>
    <input type="number" id="threshold" name="threshold" min="0" max="100" value="50" required>

    <button type="button" onclick="processImage()">Process Image</button>
  </form>

  <div id="progress" class="progress">
    <div class="bouncing-ball"></div>
  </div>
  <div id="imagePreview" class="image-preview"></div>
  <div id="debug" class="debug-window"></div>
  <div id="tags-container"></div>
  
  <div id="display-metadata-btn-container">
    <button id="displayMetadataBtn">Display Metadata</button>
  </div>

  <div id="metadataModal" class="modal">
    <div class="modal-content">
      <span id="closeModal" class="close">&times;</span>
      <div id="metadataReview" class="metadata-review">
      </div>
    </div>
  </div>

</div>
<script src="js/app.js"></script>
</body>
</html>
