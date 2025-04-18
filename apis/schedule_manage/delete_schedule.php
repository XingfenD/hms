<?php
/**
 * @file apis/DeleteSchedule.php
 * @brief API for deleting doctor schedules
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

function handleRequest() {
    try {
        session_start();
        verifyMethods(['POST']);

        if ($_SESSION["UserType"] !== "admin") {
            //throw new \Exception("operation not permitted for current user", 403);
        }

        $db = initializeDatabase();
        $schedule_id = $_POST['schedule_id'] ?? null;

        if (!$schedule_id) {
            throw new \Exception("缺少 schedule_id 参数", 400);
        }

        // 检查排班是否存在
        $stmt = $db->prepare("SELECT * FROM schedule WHERE schedule_id = ?");
        $stmt->execute([$schedule_id]);
        if ($stmt->rowCount() === 0) {
            throw new \Exception("排班记录不存在", 404);
        }

        $stmt = $db->prepare("DELETE FROM schedule WHERE schedule_id = ?");
        $stmt->execute([$schedule_id]);

        echo ApiResponse::success(["message" => "排班删除成功"])->toJson();
    } catch (\Exception $e) {
        echo ApiResponse::error($e->getCode(), $e->getMessage())->toJson();
    }
}

handleRequest();