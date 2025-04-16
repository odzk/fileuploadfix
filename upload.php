<?php
// Set header to ensure proper content type and encoding
header('Content-Type: text/html; charset=utf-8');

// Start building the HTML structure
$head = '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>File Upload Result</title>
    <style>
        body {
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f7fa;
        }
        
        .container {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            padding: 30px;
            margin-top: 40px;
            text-align: center;
        }
        
        h2 {
            color: #2c3e50;
            margin-top: 0;
            margin-bottom: 25px;
            font-weight: 600;
        }
        
        .message {
            margin-bottom: 25px;
            font-size: 18px;
        }
        
        .success {
            color: #27ae60;
        }
        
        .error {
            color: #e74c3c;
        }
        
        .file-info {
            background-color: #f8f9fa;
            border-radius: 6px;
            padding: 15px;
            margin-bottom: 20px;
            word-wrap: break-word;
        }
        
        .file-link {
            display: inline-block;
            background-color: #3498db;
            color: white;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 5px;
            font-weight: 500;
            margin-top: 15px;
            transition: background-color 0.3s ease;
        }
        
        .file-link:hover {
            background-color: #2980b9;
        }
        
        .back-link {
            display: inline-block;
            margin-top: 20px;
            color: #7f8c8d;
            text-decoration: none;
        }
        
        .back-link:hover {
            color: #3498db;
            text-decoration: underline;
        }
        
        /* Responsive adjustments */
        @media (max-width: 600px) {
            .container {
                padding: 20px;
                margin-top: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">';

$foot = '        <a href="index.html" class="back-link">← Back to Upload Form</a>
    </div>
</body>
</html>';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Directory where file will be uploaded
    $target_dir = "uploads/";
    $output = '';

    // Validate and sanitize file input
    if (!isset($_FILES["file"]) || $_FILES["file"]["error"] !== UPLOAD_ERR_OK) {
        $error_code = isset($_FILES["file"]) ? $_FILES["file"]["error"] : "unknown";
        $output = '<h2>Upload Failed</h2>
        <p class="message error">Error: File upload error code ' . $error_code . '</p>';
        echo $head . $output . $foot;
        exit;
    }

    $allowed_types = ['image/jpeg', 'image/png', 'application/pdf', 'text/html'];
    $file_type = mime_content_type($_FILES["file"]["tmp_name"]);

    if (!in_array($file_type, $allowed_types)) {
        $output = '<h2>Upload Failed</h2>
        <p class="message error">Error: Only JPEG, PNG, PDF, and HTML files are allowed.</p>';
        echo $head . $output . $foot;
        exit;
    }

    $max_file_size = 5 * 1024 * 1024; // 5 MB
    if ($_FILES["file"]["size"] > $max_file_size) {
        $output = '<h2>Upload Failed</h2>
        <p class="message error">Error: File size exceeds the 5MB limit.</p>';
        echo $head . $output . $foot;
        exit;
    }

    $filename = preg_replace('/[^A-Za-z0-9.\-_]/', '_', basename($_FILES["file"]["name"]));
    $target_file = $target_dir . $filename;
    $file_size_kb = round($_FILES["file"]["size"] / 1024, 2);

    // Check if directory exists, if not, create it
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0755, true);
    }

    // Attempt to move the uploaded file
    if (move_uploaded_file($_FILES["file"]["tmp_name"], $target_file)) {
        // If the file is an HTML file, inject the CSS code
        if ($file_type === 'text/html') {
            $html_content = file_get_contents($target_file);

            // Define the style block with relative sizing
            $style_block = "<style>
                .client-shared-table-Table__tr--f-a8J {
                    width: 2000px !important;
                }
                .client-shared-table-Table__td--HJGGb:first-child,
                .client-shared-table-Table__th--fE55m:first-child {
                    width: 700px !important;
                    max-width: 700px !important;
                    word-wrap: break-word; 
                }
                
                /* Make table responsive */
                table {
                    width: 700px !important;
                    max-width: 700px !important;
                    table-layout: fixed !important;
                }
                
                /* Ensure all cells can wrap text as needed */
                td, th {
                    word-wrap: break-word !important;
                    overflow-wrap: break-word !important;
                }
            </style>";

            // Inject the style block before </body>
            $html_content = str_replace('</body>', $style_block . '</body>', $html_content);

            // Save the modified HTML content back to the file
            file_put_contents($target_file, $html_content);
        }

        $file_url = "https://" . $_SERVER['HTTP_HOST'] . "/" . $target_file;
        
        // Get file type name for display
        $file_type_name = "File";
        switch ($file_type) {
            case 'image/jpeg':
                $file_type_name = "JPEG Image";
                break;
            case 'image/png':
                $file_type_name = "PNG Image";
                break;
            case 'application/pdf':
                $file_type_name = "PDF Document";
                break;
            case 'text/html':
                $file_type_name = "HTML Document";
                break;
        }
        
        $output = '<h2>Upload Successful</h2>
        <p class="message success">Your file has been uploaded successfully!</p>
        <div class="file-info">
            <p><strong>Filename:</strong> ' . htmlspecialchars($filename) . '</p>
            <p><strong>Type:</strong> ' . $file_type_name . '</p>
            <p><strong>Size:</strong> ' . $file_size_kb . ' KB</p>
        </div>
        <a href="' . $file_url . '" class="file-link" target="_blank">View File</a>';
    } else {
        $output = '<h2>Upload Failed</h2>
        <p class="message error">Sorry, there was an error uploading your file.</p>';
    }
    
    echo $head . $output . $foot;
}
?>