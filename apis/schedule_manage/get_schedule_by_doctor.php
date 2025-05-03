<?php
/**
 * @file apis/get_doctor_schedules.php
 * @brief 获取某个医生的排班信息的 API
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
header("Access-Control-Allow-Methods: GET");        /* NOTE: change the allow method for each single api */
header("Access-Control-Allow-Headers: Content-Type");

require_once __DIR__ . '/../utils/ApiResponse.php';
require_once __DIR__ . '/../utils/Database.php';
require_once __DIR__ . '/../utils/utils.php';

use App\Response\ApiResponse;
use App\Database\Database;

/**
 * 根据指定时间范围获取医生排班信息
 * @param PDO $db 数据库连接对象
 * @param string $startDate 开始日期
 * @param string $endDate 结束日期
 * @return array 查询结果数组
 * @throws Exception 如果数据库查询失败
 */
function getDoctorSchedulesByDateRange($db, $doctorID) {
    $sql = "SELECT
                s.schedule_id,
                s.doc_id AS doctor_id,
                d.FullName AS DoctorName,
                dept.Department,
                s.date AS ScheduleDate,
                s.start_time AS StartTime,
                s.end_time AS EndTime
            FROM
                schedule s
            JOIN
                doctors d ON s.doc_id = d.DoctorOwnID
            JOIN
                departments dept ON d.DepartmentID = dept.DepartmentId
            WHERE
                s.doc_id = :doctorID";

    $params = [
        ':doctorID' => $doctorID
    ];

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
        verifyMethods(['GET']);

        // 初始化数据库连接
        $db = initializeDatabase();

        // 获取 GET 请求中的医生ID
        $doctorID = $_GET['doctor_id'] ?? '';

        // 验证日期参数
        if (empty($doctorID)) {
            throw new Exception("Missing doctorID parameter", 400);
        }

        // 执行查询
        $schedules = getDoctorSchedulesByDateRange($db, $doctorID);

        // 返回成功响应
        echo ApiResponse::success($schedules)->toJson();
    } catch (Exception $e) {
        // 返回失败响应
        echo ApiResponse::error($e->getCode(), $e->getMessage())->toJson();
    }
}

// 调用处理请求的函数
handleRequest();