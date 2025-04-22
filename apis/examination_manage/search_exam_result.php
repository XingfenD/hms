<?php
/**
 * @file apis/search_appointments.php
 * @brief 根据 ap_id 查询挂号记录及检查项目信息的 API
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
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type");

require_once __DIR__ . '/../utils/ApiResponse.php';
require_once __DIR__ . '/../utils/Database.php';
require_once __DIR__ . '/../utils/utils.php';

use App\Response\ApiResponse;
use App\Database\Database;

/**
 * 根据 ap_id 查询挂号记录及检查项目信息
 * @param \PDO $db 数据库连接对象
 * @param int $apId 挂号记录 ID
 * @return array 查询结果数组
 * @throws \Exception 如果数据库查询失败
 */
function searchAppointments($db, $apId) {
    $sql = "
        SELECT
            a.pat_id AS PatientId,
            ui_pat.user_name AS PatientName,
            a.doc_id AS DoctorID,
            ui_doc.user_name AS DoctorName,
            e_def.exam_name AS ExaminationItem,
            -- 这里假设你想取每个 exam_id 对应的最新检查结果，如果有其他需求可以调整
            MAX(e_res.exam_result) AS ExaminationResult
        FROM
            appointment a
        JOIN
            user_info ui_pat ON a.pat_id = ui_pat.user_id
        JOIN
            doctor d ON a.doc_id = d.doc_id
        JOIN
            user_info ui_doc ON d.doc_uid = ui_doc.user_id
        LEFT JOIN
            exam_record e ON a.ap_id = e.ap_id
        LEFT JOIN
            examination_def e_def ON e.exam_def_id = e_def.exam_def_id
        LEFT JOIN
            exam_result e_res ON e.exam_rcd_id = e_res.exam_id
        WHERE
            a.ap_id = :apId
        GROUP BY
            a.pat_id, ui_pat.user_name, a.doc_id, ui_doc.user_name, e_def.exam_name, e.exam_id
    ";

    $params = [':apId' => $apId];

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
        verifyMethods(['GET']);

        // 建立数据库连接
        $database = new Database();
        $db = $database->connect();

        // 获取 GET 请求中的 ap_id
        $apId = $_GET['ap_id'] ?? null;

        // 验证 ap_id 是否存在
        if ($apId === null) {
            throw new \Exception("ap_id 参数是必需的", 400);
        }

        // 执行搜索
        $appointments = searchAppointments($db, $apId);

        // 返回成功响应
        echo ApiResponse::success($appointments)->toJson();
    } catch (\Exception $e) {
        // 返回失败响应
        echo ApiResponse::error($e->getCode(), $e->getMessage())->toJson();
    }
}

// 调用处理请求的函数
handleRequest();