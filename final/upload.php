<?php
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Upload File</title>
  <style>
    /* Your CSS styling here */
  </style>
</head>
<body>
  <form id="uploadForm" enctype="multipart/form-data">
    <h2>Upload File</h2>
    <input type="file" id="file" name="file" required>
    <button type="button" onclick="uploadFile()">Upload via FTP</button>
    <p id="description"></p>
  </form>
  <script>
    function uploadFile() {
      const formData = new FormData(document.getElementById("uploadForm"));
      fetch("ftp_upload.php", { method: "POST", body: formData })
        .then(response => response.json())
        .then(data => {
          alert(data.message);

          // Display description from API, if available
          if (data.description) {
            document.getElementById("description").innerText = "Image Description: " + data.description;
          }

          // Log debug information to the console
          console.log("Debug Information:", data.debug);
        })
        .catch(error => console.error("Error:", error));
    }
  </script>
</body>
</html>
