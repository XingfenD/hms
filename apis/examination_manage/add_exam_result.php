<?php
/**
 * @file apis/add_exam_result.php
 * @brief 向exam_result表中插入exam_id和exam_result的API
 * @author xvjie
 * @date 2025-04-23
 */

// 设置响应头为JSON格式
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
 * 向exam_result表中插入exam_id和exam_result
 * @param \PDO $db 数据库连接对象
 * @param int $examId 检查记录ID
 * @param string $examResult 检查结果
 * @return bool 插入是否成功
 * @throws \Exception 如果数据库插入失败
 */
function addExamResult($db, $examId, $examResult) {
    try {
        $sql = "INSERT INTO exam_result (exam_id, exam_result) VALUES (:examId, :examResult)";
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':examId', $examId, PDO::PARAM_INT);
        $stmt->bindParam(':examResult', $examResult, PDO::PARAM_STR);
        return $stmt->execute();
    } catch (\PDOException $e) {
        throw new \Exception("Database insert failed: " . $e->getMessage(), 500);
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

        // 获取 POST 请求中的数据
        $examId = $_POST['exam_id'] ?? null;
        $examResult = $_POST['exam_result'] ?? null;

        // 验证输入参数
        if ($examId === null || $examResult === null) {
            throw new \Exception("exam_id and exam_result parameters are required", 400);
        }

        // 执行插入操作
        $success = addExamResult($db, $examId, $examResult);

        if ($success) {
            // 返回成功响应
            echo ApiResponse::success(["message" => "插入检查结果成功"])->toJson();
        } else {
            throw new \Exception("插入检查结果失败", 500);
        }
    } catch (\Exception $e) {
        // 返回失败响应
        echo ApiResponse::error($e->getCode(), $e->getMessage())->toJson();
    }
}

// 调用处理请求的函数
handleRequest();