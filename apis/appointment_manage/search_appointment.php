<?php
/**
 * @file apis/search_appointments.php
 * @brief 根据搜索条件查询挂号记录的 API
 * @author xvjie
 * @date 2025-04-16
 */

// 设置响应头为 JSON 格式
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

// 引入必要的文件
require_once __DIR__ . '/../utils/ApiResponse.php';
require_once __DIR__ . '/../utils/Database.php';
require_once __DIR__ . '/../utils/utils.php';

use App\Response\ApiResponse;
use App\Database\Database;

/**
 * 根据搜索条件查询挂号记录
 * @param PDO $db 数据库连接对象
 * @param array $searchParams 搜索参数数组
 * @return array 查询结果数组
 * @throws Exception 如果数据库查询失败
 */
function searchAppointments($db, $searchParams) {
    $sql = "SELECT
                a.AppointmentID,
                p.FullName AS PatientName,
                p.Gender AS PatientGender,
                p.Age AS PatientAge,
                d.FullName AS DoctorName,
                dept.Department AS DepartmentName,
                a.AppointmentType,
                CONCAT(a.AppointmentDate, ' ', a.AppointmentTime) AS AppointmentDateTime,
                a.AppointmentStatus
            FROM
                appointments a
            JOIN
                patients p ON a.PatientID = p.PatientID
            JOIN
                doctors d ON a.DoctorID = d.DoctorID
            JOIN
                departments dept ON a.DepartmentID = dept.DepartmentID
            WHERE
                1 = 1";

    $params = [];

    if (isset($searchParams['patientName']) && !empty($searchParams['patientName'])) {
        $sql .= " AND p.FullName LIKE :patientName";
        $params[':patientName'] = '%' . $searchParams['patientName'] . '%';
    }

    if (isset($searchParams['doctorName']) && !empty($searchParams['doctorName'])) {
        $sql .= " AND d.FullName LIKE :doctorName";
        $params[':doctorName'] = '%' . $searchParams['doctorName'] . '%';
    }

    if (isset($searchParams['appointmentType']) && !empty($searchParams['appointmentType'])) {
        $sql .= " AND a.AppointmentType = :appointmentType";
        $params[':appointmentType'] = $searchParams['appointmentType'];
    }

    if (isset($searchParams['appointmentDate']) && !empty($searchParams['appointmentDate'])) {
        $sql .= " AND a.AppointmentDate = :appointmentDate";
        $params[':appointmentDate'] = $searchParams['appointmentDate'];
    }

    if (isset($searchParams['appointmentStatus']) && !empty($searchParams['appointmentStatus'])) {
        $sql .= " AND a.AppointmentStatus = :appointmentStatus";
        $params[':appointmentStatus'] = $searchParams['appointmentStatus'];
    }

    try {
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        throw new Exception("Database query failed: " . $e->getMessage(), 500);
    }
}

/**
 * 处理请求的函数
 */
function handleRequest() {
    try {
        // 验证请求方法
        session_start();
        verifyMethods(['POST']);

        // 初始化数据库连接
        $db = initializeDatabase();

        // 获取 POST 请求中的搜索参数
        $searchParams = [
            'patientName' => $_POST['patientName'] ?? '',
            'doctorName' => $_POST['doctorName'] ?? '',
            'appointmentType' => $_POST['appointmentType'] ?? '',
            'appointmentDate' => $_POST['appointmentDate'] ?? '',
            'appointmentStatus' => $_POST['appointmentStatus'] ?? ''
        ];

        // 执行搜索
        $appointments = searchAppointments($db, $searchParams);

        // 返回成功响应
        echo ApiResponse::success("Search successful", $appointments)->toJson();
    } catch (Exception $e) {
        // 返回失败响应
        echo ApiResponse::error($e->getCode(), $e->getMessage())->toJson();
    }
}

// 调用处理请求的函数
handleRequest();