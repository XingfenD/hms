<?php
/* 查看处方.php ==> view_prescription.php */
include '../db_connection.php';
session_start();

// Check if doctor is logged in
$doctor_id = $_SESSION["doctor_login"];
if (empty($doctor_id) || !is_numeric($doctor_id)) {
    echo "请先登录。";
    exit();
}

// Fetch prescriptions created by the doctor with patient names and drug names
$sql = "SELECT prescriptions.PrescriptionID, patients.FullName, drugs.DrugName, prescriptions.Quantity, prescriptions.SumPrice, prescriptions.PaymentStatus, prescriptions.PrescriptionDate
        FROM prescriptions
        INNER JOIN patients ON prescriptions.PatientID = patients.PatientID
        INNER JOIN drugs ON prescriptions.DrugID = drugs.DrugID
        WHERE prescriptions.DoctorID = '$doctor_id'";
$result = $conn->query($sql);

// Close database connection
$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>查看处方</title>
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
            border-radius: 10px;
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
        .button:hover {
            background-color: #007B9E;
        }
    </style>
</head>
<body>
    <h1>查看处方</h1>
    <div class="container">
        <?php if ($result->num_rows > 0) { ?>
            <table>
                <thead>
                    <tr>
                        <th>处方ID</th>
                        <th>患者姓名</th>
                        <th>药品名称</th>
                        <th>数量</th>
                        <th>总价</th>
                        <th>缴费状态</th>
                        <th>处方日期</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()) { ?>
                        <tr>
                            <td><?php echo $row['PrescriptionID']; ?></td>
                            <td><?php echo $row['FullName']; ?></td>
                            <td><?php echo $row['DrugName']; ?></td>
                            <td><?php echo $row['Quantity']; ?></td>
                            <td><?php echo $row['SumPrice']; ?></td>
                            <td><?php echo $row['PaymentStatus']; ?></td>
                            <td><?php echo $row['PrescriptionDate']; ?></td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        <?php } else { ?>
            <p>没有处方记录。</p>
        <?php } ?>
        <a class="button" href="doctor.html">返回到主页</a>
        <a class="button" href="../logout.php">退出登录</a>
    </div>
</body>
</html>
