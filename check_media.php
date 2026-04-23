<?php
$conn = mysqli_connect('localhost', 'kbelstisokolicz', 'itipenib', 'kbelstisokolicz');
if (!$conn) {
    $conn = mysqli_connect('db.dw194.webglobe.com', 'kbelstisokolicz', 'itipenib', 'kbelstisokolicz');
}

if (!$conn) {
    die('Connect Error (' . mysqli_connect_errno() . ') ' . mysqli_connect_error() . "\n");
}

echo "Checking media table for player_photos...\n";
$res = mysqli_query($conn, "SELECT id, model_id, collection_name, disk, file_name, custom_properties FROM media WHERE collection_name = 'player_photos' LIMIT 20");
if (!$res) {
    die('Query Error: ' . mysqli_error($conn) . "\n");
}

while ($row = mysqli_fetch_assoc($res)) {
    echo "ID: {$row['id']}, UserID: {$row['model_id']}, Disk: {$row['disk']}, File: {$row['file_name']}\n";
    // Check path if possible? No, we don't have the path generator logic here, but we can see the disk.
}

mysqli_close($conn);
