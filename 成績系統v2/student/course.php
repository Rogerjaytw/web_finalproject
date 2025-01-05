<?php
session_start();
$sid = $_SESSION['sid'];

$link = mysqli_connect("localhost", "root", "root", "Stuschool");
if (!$link) {
    die("連接失敗: " . mysqli_connect_error());
}

// 查詢已選課程
$enrolled_sql = "SELECT c.c_no, c.title, c.credits, c.semester, c.day_of_week, 
                c.start_time, c.end_time, 
                GROUP_CONCAT(i.name SEPARATOR ', ') as instructor_name
                FROM enrollments en
                JOIN courses c ON en.c_no = c.c_no
                LEFT JOIN instructor_courses ic ON c.c_no = ic.c_no
                LEFT JOIN instructors i ON ic.eid = i.eid
                WHERE en.sid = ? AND en.status = '已選'
                GROUP BY c.c_no, c.title, c.credits, c.semester, c.day_of_week, 
                        c.start_time, c.end_time
                ORDER BY c.day_of_week, c.start_time";

$stmt = mysqli_prepare($link, $enrolled_sql);
mysqli_stmt_bind_param($stmt, "s", $sid);
mysqli_stmt_execute($stmt);
$enrolled_result = mysqli_stmt_get_result($stmt);

$enrolled_courses = [];
$total_credits = 0;
$enrolled_course_ids = [];

while ($row = mysqli_fetch_assoc($enrolled_result)) {
    $row['start_time'] = substr($row['start_time'], 0, 5);
    $row['end_time'] = substr($row['end_time'], 0, 5);
    $enrolled_courses[] = $row;
    $total_credits += $row['credits'];
    $enrolled_course_ids[] = $row['c_no'];
}

// 查詢可選課程
$available_sql = "SELECT DISTINCT c.c_no, c.title, c.credits, c.semester, c.day_of_week, 
                c.start_time, c.end_time, 
                GROUP_CONCAT(i.name SEPARATOR ', ') as instructor_name,
                CASE WHEN en.status IS NOT NULL THEN en.status ELSE '未選' END as enrollment_status
                FROM courses c 
                LEFT JOIN instructor_courses ic ON c.c_no = ic.c_no
                LEFT JOIN instructors i ON ic.eid = i.eid
                LEFT JOIN enrollments en ON c.c_no = en.c_no AND en.sid = ? AND en.status = '已選'
                WHERE c.semester = '1121' AND c.c_no NOT IN ('" . implode("','", $enrolled_course_ids) . "')
                GROUP BY c.c_no, c.title, c.credits, c.semester, c.day_of_week, 
                        c.start_time, c.end_time, enrollment_status
                ORDER BY c.day_of_week, c.start_time";

$stmt = mysqli_prepare($link, $available_sql);
mysqli_stmt_bind_param($stmt, "s", $sid);
mysqli_stmt_execute($stmt);
$available_result = mysqli_stmt_get_result($stmt);

$available_courses = [];
while ($row = mysqli_fetch_assoc($available_result)) {
    $row['start_time'] = substr($row['start_time'], 0, 5);
    $row['end_time'] = substr($row['end_time'], 0, 5);
    $available_courses[] = $row;
}

header('Content-Type: application/json');
echo json_encode([
    'enrolled' => $enrolled_courses,
    'available' => $available_courses,
    'totalCredits' => $total_credits
]);

mysqli_close($link);
?>