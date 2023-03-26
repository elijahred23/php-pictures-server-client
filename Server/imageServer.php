<?php

$uploadsDir = 'images';
$foldersFile = 'folders.json';

// Ensure uploads directory exists
if (!is_dir($uploadsDir)) {
    mkdir($uploadsDir);
}

// Ensure folders JSON file exists
if (!file_exists($foldersFile)) {
    file_put_contents($foldersFile, json_encode([]));
}

// Load folders from JSON file
$folders = json_decode(file_get_contents($foldersFile), true);

// Handle image upload request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['image_url']) && isset($_POST['folder_name'])) {
    // Get image URL and folder name from POST request
    $imageUrl = $_POST['image_url'];
    $folderName = $_POST['folder_name'];

    // Validate input
    if (empty($imageUrl) || empty($folderName)) {
        http_response_code(400);
        die('Error: Invalid input');
    }

    // Ensure folder exists, or create it
    $folderPath = "$uploadsDir/$folderName";
    if (!is_dir($folderPath)) {
        mkdir($folderPath);
        $folders[$folderName] = [];
    }

    // Download image from URL
    $filename = basename($imageUrl);
    $filepath = "$folderPath/$filename";
    if (file_exists($filepath)) {
        http_response_code(400);
        die('Error: File already exists');
    }
    file_put_contents($filepath, file_get_contents($imageUrl));

    // Add image to folder list
    $folders[$folderName][] = $filename;

    // Save folders to JSON file
    file_put_contents($foldersFile, json_encode($folders));

    // Return success message
    echo "Image uploaded to $folderName folder";
}

// Handle folder list request
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'folders') {
    // Return list of folders and images
    header('Content-Type: application/json');
    echo json_encode($folders);
}
