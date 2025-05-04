<?php
/**
 * @file apis/prescription_manage/delete_prescription.php
 * @brief API for deleting a prescription record
 * @author xvjie
 * @date 2025-05-03
 */

// 设置响应头为 JSON 格式
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

function handleRequest() {
    try {
        session_start();

        // 仅允许 admin 操作
        if ($_SESSION["UserType"] !== "admin") {
            //throw new \Exception("operation not permitted for current user", 401);
        }

        // 验证请求方法
        verifyMethods(['POST']);

        // 初始化数据库连接
        $db = initializeDatabase();

        // 获取 POST 请求中的数据
        $presId = $_POST['pres_id'] ?? '';

        // 检查 pres_id 是否为空
        if (empty($presId)) {
            throw new \Exception("Missing required parameter: pres_id", 400);
        }

        // 执行删除操作
        $stmt = $db->prepare("DELETE FROM prescription_record WHERE pres_id = :pres_id");
        $stmt->bindParam(':pres_id', $presId, PDO::PARAM_INT);
        $stmt->execute();

        // 检查是否删除成功
        if ($stmt->rowCount() > 0) {
            echo ApiResponse::success(["message" => "Prescription record deleted successfully"])->toJson();
        } else {
            throw new \Exception("Prescription record not found", 404);
        }

    } catch (\Exception $e) {
        echo ApiResponse::error($e->getCode(), $e->getMessage())->toJson();
    }
}

handleRequest();