<!DOCTYPE html>
<!-- 医生管理.php ==> doctor_manage.php -->
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>医生列表</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f4f4;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }
        h1 {
            color: #333;
            font-size: 32px;
            margin-bottom: 20px;
        }
        form {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 10px;
        }
        input[type="text"], input[type="submit"] {
            padding: 8px;
            margin-bottom: 10px;
            border-radius: 4px;
            border: 1px solid #ccc;
        }
        input[type="submit"] {
            background-color: #008CBA;
            color: #fff;
            border: none;
            cursor: pointer;
        }
        input[type="submit"]:hover {
            background-color: #008CBA;
        }
        p {
            margin: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table th, table td {
            border: 1px solid #ccc;
            padding: 8px;
        }
        table th {
            background-color: #008CBA;
            color: #fff;
        }
        a.button {
            display: inline-block;
            padding: 8px 15px;
            margin-right: 10px;
            background-color: #008CBA;
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
        }

        a.button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>医生列表</h1>

        <!-- 查询科室的表单 -->
        <form method="post">
            <label for="departmentName">输入科室名称查询医生：</label>
            <input type="text" id="departmentName" name="departmentName">
            <input type="submit" value="查询">
        </form>

        <?php
        // 连接数据库
        include '../db_connection.php'; // 包含数据库连接文件

        // 查询departments表内容
        $sql = "SELECT * FROM departments
                ORDER BY DepartmentID ASC";
        $result = $conn->query($sql);

        // 显示departments表内容
        if ($result->num_rows > 0) {
            echo "<h2>科室列表：</h2>";
            echo "<table>";
            echo "<tr><th>科室ID</th><th>科室名称</th></tr>";
            while($row = $result->fetch_assoc()) {
                echo "<tr><td>" . $row["DepartmentID"]. "</td><td>" . $row["Department"]. "</td></tr>";
            }
            echo "</table>";
        } else {
            echo "<p>0 结果</p>";
        }

        // 查询指定科室的医生
        if (isset($_POST['departmentName'])) {
            $departmentName = $_POST['departmentName'];
            getDoctorsByDepartment($conn, $departmentName);
        }

        // 查询指定科室的医生
        function getDoctorsByDepartment($conn, $departmentName) {
        // 使用预处理语句防止SQL注入
            $sql = "SELECT doctors.DoctorID, doctors.FullName, departments.Department
            FROM doctors
            INNER JOIN departments ON doctors.DepartmentID = departments.DepartmentID
            WHERE departments.Department = ?
            ORDER BY doctors.DoctorID ASC";

            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $departmentName);
            $stmt->execute();
            $result = $stmt->get_result();

            // 显示指定科室的医生
            if ($result->num_rows > 0) {
                echo "<h2>指定科室的医生：</h2>";
                echo "<table>";
                echo "<tr><th>医生ID</th><th>医生姓名</th><th>所属科室</th></tr>";
                while($row = $result->fetch_assoc()) {
                    echo "<tr><td>" . $row["DoctorID"]. "</td><td>" . $row["FullName"]. "</td><td>" . $row["Department"]. "</td></tr>";
                }
                echo "</table>";
            } else {
                echo "<p>未找到该科室的医生</p>";
            }

            $stmt->close();
        }
        $conn->close();
        ?>
    <a class="button" href="admin.html">返回到管理员仪表盘</a>
    <a class="button" href="logout.php">注销登录</a>
    </div>
</body>
</html>
