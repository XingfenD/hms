<?php
session_start();
include '../db_connection.php';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 检查用户是否登录且为管理员
    if ($_SESSION["UserType"] !== "admin") {
        header("Location: index.html");
        exit();
    }

    // 获取表单提交的数据
    $new_userid = $_POST["new_userid"];
    $new_username = $_POST["new_username"];
    $new_password = $_POST["new_password"];
    $user_type = $_POST["user_type"];
    $department_id = $_POST["department_id"];

    $gender = $_POST["gender"];
    $age = $_POST["age"];

    // 使用 password_hash() 函数对密码进行哈希处理
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

    // 开始事务
    $conn->begin_transaction();

    try {
        // 插入新用户数据，使用参数化查询
        $stmt_user = $conn->prepare("INSERT INTO users (UserID, Username, PasswordHash, UserType) VALUES (?, ?, ?, ?)");
        $stmt_user->bind_param("ssss", $new_userid, $new_username, $hashed_password, $user_type);
        $stmt_user->execute();

        // 如果用户类型是医生，则插入医生表中
        if ($user_type === "doctor") {
            $stmt_doctor = $conn->prepare("INSERT INTO doctors (DoctorID, FullName, DepartmentID) VALUES (?, ?, ?)");
            $stmt_doctor->bind_param("ssi", $new_userid, $new_username, $department_id);
            $stmt_doctor->execute();
        }

        // 如果用户类型是患者，则插入患者表中
        if ($user_type === "patient") {
            $stmt_patient = $conn->prepare("INSERT INTO patients (PatientID, FullName, Gender, Age) VALUES (?, ?, ?, ?)");
            $stmt_patient->bind_param("sssi", $new_userid, $new_username, $gender, $age);
            $stmt_patient->execute();
        }

        // 如果所有操作都成功，提交事务
        $conn->commit();
        echo "<script>alert('用户添加成功');</script>";
    } catch (Exception $e) {
        // 如果有任何异常发生，回滚事务
        $conn->rollback();
        echo "Error: " . $e->getMessage();
    }

    // 关闭数据库连接
    $conn->close();

    // 返回用户管理页面
    echo "<script>window.location.href='user_manage.php';</script>";
}
?>
