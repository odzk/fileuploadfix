<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Directory where file will be uploaded
    $target_dir = "uploads/";

    // Get the uploaded file
    $target_file = $target_dir . basename($_FILES["file"]["name"]);

    // Check if directory exists, if not, create it
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    // Check if the file was uploaded successfully
    if (move_uploaded_file($_FILES["file"]["tmp_name"], $target_file)) {
        // Display success message with the file URL
        $file_url = "http://" . $_SERVER['HTTP_HOST'] . "/" . $target_file;
        echo "The file ". htmlspecialchars(basename($_FILES["file"]["name"])) . " has been uploaded.<br>";
        echo "File URL: <a href='" . $file_url . "' target='_blank'>" . $file_url . "</a>";
    } else {
        echo "Sorry, there was an error uploading your file.";
    }
}
?>
