<?php
/**
 * @file apis/ApiDatabaseBackupHistory.php
 * @brief API for retrieving the database backup history
 * @author xvjie
 * @date 2025-04-16
 */

// 设置响应头为 JSON 格式
header('Content-Type: application/json');
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
$allowedOrigins = ['http://localhost:5173'];
if (in_array($origin, $allowedOrigins)) {
    header("Access-Control-Allow-Origin: $origin");
    header("Access-Control-Allow-Credentials: true");
}
header("Access-Control-Allow-Methods: GET");        /* NOTE: change the allow method for each single api */
header("Access-Control-Allow-Headers: Content-Type");

require_once __DIR__ . '/../utils/ApiResponse.php';
require_once __DIR__ . '/../utils/Database.php';
require_once __DIR__ . '/../utils/utils.php';

use App\Response\ApiResponse;
use App\Database\Database;

/**
 * 将字节转换为合适的文件大小单位
 * @param int $bytes 文件大小（字节）
 * @param int $precision 保留小数位数
 * @return string 包含合适单位的文件大小
 */
function formatBytes($bytes, $precision = 2) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];

    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);

    $bytes /= pow(1024, $pow);

    return round($bytes, $precision) . ' ' . $units[$pow];
}

/**
 * 获取备份历史记录
 * @param string $backupDir 备份目录
 * @return array 包含备份历史记录的数组
 * @throws Exception 如果备份目录不存在
 */
function getBackupHistory($backupDir) {
    // 验证备份目录是否存在
    if (!is_dir($backupDir)) {
        throw new \Exception("备份目录不存在", 404);
    }

    // 获取备份目录中的所有文件
    $files = scandir($backupDir);
    $backupHistory = [];

    foreach ($files as $file) {
        // 排除当前目录和父级目录
        if ($file === '.' || $file === '..') {
            continue;
        }

        $filePath = "{$backupDir}/{$file}";

        // 获取文件信息
        $fileSize = filesize($filePath);
        $fileModifiedTime = filemtime($filePath);

        // 构建备份历史记录数据
        $backupHistory[] = [
            'filename' => $file,
            'path' => $filePath,
            'size' => formatBytes($fileSize),  // 转换为合适的文件大小单位
            'backup_time' => date('Y-m-d H:i:s', $fileModifiedTime)
        ];
    }

    return $backupHistory;
}

/**
 * 处理请求的函数
 */
function handleRequest() {
    try {
        // 验证请求方法
        verifyMethods(['GET']);

        // 初始化数据库连接（这里虽然获取了数据库连接，但在本接口中未使用）
        $db = (new Database())->connect();

        // 备份目录
        $backupDir = realpath(__DIR__ . '/../../backup');

        // 获取备份历史记录
        $backupHistory = getBackupHistory($backupDir);

        // 返回成功响应
        echo ApiResponse::success($backupHistory)->toJson();
    } catch (\Exception $e) {
        // 返回失败响应
        echo ApiResponse::error($e->getCode(), $e->getMessage())->toJson();
    }
}

// 调用处理请求的函数
handleRequest();