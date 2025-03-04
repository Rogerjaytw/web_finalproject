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

// GET 請求 - 獲取課程列表或學生列表
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (!isset($_GET['c_no'])) {
        // 獲取教師的課程列表
        $sql = "SELECT c.c_no, c.title, c.semester 
                FROM courses c 
                JOIN instructor_courses ic ON c.c_no = ic.c_no 
                WHERE ic.eid = ?
                ORDER BY c.semester DESC, c.c_no";
        
        $stmt = mysqli_prepare($link, $sql);
        mysqli_stmt_bind_param($stmt, "s", $_SESSION['eid']);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        $courses = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $courses[] = $row;
        }
        
        echo json_encode(['courses' => $courses]);
    } else {
        // 檢查教師是否有權限查看此課程
        $check_sql = "SELECT 1 FROM instructor_courses WHERE eid = ? AND c_no = ?";
        $check_stmt = mysqli_prepare($link, $check_sql);
        mysqli_stmt_bind_param($check_stmt, "ss", $_SESSION['eid'], $_GET['c_no']);
        mysqli_stmt_execute($check_stmt);
        $check_result = mysqli_stmt_get_result($check_stmt);
        
        if (mysqli_num_rows($check_result) == 0) {
            echo json_encode(['message' => '您沒有權限查看此課程']);
            exit();
        }

        // 獲取特定課程的學生列表和成績
        $sql = "SELECT DISTINCT s.sid, s.name, 
                MAX(CASE WHEN sg.exam_type = '期中' THEN sg.score END) as midterm,
                MAX(CASE WHEN sg.exam_type = '期末' THEN sg.score END) as final
                FROM enrollments e 
                JOIN students s ON e.sid = s.sid 
                LEFT JOIN student_grades sg ON s.sid = sg.sid AND e.c_no = sg.c_no 
                WHERE e.c_no = ? AND e.status = '已選'
                GROUP BY s.sid, s.name
                ORDER BY s.sid";
        
        error_log("教師ID: " . $_SESSION['eid']);
        error_log("課程編號: " . $_GET['c_no']);
        error_log("SQL查詢: " . $sql);
        
        $stmt = mysqli_prepare($link, $sql);
        mysqli_stmt_bind_param($stmt, "s", $_GET['c_no']);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        error_log("查詢結果數量: " . mysqli_num_rows($result));
        
        if (mysqli_num_rows($result) == 0) {
            // 檢查課程是否存在
            $course_check = "SELECT 1 FROM courses WHERE c_no = ?";
            $check_stmt = mysqli_prepare($link, $course_check);
            mysqli_stmt_bind_param($check_stmt, "s", $_GET['c_no']);
            mysqli_stmt_execute($check_stmt);
            
            if (mysqli_num_rows(mysqli_stmt_get_result($check_stmt)) == 0) {
                echo json_encode(['message' => '課程不存在']);
            } else {
                echo json_encode(['message' => '尚未有人選修']);
            }
            exit();
        }
        
        $students = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $students[] = $row;
            error_log("學生資料: " . print_r($row, true));
        }
        
        echo json_encode(['students' => $students]);
    }
}

// POST 請求 - 更新成績
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    mysqli_begin_transaction($link);
    try {
        // 更新或插入期中考成績
        if ($data['midterm'] !== null) {
            $sql = "INSERT INTO student_grades (sid, c_no, exam_type, score) 
                   VALUES (?, ?, '期中', ?)
                   ON DUPLICATE KEY UPDATE score = ?";
            $stmt = mysqli_prepare($link, $sql);
            mysqli_stmt_bind_param($stmt, "ssdd", 
                $data['sid'], $data['c_no'], 
                $data['midterm'], $data['midterm']);
            mysqli_stmt_execute($stmt);
        }
        
        // 更新或插入期末考成績
        if ($data['final'] !== null) {
            $sql = "INSERT INTO student_grades (sid, c_no, exam_type, score) 
                   VALUES (?, ?, '期末', ?)
                   ON DUPLICATE KEY UPDATE score = ?";
            $stmt = mysqli_prepare($link, $sql);
            mysqli_stmt_bind_param($stmt, "ssdd", 
                $data['sid'], $data['c_no'], 
                $data['final'], $data['final']);
            mysqli_stmt_execute($stmt);
        }
        
        mysqli_commit($link);
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        mysqli_rollback($link);
        echo json_encode([
            'success' => false, 
            'message' => '更新失敗：' . $e->getMessage()
        ]);
    }
}

mysqli_close($link);
?>
