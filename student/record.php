<?php
session_start();
$sid = $_SESSION['sid'];

$link = mysqli_connect("localhost", "root", "root", "Stuschool");
if (!$link) {
    die("連接失敗: " . mysqli_connect_error());
}

$sql = "SELECT award_type, award_date, description, quantity 
        FROM record 
        WHERE sid = ?
        ORDER BY award_date DESC";

$stmt = mysqli_prepare($link, $sql);
mysqli_stmt_bind_param($stmt, "s", $sid);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$records = [];
while ($row = mysqli_fetch_assoc($result)) {
    $row['award_date'] = date('Y-m-d', strtotime($row['award_date']));
    $records[] = $row;
}

mysqli_close($link);

header('Content-Type: application/json');
echo json_encode(['records' => $records]);
?>