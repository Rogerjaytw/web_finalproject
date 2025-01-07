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
    try {
        // 獲取教師的課程
        $sql = "SELECT c.c_no, c.title, c.credits, c.semester, c.day_of_week, 
                TIME_FORMAT(c.start_time, '%H:%i') as start_time, 
                TIME_FORMAT(c.end_time, '%H:%i') as end_time
                FROM courses c 
                JOIN instructor_courses ic ON c.c_no = ic.c_no
                WHERE ic.eid = ?
                ORDER BY c.semester DESC, c.day_of_week, c.start_time";
        
        $stmt = mysqli_prepare($link, $sql);
        mysqli_stmt_bind_param($stmt, "s", $_SESSION['eid']);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        $my_courses = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $weekdays = ['', '星期一', '星期二', '星期三', '星期四', '星期五'];
            $row['day_of_week'] = $weekdays[(int)$row['day_of_week']];
            $my_courses[] = $row;
        }
        
        // 獲取所有課程
        $all_courses_sql = "SELECT 
            c.c_no, 
            c.title, 
            c.credits, 
            c.semester, 
            c.day_of_week, 
            TIME_FORMAT(c.start_time, '%H:%i') as start_time, 
            TIME_FORMAT(c.end_time, '%H:%i') as end_time,
            GROUP_CONCAT(DISTINCT i.name SEPARATOR ', ') as instructor_names,
            MAX(CASE WHEN ic2.eid = ? THEN 1 ELSE 0 END) as is_teaching
            FROM courses c 
            LEFT JOIN instructor_courses ic ON c.c_no = ic.c_no
            LEFT JOIN instructors i ON ic.eid = i.eid
            LEFT JOIN instructor_courses ic2 ON c.c_no = ic2.c_no AND ic2.eid = ?
            GROUP BY c.c_no, c.title, c.credits, c.semester, c.day_of_week, 
                     c.start_time, c.end_time
            ORDER BY c.semester DESC, c.day_of_week, c.start_time";

        $stmt = mysqli_prepare($link, $all_courses_sql);
        mysqli_stmt_bind_param($stmt, "ss", $_SESSION['eid'], $_SESSION['eid']);
        mysqli_stmt_execute($stmt);
        $all_result = mysqli_stmt_get_result($stmt);

        $all_courses = [];
        while ($row = mysqli_fetch_assoc($all_result)) {
            // 將數字星期幾轉換為中文
            $weekdays = ['', '星期一', '星期二', '星期三', '星期四', '星期五', '星期六', '星期日'];
            $row['day_of_week'] = $weekdays[date('N', strtotime($row['day_of_week']))];
            $all_courses[] = $row;
        }

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => true,
            'allCourses' => $all_courses,
            'myCourses' => $my_courses
        ], JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
        
    } catch (Exception $e) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ], JSON_UNESCAPED_UNICODE);
    }
    exit();
}

// 處理 POST 請求 - 新增或更新課程
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    mysqli_begin_transaction($link);
    try {
        $c_no = $data['c_no'];
        $title = $data['title'];
        $credits = $data['credits'];
        $semester = $data['semester'];
        $day_of_week = $data['day_of_week'];
        $start_time = $data['start_time'];
        $end_time = $data['end_time'];

        // 檢查課程是否已存在
        $check_sql = "SELECT c_no FROM courses WHERE c_no = ?";
        $stmt = mysqli_prepare($link, $check_sql);
        mysqli_stmt_bind_param($stmt, "s", $c_no);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if (mysqli_fetch_assoc($result)) {
            // 更新現有課程
            $update_sql = "UPDATE courses SET title = ?, credits = ?, semester = ?, 
                            day_of_week = ?, start_time = ?, end_time = ? WHERE c_no = ?";
            $stmt = mysqli_prepare($link, $update_sql);
            mysqli_stmt_bind_param($stmt, "sisssss", $title, $credits, $semester, 
                                    $day_of_week, $start_time, $end_time, $c_no);
            mysqli_stmt_execute($stmt);
        } else {
            // 新增課程
            $insert_sql = "INSERT INTO courses (c_no, title, credits, semester, day_of_week, 
                            start_time, end_time) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = mysqli_prepare($link, $insert_sql);
            mysqli_stmt_bind_param($stmt, "ssissss", $c_no, $title, $credits, $semester, 
                                    $day_of_week, $start_time, $end_time);
            mysqli_stmt_execute($stmt);

            // 新增教師關聯
            $insert_ic_sql = "INSERT INTO instructor_courses (eid, c_no) VALUES (?, ?)";
            $stmt = mysqli_prepare($link, $insert_ic_sql);
            mysqli_stmt_bind_param($stmt, "ss", $_SESSION['eid'], $c_no);
            mysqli_stmt_execute($stmt);
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
