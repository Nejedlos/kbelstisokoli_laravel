<?php
$conn = mysqli_connect('localhost', 'kbelstisokolicz', 'itipenib', 'kbelstisokolicz');
if (!$conn) {
    // Try with different host?
    $conn = mysqli_connect('db.dw194.webglobe.com', 'kbelstisokolicz', 'itipenib', 'kbelstisokolicz');
}

if (!$conn) {
    die('Connect Error (' . mysqli_connect_errno() . ') ' . mysqli_connect_error() . "\n");
}

$res = mysqli_query($conn, "SELECT COUNT(*) as cnt FROM zapasy WHERE datum >= '2026-04-01'");
if (!$res) {
    die('Query Error: ' . mysqli_error($conn) . "\n");
}
$row = mysqli_fetch_assoc($res);
echo "Found " . $row['cnt'] . " records in April 2026\n";
mysqli_close($conn);
