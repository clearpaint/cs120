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
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="css/style.css">
<style>
* {
  box-sizing: border-box;
  margin: 0;
  padding: 0;
}
body {
  font-family: Arial, sans-serif;
  background: #000;
  width: 100%;
}
.header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: #111;
  color: #fff;
  padding: 1em;
  width: 100%;
  position: fixed;
  top: 0;
  left: 0;
  z-index: 999;
}
.header .logout {
  color: #fff;
  text-decoration: none;
  font-size: 0.9em;
}
.container {
  background: #222;
  color: #fff;
  width: 100%;
  padding: 1em;
  margin-top: 70px;
}
h2 {
  text-align: center;
  margin-bottom: 1em;
  font-size: 1.4em;
  color: #fff;
}
form {
  display: flex;
  flex-direction: column;
  gap: 1em;
}
label {
  font-size: 1em;
  display: block;
  margin-bottom: 0.5em;
  color: #fff;
}
input[type="radio"] {
  margin-right: 0.5em;
  vertical-align: middle;
}
#uploadSection,
#urlSection {
  margin-bottom: 1em;
}
#drop-area {
  border: 2px dashed #555;
  padding: 1em;
  text-align: center;
  background: #333;
  cursor: pointer;
}
#drop-area p {
  margin: 0.5em 0;
  font-size: 1em;
  color: #fff;
}
input[type="file"],
input[type="url"],
input[type="number"] {
  width: 100%;
  padding: 0.5em;
  font-size: 1em;
  border: 1px solid #555;
  border-radius: 4px;
  background: #333;
  color: #fff;
}
button {
  background: #2c89e5;
  color: #fff;
  border: none;
  padding: 0.75em;
  font-size: 1em;
  border-radius: 5px;
  cursor: pointer;
  text-align: center;
}
button:hover {
  background: #1f6bb2;
}
.progress {
  display: none;
  margin: 1em 0;
}
.bouncing-ball {
  width: 20px;
  height: 20px;
  background: #2c89e5;
  border-radius: 50%;
  animation: bounce 1s infinite;
  margin: 0 auto;
}
@keyframes bounce {
  0%,100% {
    transform: translateY(0);
  }
  50% {
    transform: translateY(-10px);
  }
}
.image-preview img {
  max-width: 100%;
  display: block;
  margin: 1em 0;
}
.debug-window {
  background: #333;
  padding: 0.5em;
  margin-top: 1em;
  display: none;
  font-size: 0.9em;
  white-space: pre-wrap;
  color: #fff;
}
#tags-container {
  margin-top: 1em;
}
@media (max-width: 600px) {
  .header {
    flex-direction: column;
    font-size: 1em;
  }
  h2 {
    font-size: 1.2em;
  }
  label {
    font-size: 0.95em;
  }
  button {
    font-size: 1em;
  }
}
</style>
</head>
<body>
<div class="header">
  Image Tagging App
  <a href="logout.php" class="logout">Logout</a>
</div>
<div class="container">
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
</div>
<script src="js/app.js"></script>
</body>
</html>
