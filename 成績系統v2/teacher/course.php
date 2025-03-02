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
if ($_SERVER['REQUEST_METHOD'] === 'GET' && !isset($_GET['c_no'])) {
    try {
        // 獲取我的課程
        $sql = "SELECT c.c_no, c.title, c.credits, c.semester, c.day_of_week, 
                TIME_FORMAT(c.start_time, '%H:%i') as start_time, 
                TIME_FORMAT(c.end_time, '%H:%i') as end_time,
                (SELECT COUNT(*) FROM instructor_courses WHERE c_no = c.c_no) as instructor_count
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
            $my_courses[] = $row;
        }
        
        // 獲取所有課程
        $all_courses_sql = "SELECT c.c_no, c.title, c.credits, c.semester, c.day_of_week, 
                           TIME_FORMAT(c.start_time, '%H:%i') as start_time, 
                           TIME_FORMAT(c.end_time, '%H:%i') as end_time,
                           GROUP_CONCAT(i.name SEPARATOR ', ') as instructor_names,
                           COUNT(DISTINCT ic.eid) as instructor_count,
                           CASE WHEN ic2.eid IS NOT NULL THEN 1 ELSE 0 END as is_teaching
                           FROM courses c 
                           LEFT JOIN instructor_courses ic ON c.c_no = ic.c_no
                           LEFT JOIN instructors i ON ic.eid = i.eid
                           LEFT JOIN instructor_courses ic2 ON c.c_no = ic2.c_no AND ic2.eid = ?
                           GROUP BY c.c_no, c.title, c.credits, c.semester, c.day_of_week, 
                                   c.start_time, c.end_time, is_teaching";

        $stmt = mysqli_prepare($link, $all_courses_sql);
        mysqli_stmt_bind_param($stmt, "s", $_SESSION['eid']);
        mysqli_stmt_execute($stmt);
        $all_result = mysqli_stmt_get_result($stmt);

        $all_courses = [];
        while ($row = mysqli_fetch_assoc($all_result)) {
            $all_courses[] = $row;
        }

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => true,
            'allCourses' => $all_courses,
            'myCourses' => $my_courses
        ], JSON_UNESCAPED_UNICODE);
        
    } catch (Exception $e) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ], JSON_UNESCAPED_UNICODE);
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
    
    // 將數字轉換為對應的星期幾
    $day_mapping = [
        '1' => 'Monday',
        '2' => 'Tuesday',
        '3' => 'Wednesday',
        '4' => 'Thursday',
        '5' => 'Friday',
    ];
    $day_of_week = $day_mapping[$_POST['day_of_week']];
    
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
                                 $c_no, $title, $credits, $semester, 
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

// 處理 GET 請求 - 獲取單一課程資訊
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['c_no'])) {
    try {
        $sql = "SELECT c.c_no, c.title, c.credits, c.semester, c.day_of_week, 
                TIME_FORMAT(c.start_time, '%H:%i') as start_time, 
                TIME_FORMAT(c.end_time, '%H:%i') as end_time
                FROM courses c 
                JOIN instructor_courses ic ON c.c_no = ic.c_no 
                WHERE c.c_no = ? AND ic.eid = ?";
                
        $stmt = mysqli_prepare($link, $sql);
        mysqli_stmt_bind_param($stmt, "ss", $_GET['c_no'], $_SESSION['eid']);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($course = mysqli_fetch_assoc($result)) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'success' => true,
                'data' => $course
            ], JSON_UNESCAPED_UNICODE);
        } else {
            throw new Exception("找不到課程或無權限查看");
        }
    } catch (Exception $e) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ], JSON_UNESCAPED_UNICODE);
    }
    exit();
}

// 處理 POST 請求
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $data = json_decode(file_get_contents('php://input'), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("JSON 解析錯誤：" . json_last_error_msg());
        }
        
        mysqli_begin_transaction($link);
        
        if (isset($data['action'])) {
            if ($data['action'] === 'join') {
                // 加入課程
                $insert_sql = "INSERT INTO instructor_courses (eid, c_no) VALUES (?, ?)";
                $stmt = mysqli_prepare($link, $insert_sql);
                mysqli_stmt_bind_param($stmt, "ss", $_SESSION['eid'], $data['c_no']);
                
                if (!mysqli_stmt_execute($stmt)) {
                    throw new Exception("加入課程失敗：" . mysqli_error($link));
                }
            } else if ($data['action'] === 'leave') {
                // 退出課程
                $delete_sql = "DELETE FROM instructor_courses WHERE eid = ? AND c_no = ?";
                $stmt = mysqli_prepare($link, $delete_sql);
                mysqli_stmt_bind_param($stmt, "ss", $_SESSION['eid'], $data['c_no']);
                
                if (!mysqli_stmt_execute($stmt)) {
                    throw new Exception("退出課程失敗：" . mysqli_error($link));
                }
            } else if ($data['action'] === 'delete') {
                // 檢查是否為唯一教師
                $check_sql = "SELECT COUNT(*) as count FROM instructor_courses WHERE c_no = ?";
                $check_stmt = mysqli_prepare($link, $check_sql);
                mysqli_stmt_bind_param($check_stmt, "s", $data['c_no']);
                mysqli_stmt_execute($check_stmt);
                $result = mysqli_stmt_get_result($check_stmt);
                $row = mysqli_fetch_assoc($result);
                
                if ($row['count'] > 1) {
                    throw new Exception("此課程還有其他教授，無法刪除");
                }
                
                // 開始刪除課程
                mysqli_begin_transaction($link);
                try {
                    // 1. 先刪除學生成績記錄
                    $delete_grades = "DELETE FROM student_grades WHERE c_no = ?";
                    $stmt = mysqli_prepare($link, $delete_grades);
                    mysqli_stmt_bind_param($stmt, "s", $data['c_no']);
                    mysqli_stmt_execute($stmt);

                    // 2. 刪除課程班級關聯
                    $delete_classes = "DELETE FROM classes WHERE c_no = ?";
                    $stmt = mysqli_prepare($link, $delete_classes);
                    mysqli_stmt_bind_param($stmt, "s", $data['c_no']);
                    mysqli_stmt_execute($stmt);

                    // 3. 刪除教師課程關聯
                    $delete_ic = "DELETE FROM instructor_courses WHERE c_no = ?";
                    $stmt = mysqli_prepare($link, $delete_ic);
                    mysqli_stmt_bind_param($stmt, "s", $data['c_no']);
                    mysqli_stmt_execute($stmt);

                    // 4. 刪除所有使用相同學期的選課記錄
                    $delete_enrollments = "DELETE FROM enrollments WHERE semester IN (
                        SELECT semester FROM courses WHERE c_no = ?
                    )";
                    $stmt = mysqli_prepare($link, $delete_enrollments);
                    mysqli_stmt_bind_param($stmt, "s", $data['c_no']);
                    mysqli_stmt_execute($stmt);

                    // 5. 最後刪除課程本身
                    $delete_course = "DELETE FROM courses WHERE c_no = ?";
                    $stmt = mysqli_prepare($link, $delete_course);
                    mysqli_stmt_bind_param($stmt, "s", $data['c_no']);
                    mysqli_stmt_execute($stmt);

                    mysqli_commit($link);
                } catch (Exception $e) {
                    mysqli_rollback($link);
                    throw new Exception("刪除課程失敗：" . $e->getMessage());
                }
            }
        } else {
            // 檢查必要欄位
            $required_fields = ['c_no', 'title', 'credits', 'semester', 'day_of_week', 'start_time', 'end_time'];
            foreach ($required_fields as $field) {
                if (!isset($data[$field]) || $data[$field] === '') {
                    throw new Exception("缺少必要欄位：" . $field);
                }
            }

            // 檢查是新增還是更新
            $check_sql = "SELECT c_no FROM courses WHERE c_no = ?";
            $check_stmt = mysqli_prepare($link, $check_sql);
            if (!$check_stmt) {
                throw new Exception("準備檢查語句失敗：" . mysqli_error($link));
            }
            
            mysqli_stmt_bind_param($check_stmt, "s", $data['c_no']);
            if (!mysqli_stmt_execute($check_stmt)) {
                throw new Exception("執行檢查失敗：" . mysqli_error($link));
            }
            
            $check_result = mysqli_stmt_get_result($check_stmt);
            $exists = mysqli_fetch_assoc($check_result);

            // 檢查教師權限
            $auth_sql = "SELECT 1 FROM instructor_courses WHERE eid = ? AND c_no = ?";
            $auth_stmt = mysqli_prepare($link, $auth_sql);
            if (!$auth_stmt) {
                throw new Exception("準備權限檢查失敗：" . mysqli_error($link));
            }
            
            mysqli_stmt_bind_param($auth_stmt, "ss", $_SESSION['eid'], $data['c_no']);
            if (!mysqli_stmt_execute($auth_stmt)) {
                throw new Exception("執行權限檢查失敗：" . mysqli_error($link));
            }
            
            $auth_result = mysqli_stmt_get_result($auth_stmt);
            $has_auth = mysqli_fetch_assoc($auth_result);

            if ($exists) {
                // 更新課程
                if (!$has_auth) {
                    throw new Exception("您沒有權限修改此課程");
                }

                // 將數字轉換為英文星期
                $day_map = [
                    "1" => "Monday",
                    "2" => "Tuesday",
                    "3" => "Wednesday",
                    "4" => "Thursday",
                    "5" => "Friday",
                    "6" => "Saturday",
                    "7" => "Sunday"
                ];

                // 檢查並轉換 day_of_week
                $day_of_week = $day_map[$data['day_of_week']] ?? null;
                if (!$day_of_week) {
                    throw new Exception("無效的星期值：" . $data['day_of_week']);
                }

                // 轉換並驗證 credits
                $credits = intval($data['credits']);
                if ($credits <= 0) {
                    throw new Exception("學分數必須大於0");
                }

                // 驗證時間格式
                if (!preg_match('/^([01][0-9]|2[0-3]):[0-5][0-9]$/', $data['start_time']) ||
                    !preg_match('/^([01][0-9]|2[0-3]):[0-5][0-9]$/', $data['end_time'])) {
                    throw new Exception("時間格式無效");
                }

                $update_sql = "UPDATE courses 
                              SET title = ?, 
                                  credits = ?, 
                                  semester = ?, 
                                  day_of_week = ?, 
                                  start_time = ?, 
                                  end_time = ? 
                              WHERE c_no = ?";
                
                error_log("Executing update with params: " . print_r([
                    'title' => $data['title'],
                    'credits' => $credits,
                    'semester' => $data['semester'],
                    'day_of_week' => $day_of_week,
                    'start_time' => $data['start_time'],
                    'end_time' => $data['end_time'],
                    'c_no' => $data['c_no']
                ], true));

                $stmt = mysqli_prepare($link, $update_sql);
                if (!$stmt) {
                    throw new Exception("準備更新課句失敗：" . mysqli_error($link));
                }

                mysqli_stmt_bind_param($stmt, "sisssss", 
                    $data['title'],
                    $credits,
                    $data['semester'],
                    $day_of_week,
                    $data['start_time'],
                    $data['end_time'],
                    $data['c_no']
                );
                
                if (!mysqli_stmt_execute($stmt)) {
                    throw new Exception("執行更新失敗：" . mysqli_error($link));
                }
            } else {
                throw new Exception("找不到指定的課程");
            }
        }
        
        mysqli_commit($link);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => true,
            'message' => '操作成功'
        ], JSON_UNESCAPED_UNICODE);
    } catch (Exception $e) {
        if (isset($link)) {
            mysqli_rollback($link);
        }
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ], JSON_UNESCAPED_UNICODE);
    }
    exit();
}

mysqli_close($link);
?>
