<?php
/**
 * @file apis/search_examination_def.php
 * @brief 根据输入的exam_name部分内容查询examination_def表信息的API
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
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type");

require_once __DIR__ . '/../utils/ApiResponse.php';
require_once __DIR__ . '/../utils/Database.php';
require_once __DIR__ . '/../utils/utils.php';

use App\Response\ApiResponse;
use App\Database\Database;

/**
 * 根据输入的exam_name部分内容查询examination_def表信息
 * @param \PDO $db 数据库连接对象
 * @param string $partialExamName 输入的exam_name部分内容
 * @return array 查询结果数组
 * @throws \Exception 如果数据库查询失败
 */
function searchExaminationDef($db, $partialExamName) {
    try {
        $sql = "SELECT * FROM examination_def WHERE exam_name LIKE :partialExamName";
        $partialExamName = "%{$partialExamName}%";

        $stmt = $db->prepare($sql);
        $stmt->bindParam(':partialExamName', $partialExamName, PDO::PARAM_STR);
        $stmt->execute();

        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $results;
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

        // 获取 GET 请求中的搜索参数
        $partialExamName = $_GET['exam_name'] ?? '';

        // 执行搜索
        $results = searchExaminationDef($db, $partialExamName);

        if (empty($results)) {
            throw new \Exception("未找到匹配的检查项目", 404);
        }

        // 返回成功响应
        echo ApiResponse::success($results)->toJson();
    } catch (\Exception $e) {
        // 返回失败响应
        echo ApiResponse::error($e->getCode(), $e->getMessage())->toJson();
    }
}

// 调用处理请求的函数
handleRequest();