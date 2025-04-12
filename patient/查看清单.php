<?php
include 'db_connection.php';
session_start();

// 检查用户是否已登录
if (!isset($_SESSION["patient_login"])) {
    echo "请先登录。";
    exit();
}

$patient_id = $_SESSION["patient_login"];

// 获取已支付的处方列表，包括药品名称、药品数量、开药的医生和科室信息
$sql = "SELECT prescriptions.PrescriptionID, prescriptions.Quantity, drugs.DrugName, doctors.FullName AS DoctorName, departments.Department AS DepartmentName 
        FROM prescriptions 
        INNER JOIN drugs ON prescriptions.DrugID = drugs.DrugID 
        INNER JOIN doctors ON prescriptions.DoctorID = doctors.DoctorID 
        INNER JOIN departments ON doctors.DepartmentID = departments.DepartmentID 
        WHERE prescriptions.PatientID = '$patient_id' AND prescriptions.PaymentStatus = '已支付'";
$result = $conn->query($sql);

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>查看清单</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f4f4f4;
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
            background-color: #008CBA;
            color: white;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .button {
            display: block;
            width: 150px;
            margin: 20px auto;
            padding: 10px 20px;
            background-color: #008CBA;
            color: #fff;
            text-align: center;
            text-decoration: none;
            border-radius: 5px;
        }

        .button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <h1>已支付处方清单</h1>
    <?php if ($result->num_rows > 0) { ?>
        <table>
            <thead>
                <tr>
                    <th>处方ID</th>
                    <th>药品名称</th>
                    <th>药品数量</th>
                    <th>开药的医生</th>
                    <th>科室</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()) { ?>
                    <tr>
                        <td><?php echo $row["PrescriptionID"]; ?></td>
                        <td><?php echo $row["DrugName"]; ?></td>
                        <td><?php echo $row["Quantity"]; ?></td>
                        <td><?php echo $row["DoctorName"]; ?></td>
                        <td><?php echo $row["DepartmentName"]; ?></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    <?php } else { ?>
        <p>您没有已支付的处方记录。</p>
    <?php } ?>
    <a class="button" href="patient.html">返回到主页</a>
    <a class="button" href="logout.php">退出登录</a>
</body>
</html>
