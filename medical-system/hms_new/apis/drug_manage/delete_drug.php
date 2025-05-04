<?php
/**
 * @file apis/DeleteDrugApi.php
 * @brief API for deleting a drug by name
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
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

require_once __DIR__ . '/../utils/ApiResponse.php';
require_once __DIR__ . '/../utils/Database.php';
require_once __DIR__ . '/../utils/utils.php';

use App\Response\ApiResponse;
use App\Database\Database;

/**
 * 根据药品名称删除药品
 *
 * @param \PDO $db 数据库连接
 * @param string $drugName 药品名称
 * @return int 删除的行数
 * @throws \Exception 如果数据库删除操作失败
 */
function deleteDrugByName($db, $drugName) {
    try {
        // 修改 SQL 查询语句以匹配实际的表名和字段名
        $stmt = $db->prepare("DELETE FROM drug_def WHERE drug_name = :name");
        $stmt->bindParam(':name', $drugName);
        $stmt->execute();
        return $stmt->rowCount(); // 返回删除的行数
    } catch (\PDOException $e) {
        throw new \Exception("Database delete failed: " . $e->getMessage(), 500);
    }
}

function handleRequest() {
    try {
        session_start();

        if (!isset($_SESSION["UserType"]) || $_SESSION["UserType"] !== "admin") {
            throw new \Exception("operation not permitted for current user", 401);
        }

        verifyMethods(['POST']);
        $database = new Database();
        $db = $database->connect();

        // 获取 POST 请求中的数据
        $drugName = $_POST['drug_name'] ?? '';

        if (empty($drugName)) {
            throw new \Exception("Missing required parameter: drug_name", 400);
        }

        $deleted = deleteDrugByName($db, $drugName);

        if ($deleted > 0) {
            echo ApiResponse::success(["message" => "药品已成功删除"])->toJson();
        } else {
            throw new \Exception("药品不存在", 404);
        }

    } catch (\Exception $e) {
        echo ApiResponse::error($e->getCode(), $e->getMessage())->toJson();
    }
}

handleRequest();