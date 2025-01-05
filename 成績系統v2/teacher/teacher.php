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

// 處理 GET 請求 - 獲取教師資料
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $sql = "SELECT i.name, i.eid, i.tel, i.department, i.job_rank 
            FROM instructors i 
            WHERE i.eid = ?";
    
    $stmt = mysqli_prepare($link, $sql);
    mysqli_stmt_bind_param($stmt, "s", $_SESSION['eid']);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($row = mysqli_fetch_assoc($result)) {
        header('Content-Type: application/json');
        echo json_encode($row);
    }
}

// 處理 POST 請求 - 更新教師資料
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tel = $_POST['tel'];
    $oldPassword = $_POST['oldPassword'];
    $newPassword = $_POST['newPassword'];
    
    // 驗證當前密碼
    $check_pwd = "SELECT * FROM admin WHERE eid = ? AND psw = ?";
    $stmt = mysqli_prepare($link, $check_pwd);
    mysqli_stmt_bind_param($stmt, "ss", $_SESSION['eid'], $oldPassword);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($result) === 0) {
        echo json_encode(['success' => false, 'message' => '目前密碼錯誤！']);
        exit();
    }
    
    // 開始交易
    mysqli_begin_transaction($link);
    try {
        // 更新電話
        $update_info = "UPDATE instructors SET tel = ? WHERE eid = ?";
        $stmt = mysqli_prepare($link, $update_info);
        mysqli_stmt_bind_param($stmt, "ss", $tel, $_SESSION['eid']);
        mysqli_stmt_execute($stmt);
        
        // 如果有新密碼，則更新密碼
        if (!empty($newPassword)) {
            $update_pwd = "UPDATE admin SET psw = ? WHERE eid = ?";
            $stmt = mysqli_prepare($link, $update_pwd);
            mysqli_stmt_bind_param($stmt, "ss", $newPassword, $_SESSION['eid']);
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