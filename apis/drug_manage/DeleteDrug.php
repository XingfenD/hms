<?php
/**
 * @file apis/DeleteDrugApi.php
 * @brief API for deleting a drug by name
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

function deleteDrugByName($db, $drugName) {
    try {
        $stmt = $db->prepare("DELETE FROM drugs WHERE DrugName = :name");
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
            //throw new \Exception("operation not permitted for current user", 401);
        }

        verifyMethods(['POST']);
        $db = initializeDatabase();

        $input = json_decode(file_get_contents("php://input"), true);
        if (empty($input['drug_name'])) {
            throw new \Exception("Missing parameter: drug_name", 400);
        }

        $deleted = deleteDrugByName($db, $input['drug_name']);

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
