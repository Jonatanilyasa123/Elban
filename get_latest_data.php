<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "newvoii";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed.']);
    exit;
}

// Fetch the latest file for each position
$positions = ['full_screen', 'left_bottom', 'right_bottom'];
$files = [];
$latestUpdate = [];

foreach ($positions as $position) {
    $sql = "SELECT file_name, file_type, created_at FROM media WHERE position = ? ORDER BY id DESC LIMIT 1";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to prepare SQL statement.']);
        exit;
    }
    $stmt->bind_param('s', $position);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result && $result->num_rows > 0) {
        $fileData = $result->fetch_assoc();
        $files[$position] = $fileData;
        $latestUpdate[$position] = $fileData['created_at'];
    } else {
        $files[$position] = null;
        $latestUpdate[$position] = null;
    }
    $stmt->close();
}

$response = [
    'files' => $files,
    'latestUpdate' => $latestUpdate
];

header('Content-Type: application/json');
echo json_encode($response);

$conn->close();
?>
