<?php
/**
 * @file apis/ModifyDrugStockApi.php
 * @brief API for modifying the stock quantity of a drug based on a new input quantity
 * @author xvjie
 * @date 2025-04-15
 */

header('Content-Type: application/json');
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
$allowedOrigins = ['http://localhost:5173'];
if (in_array($origin, $allowedOrigins)) {
    header("Access-Control-Allow-Origin: $origin");
    header("Access-Control-Allow-Credentials: true");
}
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

require_once __DIR__ . '/../utils/ApiResponse.php';
require_once __DIR__ . '/../utils/Database.php';
require_once __DIR__ . '/../utils/utils.php';

use App\Response\ApiResponse;
use App\Database\Database;

function modifyDrugStock($db, $drugId, $newStock) {
    try {
        // 检查药品是否存在
        $drugIdStmt = $db->prepare("SELECT drug_id, drug_store FROM drug_def WHERE drug_id = :id");
        $drugIdStmt->bindParam(':id', $drugId, \PDO::PARAM_INT);
        $drugIdStmt->execute();
        $drugIdResult = $drugIdStmt->fetch(PDO::FETCH_ASSOC);

        if (!$drugIdResult) {
            throw new \Exception("药品不存在", 404);
        }

        $currentStock = $drugIdResult['drug_store'];
        $operAmount = abs($newStock - $currentStock);
        $statusCode = $newStock > $currentStock ? 1 : 0;

        // 插入操作记录到 drug_record 表，让触发器去更新库存
        $now = date("Y-m-d H:i:s");
        $insertSql = "INSERT INTO drug_record (oper_time, drug_id, oper_amount, status_code)
                      VALUES (:time, :drug_id, :amount, :status)";
        $insertStmt = $db->prepare($insertSql);
        $insertStmt->bindParam(':time', $now);
        $insertStmt->bindParam(':drug_id', $drugId, \PDO::PARAM_INT);
        $insertStmt->bindParam(':amount', $operAmount, \PDO::PARAM_INT);
        $insertStmt->bindParam(':status', $statusCode, \PDO::PARAM_INT);
        $insertStmt->execute();

        return $insertStmt->rowCount(); // 返回影响的行数
    } catch (\Exception $e) {
        throw new \Exception("数据库操作失败: " . $e->getMessage(), 500);
    }
}

function handleRequest() {
    try {
        session_start();

        if (!isset($_SESSION["UserType"]) || $_SESSION["UserType"] !== "admin") {
            //throw new \Exception("operation not permitted for current user", 401);
        }

        verifyMethods(['POST']);
        $database = new Database();
        $db = $database->connect();

        // 获取 POST 请求中的数据
        $drugId = intval($_POST['drug_id'] ?? 0);
        $newStock = intval($_POST['new_stock'] ?? 0);

        if (empty($drugId) || empty($newStock)) {
            throw new \Exception("缺少必要参数", 400);
        }

        // 调用函数
        $changed = modifyDrugStock($db, $drugId, $newStock);

        if ($changed > 0) {
            echo ApiResponse::success(["message" => "库存已更新"])->toJson();
        } else {
            throw new \Exception("药品不存在或数量未变化", 404);
        }

    } catch (\Exception $e) {
        echo ApiResponse::error($e->getCode(), $e->getMessage())->toJson();
    }
}

handleRequest();