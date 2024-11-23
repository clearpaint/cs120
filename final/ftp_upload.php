<?php
session_start();

$debug = [];

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    echo json_encode(['message' => 'Unauthorized access', 'debug' => $debug]);
    exit;
}

// FTP server details
$config = include 'config.php';
$ftp_server = $config['ftp_server'];
$ftp_port = $config['ftp_port'];
$ftp_user = $config['ftp_user'];
$ftp_pass = $config['ftp_pass'];

if (isset($_FILES['file'])) {
    $file = $_FILES['file']['tmp_name'];
    $file_name = basename($_FILES['file']['name']);
    $remote_file = "uploads/" . $file_name;

    // Step 1: Connect to FTP server
    $ftp_conn = ftp_connect($ftp_server, $ftp_port);
    if ($ftp_conn) {
        $debug[] = "Connected to FTP server: $ftp_server:$ftp_port";
    } else {
        echo json_encode(['message' => 'Could not connect to FTP server', 'debug' => $debug]);
        exit;
    }

    // Step 2: Attempt FTP login
    $login = ftp_login($ftp_conn, $ftp_user, $ftp_pass);
    if ($login) {
        $debug[] = "FTP login successful for user: $ftp_user";
        ftp_pasv($ftp_conn, true); // Enable passive mode
    } else {
        $ftp_response = ftp_raw($ftp_conn, 'NOOP');
        $debug[] = "FTP login failed for user: $ftp_user";
        $debug[] = "FTP Server Response: " . implode(" | ", $ftp_response);
        ftp_close($ftp_conn);
        echo json_encode(['message' => 'FTP login failed', 'debug' => $debug]);
        exit;
    }

    // Step 3: Upload the file
    if (ftp_put($ftp_conn, $remote_file, $file, FTP_BINARY)) {
        $debug[] = "File uploaded successfully to: $remote_file";

        // Step 4: Call API with FTP file URL
        $file_url = $config['ftp_domain'] . urlencode($file_name);
        $api_key =  $config['api_key'];
        $api_secret =  $config['api_secret'];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://api.imagga.com/v2/tags?image_url=" . urlencode($file_url));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_USERPWD, "$api_key:$api_secret");

        $response = curl_exec($ch);
        $curl_error = curl_error($ch);
        curl_close($ch);

        if ($response) {
            $debug[] = "API call successful for file: $file_url";
            $data = json_decode($response, true);
        } else {
            $debug[] = "API call failed: $curl_error";
            echo json_encode(['message' => 'API call failed', 'debug' => $debug]);
            ftp_close($ftp_conn);
            exit;
        }

        // Step 5: Write metadata based on API response
        if (isset($data['result']['tags'])) {
            $tags = array_map(function($tag) {
                return $tag['tag']['en'];
            }, $data['result']['tags']);
            $description = implode(", ", $tags);

            $metadata_content = "Image Description: " . $description . "\nUploaded File Name: " . $file_name;
            $metadata_file = "uploads/" . pathinfo($file_name, PATHINFO_FILENAME) . "_metadata.txt";

            $temp_metadata_file = tempnam(sys_get_temp_dir(), 'meta');
            file_put_contents($temp_metadata_file, $metadata_content);

            if (ftp_put($ftp_conn, $metadata_file, $temp_metadata_file, FTP_ASCII)) {
                $debug[] = "Metadata file uploaded successfully to: $metadata_file";
                echo json_encode([
                    'message' => 'File and metadata uploaded successfully',
                    'description' => $description,
                    'debug' => $debug
                ]);
            } else {
                $debug[] = "Failed to upload metadata file to: $metadata_file";
                echo json_encode(['message' => 'Failed to upload metadata file', 'debug' => $debug]);
            }

            unlink($temp_metadata_file);
        } else {
            $debug[] = "No tags found in API response.";
            echo json_encode(['message' => 'Failed to get description from API', 'debug' => $debug]);
        }
    } else {
        $debug[] = "Failed to upload file to FTP: $remote_file";
        echo json_encode(['message' => 'Failed to upload file', 'debug' => $debug]);
    }

    ftp_close($ftp_conn);
} else {
    $debug[] = "No file was uploaded";
    echo json_encode(['message' => 'No file was uploaded', 'debug' => $debug]);
}
?>
