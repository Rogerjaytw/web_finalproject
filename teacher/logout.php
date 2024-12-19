<?php
session_start();
session_destroy();  // 清除所有session數據
header("Location: login.html");
exit();
?>