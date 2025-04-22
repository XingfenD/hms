<?php
/**
 * @file apis/exam_record_manage/delete_exam_record.php
 * @brief API for deleting an exam record
 * @author 示例作者
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
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

require_once __DIR__ . '/../utils/ApiResponse.php';
require_once __DIR__ . '/../utils/Database.php';
require_once __DIR__ . '/../utils/utils.php';

use App\Response\ApiResponse;
use App\Database\Database;

/**
 * 删除 exam_record 表中的记录
 * @param \PDO $db 数据库连接对象
 * @param int $exam_id 检查记录的 ID
 * @return array 删除结果数组
 * @throws \Exception 如果数据库操作失败或状态码不符合要求
 */
function deleteExamRecord($db, $exam_id) {
    try {
        // 检查 exam_id 对应的记录中是否有 status 为 1 或 2 的记录
        $checkStmt = $db->prepare("SELECT COUNT(*) FROM exam_record WHERE exam_id = :exam_id AND (status_code = 1 OR status_code = 2)");
        $checkStmt->bindParam(':exam_id', $exam_id, \PDO::PARAM_INT);
        $checkStmt->execute();
        $count = $checkStmt->fetchColumn();

        if ($count > 0) {
            throw new \Exception("已付款或已检查，无法删除", 400);
        }

        // 检查记录是否存在
        $checkExistsStmt = $db->prepare("SELECT COUNT(*) FROM exam_record WHERE exam_id = :exam_id");
        $checkExistsStmt->bindParam(':exam_id', $exam_id, \PDO::PARAM_INT);
        $checkExistsStmt->execute();
        $existsCount = $checkExistsStmt->fetchColumn();

        if ($existsCount === 0) {
            throw new \Exception("未找到对应的检查记录", 404);
        }

        // 删除记录
        $deleteStmt = $db->prepare("DELETE FROM exam_record WHERE exam_id = :exam_id");
        $deleteStmt->bindParam(':exam_id', $exam_id, \PDO::PARAM_INT);
        $deleteStmt->execute();

        return [
            "message" => "检查记录删除成功"
        ];
    } catch (\PDOException $e) {
        throw new \Exception("数据库操作失败: " . $e->getMessage(), 500);
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

        // 获取 POST 请求中的 exam_id
        $exam_id = $_POST['exam_id'] ?? null;

        // 验证 exam_id 是否存在
        if ($exam_id === null) {
            throw new \Exception("exam_id 参数是必需的", 400);
        }

        // 执行删除操作
        $result = deleteExamRecord($db, $exam_id);

        // 返回成功响应
        echo ApiResponse::success($result)->toJson();
    } catch (\Exception $e) {
        // 返回失败响应
        echo ApiResponse::error($e->getCode(), $e->getMessage())->toJson();
    }
}

// 调用处理请求的函数
handleRequest();