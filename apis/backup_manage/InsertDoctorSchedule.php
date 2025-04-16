<?php
/**
 * @file apis/ScheduleAdd.php
 * @brief API for adding schedule
 * @author xingfen
 * @date 2025-04-13
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

        if ($_SESSION["UserType"] !== "admin") {
            throw new \Exception("operation not permitted for current user", 403);
        }

        verifyMethods(['POST']);
        $db = initializeDatabase();

        $input = json_decode(file_get_contents('php://input'), true);

        if (!isset($input['action']) || $input['action'] !== 'add') {
            throw new \Exception("Invalid action", 400);
        }

        $doctor_id = $input['doctor_id'] ?? null;
        $schedule_date = $input['schedule_date'] ?? null;
        $shift = $input['shift'] ?? null;
        $department_id = $input['department_id'] ?? null;

        if (!$doctor_id || !$schedule_date || !$shift || !$department_id) {
            throw new \Exception("Missing parameters", 400);
        }

        // 设置班次时间
        $specific_shift = '';
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

        // 验证排班时间是否为将来
        $current_datetime = date("Y-m-d H:i:s");
        if ($schedule_date < date("Y-m-d") || ($schedule_date == date("Y-m-d") && $start_time <= date("H:i:s"))) {
            throw new \Exception("排班时间无效", 400);
        }

        // 查询是否已有相同排班
        $stmt_check = $db->prepare("SELECT * FROM schedules WHERE DoctorID = :doctor_id AND ScheduleDate = :schedule_date AND Shift = :shift");
        $stmt_check->execute([
            ':doctor_id' => $doctor_id,
            ':schedule_date' => $schedule_date,
            ':shift' => $specific_shift
        ]);
        if ($stmt_check->rowCount() > 0) {
            throw new \Exception("该医生在指定日期已有相同班次的排班记录", 409);
        }

        if ($shift == 'night') {
            // 晚班需插入两条记录
            $db->beginTransaction();

            $stmt1 = $db->prepare("INSERT INTO schedules (DoctorID, ScheduleDate, StartTime, EndTime, Shift, DepartmentID)
                                   VALUES (:doctor_id, :schedule_date, '18:00:00', '23:59:59', :shift, :department_id)");
            $stmt1->execute([
                ':doctor_id' => $doctor_id,
                ':schedule_date' => $schedule_date,
                ':shift' => $specific_shift,
                ':department_id' => $department_id
            ]);
            $first_id = $db->lastInsertId();

            $stmt2 = $db->prepare("INSERT INTO schedules (DoctorID, ScheduleDate, StartTime, EndTime, Shift, DepartmentID)
                                   VALUES (:doctor_id, :schedule_date_end, '00:00:00', '07:00:00', :shift, :department_id)");
            $stmt2->execute([
                ':doctor_id' => $doctor_id,
                ':schedule_date_end' => $schedule_date_end,
                ':shift' => $specific_shift,
                ':department_id' => $department_id
            ]);
            $second_id = $db->lastInsertId();

            // 关联两个班次
            $stmt3 = $db->prepare("UPDATE schedules SET RelatedScheduleID = :related_id WHERE ScheduleID = :id");
            $stmt3->execute([':related_id' => $second_id, ':id' => $first_id]);
            $stmt3->execute([':related_id' => $first_id, ':id' => $second_id]);

            $db->commit();

            echo ApiResponse::success(["message" => "晚班排班成功"])->toJson();
        } else {
            // 早班/下午班
            $stmt = $db->prepare("INSERT INTO schedules (DoctorID, ScheduleDate, StartTime, EndTime, Shift, DepartmentID)
                                  VALUES (:doctor_id, :schedule_date, :start_time, :end_time, :shift, :department_id)");
            $stmt->execute([
                ':doctor_id' => $doctor_id,
                ':schedule_date' => $schedule_date,
                ':start_time' => $start_time,
                ':end_time' => $end_time,
                ':shift' => $specific_shift,
                ':department_id' => $department_id
            ]);

            echo ApiResponse::success(["message" => "排班成功"])->toJson();
        }

    } catch (\Exception $e) {
        if ($db && $db->inTransaction()) {
            $db->rollBack();
        }
        echo ApiResponse::error($e->getCode() ?: 500, $e->getMessage())->toJson();
    }
}

handleRequest();
