<?php
session_start();
$location = "localhost";
$account = "root";
$password = "root";

if(isset($location) && isset($account) && isset($password)) {
    $link = @mysqli_connect($location, $account, $password);
    if(!$link) {
        echo "連接失敗: " . mysqli_connect_error();
        exit();
    }
}

$database = "Stuschool";
$select_db = @mysqli_select_db($link, $database);

$sid = $_GET['sid'];
$password = $_GET['psw'];

if (isset($sid) && isset($password)) {
    // 使用預處理語句防止 SQL 注入
    $sql = "SELECT s.sid, s.name 
            FROM usr u 
            JOIN students s ON u.sid = s.sid 
            WHERE u.sid = ? AND u.psw = ?";
            
    $stmt = mysqli_prepare($link, $sql);
    mysqli_stmt_bind_param($stmt, "ss", $sid, $password);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($row = mysqli_fetch_assoc($result)) {
        $_SESSION['sid'] = $row['sid'];
        $_SESSION['name'] = $row['name'];
        header("Location: main.html");
        exit();
    } else {
        echo "帳號或密碼錯誤";
    }
} else {
    echo "請輸入帳號密碼";
}

mysqli_close($link);
?>