<?php
// 包含数据库连接文件
include 'db_connection.php';

// 获取科室列表
$departments = [];
$sql = "SELECT DepartmentID, Department FROM departments";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $departments[] = $row;
    }
}

session_start();

// 获取用户ID
$patient_id = $_SESSION["patient_login"];

// 确保 $patient_id 不为空且是一个整数
if (!empty($patient_id) && is_numeric($patient_id)) {
    // 处理预约信息提交
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["appointment_date"]) && isset($_POST["doctor"])) {
        $appointment_date = $_POST["appointment_date"];
        $appointment_shift = $_POST["appointment_shift"];
        $appointment_time = $_POST["appointment_time"];
        $department = $_POST["department"];
        $doctor = $_POST["doctor"];
        $appointment_type = $_POST["appointment_type"];

        // 定义班次的时间范围
        $shift_start_time = "";
        $shift_end_time = "";

        if ($appointment_shift == "早班") {
            $shift_start_time = "07:59:59";
            $shift_end_time = "12:00:00";
        } elseif ($appointment_shift == "下午班") {
            $shift_start_time = "13:59:59";
            $shift_end_time = "17:30:00";
        } elseif ($appointment_shift == "晚班") {
            $shift_start_time = "17:59:59";
            $shift_end_time = "23:59:59";
        }

// Check for existing appointments
$check_sql = "SELECT * FROM appointments 
              WHERE PatientID = '$patient_id' 
              AND DoctorID = '$doctor' 
              AND AppointmentDate = '$appointment_date' 
              AND AppointmentTime BETWEEN '$shift_start_time' AND '$shift_end_time'";

$check_result = $conn->query($check_sql);
if ($check_result->num_rows > 0) {
    echo "<script>alert('您已在该时段预约同一名医生，请取消预约后再进行预约。');</script>";
} else {
    // Proceed with appointment insertion
    $sql = "INSERT INTO appointments (PatientID, DoctorID, AppointmentDate, AppointmentTime, AppointmentType, AppointmentStatus, DepartmentID)
            VALUES ('$patient_id', '$doctor', '$appointment_date', '$appointment_time', '$appointment_type', '已预约', '$department')";

    if ($conn->query($sql) === TRUE) {
        echo "<script>alert('预约成功！');</script>";
    } else {
        echo "<script>alert('预约时发生错误，请重试。');</script>";
    }
}

    }
} else {
    // 当前用户ID为空或不是一个有效的整数
    echo "<script>alert('当前用户无效，请重新登陆。');</script>";
    echo "<script>window.location.href='../login.php';</script>";
}

// 获取医生列表（如果有部门选择）
$doctors = [];
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["department"]) && isset($_POST["appointment_date"]) && isset($_POST["appointment_shift"]) && !isset($_POST["doctor"])) {
    $department = $_POST["department"];
    $appointment_date = $_POST["appointment_date"];
    $appointment_shift = $_POST["appointment_shift"];

    // 根据选择的日期和时段获取医生列表
    if ($appointment_shift == "晚班") {
        // 查询晚班的医生，包括跨天晚班的医生
        $sql = "SELECT DISTINCT doctors.DoctorID, doctors.FullName 
                FROM schedules 
                INNER JOIN doctors ON schedules.DoctorID = doctors.DoctorID 
                WHERE schedules.DepartmentID = '$department'
                AND (
                    (schedules.ScheduleDate = '$appointment_date' AND schedules.Shift = '晚班' AND schedules.RelatedScheduleID IS NULL)
                    OR 
                    (schedules.ScheduleDate = DATE_ADD('$appointment_date', INTERVAL 1 DAY) AND schedules.RelatedScheduleID IS NOT NULL AND schedules.Shift = '晚班')
                )";
    } else {
        // 查询早班和下午班的医生
        $sql = "SELECT DISTINCT doctors.DoctorID, doctors.FullName 
                FROM schedules 
                INNER JOIN doctors ON schedules.DoctorID = doctors.DoctorID 
                WHERE schedules.DepartmentID = '$department'
                AND schedules.ScheduleDate = '$appointment_date'
                AND schedules.Shift = '$appointment_shift'";
    }

    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $doctors[] = $row;
        }
    }

    // 输出医生选项
    foreach ($doctors as $doc) {
        echo "<option value='" . $doc['DoctorID'] . "'>" . $doc['FullName'] . "</option>";
    }
    exit();
}

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>挂号页面</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 50%;
            margin: 0 auto;
            background-color: #fff;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            margin-top: 50px;
        }
        h1 {
            text-align: center;
            color: #333;
        }
        label, select, input[type="date"], input[type="submit"] {
            display: block;
            margin: 10px auto;
            text-align: center;
            width: 30%; 
        }
        label {
            text-align: left;
        }
        select, input[type="date"] {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        input[type="submit"] {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            text-align: center;
        }
        input[type="submit"]:hover {
            background-color: #45a049;
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
            width: 40%;
        }
        .button:hover {
            background-color: #007B9E;
        }
    </style>
    <script>
        function loadDoctors() {
            var departmentId = document.getElementById("department").value;
            var appointmentDate = document.getElementById("appointment_date").value;
            var appointmentShift = document.getElementById("appointment_shift").value;

            if (departmentId && appointmentDate && appointmentShift) {
                var xhr = new XMLHttpRequest();
                xhr.open("POST", "<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>", true);
                xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");

                xhr.onreadystatechange = function () {
                    if (xhr.readyState == 4 && xhr.status == 200) {
                        document.getElementById("doctor").innerHTML = "<option value=''>选择医生</option>" + xhr.responseText;
                    }
                };

                xhr.send("department=" + departmentId + "&appointment_date=" + appointmentDate + "&appointment_shift=" + appointmentShift);
            }
        }

        function loadTimes() {
            var appointmentShift = document.getElementById("appointment_shift").value;
            var appointmentTimeSelect = document.getElementById("appointment_time");
            appointmentTimeSelect.innerHTML = ""; // 清空时间选项

            if (appointmentShift === "早班") {
                var times = ["请选择时间", "08:00", "08:30", "09:00", "09:30", "10:00", "10:30", "11:00", "11:30"];
            } else if (appointmentShift === "下午班") {
                var times = ["请选择时间", "14:00", "14:30", "15:00", "15:30", "16:00", "16:30", "17:00"];
            } else if (appointmentShift === "晚班") {
                var times = ["请选择时间", "18:00"];
            }

            for (var i = 0; i < times.length; i++) {
                var option = document.createElement("option");
                option.value = times[i];
                option.text = times[i];
                appointmentTimeSelect.appendChild(option);
            }
        }

        function validateForm() {
            var appointmentTime = document.getElementById("appointment_time").value;
            if (appointmentTime === "" || appointmentTime === "请选择时间") {
                alert("请选择预约时间！");
                return false;
            }
            return true;
        }
    </script>
</head>
<body>
    <h1>在线挂号</h1>
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST" onsubmit="return validateForm()">
        <label for="appointment_date">选择预约日期：</label>
        <input type="date" id="appointment_date" name="appointment_date" required onchange="loadDoctors()"><br><br>

        <label for="appointment_shift">选择预约时段：</label>
        <select id="appointment_shift" name="appointment_shift" required onchange="loadDoctors(); loadTimes();">
            <option value="">选择时间</option>
            <option value="早班">上午</option>
            <option value="下午班">下午</option>
            <option value="晚班">晚上</option>
        </select><br><br>

        <label for="department">选择科室：</label>
        <select id="department" name="department" required onchange="loadDoctors()">
            <option value="">选择科室</option>
            <?php
            foreach ($departments as $dept) {
                echo "<option value='" . $dept['DepartmentID'] . "'>" . $dept['Department'] . "</option>";
            }
            ?>
        </select><br><br>

        <label for="doctor">选择医生：</label>
        <select id="doctor" name="doctor" required>
            <option value="">选择医生</option>
        </select><br><br>

        <label for="appointment_time">选择预约时间：</label>
        <select id="appointment_time" name="appointment_time" required>
            <option value="">选择时间</option>
        </select><br><br>

        <label for="appointment_type">选择预约类型：</label>
        <select id="appointment_type" name="appointment_type" required>
            <option value="初诊">初诊</option>
            <option value="复诊">复诊</option>
        </select><br><br>

        <input type="submit" value="确认预约">
    </form>
<a class="button" href="patient.html">返回到主页</a>
<a class="button" href="logout.php">退出登录</a>
</body>
</html>
