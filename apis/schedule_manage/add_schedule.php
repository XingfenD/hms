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
header("Access-Control-Allow-Methods: POST");        /* NOTE: change the allow method for each single api */
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

        $doctor_id = $_POST['doctor_id'];
        $schedule_date = $_POST['schedule_date'];
        $shift = $_POST['shift'];
        $department_id = $_POST['department_id'];

        if ($shift == 'morning') {
            $start_time = '08:30:00';
            $end_time = '12:00:00';
            $specific_shift = '早班';
        } elseif ($shift == 'afternoon') {
            $start_time = '14:00:00';
            $end_time = '17:30:00';
            $specific_shift = '下午班';
        } elseif ($shift == 'night') {
            $start_time = '18:00:00';
            $end_time = '07:00:00';
            $specific_shift = '晚班';
            $next_day = new DateTime($schedule_date);
            $next_day->modify('+1 day');
            $schedule_date_end = $next_day->format('Y-m-d');
        } else {
            throw new \Exception("无效的班次", 400);
        }

        // 验证排班时间是否已过
        $current_datetime = date("Y-m-d H:i:s");
        if ($schedule_date < date("Y-m-d") || ($schedule_date == date("Y-m-d") && $start_time <= date("H:i:s"))) {
            throw new \Exception("排班时间不能早于当前时间", 400);
        }

        // 检查重复排班
        $stmt = $db->prepare("SELECT * FROM schedules WHERE DoctorID=? AND ScheduleDate=? AND Shift=?");
        $stmt->execute([$doctor_id, $schedule_date, $specific_shift]);
        if ($stmt->rowCount() > 0) {
            throw new \Exception("该医生在指定日期已有相同班次的排班记录", 409);
        }

        if ($shift === 'night') {
            // 夜班插入两天
            $sql = "INSERT INTO schedules (DoctorID, ScheduleDate, StartTime, EndTime, Shift, DepartmentID) VALUES
                    (?, ?, ?, '23:59:59', ?, ?),
                    (?, ?, '00:00:00', ?, ?, ?)";
            $stmt = $db->prepare($sql);
            $stmt->execute([
                $doctor_id, $schedule_date, $start_time, $specific_shift, $department_id,
                $doctor_id, $schedule_date_end, $end_time, $specific_shift, $department_id
            ]);
            $first_id = $db->lastInsertId();
            $second_id = $first_id + 1;

            $db->prepare("UPDATE schedules SET RelatedScheduleID=? WHERE ScheduleID=?")->execute([$second_id, $first_id]);
            $db->prepare("UPDATE schedules SET RelatedScheduleID=? WHERE ScheduleID=?")->execute([$first_id, $second_id]);
        } else {
            $stmt = $db->prepare("INSERT INTO schedules (DoctorID, ScheduleDate, StartTime, EndTime, Shift, DepartmentID)
                                  VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$doctor_id, $schedule_date, $start_time, $end_time, $specific_shift, $department_id]);
        }

        echo ApiResponse::success(["message" => "排班成功"])->toJson();
    } catch (\Exception $e) {
        echo ApiResponse::error($e->getCode(), $e->getMessage())->toJson();
    }
}

handleRequest();