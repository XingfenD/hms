<?php
/**
 * @file apis/create_appointment.php
 * @brief the api to create an appointment record
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
header("Access-Control-Allow-Methods: POST");        /* NOTE: change the allow method for each single api */
header("Access-Control-Allow-Headers: Content-Type");

require_once __DIR__ . '/../utils/ApiResponse.php';
require_once __DIR__ . '/../utils/Database.php';
require_once __DIR__ . '/../utils/utils.php';

use App\Response\ApiResponse;
use App\Database\Database;

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
        $patientId = $_POST['patient_id'] ?? '';
        $apScId = $_POST['schedule_id'] ?? '';
        $appointmentDate = $_POST['appointment_date'] ?? '';
        $appointmentTime = $_POST['appointment_time'] ?? '';

        /* verify the arguments */
        if (empty($patientId) || empty($appointmentDate) || empty($appointmentTime) || empty($apScId)) {
            throw new \Exception("empty field", 400);
        }

        // 通过 ap_sc_id 从 schedule 表中获取 doc_id
        $stmt = $db->prepare("SELECT doc_id FROM schedule WHERE schedule_id = :apScId");
        $stmt->bindParam(':apScId', $apScId, \PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$result) {
            throw new \Exception("未找到对应的排班信息", 404);
        }

        $doctorId = $result['doc_id'];

        // 插入新的挂号记录，修改 SQL 语句以匹配实际表名和字段名
        $stmt = $db->prepare("INSERT INTO appointment (pat_id, doc_id, ap_date, ap_time, ap_status, ap_sc_id) VALUES (:patientId, :doctorId, :appointmentDate, :appointmentTime, '0', :apScId)");
        $stmt->bindParam(':patientId', $patientId, \PDO::PARAM_INT);
        $stmt->bindParam(':doctorId', $doctorId, \PDO::PARAM_INT);
        $stmt->bindParam(':appointmentDate', $appointmentDate, \PDO::PARAM_STR);
        $stmt->bindParam(':appointmentTime', $appointmentTime, \PDO::PARAM_STR);
        $stmt->bindParam(':apScId', $apScId, \PDO::PARAM_INT);
        $stmt->execute();

        // 获取插入的挂号记录的 ID
        $appointmentId = $db->lastInsertId();

        /* return success response */
        echo ApiResponse::success(["message" => "create appointment success", "appointment_id" => $appointmentId])->toJson();
    } catch (\Exception $e) {
        /* return fail response */
        echo ApiResponse::error($e->getCode(), $e->getMessage())->toJson();
    }
}

/* call the request handling function */
handleRequest();