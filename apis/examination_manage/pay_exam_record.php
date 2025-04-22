<?php
/**
 * @file apis/exam_record_manage/pay_exam_record.php
 * @brief API for updating exam record status after payment
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
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

require_once __DIR__ . '/../utils/ApiResponse.php';
require_once __DIR__ . '/../utils/Database.php';
require_once __DIR__ . '/../utils/utils.php';

use App\Response\ApiResponse;
use App\Database\Database;

/**
 * 根据 exam_id 插入一条已付款的检查记录
 * @param \PDO $db 数据库连接对象
 * @param int $exam_id 检查记录的 ID
 * @return array 插入结果数组
 * @throws \Exception 如果数据库操作失败或未找到符合条件的记录
 */
function payExamRecord($db, $exam_id) {
    try {
        // 查询 status=0 且 exam_id 相同的记录
        $query = "SELECT ap_id, exam_def_id FROM exam_record WHERE exam_id = :exam_id AND status_code = 0 LIMIT 1";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':exam_id', $exam_id, \PDO::PARAM_INT);
        $stmt->execute();
        $record = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$record) {
            throw new \Exception("未找到符合条件的未付款检查记录", 404);
        }

        $ap_id = $record['ap_id'];
        $exam_def_id = $record['exam_def_id'];

        // 插入一条状态为 1（已付款）的记录
        $insertQuery = "INSERT INTO exam_record (ap_id, exam_def_id, status_code) VALUES (:ap_id, :exam_def_id, 1)";
        $insertStmt = $db->prepare($insertQuery);
        $insertStmt->bindParam(':ap_id', $ap_id, \PDO::PARAM_INT);
        $insertStmt->bindParam(':exam_def_id', $exam_def_id, \PDO::PARAM_INT);
        $insertStmt->execute();

        $insertedId = $db->lastInsertId();

        return [
            "message" => "检查记录付款成功",
            "exam_rcd_id" => $insertedId
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

        // 执行付款操作
        $result = payExamRecord($db, $exam_id);

        // 返回成功响应
        echo ApiResponse::success($result)->toJson();
    } catch (\Exception $e) {
        // 返回失败响应
        echo ApiResponse::error($e->getCode(), $e->getMessage())->toJson();
    }
}

// 调用处理请求的函数
handleRequest();