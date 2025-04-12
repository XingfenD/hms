<?php
/* 用户管理.php ==> user_manage.php */
session_start();
if ($_SESSION["UserType"] !== "admin") {
    header("Location: index.html");
    exit();
}

include '../db_connection.php'; // 包含数据库连接文件

// 查询医生用户
$sql_doctor = "SELECT * FROM Users WHERE UserType='doctor'";
$result_doctor = $conn->query($sql_doctor);

// 查询患者用户
$sql_patient = "SELECT * FROM Users WHERE UserType='patient'";
$result_patient = $conn->query($sql_patient);

// 查询管理员用户
$sql_admin = "SELECT * FROM Users WHERE UserType='admin'";
$result_admin = $conn->query($sql_admin);
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>用户管理</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f2f2f2;
            color: #333;
        }

        .container {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        h1 {
            text-align: center;
        }

        h2 {
            margin-top: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }

        form {
            margin-top: 20px;
        }

        form label {
            display: block;
            margin-bottom: 5px;
        }

        form input[type="text"],
        form input[type="password"],
        form input[type="number"],
        form select {
            width: calc(100% - 22px);
            padding: 10px;
            margin: 5px 0 15px;
            border: 1px solid #ccc;
            border-radius: 10px;
            box-sizing: border-box;
        }

        form input[type="submit"] {
            background-color: #4CAF50;
            color: white;
            padding: 14px 20px;
            margin-top: 10px;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            width: 100%;
            display: block;
        }

        form input[type="submit"]:hover {
            background-color: #45a049;
        }

        a.button {
            text-align: center;
            display: block;
            margin: 20px auto;
            padding: 10px 20px;
            background-color: #008CBA;
            color: white;
            text-decoration: none;
            border-radius: 10px;
        }
    </style>
    <script>
        function showFields() {
            var userType = document.getElementById("user_type").value;
            var doctorFields = document.getElementById("doctor_fields");
            var patientFields = document.getElementById("patient_fields");

            doctorFields.style.display = userType === "doctor" ? "block" : "none";
            patientFields.style.display = userType === "patient" ? "block" : "none";
        }
    </script>
</head>
<body>
    <div class="container">
        <h1>用户管理</h1>

        <h2>用户详情</h2>
        <table>
            <tr>
                <th>医生</th>
                <th>患者</th>
                <th>管理员</th>
            </tr>
            <tr>
                <td valign="top">
                    <?php
                    if ($result_doctor->num_rows > 0) {
                        echo "<ul>";
                        while ($row = $result_doctor->fetch_assoc()) {
                            echo "<li>ID: " . $row["UserID"] . " - 用户名: " . $row["Username"] . "</li>";
                        }
                        echo "</ul>";
                    } else {
                        echo "没有医生用户";
                    }
                    ?>
                </td>
                <td valign="top">
                    <?php
                    if ($result_patient->num_rows > 0) {
                        echo "<ul>";
                        while ($row = $result_patient->fetch_assoc()) {
                            echo "<li>ID: " . $row["UserID"] . " - 用户名: " . $row["Username"] . "</li>";
                        }
                        echo "</ul>";
                    } else {
                        echo "没有患者用户";
                    }
                    ?>
                </td>
                <td valign="top">
                    <?php
                    if ($result_admin->num_rows > 0) {
                        echo "<ul>";
                        while ($row = $result_admin->fetch_assoc()) {
                            echo "<li>ID: " . $row["UserID"] . " - 用户名: " . $row["Username"] . "</li>";
                        }
                        echo "</ul>";
                    } else {
                        echo "没有管理员用户";
                    }
                    ?>
                </td>
            </tr>
        </table>

        <!-- 删除用户表单 -->
        <h2>删除用户</h2>
        <form action="delete_user.php" method="post">
            <label for="UserID">要删除的用户ID：</label>
            <input type="text" id="UserID" name="UserID" required><br>
            <label for="UserType">用户类型：</label>
            <select id="UserType" name="UserType" required>
                <option value="doctor">医生</option>
                <option value="patient">患者</option>
                <option value="admin">管理员</option>
            </select><br>
            <input type="submit" value="删除用户">
        </form>

        <!-- 添加用户表单 -->
        <h2>添加用户</h2>
        <form action="add_user.php" method="post">
            <label for="new_userid">新用户ID：</label>
            <input type="number" id="new_userid" name="new_userid" required><br>
            <label for="new_username">新用户名：</label>
            <input type="text" id="new_username" name="new_username" required><br>
            <label for="new_password">密码：</label>
            <input type="password" id="new_password" name="new_password" required><br>
            <label for="user_type">用户类型：</label>
            <select id="user_type" name="user_type" required onchange="showFields()">
                <option value="">请选择</option>
                <option value="doctor">医生</option>
                <option value="patient">患者</option>
                <option value="admin">管理员</option>
            </select><br>
            <div id="doctor_fields" style="display:none;">
                <label for="department_id">科室：</label>
                <select id="department_id" name="department_id">
                    <option value="">请选择</option>
                    <option value="1">内科</option>
                    <option value="2">外科</option>
                    <option value="3">儿科</option>
                    <option value="4">妇产科</option>
                    <option value="5">眼科</option>
                </select><br>
            </div>
            <div id="patient_fields" style="display:none;">
                <label for="gender">性别：</label>
                <select id="gender" name="gender">
                    <option value="男">男</option>
                    <option value="女">女</option>
                    <option value="其他">其他</option>
                </select><br>
                <label for="age">年龄：</label>
                <input type="number" id="age" name="age" min="0"><br>
            </div>
            <input type="submit" value="添加用户">
        </form>

        <!-- 修改用户表单 -->
        <h2>修改用户密码</h2>
        <form action="update_user.php" method="post">
            <label for="UserID">用户ID：</label>
            <input type="text" id="UserID" name="UserID" required><br>
            <label for="UserName">用户名：</label>
            <input type="text" id="UserName" name="UserName" required><br>
            <label for="original_user_type">原用户类型：</label>
            <select id="original_user_type" name="original_user_type" required>
                <option value="doctor">医生</option>
                <option value="patient">患者</option>
                <option value="admin">管理员</option>
            </select><br>
            <label for="new_password">新密码：</label>
            <input type="password" id="new_password" name="new_password"><br>
            <input type="submit" value="修改用户密码">
        </form>
    <a class="button" href="admin.html">返回到管理员仪表盘</a>
    <a class="button" href="logout.php">注销登录</a>
</div>
</body>
</html>