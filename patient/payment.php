<?php
/* 支付.php ==> payment.php */
include '../db_connection.php';
session_start();

// 检查用户是否已登录
if (!isset($_SESSION["patient_login"])) {
    echo "请先登录。";
    exit();
}

$patient_id = $_SESSION["patient_login"];
$message = "";

// 处理支付请求
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["prescription_ids"])) {
    $prescription_ids = $_POST["prescription_ids"];

    if (count($prescription_ids) > 0) {
        // 启动事务
        $conn->begin_transaction();

        try {
            $total_amount = 0;
            foreach ($prescription_ids as $prescription_id) {
                // 获取处方信息
                $prescription_sql = "SELECT DrugID, Quantity, SumPrice FROM prescriptions WHERE PrescriptionID = ? AND PatientID = ? AND PaymentStatus = '未支付'";
                $stmt = $conn->prepare($prescription_sql);
                $stmt->bind_param("ii", $prescription_id, $patient_id);
                $stmt->execute();
                $prescription_result = $stmt->get_result();

                if ($prescription_result->num_rows > 0) {
                    $prescription = $prescription_result->fetch_assoc();
                    $drug_id = $prescription['DrugID'];
                    $quantity = $prescription['Quantity'];
                    $total_amount += $prescription['SumPrice'];
                    
                    // 更新药品库存
                    $update_stock_sql = "UPDATE drugs SET StockQuantity = StockQuantity - ? WHERE DrugID = ?";
                    $stmt = $conn->prepare($update_stock_sql);
                    $stmt->bind_param("ii", $quantity, $drug_id);
                    if ($stmt->execute() === TRUE) {
                        // 更新支付状态为已支付
                        $update_status_sql = "UPDATE prescriptions SET PaymentStatus = '已支付' WHERE PrescriptionID = ?";
                        $stmt = $conn->prepare($update_status_sql);
                        $stmt->bind_param("i", $prescription_id);
                        if ($stmt->execute() !== TRUE) {
                            throw new Exception("更新支付状态时出错：" . $conn->error);
                        }
                    } else {
                        throw new Exception("更新药品库存时出错：" . $conn->error);
                    }
                } else {
                    throw new Exception("无效的处方或该处方已支付。");
                }
            }

            // 提交事务
            $conn->commit();
            $message = "支付成功！总支付金额：￥" . $total_amount;
        } catch (Exception $e) {
            // 回滚事务
            $conn->rollback();
            $message = $e->getMessage();
        }
    } else {
        $message = "请选择至少一个处方进行支付。";
    }
}

// 获取患者的未支付处方列表，包含药品名称
$prescriptions_sql = "SELECT prescriptions.PrescriptionID, prescriptions.Quantity, drugs.DrugName, prescriptions.PrescriptionDate, SumPrice 
                      FROM prescriptions
                      INNER JOIN drugs ON prescriptions.DrugID = drugs.DrugID
                      WHERE prescriptions.PatientID = ? AND prescriptions.PaymentStatus = '未支付'
                      ORDER BY prescriptions.PrescriptionDate DESC";
$stmt = $conn->prepare($prescriptions_sql);
$stmt->bind_param("i", $patient_id);
$stmt->execute();
$prescriptions_result = $stmt->get_result();

$conn->close();
?>


<!DOCTYPE html>
<html>
<head>
    <title>在线支付</title>
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
    <script>
        function calculateTotal() {
            var checkboxes = document.querySelectorAll('input[name="prescription_ids[]"]:checked');
            var totalAmount = 0;
            checkboxes.forEach(function(checkbox) {
                totalAmount += parseFloat(checkbox.getAttribute('data-sumprice'));
            });
            document.getElementById('totalAmount').textContent = "总支付金额：￥" + totalAmount.toFixed(2);
        }
    </script>
</head>
<body>
    <div class="container">
        <h1>在线支付</h1>
        <?php if (!empty($message)) { echo "<p class='message'>$message</p>"; } ?>
        <form action="payment.php" method="POST">
            <table>
                <thead>
                    <tr>
                        <th>选择</th>
                        <th>处方时间</th>
                        <th>药品名称</th>
                        <th>数量</th>
                        <th>总价</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($prescriptions_result->num_rows > 0) { ?>
                        <?php while ($row = $prescriptions_result->fetch_assoc()) { ?>
                            <tr>
                                <td><input type="checkbox" name="prescription_ids[]" value="<?php echo $row["PrescriptionID"]; ?>" data-sumprice="<?php echo $row["SumPrice"]; ?>" onclick="calculateTotal()"></td>
                                <td><?php echo $row["PrescriptionDate"]; ?></td>
                                <td><?php echo $row["DrugName"]; ?></td>
                                <td><?php echo $row["Quantity"]; ?></td>
                                <td><?php echo $row["SumPrice"]; ?></td>
                            </tr>
                        <?php } ?>
                    <?php } else { ?>
                        <tr>
                            <td colspan="6">没有未支付的处方</td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>

            <div class="qr-code">
                <p>请使用手机扫描下方二维码进行支付:</p>
                <img src="payment.jpg" alt="收款二维码" width="300" height="400">
            </div>

            <div class="total-amount">
                <p id="totalAmount">总支付金额：￥0.00</p>
            </div>

            <input type="submit" value="确认支付">
        </form>
        <a class="button" href="patient.html">返回到主页</a>
        <a class="button" href="logout.php">退出登录</a>
    </div>
</body>
</html>
