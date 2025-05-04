<?php
/**
 * @file apis/ModifyDrugApi.php
 * @brief API for modifying the information of a drug based on drug_id
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

/**
 * 修改 drug_def 表中的药品记录
 *
 * @param \PDO $db 数据库连接
 * @param int $drugId 药品ID
 * @param string $drugName 药品名称
 * @param float $price 药品价格
 * @param string $specification 药品规格
 * @param string $manufacturer 药品制造商
 * @param string $category 药品类别
 * @param string $instruction 药品说明
 * @return bool 是否修改成功
 */
function modifyDrug($db, $drugId, $drugName, $price, $specification, $manufacturer, $category, $instruction) {
    try {
        $updateSql = "UPDATE drug_def 
                      SET drug_name = :name, 
                          drug_price = :price, 
                          drug_specification = :specification, 
                          drug_manufacturer = :manufacturer, 
                          drug_category = :category, 
                          drug_instructions = :instruction 
                      WHERE drug_id = :id";
        $stmt = $db->prepare($updateSql);
        $stmt->bindParam(':id', $drugId, PDO::PARAM_INT);
        $stmt->bindParam(':name', $drugName);
        $stmt->bindParam(':price', $price);
        $stmt->bindParam(':specification', $specification);
        $stmt->bindParam(':manufacturer', $manufacturer);
        $stmt->bindParam(':category', $category);
        $stmt->bindParam(':instruction', $instruction);
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
        $drugId = intval($_POST['drug_id'] ?? 0);
        $drugName = $_POST['drug_name'] ?? '';
        $price = floatval($_POST['price'] ?? 0);
        $specification = $_POST['drug_specification'] ?? '';
        $manufacturer = $_POST['drug_manufacturer'] ?? '';
        $category = $_POST['drug_category'] ?? '';
        $instruction = $_POST['drug_instructions'] ?? '';

        if (empty($drugId) || empty($drugName) || empty($price)) {
            throw new \Exception("Missing required parameters", 400);
        }

        // 调用函数
        $result = modifyDrug($db, $drugId, $drugName, $price, $specification, $manufacturer, $category, $instruction);

        if ($result) {
            echo ApiResponse::success(["message" => "药品信息修改成功！"])->toJson();
        } else {
            throw new \Exception("药品信息修改失败", 500);
        }
    } catch (\Exception $e) {
        echo ApiResponse::error($e->getCode(), $e->getMessage())->toJson();
    }
}

handleRequest();