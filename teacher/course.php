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

// 處理 GET 請求 - 獲取教師的課程
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $sql = "SELECT c.c_no, c.title, c.credits, c.semester, c.day_of_week, 
            TIME_FORMAT(c.start_time, '%H:%i') as start_time, 
            TIME_FORMAT(c.end_time, '%H:%i') as end_time,
            (SELECT COUNT(*) FROM instructor_courses WHERE c_no = c.c_no) as teacher_count
            FROM courses c 
            JOIN instructor_courses ic ON c.c_no = ic.c_no
            WHERE ic.eid = ?
            ORDER BY c.semester DESC, c.day_of_week, c.start_time";
    
    $stmt = mysqli_prepare($link, $sql);
    mysqli_stmt_bind_param($stmt, "s", $_SESSION['eid']);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $courses = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $courses[] = $row;
    }
    
    // 查詢所有課程
    $all_courses_sql = "SELECT c.c_no, c.title, c.credits, c.semester, c.day_of_week, 
                        TIME_FORMAT(c.start_time, '%H:%i') as start_time, 
                        TIME_FORMAT(c.end_time, '%H:%i') as end_time,
                        GROUP_CONCAT(i.name SEPARATOR ', ') as instructor_names,
                        CASE WHEN ic2.eid IS NOT NULL THEN 1 ELSE 0 END as is_teaching
                        FROM courses c 
                        LEFT JOIN instructor_courses ic ON c.c_no = ic.c_no
                        LEFT JOIN instructors i ON ic.eid = i.eid
                        LEFT JOIN instructor_courses ic2 ON c.c_no = ic2.c_no AND ic2.eid = ?
                        GROUP BY c.c_no, c.title, c.credits, c.semester, c.day_of_week, 
                                c.start_time, c.end_time, is_teaching
                        ORDER BY c.semester DESC, c.day_of_week, c.start_time";

    $stmt = mysqli_prepare($link, $all_courses_sql);
    mysqli_stmt_bind_param($stmt, "s", $_SESSION['eid']);
    mysqli_stmt_execute($stmt);
    $all_result = mysqli_stmt_get_result($stmt);

    $all_courses = [];
    while ($row = mysqli_fetch_assoc($all_result)) {
        $all_courses[] = $row;
    }

    header('Content-Type: application/json');
    echo json_encode([
        'allCourses' => $all_courses,
        'myCourses' => $courses
    ]);
}

// 處理 POST 請求 - 建立新課程
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $c_no = $_POST['c_no'];
    $title = $_POST['title'];
    $credits = $_POST['credits'];
    $semester = $_POST['semester'];
    $day_of_week = $_POST['day_of_week'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];
    
    mysqli_begin_transaction($link);
    try {
        // 檢查課程是否存在
        $check_sql = "SELECT c.c_no 
                     FROM courses c 
                     JOIN instructor_courses ic ON c.c_no = ic.c_no 
                     WHERE c.c_no = ? AND ic.eid = ?";
        $stmt = mysqli_prepare($link, $check_sql);
        mysqli_stmt_bind_param($stmt, "ss", $c_no, $_SESSION['eid']);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $exists = mysqli_fetch_assoc($result);
        
        if ($exists) {
            // 更新現有課程
            $update_sql = "UPDATE courses 
                          SET title = ?, credits = ?, semester = ?, 
                              day_of_week = ?, start_time = ?, end_time = ? 
                          WHERE c_no = ?";
            $stmt = mysqli_prepare($link, $update_sql);
            mysqli_stmt_bind_param($stmt, "sisssss", 
                                 $title, $credits, $semester, 
                                 $day_of_week, $start_time, $end_time, $c_no);
            
            if (!mysqli_stmt_execute($stmt)) {
                throw new Exception("更新課程失敗：" . mysqli_error($link));
            }
        } else {
            // 新增課程
            $insert_course = "INSERT INTO courses (c_no, title, credits, semester, 
                                                 day_of_week, start_time, end_time) 
                            VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = mysqli_prepare($link, $insert_course);
            mysqli_stmt_bind_param($stmt, "ssissss", 
                                 $c_no, $title, $credits, semester, 
                                 $day_of_week, $start_time, $end_time);
            
            if (!mysqli_stmt_execute($stmt)) {
                throw new Exception("新增課程失敗：" . mysqli_error($link));
            }
            
            // 建立教師與課程的關聯
            $insert_ic = "INSERT INTO instructor_courses (eid, c_no) VALUES (?, ?)";
            $stmt = mysqli_prepare($link, $insert_ic);
            mysqli_stmt_bind_param($stmt, "ss", $_SESSION['eid'], $c_no);
            
            if (!mysqli_stmt_execute($stmt)) {
                throw new Exception("建立教師關聯失敗：" . mysqli_error($link));
            }
        }
        
        mysqli_commit($link);
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        mysqli_rollback($link);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit();
}

mysqli_close($link);
?>
