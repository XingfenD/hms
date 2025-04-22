<?php
/**
 * @file apis/exam_record_manage/insert_exam_record.php
 * @brief 向 exam_record 表中插入一条记录的 API
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
 * 向 exam_record 表中插入一条记录
 * @param \PDO $db 数据库连接对象
 * @param int $ap_id 挂号记录 ID
 * @param int $exam_def_id 检查项目定义 ID
 * @return array 插入结果数组
 * @throws \Exception 如果数据库插入失败
 */
function insertExamRecord($db, $ap_id, $exam_def_id) {
    $sql = "
        INSERT INTO exam_record (ap_id, exam_def_id, status_code)
        VALUES (:ap_id, :exam_def_id, 0)
    ";

    $params = [
        ':ap_id' => $ap_id,
        ':exam_def_id' => $exam_def_id
    ];

    try {
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $insertedId = $db->lastInsertId();
        return [
            "message" => "检查记录插入成功",
            "exam_rcd_id" => $insertedId
        ];
    } catch (\PDOException $e) {
        throw new \Exception("数据库插入失败: " . $e->getMessage(), 500);
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

        // 获取 POST 请求中的 ap_id 和 exam_def_id
        $ap_id = $_POST['ap_id'] ?? null;
        $exam_def_id = $_POST['exam_def_id'] ?? null;

        // 验证 ap_id 和 exam_def_id 是否存在
        if ($ap_id === null || $exam_def_id === null) {
            throw new \Exception("ap_id 和 exam_def_id 参数是必需的", 400);
        }

        // 执行插入操作
        $result = insertExamRecord($db, $ap_id, $exam_def_id);

        // 返回成功响应
        echo ApiResponse::success($result)->toJson();
    } catch (\Exception $e) {
        // 返回失败响应
        echo ApiResponse::error($e->getCode(), $e->getMessage())->toJson();
    }
}

// 调用处理请求的函数
handleRequest();