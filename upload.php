<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Directory where file will be uploaded
    $target_dir = "uploads/";

    // Validate and sanitize file input
    if (!isset($_FILES["file"]) || $_FILES["file"]["error"] !== UPLOAD_ERR_OK) {
        die("Error: File upload error code " . $_FILES["file"]["error"]);
    }

    $allowed_types = ['image/jpeg', 'image/png', 'application/pdf', 'text/html'];
    $file_type = mime_content_type($_FILES["file"]["tmp_name"]);

    if (!in_array($file_type, $allowed_types)) {
        die("Error: Only JPEG, PNG, PDF, and HTML files are allowed.");
    }

    $max_file_size = 5 * 1024 * 1024; // 5 MB
    if ($_FILES["file"]["size"] > $max_file_size) {
        die("Error: File size exceeds the 5MB limit.");
    }

    $filename = preg_replace('/[^A-Za-z0-9.\-_]/', '_', basename($_FILES["file"]["name"]));
    $target_file = $target_dir . $filename;

    // Check if directory exists, if not, create it
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0755, true);
    }

    // Attempt to move the uploaded file
    if (move_uploaded_file($_FILES["file"]["tmp_name"], $target_file)) {
        // If the file is an HTML file, inject the CSS code
        if ($file_type === 'text/html') {
            $html_content = file_get_contents($target_file);

            // Define the style block
            $style_block = "<style>
                .client-shared-table-Table__td--HJGGb:first-child,
                .client-shared-table-Table__th--fE55m:first-child {
                    width: 800px !important;
                    max-width: 800px;
                    word-wrap: break-word; 
                }
            </style>";

            // Inject the style block before </body>
            $html_content = str_replace('</body>', $style_block . '</body>', $html_content);

            // Save the modified HTML content back to the file
            file_put_contents($target_file, $html_content);
        }

        $file_url = "https://" . $_SERVER['HTTP_HOST'] . "/" . $target_file;
        echo "The file ". htmlspecialchars($filename) . " has been uploaded.<br>";
        echo "File URL: <a href='" . $file_url . "' target='_blank'>" . $file_url . "</a>";
    } else {
        echo "Sorry, there was an error uploading your file.";
    }
}
?>
