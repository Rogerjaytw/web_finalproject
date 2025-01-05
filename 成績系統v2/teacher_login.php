<?php
session_start();

$location = "localhost";
$account  = "root";
$password = "root";
$database = "Stuschool";

// 連接資料庫
$link = mysqli_connect($location, $account, $password, $database);
if (!$link) {
    die("連接失敗: " . mysqli_connect_error());
}

// 取得 GET 參數 (若是 POST，請改用 $_POST)
$eid      = $_GET['eid'] ?? '';
$psw      = $_GET['psw'] ?? '';

// 若有輸入才做查詢
if (!empty($eid) && !empty($psw)) {
    // 使用預處理語句，防止 SQL 注入
    $sql = "SELECT eid, eid
            FROM admin
            WHERE eid = ? AND psw = ?";
    $stmt = mysqli_prepare($link, $sql);
    mysqli_stmt_bind_param($stmt, "ss", $eid, $psw);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    // 驗證結果
    if ($row = mysqli_fetch_assoc($result)) {
        // 存 Session，視需求而定
        $_SESSION['eid']  = $row['eid'];
        $_SESSION['eid'] = $row['eid'];
        
        // 成功則導向教師專屬資料夾
        header("Location: teacher/main.php");
        exit();
    } else {
        echo "帳號或密碼錯誤";
    }
} else {
    echo "請輸入帳號密碼";
}

mysqli_close($link);
?>