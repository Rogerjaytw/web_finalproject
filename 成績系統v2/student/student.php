<?php
session_start();
if (!isset($_SESSION['sid'])) {
    header('Location: login.html');
    exit();
}

$link = mysqli_connect("localhost", "root", "root", "Stuschool");
if (!$link) {
    die("連接失敗: " . mysqli_connect_error());
}

// 處理 GET 請求 - 獲取學生資料
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $sql = "SELECT s.name, s.sid, s.tel, s.birthday 
            FROM students s 
            WHERE s.sid = ?";
    
    $stmt = mysqli_prepare($link, $sql);
    mysqli_stmt_bind_param($stmt, "s", $_SESSION['sid']);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($row = mysqli_fetch_assoc($result)) {
        header('Content-Type: application/json');
        echo json_encode($row);
    }
}

// 處理 POST 請求 - 更新學生資料
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tel = $_POST['tel'];
    $birthday = $_POST['birthday'];
    $oldPassword = $_POST['oldPassword'];
    $newPassword = $_POST['newPassword'];
    
    // 轉換日期格式
    $birthday = date('Y-m-d', strtotime($birthday));
    
    // 驗證當前密碼
    $check_pwd = "SELECT * FROM usr WHERE sid = ? AND psw = ?";
    $stmt = mysqli_prepare($link, $check_pwd);
    mysqli_stmt_bind_param($stmt, "ss", $_SESSION['sid'], $oldPassword);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($result) === 0) {
        echo json_encode(['success' => false, 'message' => '目前密碼錯誤！']);
        exit();
    }
    
    // 開始交易
    mysqli_begin_transaction($link);
    try {
        // 更新電話和生日
        $update_info = "UPDATE students SET tel = ?, birthday = ? WHERE sid = ?";
        $stmt = mysqli_prepare($link, $update_info);
        mysqli_stmt_bind_param($stmt, "sss", $tel, $birthday, $_SESSION['sid']);
        mysqli_stmt_execute($stmt);
        
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("更新失敗：" . mysqli_error($link));
        }
        
        // 如果有新密碼，則更新密碼
        if (!empty($newPassword)) {
            $update_pwd = "UPDATE usr SET psw = ? WHERE sid = ?";
            $stmt = mysqli_prepare($link, $update_pwd);
            mysqli_stmt_bind_param($stmt, "ss", $newPassword, $_SESSION['sid']);
            mysqli_stmt_execute($stmt);
        }
        
        mysqli_commit($link);
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        mysqli_rollback($link);
        echo json_encode(['success' => false, 'message' => '更新失敗：' . $e->getMessage()]);
    }
}

mysqli_close($link);
?>