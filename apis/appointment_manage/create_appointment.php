<?php
/**
 * @file apis/create_appointment.php
 * @brief the api to create an appointment record
 * @author Your Name
 * @date 2025-04-14
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
        $patientId = $_POST['patientId'];
        $doctorId = $_POST['doctorId'];
        $appointmentDate = $_POST['appointmentDate'];
        $appointmentTime = $_POST['appointmentTime'];
        $appointmentType = $_POST['appointmentType'];
        $departmentId = $_POST['departmentId'];

        /* verify the arguments */
        if (empty($patientId) || empty($doctorId) || empty($appointmentDate) || empty($appointmentTime) || empty($appointmentType) || empty($departmentId)) {
            throw new \Exception("empty field", 400);
        }

        // 插入新的挂号记录
        $stmt = $db->prepare("INSERT INTO appointments (PatientID, DoctorID, AppointmentDate, AppointmentTime, AppointmentType, AppointmentStatus, DepartmentID) VALUES (:patientId, :doctorId, :appointmentDate, :appointmentTime, :appointmentType, '待就诊', :departmentId)");
        $stmt->bindParam(':patientId', $patientId, \PDO::PARAM_INT);
        $stmt->bindParam(':doctorId', $doctorId, \PDO::PARAM_INT);
        $stmt->bindParam(':appointmentDate', $appointmentDate, \PDO::PARAM_STR);
        $stmt->bindParam(':appointmentTime', $appointmentTime, \PDO::PARAM_STR);
        $stmt->bindParam(':appointmentType', $appointmentType, \PDO::PARAM_STR);
        $stmt->bindParam(':departmentId', $departmentId, \PDO::PARAM_INT);
        $stmt->execute();

        /* return success response */
        echo ApiResponse::success("appointment created successfully")->toJson();
    } catch (\Exception $e) {
        /* return fail response */
        echo ApiResponse::error($e->getCode(), $e->getMessage())->toJson();
    }
}

/* call the request handling function */
handleRequest();