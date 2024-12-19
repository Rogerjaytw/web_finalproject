<?php
session_start();
$sid = $_SESSION['sid'];
$c_no = $_POST['c_no'];

$link = mysqli_connect("localhost", "root", "root", "Stuschool");
if (!$link) {
    die("連接失敗: " . mysqli_connect_error());
}

// 檢查是否確實已選這門課
$check_sql = "SELECT * FROM enrollments 
            WHERE sid = ? AND c_no = ? AND status = '已選'";
$stmt = mysqli_prepare($link, $check_sql);
mysqli_stmt_bind_param($stmt, "ss", $sid, $c_no);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) == 0) {
    echo "<script>alert('你尚未選修此課程！');window.location.href='course.html';</script>";
    exit();
}

// 執行退選
$delete_sql = "DELETE FROM enrollments WHERE sid = ? AND c_no = ?";
$stmt = mysqli_prepare($link, $delete_sql);
mysqli_stmt_bind_param($stmt, "ss", $sid, $c_no);

if (mysqli_stmt_execute($stmt)) {
    echo "<script>alert('退選成功！');window.location.href='course.html';</script>";
} else {
    echo "<script>alert('退選失敗！');window.location.href='course.html';</script>";
}

mysqli_close($link);
?>