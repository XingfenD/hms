<?php
/**
 * @file apis/AddStockApi.php
 * @brief API for increasing or decreasing the stock quantity of a drug
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

function changeDrugStock($db, $drugId, $operAmount, $statusCode) {
    try {
        // 检查药品是否存在
        $drugIdStmt = $db->prepare("SELECT drug_id FROM drug_def WHERE drug_id = :id");
        $drugIdStmt->bindParam(':id', $drugId, \PDO::PARAM_INT);
        $drugIdStmt->execute();
        $drugIdResult = $drugIdStmt->fetch(PDO::FETCH_ASSOC);

        if (!$drugIdResult) {
            throw new \Exception("药品不存在", 404);
        }

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
        $operAmount = intval($_POST['oper_amount'] ?? 0);
        $statusCode = intval($_POST['status_code'] ?? -1);

        if (empty($drugId) || empty($operAmount) || ($statusCode != 0 && $statusCode != 1)) {
            throw new \Exception("缺少必要参数或 status_code 无效", 400);
        }

        // 调用函数
        $changed = changeDrugStock($db, $drugId, $operAmount, $statusCode);

        if ($changed > 0) {
            if ($statusCode == 1) {
                echo ApiResponse::success(["message" => "库存已增加"])->toJson();
            } else {
                echo ApiResponse::success(["message" => "库存已减少"])->toJson();
            }
        } else {
            throw new \Exception("药品不存在或数量未变化", 404);
        }

    } catch (\Exception $e) {
        echo ApiResponse::error($e->getCode(), $e->getMessage())->toJson();
    }
}

handleRequest();