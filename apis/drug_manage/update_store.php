<?php
/**
 * @file apis/UpdateStockApi.php
 * @brief API for updating drug stock quantity
 * @author xvjie
 * @date 2025-04-18
 */

header('Content-Type: application/json');
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
$allowedOrigins = ['http://localhost:5173'];
if (in_array($origin, $allowedOrigins)) {
    header("Access-Control-Allow-Origin: $origin");
    header("Access-Control-Allow-Credentials: true");
}
header("Access-Control-Allow-Methods: POST");        /* NOTE: change the allow method for each single api */
header("Access-Control-Allow-Headers: Content-Type");

require_once __DIR__ . '/../utils/ApiResponse.php';
require_once __DIR__ . '/../utils/Database.php';
require_once __DIR__ . '/../utils/utils.php';

use App\Response\ApiResponse;
use App\Database\Database;

/**
 * 更新指定药品的库存数量
 *
 * @param \PDO $db 数据库连接
 * @param string $drugName 药品名称
 * @param int $newStockQuantity 新的库存数量
 * @return bool 是否更新成功
 */
function updateDrugStock($db, $drugName, $newStockQuantity) {
    try {
        // 修改 SQL 查询语句以匹配实际的表名和字段名
        $stmt = $db->prepare("UPDATE drug_def SET drug_store = :qty WHERE drug_name = :name");
        $stmt->bindParam(':qty', $newStockQuantity, \PDO::PARAM_INT);
        $stmt->bindParam(':name', $drugName);
        return $stmt->execute();
    } catch (\PDOException $e) {
        throw new \Exception("Database update failed: " . $e->getMessage(), 500);
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
        $drugName = $_POST['drug_name'] ?? '';
        $newStockQuantity = intval($_POST['new_stock_quantity'] ?? 0);

        if (empty($drugName) || empty($newStockQuantity)) {
            throw new \Exception("Missing required parameters", 400);
        }

        $success = updateDrugStock($db, $drugName, $newStockQuantity);

        if ($success) {
            echo ApiResponse::success(["message" => "库存数量更新成功！"])->toJson();
        } else {
            throw new \Exception("更新失败：药品不存在或数量未变", 400);
        }

    } catch (\Exception $e) {
        echo ApiResponse::error($e->getCode(), $e->getMessage())->toJson();
    }
}

handleRequest();