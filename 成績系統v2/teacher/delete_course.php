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

// 檢查是否為課程的授課教師
$check_auth_sql = "SELECT 1 FROM instructor_courses WHERE eid = ? AND c_no = ?";
$stmt = mysqli_prepare($link, $check_auth_sql);
mysqli_stmt_bind_param($stmt, "ss", $eid, $c_no);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) == 0) {
    echo json_encode(['success' => false, 'message' => '您不是此課程的授課教師！']);
    exit();
}

// 檢查是否有學生選修此課程
$check_enrollments = "SELECT COUNT(*) as count FROM enrollments WHERE c_no = ? AND status = '已選'";
$stmt = mysqli_prepare($link, $check_enrollments);
mysqli_stmt_bind_param($stmt, "s", $c_no);
mysqli_stmt_execute($stmt);
$enrollment_result = mysqli_stmt_get_result($stmt);
$enrollment_count = mysqli_fetch_assoc($enrollment_result)['count'];

if ($enrollment_count > 0) {
    echo json_encode(['success' => false, 'message' => '無法刪除課程：目前有 ' . $enrollment_count . ' 位學生正在修習此課程！']);
    exit();
}

// 開始刪除課程
mysqli_begin_transaction($link);
try {
    // 1. 刪除學生成績記錄
    $delete_grades = "DELETE FROM student_grades WHERE c_no = ?";
    $stmt = mysqli_prepare($link, $delete_grades);
    mysqli_stmt_bind_param($stmt, "s", $c_no);
    mysqli_stmt_execute($stmt);

    // 2. 刪除課程班級關聯
    $delete_classes = "DELETE FROM classes WHERE c_no = ?";
    $stmt = mysqli_prepare($link, $delete_classes);
    mysqli_stmt_bind_param($stmt, "s", $c_no);
    mysqli_stmt_execute($stmt);

    // 3. 刪除教師課程關聯
    $delete_ic = "DELETE FROM instructor_courses WHERE c_no = ?";
    $stmt = mysqli_prepare($link, $delete_ic);
    mysqli_stmt_bind_param($stmt, "s", $c_no);
    mysqli_stmt_execute($stmt);


    // 4. 最後刪除課程本身
    $delete_course = "DELETE FROM courses WHERE c_no = ?";
    $stmt = mysqli_prepare($link, $delete_course);
    mysqli_stmt_bind_param($stmt, "s", $c_no);
    mysqli_stmt_execute($stmt);

    mysqli_commit($link);
    echo json_encode(['success' => true, 'message' => '課程已成功刪除！']);
} catch (Exception $e) {
    mysqli_rollback($link);
    echo json_encode(['success' => false, 'message' => '刪除課程失敗：' . $e->getMessage()]);
}

mysqli_close($link);
?> 