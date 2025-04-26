<?php
/**
 * @file apis/search_appointments.php
 * @brief 根据搜索条件查询挂号记录的 API
 * @author xvjie
 * @date 2025-04-22
 */

// 设置响应头为 JSON 格式
header('Content-Type: application/json');
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
$allowedOrigins = ['http://localhost:5173'];
if (in_array($origin, $allowedOrigins)) {
    header("Access-Control-Allow-Origin: $origin");
    header("Access-Control-Allow-Credentials: true");
}
// 修改允许的请求方法为 GET
header("Access-Control-Allow-Methods: GET");
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
                d.doc_uid AS DoctorUserID,
                dt.title_name AS DoctorTitle,
                dept.dep_name AS DoctorDepartmentName,
                ui_doctor.user_gender AS DoctorGender,
                ui_doctor.user_name AS DoctorName,
                ui_doctor.user_age AS DoctorAge,
                CASE a.ap_status
                    WHEN 0 THEN '已预约'
                    WHEN 1 THEN '正在进行'
                    WHEN 2 THEN '已结束'
                    WHEN 3 THEN '过号'
                    WHEN 4 THEN '患者已签到'
                    ELSE '未知状态'
                END AS AppointmentStatus,
                CONCAT(a.ap_date, ' ', a.ap_time) AS AppointmentDateTime,
                ei.exam_name AS ExaminationName,
                ei.exam_price AS ExaminationPrice,
                ei.exam_result AS ExaminationResult,
                pi.drug_name AS DrugName,
                pi.oper_amount AS DrugQuantity,
                pi.drug_price * pi.oper_amount AS DrugSumPrice
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
            JOIN
                user_info ui_doctor ON d.doc_uid = ui_doctor.user_id
            LEFT JOIN
                exam_info ei ON a.ap_id = ei.ap_id
            LEFT JOIN
                pres_info pi ON a.ap_id = pi.ap_id
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

    if (isset($searchParams['ap_sc_id']) && !empty($searchParams['ap_sc_id'])) {
        $sql .= " AND a.ap_sc_id = :ap_sc_id";
        $params[':ap_sc_id'] = $searchParams['ap_sc_id'];
    }

    if (isset($searchParams['ap_status']) && !empty($searchParams['ap_status'])) {
        $sql .= " AND a.ap_status = :ap_status";
        $params[':ap_status'] = $searchParams['ap_status'];
    }

    try {
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $formattedResults = [];
        foreach ($results as $result) {
            $pat_if = [
                'PatientId' => $result['PatientId'],
                'PatientName' => $result['PatientName'],
                'PatientGender' => $result['PatientGender'],
                'PatientAge' => $result['PatientAge']
            ];

            $doc_if = [
                'DoctorID' => $result['DoctorID'],
                'DoctorUserID' => $result['DoctorUserID'],
                'DoctorName' => $result['DoctorName'],
                'DoctorGender' => $result['DoctorGender'],
                'DoctorAge' => $result['DoctorAge'],
                'DoctorDepartmentName' => $result['DoctorDepartmentName'],
                'DoctorTitle' => $result['DoctorTitle'],
            ];

            $app_if = [
                'AppointmentID' => $result['AppointmentID'],
                'AppointmentStatus' => $result['AppointmentStatus'],
                'AppointmentDateTime' => $result['AppointmentDateTime']
            ];

            $exam_if = [
                'ExaminationName' => $result['ExaminationName'],
                'ExaminationPrice' => $result['ExaminationPrice'],
                'ExaminationResult' => $result['ExaminationResult']
            ];

            $pres_if = [
                'DrugName' => $result['DrugName'],
                'DrugQuantity' => $result['DrugQuantity'],
                'DrugSumPrice' => $result['DrugSumPrice']
            ];

            $formattedResults[] = [
                'pat_if' => $pat_if,
                'doc_if' => $doc_if,
                'app_if' => $app_if,
                'exam_if' => $exam_if,
                'pres_if' => $pres_if
            ];
        }

        return $formattedResults;
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
        // 修改验证的请求方法为 GET
        verifyMethods(['GET']);

        // 建立数据库连接
        $database = new Database();
        $db = $database->connect();

        // 从 $_GET 数组中获取搜索参数
        $searchParams = [
            'pat_id' => $_GET['patient_id'] ?? '',
            'doc_id' => $_GET['doctor_id'] ?? '',
            'ap_sc_id' => $_GET['ap_sc_id'] ?? '',
            'ap_status' => $_GET['appointment_status'] ?? ''
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