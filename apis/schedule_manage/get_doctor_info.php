<?php
/**
 * @file apis/get_doctors_info.php
 * @brief 获取医生信息的 API
 * @author 你的名字
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
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type");

require_once __DIR__ . '/../utils/ApiResponse.php';
require_once __DIR__ . '/../utils/Database.php';
require_once __DIR__ . '/../utils/utils.php';

use App\Response\ApiResponse;
use App\Database\Database;

/**
 * 获取医生信息
 * @param \PDO $db 数据库连接对象
 * @return array 查询结果数组
 * @throws \Exception 如果数据库查询失败
 */
function getDoctorsInfo($db) {
    try {
        // SQL 查询语句，从 doctors 视图中选择需要的字段
        $sql = "SELECT DoctorOwnID, DepartmentName, Title FROM doctors";
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

        // 执行查询
        $doctors = getDoctorsInfo($db);

        // 返回成功响应
        echo ApiResponse::success($doctors)->toJson();
    } catch (\Exception $e) {
        // 返回失败响应
        echo ApiResponse::error($e->getCode(), $e->getMessage())->toJson();
    }
}

// 调用处理请求的函数
handleRequest();