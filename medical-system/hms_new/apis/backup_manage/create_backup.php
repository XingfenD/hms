<?php
// 设置时区为北京时间
date_default_timezone_set('Asia/Shanghai');

/**
 * @file apis/backup_database.php
 * @brief 备份数据库的 API
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
 * 备份数据库到指定目录
 * @param PDO $db 数据库连接对象
 * @param string $backupDir 备份目录
 * @return array 包含备份文件信息的数组
 * @throws Exception 如果备份过程中出现错误
 */
function backupDatabase($db, $backupDir) {
    // 确保备份目录存在
    if (!is_dir($backupDir)) {
        mkdir($backupDir, 0777, true);
    }

    // 创建 Database 实例并获取连接参数
    $database = new Database();
    $params = $database->getConnectionParams();
    $servername = $params['host'];
    $dbname = $params['db_name'];
    $username = $params['username'];
    $password = $params['password'];
    
    // 生成备份文件名
    $backupFile = $backupDir . '/backup_' . date('YmdHis') . '.sql';

    // 构建 mysqldump 命令
    $command = "mysqldump --user={$username} --password={$password} --host={$servername} {$dbname} > {$backupFile}";

    // 执行命令
    exec($command, $output, $returnCode);

    if ($returnCode !== 0) {
        throw new Exception("Database backup failed: " . implode("\n", $output), 500);
    }

    // 获取文件大小
    $fileSize = filesize($backupFile);
    $formattedFileSize = formatBytes($fileSize);

    // 获取备份时间
    $backupTime = date('Y-m-d H:i:s');

    return [
        'backupFileName' => basename($backupFile),
        'fileSize' => $formattedFileSize,
        'backupTime' => $backupTime
    ];
}

/**
 * 处理请求的函数
 */
function handleRequest() {
    try {
        // 验证请求方法
        verifyMethods(['GET']);

        // 建立数据库连接
        $database = new Database();
        $db = $database->connect();

        // 备份目录
        $backupDir = realpath(__DIR__ . '/../../backup');

        // 执行备份操作
        $backupInfo = backupDatabase($db, $backupDir);

        // 返回成功响应
        echo ApiResponse::success($backupInfo)->toJson();
    } catch (Exception $e) {
        // 返回失败响应
        echo ApiResponse::error($e->getCode(), $e->getMessage())->toJson();
    }
}

// 调用处理请求的函数
handleRequest();