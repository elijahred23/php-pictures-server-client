<?php
$UPLOADS_DIR = 'images';

// Ensure uploads directory exists
if (!is_dir($UPLOADS_DIR)) {
    mkdir($UPLOADS_DIR);
}

// Define folders JSON file
$FOLDERS_FILE = 'folders.json';

// Ensure folders JSON file exists
if (!file_exists($FOLDERS_FILE)) {
    file_put_contents($FOLDERS_FILE, json_encode([]));
}

// Load folders from JSON file
$folders = json_decode(file_get_contents($FOLDERS_FILE), true);

// Handle image upload
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get image URL and folder name from form data
    $image_url = $_POST['image_url'] ?? '';
    $folder_name = $_POST['folder_name'] ?? '';

    // Validate input
    if (empty($image_url) || empty($folder_name)) {
        die('Error: Invalid input');
    }

    // Ensure folder exists, or create it
    $folder_path = "$UPLOADS_DIR/$folder_name";
    if (!is_dir($folder_path)) {
        mkdir($folder_path);
        $folders[$folder_name] = [];
    }

    // Download image from URL
    $filename = basename($image_url);
    $filepath = "$folder_path/$filename";
    if (file_put_contents($filepath, file_get_contents($image_url))) {
        // Add image to folder list
        $folders[$folder_name][] = $filename;

        // Update folders JSON file
        file_put_contents($FOLDERS_FILE, json_encode($folders));

        // Redirect back to index page
        header('Location: /');
        exit;
    } else {
        die('Error: Failed to download image');
    }
}

// Handle folder list request
if ($_SERVER['REQUEST_METHOD'] === 'GET' && $_SERVER['REQUEST_URI'] === '/folders') {
    header('Content-Type: application/json');
    echo json_encode($folders);
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Image Uploader</title>
</head>
<body>
    <h1>Image Uploader</h1>
    <form method="post">
        <label>Image URL:</label>
        <input type="text" name="image_url"><br>
        <label>Folder Name:</label>
        <input type="text" name="folder_name"><br>
        <input type="submit" value="Upload">
    </form>
    <hr>
    <h2>Folder List</h2>
    <ul>
        <?php foreach ($folders as $folder_name => $images): ?>
            <li>
                <?php echo $folder_name; ?>
                <ul>
                    <?php foreach ($images as $image): ?>
                        <li><?php echo $image; ?></li>
                    <?php endforeach; ?>
                </ul>
            </li>
        <?php endforeach; ?>
    </ul>
</body>
</html>
