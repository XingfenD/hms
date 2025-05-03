<?php
/**
 * @file apis/DispenseDrug.php
 * @brief API for dispensing drug based on prescription record (with inventory check)
 * @author xing
 * @date 2025-04-13
 */

header('Content-Type: application/json');
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
$allowedOrigins = ['http://localhost:5173'];
if (in_array($origin, $allowedOrigins)) {
    header("Access-Control-Allow-Origin: $origin");
    header("Access-Control-Allow-Credentials: true");
}
header("Access-Control-Allow-Methods: PUT");
header("Access-Control-Allow-Headers: Content-Type");

require_once __DIR__ . '/utils/ApiResponse.php';
require_once __DIR__ . '/utils/Database.php';
require_once __DIR__ . '/utils/utils.php';

use App\Response\ApiResponse;
use App\Database\Database;

function handleRequest() {
    try {
        session_start();

        if ($_SESSION["UserType"] !== "admin") {
            throw new \Exception("operation not permitted for current user", 401);
        }

        verifyMethods(['PUT']);
        $db = initializeDatabase();

        parse_str(file_get_contents("php://input"), $putData);
        $presRcdId = isset($putData['pres_rcd_id']) ? intval($putData['pres_rcd_id']) : 0;

        if ($presRcdId <= 0) {
            throw new \Exception("pres_rcd_id can't be null or invalid", 400);
        }

        // 查询处方记录
        $stmt = $db->prepare("SELECT * FROM prescription_record WHERE pres_rcd_id = :id");
        $stmt->bindParam(':id', $presRcdId, PDO::PARAM_INT);
        $stmt->execute();
        $record = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$record) {
            throw new \Exception("Prescription record not found", 404);
        }

        if ($record['status_code'] == 2) {
            throw new \Exception("Prescription already dispensed", 400);
        }

        $drugId = $record['drug_id'];
        $amount = $record['oper_amount'];

        // 查询药品库存
        $stockStmt = $db->prepare("SELECT drug_store FROM drug_def WHERE drug_id = :drug_id");
        $stockStmt->bindParam(':drug_id', $drugId, PDO::PARAM_INT);
        $stockStmt->execute();
        $stock = $stockStmt->fetch(PDO::FETCH_ASSOC);

        if (!$stock) {
            throw new \Exception("Drug not found in inventory", 404);
        }

        if ($stock['drug_store'] < $amount) {
            throw new \Exception("库存不足，无法发药", 400);
        }

        $now = date("Y-m-d H:i:s");
        $db->beginTransaction();

        // 1. 更新处方状态为“已发药”
        $updateSql = "UPDATE prescription_record SET status_code = 2, oper_time = :time WHERE pres_rcd_id = :id";
        $updateStmt = $db->prepare($updateSql);
        $updateStmt->bindParam(':time', $now);
        $updateStmt->bindParam(':id', $presRcdId, PDO::PARAM_INT);
        $updateStmt->execute();

        // 2. 插入库存操作记录（减少库存）
        $insertSql = "INSERT INTO drug_stock_record (oper_time, drug_id, oper_amount, status_code)
                      VALUES (:time, :drug_id, :amount, 0)";
        $insertStmt = $db->prepare($insertSql);
        $insertStmt->bindParam(':time', $now);
        $insertStmt->bindParam(':drug_id', $drugId, PDO::PARAM_INT);
        $insertStmt->bindParam(':amount', $amount, PDO::PARAM_INT);
        $insertStmt->execute();

        // 3. 更新 drug_def 表中库存数量
        $updateStockSql = "UPDATE drug_def SET drug_store = drug_store - :amount WHERE drug_id = :drug_id";
        $updateStockStmt = $db->prepare($updateStockSql);
        $updateStockStmt->bindParam(':amount', $amount, PDO::PARAM_INT);
        $updateStockStmt->bindParam(':drug_id', $drugId, PDO::PARAM_INT);
        $updateStockStmt->execute();

        $db->commit();

        echo ApiResponse::success([
            "message" => "药品发放成功",
            "pres_rcd_id" => $presRcdId
        ])->toJson();

    } catch (\Exception $e) {
        if (isset($db) && $db->inTransaction()) {
            $db->rollBack();
        }
        echo ApiResponse::error($e->getCode(), $e->getMessage())->toJson();
    }
}

handleRequest();
