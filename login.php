<?php
session_start();
include 'db_connection.php'; // 包含数据库连接文件
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 接收表单提交的数据
    $username = $_POST['username'];
    $password = $_POST['password'];
    $role = $_POST['role'];

    // 准备并绑定
    $stmt = $conn->prepare("SELECT * FROM users WHERE Username=? AND UserType=?");
    $stmt->bind_param("ss", $username, $role);

    // 执行查询
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        // 获取数据库中存储的密码
        $row = $result->fetch_assoc();
        $stored_password_hash = $row["PasswordHash"];
        $user_id = $row["UserID"];

        // 比较密码哈希
        if (password_verify($password, $stored_password_hash)) {
            // 登录成功，根据用户角色重定向到不同页面，并设置会话变量
            switch ($role) {
                case 'doctor':
                    $_SESSION["doctor_login"] = $user_id;
                    header("Location: doctor/doctor.html");
                    break;
                case 'patient':
                    $_SESSION["patient_login"] = $user_id;
                    header("Location: patient/patient.html");
                    break;
                case 'admin':
                    $_SESSION["UserType"] = "admin";
                    header("Location: admin/admin.html");
                    break;
                default:
                    echo "未知角色";
            }
        } else {
            // 密码错误，重定向回登录页面并传递密码错误提示参数
            header("Location: index.html?error=password");
        }
    } else {
        // 用户不存在，重定向回登录页面并传递用户不存在提示参数
        header("Location: index.html?error=user_not_found");
    }

    // 关闭语句和连接
    $stmt->close();
    $conn->close();
}
?>
