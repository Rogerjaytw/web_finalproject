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

$eid = $_GET['eid'];
$password = $_GET['psw'];

if (isset($eid) && isset($password)) {
    $sql = "SELECT eid FROM admin WHERE eid = ? AND psw = ?";
            
    $stmt = mysqli_prepare($link, $sql);
    mysqli_stmt_bind_param($stmt, "ss", $eid, $password);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($row = mysqli_fetch_assoc($result)) {
        $_SESSION['eid'] = $row['eid'];
        $_SESSION['psw'] = $row['psw'];
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