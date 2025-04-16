<?php
/**
 * @file apis/AddDrugApi.php
 * @brief API for adding a new drug
 * @author 
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
use App\Database\Database;

/**
 * 向 drugs 表插入新药品记录
 *
 * @param \PDO $db 数据库连接
 * @param string $drugName 药品名称
 * @param int $stockQuantity 库存数量
 * @param float $price 药品价格
 * @return bool 是否添加成功
 */
function addDrug($db, $drugName, $stockQuantity, $price) {
    try {
        $stmt = $db->prepare("INSERT INTO drugs (DrugName, StockQuantity, Price) VALUES (:name, :qty, :price)");
        $stmt->bindParam(':name', $drugName);
        $stmt->bindParam(':qty', $stockQuantity, \PDO::PARAM_INT);
        $stmt->bindParam(':price', $price);
        return $stmt->execute();
    } catch (\PDOException $e) {
        throw new \Exception("Database insertion failed: " . $e->getMessage(), 500);
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

        // 获取 POST 请求体数据
        $input = json_decode(file_get_contents("php://input"), true);

        if (empty($input['drug_name']) || !isset($input['stock_quantity']) || !isset($input['price'])) {
            throw new \Exception("Missing required parameters", 400);
        }

        $drugName = $input['drug_name'];
        $stockQuantity = intval($input['stock_quantity']);
        $price = floatval($input['price']);

        $result = addDrug($db, $drugName, $stockQuantity, $price);

        if ($result) {
            echo ApiResponse::success(["message" => "药品添加成功！"])->toJson();
        } else {
            throw new \Exception("药品添加失败", 500);
        }
    } catch (\Exception $e) {
        echo ApiResponse::error($e->getCode(), $e->getMessage())->toJson();
    }
}

handleRequest();
