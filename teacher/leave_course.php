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

// 檢查是否為唯一教師
$check_sql = "SELECT COUNT(*) as count FROM instructor_courses WHERE c_no = ?";
$stmt = mysqli_prepare($link, $check_sql);
mysqli_stmt_bind_param($stmt, "s", $c_no);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_assoc($result);

if ($row['count'] <= 1) {
    echo json_encode(['success' => false, 'message' => '您是唯一的授課教師，無法退出！']);
    exit();
}

// 執行退出操作
$delete_sql = "DELETE FROM instructor_courses WHERE eid = ? AND c_no = ?";
$stmt = mysqli_prepare($link, $delete_sql);
mysqli_stmt_bind_param($stmt, "ss", $eid, $c_no);

if (mysqli_stmt_execute($stmt)) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => '退出失敗：' . mysqli_error($link)]);
}

mysqli_close($link);
?>