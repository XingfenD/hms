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

    // 生成备份文件名
    $backupFileName = $backupDir . '/backup_' . date('YmdHis') . '.sql';

    try {
        // 获取数据库名
        $databaseName = $db->query("SELECT DATABASE()")->fetchColumn();

        // 打开文件以写入备份内容
        $file = fopen($backupFileName, 'w');

        // 备份表结构
        $tables = $db->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
        foreach ($tables as $table) {
            $createTableQuery = $db->query("SHOW CREATE TABLE $table")->fetch(PDO::FETCH_ASSOC);
            fwrite($file, $createTableQuery['Create Table'] . ";\n\n");

            // 备份表数据
            $rows = $db->query("SELECT * FROM $table");
            while ($row = $rows->fetch(PDO::FETCH_ASSOC)) {
                $columns = implode(', ', array_keys($row));
                $values = implode(', ', array_map(function ($value) use ($db) {
                    return $db->quote($value);
                }, $row));
                $insertQuery = "INSERT INTO $table ($columns) VALUES ($values);\n";
                fwrite($file, $insertQuery);
            }
            fwrite($file, "\n");
        }

        // 关闭文件
        fclose($file);

        // 获取文件大小
        $fileSize = filesize($backupFileName);
        $formattedFileSize = formatBytes($fileSize);

        // 获取备份时间
        $backupTime = date('Y-m-d H:i:s');

        return [
            'backupFileName' => basename($backupFileName),
            'fileSize' => $formattedFileSize,
            'backupTime' => $backupTime
        ];
    } catch (PDOException $e) {
        throw new Exception("Database backup failed: " . $e->getMessage(), 500);
    }
}

/**
 * 处理请求的函数
 */
function handleRequest() {
    try {
        // 验证请求方法
        verifyMethods(['GET']);

        // 初始化数据库连接
        $db = initializeDatabase();

        // 备份目录
        $backupDir = realpath(__DIR__ . '/../../backup');

        // 执行备份操作
        $backupInfo = backupDatabase($db, $backupDir);

        // 返回成功响应
        echo ApiResponse::success($backupInfo, "Database backup successful")->toJson();
    } catch (Exception $e) {
        // 返回失败响应
        echo ApiResponse::error($e->getCode(), $e->getMessage())->toJson();
    }
}

// 调用处理请求的函数
handleRequest();