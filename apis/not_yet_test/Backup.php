<?php
/**
 * @file apis/ApiDatabaseBackup.php
 * @brief API for database backup operation
 * @author xing
 * @date 2025-04-14
 */

header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

require_once __DIR__ . '/utils/ApiResponse.php';
require_once __DIR__ . '/utils/Database.php';
require_once __DIR__ . '/utils/utils.php';

use App\Response\ApiResponse;
use App\Database\Database;

function handleRequest() {
    try {
        session_start();
        verifyMethods(['POST']);

        // 判断用户是否为管理员
        if ($_SESSION["UserType"] !== "admin") {
            throw new \Exception("操作不被当前用户允许", 403);
        }

        // 获取请求参数
        $backupPath = $_POST['backupPath'] ?? null;

        // 默认备份路径
        $defaultBackupPath = __DIR__ . '/../backups';
        if (!is_dir($defaultBackupPath)) {
            mkdir($defaultBackupPath, 0755, true);
        }

        // 如果没有传入备份路径，则使用默认路径
        $backupDir = $backupPath ?: $defaultBackupPath;

        // 验证备份路径是否存在并且可写
        if (!is_dir($backupDir) || !is_writable($backupDir)) {
            throw new \Exception("备份目录不存在或不可写", 400);
        }

        // 获取当前时间戳，用于生成备份文件名
        $timestamp = date('Ymd_His');
        $filename = "backup_{$timestamp}.sql";
        $filepath = "{$backupDir}/{$filename}";

        // 配置数据库连接信息
        $host = 'localhost';
        $username = 'yiliao';           // ⚠️ 替换为你的数据库用户名
        $password = 'yiliao123';               // ⚠️ 替换为你的数据库密码
        $database = 'yiliao';  // ⚠️ 替换为你的数据库名

        // 使用 mysqldump 命令进行数据库备份
        $command = "mysqldump -h {$host} -u {$username} " .
                   ($password !== '' ? "-p{$password} " : "") .
                   "{$database} > {$filepath}";

        // 执行备份操作
        system($command, $retval);

        // 检查备份是否成功
        if ($retval !== 0 || !file_exists($filepath)) {
            throw new \Exception("数据库备份失败", 500);
        }

        // 获取文件大小，单位 KB
        $filesize_kb = round(filesize($filepath) / 1024, 2);

        // 返回备份文件的相关信息
        echo ApiResponse::success([
            "filename" => $filename,
            "size_kb" => $filesize_kb,
            "backup_time" => date('Y-m-d H:i:s')
        ])->toJson();

    } catch (\Exception $e) {
        // 错误处理
        echo ApiResponse::error($e->getCode(), $e->getMessage())->toJson();
    }
}

handleRequest();
