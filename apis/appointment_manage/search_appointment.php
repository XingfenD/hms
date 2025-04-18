<?php
/**
 * @file apis/search_appointments.php
 * @brief 根据搜索条件查询挂号记录的 API
 * @author xvjie
 * @date 2025-04-18
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

/**
 * 根据搜索条件查询挂号记录
 * @param \PDO $db 数据库连接对象
 * @param array $searchParams 搜索参数数组
 * @return array 查询结果数组
 * @throws \Exception 如果数据库查询失败
 */
function searchAppointments($db, $searchParams) {
    $sql = "SELECT
                a.ap_id AS AppointmentID,
                a.pat_id AS PatientId,
                ui.user_name AS PatientName,
                ui.user_gender AS PatientGender,
                ui.user_age AS PatientAge,
                a.doc_id AS DoctorID,
                dt.title_name AS DoctorTitle,
                dept.dep_name AS DepartmentName,
                CONCAT(a.ap_date, ' ', a.ap_time) AS AppointmentDateTime,
                CASE a.ap_status
                    WHEN 0 THEN '已预约'
                    WHEN 1 THEN '正在进行'
                    WHEN 2 THEN '已结束'
                    ELSE '未知状态'
                END AS AppointmentStatus
            FROM
                appointment a
            JOIN
                user_info ui ON a.pat_id = ui.user_id
            JOIN
                doctor d ON a.doc_id = d.doc_id
            JOIN
                doc_title dt ON d.title_id = dt.title_id
            JOIN
                department dept ON d.dep_id = dept.dep_id
            WHERE
                1 = 1";

    $params = [];

    if (isset($searchParams['pat_id']) && !empty($searchParams['pat_id'])) {
        $sql .= " AND a.pat_id = :pat_id";
        $params[':pat_id'] = $searchParams['pat_id'];
    }

    if (isset($searchParams['doc_id']) && !empty($searchParams['doc_id'])) {
        $sql .= " AND a.doc_id = :doc_id";
        $params[':doc_id'] = $searchParams['doc_id'];
    }

    if (isset($searchParams['ap_date']) && !empty($searchParams['ap_date'])) {
        $sql .= " AND a.ap_date = :ap_date";
        $params[':ap_date'] = $searchParams['ap_date'];
    }

    try {
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    } catch (\PDOException $e) {
        throw new \Exception("Database query failed: " . $e->getMessage(), 500);
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

        // 建立数据库连接
        $database = new Database();
        $db = $database->connect();

        // 获取 POST 请求中的搜索参数
        $searchParams = [
            'pat_id' => $_POST['patient_id'] ?? '',
            'doc_id' => $_POST['doctor_id'] ?? '',
            'ap_date' => $_POST['appointment_date'] ?? ''
        ];

        // 执行搜索
        $appointments = searchAppointments($db, $searchParams);

        // 返回成功响应
        echo ApiResponse::success($appointments)->toJson();
    } catch (\Exception $e) {
        // 返回失败响应
        echo ApiResponse::error($e->getCode(), $e->getMessage())->toJson();
    }
}

// 调用处理请求的函数
handleRequest();