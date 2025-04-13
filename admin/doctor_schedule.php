<?php
/* 医生排班.php ==> doctor_schedule.php */
session_start();
include '../db_connection.php';
if ($_SESSION["UserType"] !== "admin") {
    header("Location: index.html");
    exit();
}

$error_message = "";

// 处理排班表单提交
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'add') {
    $doctor_id = $_POST['doctor_id'];
    $schedule_date = $_POST['schedule_date'];
    $shift = $_POST['shift'];
    $department_id = $_POST['department_id'];

    // 设置班次时间和具体班次
    if ($shift == 'morning') {
        $start_time = '08:30:00';
        $end_time = '12:00:00';
        $specific_shift = '早班';
    } elseif ($shift == 'afternoon') {
        $start_time = '14:00:00';
        $end_time = '17:30:00';
        $specific_shift = '下午班';
    } elseif ($shift == 'night') {
        $start_time = '18:00:00';
        $end_time = '07:00:00';  // 次日早上
        $specific_shift = '晚班';
        // 修改排班日期为次日，以表示过夜班次
        $next_day = new DateTime($schedule_date);
        $next_day->modify('+1 day');
        $schedule_date_end = $next_day->format('Y-m-d');
    } else {
        $error_message = "无效的班次";
    }

    // 验证排班时间
    $current_datetime = date("Y-m-d H:i:s");
    if ($schedule_date < date("Y-m-d") || ($schedule_date == date("Y-m-d") && $start_time <= date("H:i:s"))) {
    } else {
        // 检查是否已存在相同班次的排班记录
        $sql_check = "SELECT * FROM schedules WHERE DoctorID='$doctor_id' AND ScheduleDate='$schedule_date' AND Shift='$specific_shift'";
        $result_check = $conn->query($sql_check);

        if ($result_check->num_rows > 0) {
            $error_message = "该医生在指定日期已有相同班次的排班记录";
        } else {
            if ($shift == 'night') {
                $sql_insert = "INSERT INTO schedules (DoctorID, ScheduleDate, StartTime, EndTime, Shift, DepartmentID)
                               VALUES ('$doctor_id', '$schedule_date', '$start_time', '23:59:59', '$specific_shift', '$department_id'),
                                      ('$doctor_id', '$schedule_date_end', '00:00:00', '$end_time', '$specific_shift', '$department_id')";

                if ($conn->multi_query($sql_insert) === TRUE) {
                    // 获取插入的记录的ScheduleID
                    $first_insert_id = $conn->insert_id;
                    $second_insert_id = $first_insert_id + 1;

                    // 更新关联关系
                    $sql_update_related = "UPDATE schedules SET RelatedScheduleID='$second_insert_id' WHERE ScheduleID='$first_insert_id'";
                    $conn->query($sql_update_related);
                    $sql_update_related = "UPDATE schedules SET RelatedScheduleID='$first_insert_id' WHERE ScheduleID='$second_insert_id'";
                    $conn->query($sql_update_related);

                    $error_message = "排班成功";
                } else {
                    $error_message = "Error: " . $sql_insert . "<br>" . $conn->error;
                }
            } else {
                // 插入其他班次的记录
                $sql_insert = "INSERT INTO schedules (DoctorID, ScheduleDate, StartTime, EndTime, Shift, DepartmentID)
                               VALUES ('$doctor_id', '$schedule_date', '$start_time', '$end_time', '$specific_shift', '$department_id')";

                if ($conn->query($sql_insert) === TRUE) {
                    $error_message = "排班成功";
                } else {
                    $error_message = "Error: " . $sql_insert . "<br>" . $conn->error;
                }
            }
        }
    }
}

// 处理删除排班
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'delete') {
    $schedule_id = $_POST['schedule_id'];
    $sql_delete = "DELETE FROM schedules WHERE ScheduleID='$schedule_id'";

    if ($conn->query($sql_delete) === TRUE) {
        // 检查是否有关联记录需要同时删除
        $sql_check_related = "SELECT * FROM schedules WHERE RelatedScheduleID='$schedule_id'";
        $result_check_related = $conn->query($sql_check_related);

        if ($result_check_related->num_rows > 0) {
            while ($row = $result_check_related->fetch_assoc()) {
                $related_id = $row['ScheduleID'];
                $sql_delete_related = "DELETE FROM schedules WHERE ScheduleID='$related_id'";
                $conn->query($sql_delete_related);
            }
        }

        $error_message = "删除成功";
    } else {
        $error_message = "Error: " . $sql_delete . "<br>" . $conn->error;
    }
}

// 获取科室ID
$department_id = isset($_POST['department_id']) ? $_POST['department_id'] : '';

// 获取医生列表
$doctors = [];
if ($department_id) {
    $sql = "SELECT * FROM doctors WHERE DepartmentID='$department_id'";
    $result = $conn->query($sql);
    while ($row = $result->fetch_assoc()) {
        $doctors[] = $row;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>医生排班</title>
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
    </style>
</head>
<body>
    <h1>医生排班</h1>
    <?php if (!empty($error_message)) echo "<p>$error_message</p>"; ?>
    <form method="POST" action="doctor_schedule.php">
        <input type="hidden" name="action" value="add">
        <label for="department_id">选择科室：</label>
        <select name="department_id" required onchange="this.form.submit()">
            <option value="">选择科室</option>
            <option value="1" <?php if ($department_id == '1') echo 'selected'; ?>>内科</option>
            <option value="2" <?php if ($department_id == '2') echo 'selected'; ?>>外科</option>
            <option value="3" <?php if ($department_id == '3') echo 'selected'; ?>>儿科</option>
            <option value="4" <?php if ($department_id == '4') echo 'selected'; ?>>妇产科</option>
            <option value="5" <?php if ($department_id == '5') echo 'selected'; ?>>眼科</option>
        </select><br>
        <label for="doctor_id">选择医生：</label>
        <select name="doctor_id" required>
            <option value="">选择医生</option>
            <?php
            foreach ($doctors as $doctor) {
                echo "<option value='" . $doctor['DoctorID'] . "'>" . $doctor['DoctorID'] . " - " . $doctor['FullName'] . "</option>";
            }
            ?>
        </select><br>
        <label for="schedule_date">排班日期：</label>
        <input type="date" name="schedule_date" required><br>
        <label for="shift">选择班次：</label>
        <select name="shift" required>
            <option value="morning">上午班 (08:30-12:00)</option>
            <option value="afternoon">下午班 (14:00-17:30)</option>
            <option value="night">晚班 (18:00-07:00)</option>
        </select><br>
        <input type="submit" value="提交">
    </form>
    <h2>已排班表</h2>
<table border="1">
    <tr>
        <th>医生姓名</th>
        <th>科室</th>
        <th>排班日期</th>
        <th>开始时间</th>
        <th>结束时间</th>
        <th>班次</th>
        <th>操作</th>
    </tr>
    <?php
    $sql = "SELECT s.*, d.FullName, de.Department FROM schedules s 
            JOIN doctors d ON s.DoctorID = d.DoctorID 
            JOIN departments de ON d.DepartmentID = de.DepartmentID";
    $schedules = $conn->query($sql);
    while ($schedule = $schedules->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $schedule['FullName'] . "</td>";
        echo "<td>" . $schedule['Department'] . "</td>";
        echo "<td>" . $schedule['ScheduleDate'] . "</td>";
        echo "<td>" . $schedule['StartTime'] . "</td>";
        echo "<td>" . $schedule['EndTime'] . "</td>";
        echo "<td>" . $schedule['Shift'] . "</td>";
        echo "<td>
                <form method='POST' action='doctor_schedule.php' style='display:inline;'>
                    <input type='hidden' name='action' value='delete'>
                    <input type='hidden' name='schedule_id' value='" . $schedule['ScheduleID'] . "'>
                    <input type='submit' value='删除'>
                </form>
            </td>";
        echo "</tr>";
    }
    ?>
</table>


<a class="button" href="admin.html">返回到管理员仪表盘</a>
<a class="button" href="../logout.php">注销登录</a>
</body>
</html>
