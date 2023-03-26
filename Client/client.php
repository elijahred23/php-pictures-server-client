<?php

// Define server endpoint URL
$endpointUrl = 'http://localhost:3888/';

try {
    // Handle command line input
    if ($argc > 1) {
        $command = $argv[1];
        switch ($command) {
            case 'upload':
                // Get image URL and folder name from command line arguments
                if ($argc < 4) {
                    die("Error: Missing arguments\nUsage: php client.php upload <image_url> <folder_name>\n");
                }
                $imageUrl = $argv[2];
                $folderName = $argv[3];

                // Validate input
                if (empty($imageUrl) || empty($folderName)) {
                    die('Error: Invalid input');
                }

                // Send POST request to server to upload image
                $data = [
                    'image_url' => $imageUrl,
                    'folder_name' => $folderName,
                ];
                $options = [
                    'http' => [
                        'method' => 'POST',
                        'header' => 'Content-Type: application/x-www-form-urlencoded',
                        'content' => http_build_query($data),
                    ],
                ];
                $context = stream_context_create($options);
                $response = file_get_contents($endpointUrl, false, $context);
                echo "$response\n";
                break;
            case 'folders':
                // Send GET request to server to get list of folders and images
                $url = $endpointUrl . '?action=folders';
                $response = file_get_contents($url);
                $folders = json_decode($response, true);

                // Print list of folders and images
                foreach ($folders as $folderName => $imageFiles) {
                    echo "$folderName:\n";
                    foreach ($imageFiles as $imageFile) {
                        echo " - $imageFile\n";
                    }
                }
                break;

            default:
                die("Error: Invalid command\nUsage: php client.php [upload|folders]\n");
        }
    } else {
        // Display usage instructions
        echo "Usage: php client.php [upload|folders]\n";
    }
} catch (Exception $e) {
    echo $e->getMessage() . " " . $e->getLine() . " " . $e->getFile() . " " . $e->getTraceAsString();
}
