<?php
session_start();
$sid = $_SESSION['sid'];
$c_no = $_POST['c_no'];
$semester = $_POST['semester'];

$link = mysqli_connect("localhost", "root", "root", "Stuschool");
if (!$link) {
    die("連接失敗: " . mysqli_connect_error());
}

// 檢查是否已經選過這門課
$check_sql = "SELECT * FROM enrollments WHERE sid = ? AND c_no = ?";
$stmt = mysqli_prepare($link, $check_sql);
mysqli_stmt_bind_param($stmt, "ss", $sid, $c_no);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) > 0) {
    echo "<script>alert('你已經選過這門課了！');window.location.href='course.php';</script>";
    exit();
}

// 檢查課程時間是否衝突
$check_time_sql = "SELECT c1.day_of_week, c1.start_time, c1.end_time
                    FROM courses c1
                    JOIN enrollments e ON c1.c_no = e.c_no
                    WHERE e.sid = ? AND e.semester = ? AND e.status = '已選'
                    AND c1.day_of_week = (SELECT day_of_week FROM courses WHERE c_no = ?)
                    AND (
                        (c1.start_time <= (SELECT start_time FROM courses WHERE c_no = ?) 
                        AND c1.end_time > (SELECT start_time FROM courses WHERE c_no = ?))
                        OR
                        (c1.start_time < (SELECT end_time FROM courses WHERE c_no = ?)
                        AND c1.end_time >= (SELECT end_time FROM courses WHERE c_no = ?))
                    )";

$stmt = mysqli_prepare($link, $check_time_sql);
mysqli_stmt_bind_param($stmt, "sssssss", $sid, $semester, $c_no, $c_no, $c_no, $c_no, $c_no);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) > 0) {
    echo "<script>alert('課程時間衝突！');window.location.href='course.html';</script>";
    exit();
}

// 執行選課
$insert_sql = "INSERT INTO enrollments (sid, c_no, semester, status) VALUES (?, ?, ?, '已選')";
$stmt = mysqli_prepare($link, $insert_sql);
mysqli_stmt_bind_param($stmt, "sss", $sid, $c_no, $semester);

if (mysqli_stmt_execute($stmt)) {
    echo "<script>alert('選課成功！');window.location.href='course.html';</script>";
} else {
    echo "<script>alert('選課失敗！');window.location.href='course.html';</script>";
}

mysqli_close($link);
?>