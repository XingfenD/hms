<?php
/* 呼叫患者.php ==> call_patient.php */
include '../db_connection.php';
session_start();

// 获取医生ID
$doctor_id = $_SESSION["doctor_login"];

// 检查医生是否已登录
if (empty($doctor_id) || !is_numeric($doctor_id)) {
    echo "请先登录。";
    exit();
}

// 处理呼叫患者和更新状态请求
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["appointment_id"]) && isset($_POST["action"])) {
    $appointment_id = $_POST["appointment_id"];
    $action = $_POST["action"];

    // 检查预约是否属于当前医生
    $check_sql = "SELECT * FROM appointments WHERE AppointmentID = ? AND DoctorID = ?";
    $stmt = $conn->prepare($check_sql);
    $stmt->bind_param("ii", $appointment_id, $doctor_id);
    $stmt->execute();
    $check_result = $stmt->get_result();

    if ($check_result->num_rows > 0) {
        if ($action == "call") {
            // 更新预约状态为已呼叫
            $update_sql = "UPDATE appointments SET AppointmentStatus = '已呼叫' WHERE AppointmentID = ?";
            $stmt = $conn->prepare($update_sql);
            $stmt->bind_param("i", $appointment_id);
            if ($stmt->execute() === TRUE) {
                $message = "患者已成功呼叫。";
            } else {
                $message = "呼叫患者时出错：" . $stmt->error;
            }
        } elseif ($action == "update") {
            $new_status = $_POST["new_status"];
            // 更新预约状态
            $update_sql = "UPDATE appointments SET AppointmentStatus = ? WHERE AppointmentID = ?";
            $stmt = $conn->prepare($update_sql);
            $stmt->bind_param("si", $new_status, $appointment_id);
            if ($stmt->execute() === TRUE) {
                $message = "预约状态已更新。";
            } else {
                $message = "更新预约状态时出错：" . $stmt->error;
            }
        }
    } else {
        $message = "无效的预约ID或该预约不属于您。";
    }
    $stmt->close();
}

// 获取医生的排队患者、已呼叫患者和已就诊患者的预约记录
$queue_sql = "SELECT appointments.AppointmentID, appointments.AppointmentDate, appointments.AppointmentTime, appointments.AppointmentType, appointments.AppointmentStatus, patients.FullName AS PatientName, departments.Department AS DepartmentName 
        FROM appointments 
        INNER JOIN patients ON appointments.PatientID = patients.PatientID 
        INNER JOIN departments ON appointments.DepartmentID = departments.DepartmentID 
        WHERE appointments.DoctorID = ? AND appointments.AppointmentStatus = '已预约'
        ORDER BY appointments.AppointmentDate DESC";
$stmt = $conn->prepare($queue_sql);
$stmt->bind_param("i", $doctor_id);
$stmt->execute();
$queue_result = $stmt->get_result();

$called_sql = "SELECT appointments.AppointmentID, appointments.AppointmentDate, appointments.AppointmentTime, appointments.AppointmentType, appointments.AppointmentStatus, patients.FullName AS PatientName, departments.Department AS DepartmentName 
        FROM appointments 
        INNER JOIN patients ON appointments.PatientID = patients.PatientID 
        INNER JOIN departments ON appointments.DepartmentID = departments.DepartmentID 
        WHERE appointments.DoctorID = ? AND appointments.AppointmentStatus = '已呼叫'
        ORDER BY appointments.AppointmentDate DESC";
$stmt = $conn->prepare($called_sql);
$stmt->bind_param("i", $doctor_id);
$stmt->execute();
$called_result = $stmt->get_result();

$treated_sql = "SELECT appointments.AppointmentID, appointments.AppointmentDate, appointments.AppointmentTime, appointments.AppointmentType, appointments.AppointmentStatus, patients.FullName AS PatientName, departments.Department AS DepartmentName 
        FROM appointments 
        INNER JOIN patients ON appointments.PatientID = patients.PatientID 
        INNER JOIN departments ON appointments.DepartmentID = departments.DepartmentID 
        WHERE appointments.DoctorID = ? AND appointments.AppointmentStatus = '已就诊'
        ORDER BY appointments.AppointmentDate DESC";
$stmt = $conn->prepare($treated_sql);
$stmt->bind_param("i", $doctor_id);
$stmt->execute();
$treated_result = $stmt->get_result();

$passed_sql = "SELECT appointments.AppointmentID, appointments.AppointmentDate, appointments.AppointmentTime, appointments.AppointmentType, appointments.AppointmentStatus, patients.FullName AS PatientName, departments.Department AS DepartmentName 
        FROM appointments 
        INNER JOIN patients ON appointments.PatientID = patients.PatientID 
        INNER JOIN departments ON appointments.DepartmentID = departments.DepartmentID 
        WHERE appointments.DoctorID = ? AND appointments.AppointmentStatus = '过号'
        ORDER BY appointments.AppointmentDate DESC";
$stmt = $conn->prepare($passed_sql);
$stmt->bind_param("i", $doctor_id);
$stmt->execute();
$passed_result = $stmt->get_result();

$conn->close();
?>


<!DOCTYPE html>
<html>
<head>
    <title>呼叫患者</title>
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
    <h1>呼叫患者</h1>
    <?php if (!empty($message)) { echo "<p>$message</p>"; } ?>

    <h2>排队的患者</h2>
    <?php if ($queue_result->num_rows > 0) { ?>
        <table>
            <thead>
                <tr>
                    <th>预约日期</th>
                    <th>预约时间</th>
                    <th>预约类型</th>
                    <th>预约状态</th>
                    <th>患者</th>
                    <th>科室</th>
                    <th>操作</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $queue_result->fetch_assoc()) { ?>
                    <tr>
                        <td><?php echo $row["AppointmentDate"]; ?></td>
                        <td><?php echo $row["AppointmentTime"]; ?></td>
                        <td><?php echo $row["AppointmentType"]; ?></td>
                        <td><?php echo $row["AppointmentStatus"]; ?></td>
                        <td><?php echo $row["PatientName"]; ?></td>
                        <td><?php echo $row["DepartmentName"]; ?></td>
                        <td>
                            <form action="call_patient.php" method="POST">
                                <input type="hidden" name="appointment_id" value="<?php echo $row["AppointmentID"]; ?>">
                                <input type="hidden" name="action" value="call">
                                <input type="submit" value="呼叫患者">
                            </form>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    <?php } else { ?>
        <p>您没有任何排队的预约记录。</p>
    <?php } ?>

    <h2>已呼叫的患者</h2>
    <?php if ($called_result->num_rows > 0) { ?>
        <table>
            <thead>
                <tr>
                    <th>预约日期</th>
                    <th>预约时间</th>
                    <th>预约类型</th>
                    <th>预约状态</th>
                    <th>患者</th>
                    <th>科室</th>
                    <th>操作</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $called_result->fetch_assoc()) { ?>
                    <tr class="called">
                        <td><?php echo $row["AppointmentDate"]; ?></td>
                        <td><?php echo $row["AppointmentTime"]; ?></td>
                        <td><?php echo $row["AppointmentType"]; ?></td>
                        <td><?php echo $row["AppointmentStatus"]; ?></td>
                        <td><?php echo $row["PatientName"]; ?></td>
                        <td><?php echo $row["DepartmentName"]; ?></td>
                        <td>
                            <form action="call_patient.php" method="POST">
                                <input type="hidden" name="appointment_id" value="<?php echo $row["AppointmentID"]; ?>">
                                <input type="hidden" name="action" value="update">
                                <select name="new_status">
                                    <option value="已就诊">已就诊</option>
                                    <option value="过号">过号</option>
                                </select>
                                <input type="submit" value="更新状态">
                            </form>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    <?php } else { ?>
        <p>您没有任何已呼叫的预约记录。</p>
    <?php } ?>

    <h2>已就诊的患者</h2>
    <?php if ($treated_result->num_rows > 0) { ?>
        <table>
            <thead>
                <tr>
                    <th>预约日期</th>
                    <th>预约时间</th>
                    <th>预约类型</th>
                    <th>预约状态</th>
                    <th>患者</th>
                    <th>科室</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $treated_result->fetch_assoc()) { ?>
                    <tr class="treated">
                        <td><?php echo $row["AppointmentDate"]; ?></td>
                        <td><?php echo $row["AppointmentTime"]; ?></td>
                        <td><?php echo $row["AppointmentType"]; ?></td>
                        <td><?php echo $row["AppointmentStatus"]; ?></td>
                        <td><?php echo $row["PatientName"]; ?></td>
                        <td><?php echo $row["DepartmentName"]; ?></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    <?php } else { ?>
        <p>您没有任何已就诊的预约记录。</p>
    <?php } ?>

    <h2>过号的患者</h2>
    <?php if ($treated_result->num_rows > 0) { ?>
        <table>
            <thead>
                <tr>
                    <th>预约日期</th>
                    <th>预约时间</th>
                    <th>预约类型</th>
                    <th>预约状态</th>
                    <th>患者</th>
                    <th>科室</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $treated_result->fetch_assoc()) { ?>
                    <tr class="treated">
                        <td><?php echo $row["AppointmentDate"]; ?></td>
                        <td><?php echo $row["AppointmentTime"]; ?></td>
                        <td><?php echo $row["AppointmentType"]; ?></td>
                        <td><?php echo $row["AppointmentStatus"]; ?></td>
                        <td><?php echo $row["PatientName"]; ?></td>
                        <td><?php echo $row["DepartmentName"]; ?></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    <?php } else { ?>
        <p>您没有任何过号的患者。</p>
    <?php } ?>

    <a class="button" href="doctor.html">返回到主页</a>
    <a class="button" href="../logout.php">退出登录</a>
</body>
</html>
