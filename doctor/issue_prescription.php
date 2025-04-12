<?php
/* 开具处方.php ==> issue_prescription.php */
include '../db_connection.php';
session_start();

// 获取医生ID
$doctor_id = $_SESSION["doctor_login"];

// 检查医生是否已登录
if (empty($doctor_id) || !is_numeric($doctor_id)) {
    echo "请先登录。";
    exit();
}

// 处理开具处方请求
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["patient_id"]) && isset($_POST["drugs"])) {
    $patient_id = $_POST["patient_id"];
    $drugs = $_POST["drugs"];
    
    // 获取患者的预约日期
    $appointment_sql = "SELECT AppointmentDate FROM appointments WHERE PatientID = ? AND DoctorID = ? AND AppointmentStatus = '已就诊'";
    $stmt = $conn->prepare($appointment_sql);
    $stmt->bind_param('ii', $patient_id, $doctor_id);
    $stmt->execute();
    $appointment_result = $stmt->get_result();
    $appointment = $appointment_result->fetch_assoc();
    $stmt->close();

    // 验证患者和药品信息
    $patient_check_sql = "SELECT * FROM appointments WHERE PatientID = ? AND DoctorID = ? AND AppointmentStatus = '已就诊'";
    $stmt = $conn->prepare($patient_check_sql);
    $stmt->bind_param('ii', $patient_id, $doctor_id);
    $stmt->execute();
    $patient_check_result = $stmt->get_result();

    if ($patient_check_result->num_rows > 0) {
        $is_valid = true;
        foreach ($drugs as $drug) {
            $drug_id = $drug['drug_id'];
            $quantity = $drug['quantity'];

            $drug_check_sql = "SELECT * FROM drugs WHERE DrugID = ?";
            $stmt = $conn->prepare($drug_check_sql);
            $stmt->bind_param('i', $drug_id);
            $stmt->execute();
            $drug_check_result = $stmt->get_result();
            $drug_info = $drug_check_result->fetch_assoc();
            $stmt->close();

            if (!$drug_info || $quantity <= 0 || $quantity > $drug_info['StockQuantity']) {
                $is_valid = false;
                break;
            }
        }

        if ($is_valid) {
            foreach ($drugs as $drug) {
                $drug_id = $drug['drug_id'];
                $quantity = $drug['quantity'];

                $sum_price = $quantity * $drug_info['Price'];

                $insert_sql = "INSERT INTO prescriptions (DoctorID, PatientID, DrugID, Quantity, SumPrice, PaymentStatus, PrescriptionDate) 
                               VALUES (?, ?, ?, ?, ?, '未支付', ?)";
                $stmt = $conn->prepare($insert_sql);
                $stmt->bind_param('iiiids', $doctor_id, $patient_id, $drug_id, $quantity, $sum_price, $appointment['AppointmentDate']);
                
                if ($stmt->execute() !== TRUE) {
                    $message = "开具处方时出错：" . $conn->error;
                    break;
                } else {
                    $message = "处方已成功开具。";
                }
                $stmt->close();
            }
        } else {
            $message = "无效的患者或药品选择，或数量超出库存。";
        }
    } else {
        $message = "无效的患者选择。";
    }
}

// 获取医生的患者列表，已呼叫状态的
$patients_sql = "SELECT appointments.PatientID, patients.FullName 
                 FROM appointments 
                 INNER JOIN patients ON appointments.PatientID = patients.PatientID 
                 WHERE appointments.DoctorID = ? AND appointments.AppointmentStatus = '已就诊'";
$stmt = $conn->prepare($patients_sql);
$stmt->bind_param('i', $doctor_id);
$stmt->execute();
$patients_result = $stmt->get_result();

$conn->close();
?>


<!DOCTYPE html>
<html>
<head>
    <title>开具处方</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 80%;
            margin: 0 auto;
            background-color: #fff;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            margin-top: 50px;
        }
        h1 {
            text-align: center;
            color: #333;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 12px;
            text-align: left;
        }
        th {
            background-color: #f4f4f9;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .qr-code {
            text-align: center;
            margin: 20px 0;
        }
        .total-amount {
            margin: 20px 0;
            text-align: center;
            font-size: 18px;
            font-weight: bold;
            color: #333;
        }
        input[type="submit"] {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            display: block;
            margin: 20px auto;
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
            border-radius: 4px;
        }
        .button:hover {
            background-color: #007B9E;
        }
        .message {
            text-align: center;
            color: red;
            font-size: 16px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <h1>开具处方</h1>
    <?php if (!empty($message)) { echo "<p>$message</p>"; } ?>
    <form action="issue_prescription.php" method="POST">
        <label for="patient_id">选择患者:</label>
        <select name="patient_id" required>
            <?php if ($patients_result->num_rows > 0) { ?>
                <?php while ($row = $patients_result->fetch_assoc()) { ?>
                    <option value="<?php echo $row["PatientID"]; ?>"><?php echo $row["FullName"]; ?></option>
                <?php } ?>
            <?php } else { ?>
                <option value="">没有待开药的患者</option>
            <?php } ?>
        </select>

        <div id="drug-list">
            <div class="drug-entry">
                <label for="drug_id">选择药品:</label>
                <input type="text" class="drug-search" placeholder="搜索药品名称" onkeyup="searchDrug(this)">
                <select name="drugs[0][drug_id]" required></select>
                <label for="quantity">数量:</label>
                <input type="number" name="drugs[0][quantity]" min="1" required>
                <button type="button" class="remove-button" onclick="removeDrugEntry(this)">删除</button>
            </div>
        </div>

        <button type="button" onclick="addDrugEntry()">添加药品</button>
        <input type="submit" value="开具处方">
    </form>

    <script>
        var drugIndex = 1;

        function addDrugEntry() {
            var drugList = document.getElementById('drug-list');
            var newEntry = document.createElement('div');
            newEntry.classList.add('drug-entry');
            
            newEntry.innerHTML = `
                <label for="drug_id">选择药品:</label>
                <input type="text" class="drug-search" placeholder="搜索药品名称" onkeyup="searchDrug(this)">
                <select name="drugs[${drugIndex}][drug_id]" required></select>
                <label for="quantity">数量:</label>
                <input type="number" name="drugs[${drugIndex}][quantity]" min="1" required>
                <button type="button" class="remove-button" onclick="removeDrugEntry(this)">删除</button>
            `;

            drugList.appendChild(newEntry);
            drugIndex++;
        }

        function removeDrugEntry(button) {
            var entry = button.parentNode;
            entry.parentNode.removeChild(entry);
        }

        function searchDrug(input) {
            var query = input.value;
            var select = input.nextElementSibling;

            var xhr = new XMLHttpRequest();
            xhr.open('GET', 'search_drug.php?q=' + query, true);
            xhr.onreadystatechange = function() {
                if (xhr.readyState == 4 && xhr.status == 200) {
                    var response = JSON.parse(xhr.responseText);
                    select.innerHTML = '';
                    response.forEach(function(drug) {
                        var option = document.createElement('option');
                        option.value = drug.DrugID;
                        option.text = `${drug.DrugName} (库存: ${drug.StockQuantity}, 单价: ${drug.Price})`;
                        select.appendChild(option);
                    });
                }
            };
            xhr.send();
        }
    </script>
    <a class="button" href="doctor.html">返回到主页</a>
    <a class="button" href="logout.php">退出登录</a>
</body>
</html>
