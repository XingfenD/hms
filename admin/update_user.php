<?php
session_start();
include '../db_connection.php';
if ($_SESSION["UserType"] !== "admin") {
    header("Location: index.html");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 获取表单提交的数据
    $user_id = $_POST["UserID"];
    $user_name = $_POST["UserName"];
    $original_user_type = $_POST["original_user_type"];
    $new_password = $_POST["new_password"];

    // 哈希新密码
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

    // 使用预处理语句更新用户密码
    $stmt = $conn->prepare("UPDATE users SET PasswordHash=? WHERE UserID=? AND Username=? AND UserType=?");
    $stmt->bind_param("ssss", $hashed_password, $user_id, $user_name, $original_user_type);

    if ($stmt->execute() === TRUE) {
        // 用户密码更新成功，输出提示信息
        echo "<script>alert('用户密码更新成功');</script>";
    } else {
        echo "Error: " . $stmt->error;
    }

    // 关闭预处理语句和连接
    $stmt->close();
    $conn->close();

    // 返回用户管理页面
    echo "<script>window.location.href='user_manage.php';</script>";
}
?>
