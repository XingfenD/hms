<?php
/**
 * @file apis/schedule_manage/batch_add_schedule.php
 * @brief 批量添加医生排班信息的 API
 * @author 你的名字
 * @date 2025-04-24
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
 * 批量添加医生排班信息
 * @param \PDO $db 数据库连接对象
 * @param array $schedules 排班数据数组
 * @return array 包含插入结果信息的数组
 * @throws \Exception 如果数据库操作失败
 */
function batchAddSchedules($db, $schedules) {
    try {
        $sql = "INSERT INTO schedule (doc_id, `date`, start_time, end_time) VALUES (?, ?, ?, ?)";
        $stmt = $db->prepare($sql);

        $insertedCount = 0;
        $errors = [];

        $db->beginTransaction();

        foreach ($schedules as $index => $schedule) {
            if (!isset($schedule['doc_id']) || !isset($schedule['date']) || !isset($schedule['start_time']) || !isset($schedule['end_time'])) {
                $errors[] = "第 " . ($index + 1) . " 条排班数据缺少必要字段 (doc_id, date, start_time, end_time)";
                continue;
            }
            if (!is_numeric($schedule['doc_id'])) {
                $errors[] = "第 " . ($index + 1) . " 条排班数据的 doc_id 无效";
                continue;
            }

            $stmt->bindParam(1, $schedule['doc_id'], \PDO::PARAM_INT);
            $stmt->bindParam(2, $schedule['date'], \PDO::PARAM_STR);
            $stmt->bindParam(3, $schedule['start_time'], \PDO::PARAM_STR);
            $stmt->bindParam(4, $schedule['end_time'], \PDO::PARAM_STR);

            if ($stmt->execute()) {
                $insertedCount++;
            } else {
                $errors[] = "第 " . ($index + 1) . " 条数据插入失败: " . $stmt->errorInfo()[2];
            }
        }

        if (empty($errors)) {
            $db->commit();
            return ['success' => true, 'message' => "成功插入 " . $insertedCount . " 条排班记录。"];
        } else {
            $db->rollBack();
            return [
                'success' => false,
                'error' => "插入过程中发生错误。",
                'details' => $errors,
                'inserted_count' => $insertedCount
            ];
        }
    } catch (\PDOException $e) {
        throw new \Exception("Database operation failed: " . $e->getMessage(), 500);
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

        // 获取 POST 请求中的 JSON 数据
        $jsonData = file_get_contents('php://input');
        $schedules = json_decode($jsonData, true);

        // 验证 JSON 数据的有效性
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception("无效的 JSON 数据: " . json_last_error_msg(), 400);
        }

        // 验证数据是否为数组且不为空
        if (!is_array($schedules) || empty($schedules)) {
            throw new \Exception("输入数据必须是一个非空的 JSON 数组", 400);
        }

        // 执行批量插入操作
        $result = batchAddSchedules($db, $schedules);

        // 返回响应
        if ($result['success']) {
            echo ApiResponse::success($result['message'])->toJson();
        } else {
            echo ApiResponse::error(500, $result['error'], $result['details'])->toJson();
        }
    } catch (\Exception $e) {
        // 返回失败响应
        echo ApiResponse::error($e->getCode(), $e->getMessage())->toJson();
    }
}

// 调用处理请求的函数
handleRequest();