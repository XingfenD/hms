<?php
session_start();
include '../db_connection.php';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 检查用户是否登录且为管理员
    if ($_SESSION["UserType"] !== "admin") {
        header("Location: index.html");
        exit();
    }

    // 处理删除用户请求
    $user_id = $_POST["UserID"];
    $user_type = $_POST["UserType"];

    // 使用预处理语句来执行查询
    $stmt_check = $conn->prepare("SELECT * FROM users WHERE UserID=? and UserType=?");
    $stmt_check->bind_param("ss", $user_id, $user_type);
    $stmt_check->execute();
    $check_result = $stmt_check->get_result();

    if ($check_result->num_rows > 0) {
        // 用户存在，执行删除操作
        $stmt_delete_user = $conn->prepare("DELETE FROM users WHERE UserID=? and UserType=?");
        $stmt_delete_user->bind_param("ss", $user_id, $user_type);

        // 根据用户类型删除相关表中的信息
        if ($user_type === "doctor") {
            $stmt_delete_related_table = $conn->prepare("DELETE FROM doctors WHERE DoctorID=?");
        } elseif ($user_type === "patient") {
            $stmt_delete_related_table = $conn->prepare("DELETE FROM patients WHERE PatientID=?");
        }

        // 开始事务
        $conn->begin_transaction();

        try {
            // 删除用户信息
            $stmt_delete_user->execute();

            // 删除相关表中的信息
            if (isset($stmt_delete_related_table)) {
                $stmt_delete_related_table->bind_param("s", $user_id);
                $stmt_delete_related_table->execute();
            }

            // 提交事务
            $conn->commit();

            // 用户删除成功，输出提示信息
            echo "<script>alert('用户删除成功');</script>";
        } catch (Exception $e) {
            // 如果有任何异常发生，回滚事务
            $conn->rollback();
            echo "Error: " . $e->getMessage();
        }
    } else {
        // 用户不存在
        echo "<script>alert('用户不存在');</script>";
    }

    // 关闭预处理语句和连接
    $stmt_check->close();
    if (isset($stmt_delete_user)) {
        $stmt_delete_user->close();
    }
    if (isset($stmt_delete_related_table)) {
        $stmt_delete_related_table->close();
    }
    $conn->close();

    // 返回用户管理页面
    echo "<script>window.location.href='user_manage.php';</script>";
}
?>
