<?php
/* 药品管理.php ==> drug_manage.php */
// 数据库连接配置
include '../db_connection.php'; // 包含数据库连接文件
session_start();

// 检查用户类型
if ($_SESSION["UserType"] !== "admin") {
    header("Location: index.html");
    exit();
}

// 处理添加药品请求
if (isset($_POST['add_drug'])) {
    $drug_name = $_POST['drug_name'];
    $stock_quantity = $_POST['stock_quantity'];
    $price = $_POST['price'];

    // 使用参数化查询插入新药品记录
    $stmt = $conn->prepare("INSERT INTO drugs (DrugName, StockQuantity, Price) VALUES (?, ?, ?)");
    $stmt->bind_param("sid", $drug_name, $stock_quantity, $price);
    
    if ($stmt->execute()) {
        echo "药品添加成功！";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close(); // 关闭查询
}

// 处理更新库存数量请求
if (isset($_POST['update_stock'])) {
    $drug_name = $_POST['drug_name'];
    $new_stock_quantity = $_POST['new_stock_quantity'];

    // 使用参数化查询更新药品库存数量
    $stmt = $conn->prepare("UPDATE drugs SET StockQuantity=? WHERE DrugName=?");
    $stmt->bind_param("is", $new_stock_quantity, $drug_name);
    
    if ($stmt->execute()) {
        echo "库存数量更新成功！";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close(); // 关闭查询
}

// 处理发药请求
if (isset($_POST['dispense_medication']) && isset($_POST['prescription_id'])) {
    $prescription_id = $_POST['prescription_id'];

    // 使用参数化查询检查开单记录是否存在且患者已经缴费
    $stmt_check = $conn->prepare("SELECT * FROM prescriptions WHERE PrescriptionID=? AND PaymentStatus='已支付'");
    $stmt_check->bind_param("i", $prescription_id);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check->num_rows > 0) {
        $row = $result_check->fetch_assoc();
        $drug_id = $row['DrugID'];
        $quantity = $row['Quantity'];

        // 使用参数化查询检查药品库存是否足够
        $stmt_stock = $conn->prepare("SELECT StockQuantity FROM drugs WHERE DrugID=?");
        $stmt_stock->bind_param("i", $drug_id);
        $stmt_stock->execute();
        $result_stock = $stmt_stock->get_result();

        if ($result_stock->num_rows > 0) {
            $stock_row = $result_stock->fetch_assoc();
            $current_stock = $stock_row['StockQuantity'];
            if ($current_stock >= $quantity) {
                // 更新开单记录的发药状态
                $stmt_update = $conn->prepare("UPDATE prescriptions SET PaymentStatus='已发药' WHERE PrescriptionID=?");
                $stmt_update->bind_param("i", $prescription_id);
                
                if ($stmt_update->execute()) {
                    // 更新药品库存数量
                    $new_stock = $current_stock - $quantity;
                    $stmt_update_stock = $conn->prepare("UPDATE drugs SET StockQuantity=? WHERE DrugID=?");
                    $stmt_update_stock->bind_param("ii", $new_stock, $drug_id);

                    if ($stmt_update_stock->execute()) {
                        // 发药成功，弹窗提示
                        echo "<script>alert('药品发放成功！');</script>";
                    } else {
                        echo "Error updating stock: " . $stmt_update_stock->error;
                    }
                    $stmt_update_stock->close(); // 关闭查询
                } else {
                    echo "Error updating prescription: " . $stmt_update->error;
                }
                $stmt_update->close(); // 关闭查询
            } else {
                echo "<script>alert('库存不足，无法发药！');</script>";
            }
        } else {
            echo "<script>alert('药品不存在！');</script>";
        }
        $stmt_stock->close(); // 关闭查询
    } else {
        echo "<script>alert('无法发药：开单记录不存在或患者未缴费！');</script>";
    }
    $stmt_check->close(); // 关闭查询
}

// 查询待发药的处方
$sql = "SELECT p.PrescriptionID, p.DoctorID, d.FullName AS DoctorName, p.PatientID, pat.FullName AS PatientName, p.DrugID, p.Quantity, dr.DrugName, dr.Price, p.PaymentStatus, p.PrescriptionDate
    FROM prescriptions p
    JOIN doctors d ON p.DoctorID = d.DoctorID
    JOIN patients pat ON p.PatientID = pat.PatientID
    JOIN drugs dr ON p.DrugID = dr.DrugID
    WHERE p.PaymentStatus='已支付'";
$result = $conn->query($sql);

// 查询药品库存
$sql_drugs = "SELECT * FROM drugs";
$result_drugs = $conn->query($sql_drugs);

// 关闭数据库连接
$conn->close();
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>药品管理</title>
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
        tr {
            height: 50px; /* 设置每行的高度为50像素，根据需要调整 */
        }
    </style>
</head>
<body>
    <h2>药品管理</h2>
    <h3>添加新药品</h3>
    <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
        <label for="drug_name">药品名称：</label>
        <input type="text" id="drug_name" name="drug_name" required><br>
        <label for="stock_quantity">库存数量：</label>
        <input type="number" id="stock_quantity" name="stock_quantity" value="0" min="0"><br>
        <label for="price">单价：</label> <!-- 添加单价输入框 -->
        <input type="number" id="price" name="price" min="0" step="0.01" required><br> <!-- 添加单价输入框 -->
        <input type="submit" name="add_drug" value="添加药品">
    </form>
    <h3>更新药品库存</h3>
    <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
    <label for="drug_name">药品名称：</label>
    <input type="text" id="drug_name" name="drug_name" required><br>
    <label for="new_stock_quantity">新库存数量：</label>
    <input type="number" id="new_stock_quantity" name="new_stock_quantity" value="0" min="0"><br>
    <input type="submit" name="update_stock" value="更新库存">
</form>

<h3>待发药清单</h3>
<?php if ($result->num_rows > 0) { ?>
    <table border="1">
        <thead>
            <tr>
                <th>开单ID</th>
                <th>医生姓名</th>
                <th>患者姓名</th>
                <th>药品ID</th>
                <th>药品名称</th>
                <th>数量</th>
                <th>单价</th>
                <th>总价</th>
                <th>缴费状态</th>
                <th>开单日期</th>
                <th>操作</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = $result->fetch_assoc()) { ?>
                <tr>
                    <td><?php echo $row['PrescriptionID'];?></td>
                    <td><?php echo $row['DoctorName'];?></td>
                    <td><?php echo $row['PatientName'];?></td>
                    <td><?php echo $row['DrugID'];?></td>
                    <td><?php echo $row['DrugName'];?></td>
                    <td><?php echo $row['Quantity'];?></td>
                    <td><?php echo $row['Price'];?></td>
                    <td><?php echo $row['Quantity'] * $row['Price'];?></td>
                    <td><?php echo $row['PaymentStatus'];?></td>
                    <td><?php echo $row['PrescriptionDate'];?></td>
                    <td>
                        <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" onsubmit="return confirm('确认发药？');">
                            <input type="hidden" name="prescription_id" value="<?php echo $row['PrescriptionID']; ?>">
                            <input type="submit" name="dispense_medication" value="发药">
                        </form>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
<?php } else { ?>
    <p>暂无待发药记录。</p>
<?php } ?>

<h3>药品库存</h3>
<?php if ($result_drugs->num_rows > 0) { ?>
    <table border="1">
        <thead>
            <tr>
                <th>药品ID</th>
                <th>药品名称</th>
                <th>库存数量</th>
                <th>单价</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = $result_drugs->fetch_assoc()) { ?>
                <tr>
                    <td><?php echo $row['DrugID'];?></td>
                    <td><?php echo $row['DrugName'];?></td>
                    <td><?php echo $row['StockQuantity'];?></td>
                    <td><?php echo $row['Price'];?></td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
<?php } else {
    echo "没有药品库存记录.";
} ?>
	<a class="button" href="admin.html">返回到管理员仪表盘</a>
    <a class="button" href="logout.php">注销登录</a>
</body>
</html>
