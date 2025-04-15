<?php
/**
 * @file apis/update_appointment_status.php
 * @brief the api to update the status of an appointment to "已签到"
 * @author xvjie
 * @date 2025-04-15
 */

/* set the response header to JSON */
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

require_once __DIR__ . '/../utils/ApiResponse.php';
require_once __DIR__ . '/../utils/Database.php';
require_once __DIR__ . '/../utils/utils.php';

use App\Response\ApiResponse;
use App\Database\Database;

/* 验证指定的 AppointmentID 是否存在 */
function checkAppointmentExists($db, $appointmentId) {
    try {
        $stmt = $db->prepare("SELECT COUNT(*) AS Count FROM appointments WHERE AppointmentID = :appointmentId");
        $stmt->bindParam(':appointmentId', $appointmentId, \PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $result['Count'] > 0;
    } catch (\PDOException $e) {
        throw new \Exception("Database query failed: ". $e->getMessage(), 500);
    }
}

/* 更新指定 AppointmentID 的挂号记录状态为已签到 */
function updateAppointmentStatus($db, $appointmentId) {
    try {
        $stmt = $db->prepare("UPDATE appointments SET AppointmentStatus = '已取消' WHERE AppointmentID = :appointmentId");
        $stmt->bindParam(':appointmentId', $appointmentId, \PDO::PARAM_INT);
        $stmt->execute();
    } catch (\PDOException $e) {
        throw new \Exception("Database query failed: ". $e->getMessage(), 500);
    }
}

/* the function to handle the request */
function handleRequest() {
    try {
        /* use verifyMethods function in utils/utils.php */
        session_start();

        verifyMethods(['POST']);

        /* use initializeDatabase() function in utils/utils.php */
        /* initialize the database connection */
        $db = initializeDatabase();

        // 获取 POST 请求中的数据
        $appointmentId = $_POST['appointmentId'];

        /* verify the arguments */
        if (empty($appointmentId)) {
            throw new \Exception("empty appointmentId field", 400);
        }

        // 验证指定的 AppointmentID 是否存在
        if (!checkAppointmentExists($db, $appointmentId)) {
            throw new \Exception("Appointment with the given ID does not exist", 404);
        }

        // 更新挂号记录状态为已签到
        updateAppointmentStatus($db, $appointmentId);

        /* return success response */
        echo ApiResponse::success("Appointment status updated to '已取消' successfully")->toJson();
    } catch (\Exception $e) {
        /* return fail response */
        echo ApiResponse::error($e->getCode(), $e->getMessage())->toJson();
    }
}

/* call the request handling function */
handleRequest();