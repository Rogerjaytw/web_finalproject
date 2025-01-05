<?php
session_start();
$sid = $_SESSION['sid'];

$link = mysqli_connect("localhost", "root", "root", "Stuschool");
if (!$link) {
    die("連接失敗: " . mysqli_connect_error());
}

$sql = "SELECT c.title, sg.exam_type, sg.score, c.semester
        FROM student_grades sg
        JOIN courses c ON sg.c_no = c.c_no
        WHERE sg.sid = ?
        ORDER BY c.semester DESC, c.title";

$stmt = mysqli_prepare($link, $sql);
mysqli_stmt_bind_param($stmt, "s", $sid);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$scores = [];
while ($row = mysqli_fetch_assoc($result)) {
    $scores[] = $row;
}

// 查詢 GPA
$sql = "SELECT GPA FROM students WHERE sid = ?";
$stmt = mysqli_prepare($link, $sql);
mysqli_stmt_bind_param($stmt, "s", $sid);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_assoc($result);
$gpa = ($row && isset($row['GPA'])) ? number_format($row['GPA'], 2) : null;

mysqli_close($link);

// 確保返回正確的 JSON 格式
header('Content-Type: application/json');
try {
    $response = [
        'scores' => $scores,
        'gpa' => $gpa
    ];
    echo json_encode($response);
} catch (Exception $e) {
    echo json_encode([
        'scores' => [],
        'gpa' => null,
        'error' => '數據處理錯誤'
    ]);
}
?>