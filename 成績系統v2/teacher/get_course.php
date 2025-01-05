<?php
session_start();
if (!isset($_SESSION['eid'])) {
    header('Location: login.html');
    exit();
}

$link = mysqli_connect("localhost", "root", "root", "Stuschool");
if (!$link) {
    die("連接失敗: " . mysqli_connect_error());
}

$c_no = $_GET['c_no'];

$sql = "SELECT c.* 
        FROM courses c 
        JOIN instructor_courses ic ON c.c_no = ic.c_no 
        WHERE c.c_no = ? AND ic.eid = ?";

$stmt = mysqli_prepare($link, $sql);
mysqli_stmt_bind_param($stmt, "ss", $c_no, $_SESSION['eid']);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($row = mysqli_fetch_assoc($result)) {
    $row['start_time'] = substr($row['start_time'], 0, 5);
    $row['end_time'] = substr($row['end_time'], 0, 5);
    header('Content-Type: application/json');
    echo json_encode($row);
} else {
    echo json_encode(['error' => '找不到課程']);
}

mysqli_close($link);
?>