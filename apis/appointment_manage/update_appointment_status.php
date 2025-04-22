<?php
/**
 * @file apis/update_appointment_status.php
 * @brief the api to update the status of an appointment to a new status
 * @author xvjie
 * @date 2025-04-22
 */

/* set the response header to JSON */
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

/* 验证指定的 AppointmentID 是否存在 */
function checkAppointmentExists($db, $appointmentId) {
    try {
        // 修改 SQL 查询语句以匹配实际的表名和字段名
        $stmt = $db->prepare("SELECT COUNT(*) AS Count FROM appointment WHERE ap_id = :appointmentId");
        $stmt->bindParam(':appointmentId', $appointmentId, \PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $result['Count'] > 0;
    } catch (\PDOException $e) {
        throw new \Exception("Database query failed: ". $e->getMessage(), 500);
    }
}

/* 更新指定 AppointmentID 的挂号记录状态为新状态 */
function updateAppointmentStatus($db, $appointmentId, $newStatus) {
    try {
        // 修改 SQL 查询语句以匹配实际的表名和字段名
        $stmt = $db->prepare("UPDATE appointment SET ap_status = :newStatus WHERE ap_id = :appointmentId");
        $stmt->bindParam(':appointmentId', $appointmentId, \PDO::PARAM_INT);
        $stmt->bindParam(':newStatus', $newStatus, \PDO::PARAM_INT);
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

        // 建立数据库连接
        $database = new Database();
        $db = $database->connect();

        // 获取 POST 请求中的数据
        $appointmentId = $_POST['appointment_id'] ?? '';
        $newStatus = $_POST['new_status'] ?? '';

        /* verify the arguments */
        if (empty($appointmentId) || empty($newStatus)) {
            throw new \Exception("empty appointmentId or new_status field", 400);
        }

        // 验证新状态是否合法
        if (!in_array($newStatus, [1, 2])) {
            throw new \Exception("Invalid new_status value. Allowed values are 1 or 2.", 400);
        }

        // 验证指定的 AppointmentID 是否存在
        if (!checkAppointmentExists($db, $appointmentId)) {
            throw new \Exception("Appointment with the given ID does not exist", 404);
        }

        // 更新挂号记录状态为新状态
        updateAppointmentStatus($db, $appointmentId, $newStatus);

        $statusText = ($newStatus == 1) ? '正在进行' : '已结束';

        /* return success response */
        echo ApiResponse::success([
            "message" => "挂号状态更新成功",
            "new_status" => $statusText
        ])->toJson();
    } catch (\Exception $e) {
        /* return fail response */
        echo ApiResponse::error($e->getCode(), $e->getMessage())->toJson();
    }
}

/* call the request handling function */
handleRequest();