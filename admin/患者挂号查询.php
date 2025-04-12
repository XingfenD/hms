<?php
// 数据库连接配置
include 'db_connection.php'; // 包含数据库连接文件
session_start();

if ($_SESSION["UserType"] !== "admin") {
    header("Location: index.html");
    exit();
}

if(isset($_GET['patient_id']) && !empty($_GET['patient_id'])){
    $patient_id = $_GET['patient_id'];

    // 使用预处理语句查询预约记录
    $stmt = $conn->prepare("SELECT * FROM appointments WHERE PatientID=? AND AppointmentStatus <> '已取消' ORDER BY AppointmentDate DESC, AppointmentTime DESC");
    $stmt->bind_param('i', $patient_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo "<table border='3'>
        <tr>
        <th>预约ID</th>
        <th>患者ID</th>
        <th>医生ID</th>
        <th>预约日期</th>
        <th>预约时间</th>
        <th>预约类型</th>
        <th>预约状态</th>
        <th>科室</th>
        </tr>";

        while($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>".$row['AppointmentID']."</td>";
            echo "<td>".$row['PatientID']."</td>";
            echo "<td>".$row['DoctorID']."</td>";
            echo "<td>".$row['AppointmentDate']."</td>";
            echo "<td>".$row['AppointmentTime']."</td>";
            echo "<td>".$row['AppointmentType']."</td>";
            echo "<td>".$row['AppointmentStatus']."</td>";
            echo "<td>".$row['DepartmentID']."</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "没有找到患者ID号为 $patient_id 的预约记录.";
    }
    $stmt->close();
}

// 关闭数据库连接
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>患者挂号记录查询</title>
    <style>
        h1 {
            text-align: center;
            color: #333;
        }
        .button {
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
</head>
<body>
    <h2>患者挂号记录查询</h2>
    <form method="get" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
        <label for="patient_id">请输入患者ID号：</label>
        <input type="text" id="patient_id" name="patient_id">
        <input type="submit" value="查询">
    </form>
    <a class="button" href="admin.html">返回到管理员仪表盘</a>
    <a class="button" href="logout.php">注销登录</a>
</body>
</html>
