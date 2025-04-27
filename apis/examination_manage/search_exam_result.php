<?php
/**
 * @file apis/search_exam_result.php
 * @brief 根据 exam_id 查询检查项目信息的 API
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
 * 根据 exam_id 查询检查项目信息
 * @param \PDO $db 数据库连接对象
 * @param int $examId 检查记录 ID
 * @return array 查询结果数组
 * @throws \Exception 如果数据库查询失败
 */
function searchExamInfo($db, $examId) {
    $sql = "
        SELECT *
        FROM exam_info
        WHERE exam_id = :examId
    ";

    $params = [':examId' => $examId];

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

        // 获取 GET 请求中的 exam_id
        $examId = $_GET['exam_id'] ?? null;

        // 验证 exam_id 是否存在
        if ($examId === null) {
            throw new \Exception("exam_id 参数是必需的", 400);
        }

        // 执行搜索
        $examInfos = searchExamInfo($db, $examId);

        if (empty($examInfos)) {
            throw new \Exception("未找到对应的检查记录", 404);
        }

        // 返回成功响应
        echo ApiResponse::success($examInfos)->toJson();
    } catch (\Exception $e) {
        // 返回失败响应
        echo ApiResponse::error($e->getCode(), $e->getMessage())->toJson();
    }
}

// 调用处理请求的函数
handleRequest();