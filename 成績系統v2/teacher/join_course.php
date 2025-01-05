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

$c_no = $_POST['c_no'];
$eid = $_SESSION['eid'];

// 檢查是否已經是授課教師
$check_sql = "SELECT * FROM instructor_courses WHERE eid = ? AND c_no = ?";
$stmt = mysqli_prepare($link, $check_sql);
mysqli_stmt_bind_param($stmt, "ss", $eid, $c_no);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) > 0) {
    echo json_encode(['success' => false, 'message' => '您已經是此課程的授課教師！']);
    exit();
}

// 加入授課教師
$insert_sql = "INSERT INTO instructor_courses (eid, c_no) VALUES (?, ?)";
$stmt = mysqli_prepare($link, $insert_sql);
mysqli_stmt_bind_param($stmt, "ss", $eid, $c_no);

if (mysqli_stmt_execute($stmt)) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => '加入失敗！']);
}

mysqli_close($link);
?>
