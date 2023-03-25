<?php

class ImageClient
{
    private $socket;

    public function __construct($host = 'localhost', $port = 3888)
    {
        $this->socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        if (!$this->socket) {
            throw new Exception("Unable to create socket: " . socket_strerror(socket_last_error()));
        }

        if (!socket_connect($this->socket, $host, $port)) {
            throw new Exception("Unable to connect to socket: " . socket_strerror(socket_last_error()));
        }
    }

    public function __destruct()
    {
        socket_close($this->socket);
    }

    public function uploadImage($name, $folder = '', $file = null, $url = null)
    {
        $params = [
            'name' => $name,
            'folder' => $folder,
        ];

        if ($file) {
            if (!file_exists($file)) {
                return "Error: File not found.\n";
            }

            $params['file'] = new CURLFile($file);
        } else if ($url) {
            $params['url'] = $url;
        } else {
            return "Error: No image specified.\n";
        }

        $request = $this->buildRequest('POST', '/upload', $params);

        return $this->sendRequest($request);
    }

    public function listImages()
    {
        $request = $this->buildRequest('GET', '/list');

        return $this->sendRequest($request);
    }

    private function buildRequest($method, $path, $params = [])
    {
        $request = "$method $path";

        if ($params) {
            $queryParams = http_build_query($params);
            $request .= "?$queryParams";
        }

        $request .= " HTTP/1.1\r\n";
        $request .= "Host: localhost:3888\r\n";
        $request .= "Connection: close\r\n";
        $request .= "\r\n";

        return $request;
    }

    private function sendRequest($request)
    {
        socket_write($this->socket, $request, strlen($request));

        $response = '';
        while ($data = socket_read($this->socket, 1024)) {
            $response .= $data;
        }

        return $response;
    }
}

$usage = "Usage: php client.php <command> [<args>]\n\n" .
    "Commands:\n" .
    "  upload <name> [--folder=<folder>] [--file=<file>|--url=<url>]\n" .
    "  list\n";

if (count($argv) < 2) {
    echo $usage;
    exit(1);
}

try {
    $client = new ImageClient();

    $command = $argv[1];
    if ($command === 'upload') {
        $name = $argv[2];

        $folder = '';
        if (isset($argv[3]) && substr($argv[3], 0, 8) === '--folder') {
            list($opt, $folder) = explode('=', $argv[3], 2);
        }

        $file = null;
        if (isset($argv[4]) && substr($argv[4], 0, 6) === '--file') {
            list($opt, $file) = explode('=', $argv[4], 2);
        }

        $url = null;
        if (isset($argv[4]) && substr($argv[4], 0, 5) === '--url') {
            list($opt, $url) = explode('=', $argv[4], 2);
        }

        $response = $client->uploadImage($name, $folder, $file, $url);
        echo $response;
    } else if ($command === 'list') {
        $response = $client->listImages();
        echo $response;
    } else {
        echo $usage;
        exit(1);
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
