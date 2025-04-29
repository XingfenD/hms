<?php
/**
 * @file apis/search_exam_status.php
 * @brief 查询 exam_record 表中每个 exam_id 的最大状态码及对应时间的 API
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
 * 查询 exam_record 表中每个 exam_id 的最大状态码及对应时间
 * @param \PDO $db 数据库连接对象
 * @return array 查询结果数组
 * @throws \Exception 如果数据库查询失败
 */
function searchExamStatus($db) {
    $sql = "
        SELECT
            e1.exam_id,
            e1.MaxStatusCode,
            e2.oper_time AS MaxStatusCodeTime
        FROM
            (
                SELECT
                    exam_id,
                    MAX(status_code) AS MaxStatusCode
                FROM
                    exam_record
                GROUP BY
                    exam_id
            ) e1
        JOIN
            exam_record e2
        ON
            e1.exam_id = e2.exam_id AND e1.MaxStatusCode = e2.status_code
        GROUP BY
            e1.exam_id, e1.MaxStatusCode
    ";

    try {
        $stmt = $db->prepare($sql);
        $stmt->execute();
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

        // 执行搜索
        $examStatus = searchExamStatus($db);

        // 返回成功响应
        echo ApiResponse::success($examStatus)->toJson();
    } catch (\Exception $e) {
        // 返回失败响应
        echo ApiResponse::error($e->getCode(), $e->getMessage())->toJson();
    }
}

// 调用处理请求的函数
handleRequest();
    