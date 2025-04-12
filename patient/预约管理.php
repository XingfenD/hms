<?php
include 'db_connection.php';
session_start();

// 获取用户ID
$patient_id = $_SESSION["patient_login"];

// 检查用户是否已登录
if (empty($patient_id) || !is_numeric($patient_id)) {
    echo "请先登录。";
    exit();
}

// 处理取消预约请求
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["appointment_id"])) {
    $appointment_id = $_POST["appointment_id"];

    // 检查预约是否属于当前用户
    $check_sql = "SELECT * FROM appointments WHERE AppointmentID = ? AND PatientID = ?";
    $stmt = $conn->prepare($check_sql);
    $stmt->bind_param("ii", $appointment_id, $patient_id);
    $stmt->execute();
    $check_result = $stmt->get_result();

    if ($check_result->num_rows > 0) {
        // 删除预约记录
        $delete_sql = "DELETE FROM appointments WHERE AppointmentID = ?";
        $stmt = $conn->prepare($delete_sql);
        $stmt->bind_param("i", $appointment_id);
        if ($stmt->execute() === TRUE) {
            $message = "预约已成功取消。";
        } else {
            $message = "取消预约时出错：" . $conn->error;
        }
    } else {
        $message = "无效的预约ID或该预约不属于您。";
    }
}

// 获取用户的预约记录
$sql = "SELECT appointments.AppointmentID, appointments.AppointmentDate, appointments.AppointmentTime, appointments.AppointmentType, appointments.AppointmentStatus, doctors.FullName AS DoctorName, departments.Department AS DepartmentName 
        FROM appointments 
        INNER JOIN doctors ON appointments.DoctorID = doctors.DoctorID 
        INNER JOIN departments ON appointments.DepartmentID = departments.DepartmentID 
        WHERE appointments.PatientID = ? 
        ORDER BY appointments.AppointmentDate DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $patient_id);
$stmt->execute();
$result = $stmt->get_result();

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>预约管理</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }
        h1 {
            text-align: center;
            color: #333;
        }
        table, th, td {
            border: 1px solid black;
        }

        th, td {
            padding: 10px;
            text-align: left;
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
        .called {
            background-color: red;
            color: white;
        }
    </style>
</head>
<body>
    <h1>预约管理</h1>
    <?php if (!empty($message)) { echo "<p>$message</p>"; } ?>
    <?php if ($result->num_rows > 0) { ?>
        <table>
            <thead>
                <tr>
                    <th>预约日期</th>
                    <th>预约时间</th>
                    <th>预约类型</th>
                    <th>预约状态</th>
                    <th>医生</th>
                    <th>科室</th>
                    <th>操作</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()) { ?>
                    <tr>
                        <td><?php echo $row["AppointmentDate"]; ?></td>
                        <td><?php echo $row["AppointmentTime"]; ?></td>
                        <td><?php echo $row["AppointmentType"]; ?></td>
                        <td><?php echo $row["AppointmentStatus"]; ?></td>
                        <td><?php echo $row["DoctorName"]; ?></td>
                        <td><?php echo $row["DepartmentName"]; ?></td>
                        <td>
                            <?php if ($row["AppointmentStatus"] == "已预约") { ?>
                                <form action="预约管理.php" method="POST" onsubmit="return confirm('您确定要取消此预约吗？');">
                                    <input type="hidden" name="appointment_id" value="<?php echo $row["AppointmentID"]; ?>">
                                    <input type="submit" value="取消预约">
                                </form>
                            <?php } else { ?>
                                无法取消
                            <?php } ?>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    <?php } else { ?>
        <p>您没有任何预约记录。</p>
    <?php } ?>
	<a class="button" href="patient.html">返回到主页</a>
    <a class="button" href="logout.php">退出登录</a>
</body>
</html>
