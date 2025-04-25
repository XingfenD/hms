<?php
/**
 * @file apis/delete_backup.php
 * @brief 删除备份文件的 API
 * @author Your Name
 * @date 2024-11-01
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
require_once __DIR__ . '/../utils/utils.php';

use App\Response\ApiResponse;

/**
 * 处理删除备份文件的请求
 */
function handleDeleteRequest() {
    try {
        // 验证请求方法
        verifyMethods(['POST']);

        // 获取备份文件名字
        $backupFileName = $_POST['backupFileName'] ?? null;
        if (!$backupFileName) {
            throw new Exception("Backup file name is required", 400);
        }

        // 备份目录
        $backupDir = realpath(__DIR__ . '/../../backup');
        $backupFilePath = $backupDir . '/' . $backupFileName;

        // 检查文件是否存在
        if (!file_exists($backupFilePath)) {
            throw new Exception("Backup file does not exist", 404);
        }

        // 删除文件
        if (!unlink($backupFilePath)) {
            throw new Exception("Failed to delete backup file", 500);
        }

        // 返回成功响应
        echo ApiResponse::success(['message' => 'Backup file deleted successfully'])->toJson();
    } catch (Exception $e) {
        // 返回失败响应
        echo ApiResponse::error($e->getCode(), $e->getMessage())->toJson();
    }
}

// 调用处理请求的函数
handleDeleteRequest();