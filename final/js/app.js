document.addEventListener("DOMContentLoaded", () => {
  const dropArea = document.getElementById("drop-area");
  const fileInput = document.getElementById("fileInput");
  const imageUrlInput = document.getElementById("imageUrl");
  const uploadOption = document.getElementById("uploadOption");
  const urlOption = document.getElementById("urlOption");
  const uploadSection = document.getElementById("uploadSection");
  const urlSection = document.getElementById("urlSection");
  const imagePreview = document.getElementById("imagePreview");
  const debugWindow = document.getElementById("debug");

  // Toggle input sections based on selected option
  uploadOption.addEventListener("change", toggleInputOption);
  urlOption.addEventListener("change", toggleInputOption);

  function toggleInputOption() {
    if (uploadOption.checked) {
      uploadSection.style.display = "block";
      urlSection.style.display = "none";
    } else {
      uploadSection.style.display = "none";
      urlSection.style.display = "block";
    }
    imagePreview.innerHTML = "";
    debugWindow.innerHTML = "";
  }

  // Drag-and-drop functionality
  ["dragenter", "dragover"].forEach((eventName) => {
    dropArea.addEventListener(eventName, highlight, false);
  });
  ["dragleave", "drop"].forEach((eventName) => {
    dropArea.addEventListener(eventName, unhighlight, false);
  });
  dropArea.addEventListener("drop", handleDrop, false);
  dropArea.addEventListener("click", () => fileInput.click(), false);
  fileInput.addEventListener("change", handleFiles, false);

  function highlight(e) {
    e.preventDefault();
    e.stopPropagation();
    dropArea.classList.add("highlight");
  }

  function unhighlight(e) {
    e.preventDefault();
    e.stopPropagation();
    dropArea.classList.remove("highlight");
  }

  function handleDrop(e) {
    e.preventDefault();
    e.stopPropagation();
    const dt = e.dataTransfer;
    const files = dt.files;
    fileInput.files = files;
    handleFiles();
  }

  function handleFiles() {
    const file = fileInput.files[0];
    if (file) {
      // Display the image preview
      const reader = new FileReader();
      reader.onload = function (e) {
        imagePreview.innerHTML = `<img src="${e.target.result}" alt="Uploaded Image">`;
      };
      reader.readAsDataURL(file);
      dropArea.querySelector("p").textContent = `File Selected: ${file.name}`;
    }
  }
});

function processImage() {
  const threshold = document.getElementById("threshold").value;
  const progress = document.getElementById("progress");
  const debugWindow = document.getElementById("debug");
  const tagsContainer = document.getElementById("tags-container");
  const imagePreview = document.getElementById("imagePreview");

  const uploadOption = document.getElementById("uploadOption");
  const fileInput = document.getElementById("fileInput");
  const imageUrlInput = document.getElementById("imageUrl");

  // Show progress indicator
  progress.style.display = "block";

  // Clear previous debug messages and tags
  debugWindow.innerHTML = "";
  tagsContainer.innerHTML = "";

  const formData = new FormData();
  formData.append("threshold", threshold);
  formData.append("inputOption", uploadOption.checked ? "upload" : "url");

  if (uploadOption.checked) {
    if (!fileInput.files[0]) {
      alert("Please select an image file.");
      progress.style.display = "none";
      return;
    }
    formData.append("file", fileInput.files[0]);
  } else {
    const imageUrl = imageUrlInput.value.trim();
    if (!imageUrl) {
      alert("Please enter an image URL.");
      progress.style.display = "none";
      return;
    }
    formData.append("imageUrl", imageUrl);
  }

  fetch("ftp_upload.php", { method: "POST", body: formData })
    .then((response) => response.json())
    .then((data) => {
      // Hide progress indicator
      progress.style.display = "none";

      if (data.error) {
        // Display error in debug window
        const p = document.createElement("p");
        p.textContent = data.error;
        debugWindow.appendChild(p);
      } else {
        // Display debug messages
        data.debug.forEach((msg) => {
          const p = document.createElement("p");
          p.textContent = msg;
          debugWindow.appendChild(p);
        });

        // Display image preview if not already displayed
        if (!imagePreview.innerHTML && data.imagePreview) {
          imagePreview.innerHTML = `<img src="${data.imagePreview}" alt="Processed Image">`;
        }

        // Display tags for selection
        const form = document.createElement("form");
        form.id = "tagsForm";

        const ul = document.createElement("ul");
        ul.className = "tag-list";

        if (data.tags && data.tags.length > 0) {
          // Process tags only if there are any
          data.tags.forEach((tag) => {
            const li = document.createElement("li");
            const checkbox = document.createElement("input");
            checkbox.type = "checkbox";
            checkbox.name = "tags[]";
            checkbox.value = tag.tag;
            checkbox.checked = true;

            const label = document.createElement("label");
            label.textContent = `${
              tag.tag
            } (Confidence: ${tag.confidence.toFixed(2)}%)`;

            li.appendChild(label);
            li.appendChild(checkbox);
            ul.appendChild(li);
          });
        }

        const downloadButton = document.createElement("button");
        downloadButton.type = "button";
        downloadButton.textContent = "Download Image with Metadata";
        downloadButton.onclick = downloadImage;

        form.appendChild(ul);
        form.appendChild(downloadButton);
        tagsContainer.appendChild(form);
      }

      // Log data to console
      console.log("Response Data:", data);
    })
    .catch((error) => {
      progress.style.display = "none";
      console.error("Error:", error);
      const p = document.createElement("p");
      p.textContent = `Error: ${error}`;
      debugWindow.appendChild(p);
    });
}

function downloadImage() {
  const form = document.getElementById("tagsForm");
  const checkedTags = [
    ...form.querySelectorAll('input[name="tags[]"]:checked'),
  ];

  // Create a new FormData object with only the checked tags
  const formData = new FormData();
  checkedTags.forEach((checkbox) => {
    formData.append("tags[]", checkbox.value);
  });

  fetch("embed_metadata.php", { method: "POST", body: formData })
    .then((response) => {
      if (!response.ok) {
        return response.text().then((text) => {
          throw new Error(text);
        });
      }
      return response.blob();
    })
    .then((blob) => {
      // Create a link to download the file
      const url = window.URL.createObjectURL(blob);
      const a = document.createElement("a");
      a.href = url;
      a.download = "modified_image.jpg";
      document.body.appendChild(a);
      a.click();
      a.remove();
    })
    .catch((error) => {
      console.error("Error:", error);
      const debugWindow = document.getElementById("debug");
      const p = document.createElement("p");
      p.textContent = `Error: ${error.message}`;
      debugWindow.appendChild(p);
    });
}
