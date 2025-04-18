<?php
/**
 * @file apis/AddStockApi.php
 * @brief API for increasing the stock quantity of a drug
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
header("Access-Control-Allow-Methods: POST");        /* NOTE: change the allow method for each single api */
header("Access-Control-Allow-Headers: Content-Type");

require_once __DIR__ . '/../utils/ApiResponse.php';
require_once __DIR__ . '/../utils/Database.php';
require_once __DIR__ . '/../utils/utils.php';

use App\Response\ApiResponse;

function addDrugStock($db, $drugName, $addQty) {
    try {
        $stmt = $db->prepare("UPDATE drugs SET StockQuantity = StockQuantity + :addQty WHERE DrugName = :name");
        $stmt->bindParam(':addQty', $addQty, \PDO::PARAM_INT);
        $stmt->bindParam(':name', $drugName);
        $stmt->execute();
        return $stmt->rowCount(); // 返回影响的行数
    } catch (\PDOException $e) {
        throw new \Exception("Database update failed: " . $e->getMessage(), 500);
    }
}

function handleRequest() {
    try {
        session_start();

        if (!isset($_SESSION["UserType"]) || $_SESSION["UserType"] !== "admin") {
            throw new \Exception("operation not permitted for current user", 401);
        }

        verifyMethods(['POST']);
        $db = initializeDatabase();

        // 获取 POST 请求中的数据
        $drugName = $_POST['drug_name'] ?? '';
        $addQuantity = intval($_POST['add_quantity'] ?? 0);

        if (empty($drugName) || empty($addQuantity)) {
            throw new \Exception("Missing required parameters", 400);
        }

        $added = addDrugStock($db, $drugName, $addQuantity);

        if ($added > 0) {
            echo ApiResponse::success(["message" => "库存已增加"])->toJson();
        } else {
            throw new \Exception("药品不存在或数量未变化", 404);
        }

    } catch (\Exception $e) {
        echo ApiResponse::error($e->getCode(), $e->getMessage())->toJson();
    }
}

handleRequest();