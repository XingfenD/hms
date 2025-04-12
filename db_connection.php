<?php
$servername = "localhost";
$username = "yiliao";
$password = "yiliao123";  // 数据库密码
$dbname = "yiliao";

// 创建连接
$conn = new mysqli($servername, $username, $password, $dbname);

// 检查连接
if ($conn->connect_error) {
    die("连接失败: " . $conn->connect_error);
}
?>
