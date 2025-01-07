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
    // 如果是最後一位教師，執行刪除課程操作
    mysqli_begin_transaction($link);
    try {
        // 1. 先刪除學生成績記錄
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

        // 4. 刪除所有使用相同學期的選課記錄
        $delete_enrollments = "DELETE FROM enrollments WHERE semester IN (
            SELECT semester FROM courses WHERE c_no = ?
        )";
        $stmt = mysqli_prepare($link, $delete_enrollments);
        mysqli_stmt_bind_param($stmt, "s", $c_no);
        mysqli_stmt_execute($stmt);

        // 5. 最後刪除課程本身
        $delete_course = "DELETE FROM courses WHERE c_no = ?";
        $stmt = mysqli_prepare($link, $delete_course);
        mysqli_stmt_bind_param($stmt, "s", $c_no);
        mysqli_stmt_execute($stmt);

        mysqli_commit($link);
        echo json_encode(['success' => true, 'message' => '課程已刪除']);
    } catch (Exception $e) {
        mysqli_rollback($link);
        echo json_encode(['success' => false, 'message' => '刪除課程失敗：' . $e->getMessage()]);
    }
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