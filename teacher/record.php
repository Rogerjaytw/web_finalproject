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

// GET 請求 - 獲取記錄列表或單筆記錄
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['r_id'])) {
        // 獲取單筆記錄
        $sql = "SELECT * FROM record WHERE r_id = ?";
        $stmt = mysqli_prepare($link, $sql);
        mysqli_stmt_bind_param($stmt, "i", $_GET['r_id']);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        echo json_encode(mysqli_fetch_assoc($result));
    } else {
        // 獲取所有記錄
        $sql = "SELECT r.*, s.name 
                FROM record r 
                JOIN students s ON r.sid = s.sid 
                ORDER BY r.award_date DESC";
        $result = mysqli_query($link, $sql);
        $records = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $records[] = $row;
        }
        echo json_encode($records);
    }
}

// POST 請求 - 新增或更新記錄
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    // 檢查學號是否存在
    $check_sql = "SELECT sid FROM students WHERE sid = ?";
    $stmt = mysqli_prepare($link, $check_sql); // 準備參數
    mysqli_stmt_bind_param($stmt, "s", $data['sid']); // 綁定參數
    mysqli_stmt_execute($stmt); //執行語句
    $result = mysqli_stmt_get_result($stmt); //獲得解果
    if (mysqli_num_rows($result) == 0) { // 計算行數
        echo json_encode(['success' => false, 'message' => '學號不存在！']);
        exit();
    }
    
    if (empty($data['r_id'])) {
        // 新增記錄
        $sql = "INSERT INTO record (sid, award_type, award_date, description, quantity) 
                VALUES (?, ?, NOW(), ?, ?)";
        $stmt = mysqli_prepare($link, $sql);
        mysqli_stmt_bind_param($stmt, "sssi", 
            $data['sid'], 
            $data['award_type'], 
            $data['description'],
            $data['quantity']
        );
    } else {
        // 更新記錄
        $sql = "UPDATE record 
                SET sid = ?, award_type = ?, description = ?, quantity = ? 
                WHERE r_id = ?";
        $stmt = mysqli_prepare($link, $sql);
        mysqli_stmt_bind_param($stmt, "sssii", 
            $data['sid'], 
            $data['award_type'], 
            $data['description'],
            $data['quantity'],
            $data['r_id']
        );
    }
    
    if (mysqli_stmt_execute($stmt)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => mysqli_error($link)]);
    }
}

// DELETE 請求 - 刪除記錄
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $sql = "DELETE FROM record WHERE r_id = ?";
    $stmt = mysqli_prepare($link, $sql);
    mysqli_stmt_bind_param($stmt, "i", $data['r_id']);
    
    if (mysqli_stmt_execute($stmt)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => mysqli_error($link)]);
    }
}

mysqli_close($link);
?>