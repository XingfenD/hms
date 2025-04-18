<?php
/**
 * @file apis/AddSchedule.php
 * @brief API for adding doctor schedules
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
            throw new \Exception("operation not permitted for current user", 403);
        }

        $db = initializeDatabase();

        $doctor_id = $_POST['doctor_id'];
        $schedule_date = $_POST['schedule_date'];
        $shift = $_POST['shift'];

        if ($shift == 'morning') {
            $start_time = '08:30:00';
            $end_time = '12:00:00';
        } elseif ($shift == 'afternoon') {
            $start_time = '14:00:00';
            $end_time = '17:30:00';
        } elseif ($shift == 'night') {
            $start_time = '18:00:00';
            $end_time = '07:00:00';
        } else {
            throw new \Exception("无效的班次", 400);
        }

        // 验证排班时间是否已过
        $current_datetime = date("Y-m-d H:i:s");
        if ($schedule_date < date("Y-m-d") || ($schedule_date == date("Y-m-d") && $start_time <= date("H:i:s"))) {
            throw new \Exception("排班时间不能早于当前时间", 400);
        }

        // 检查重复排班
        $stmt = $db->prepare("SELECT * FROM schedule WHERE doc_id = ? AND date = ? AND start_time = ? AND end_time = ?");
        $stmt->execute([$doctor_id, $schedule_date, $start_time, $end_time]);
        if ($stmt->rowCount() > 0) {
            throw new \Exception("该医生在指定日期和时间段已有排班记录", 409);
        }

        //插入记录
        $stmt = $db->prepare("INSERT INTO schedule (doc_id, date, start_time, end_time) VALUES (?, ?, ?, ?)");
        $stmt->execute([$doctor_id, $schedule_date, $start_time, $end_time]);

        echo ApiResponse::success(["message" => "排班成功"])->toJson();
    } catch (\Exception $e) {
        echo ApiResponse::error($e->getCode(), $e->getMessage())->toJson();
    }
}

handleRequest();