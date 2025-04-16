<?php
/**
 * @file apis/ApiDatabaseBackupDelete.php
 * @brief API for deleting a specific database backup file.
 * @author xing
 * @date 2025-04-14
 */

header('Content-Type: application/json');
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
$allowedOrigins = ['http://localhost:5173'];
if (in_array($origin, $allowedOrigins)) {
    header("Access-Control-Allow-Origin: $origin");
    header("Access-Control-Allow-Credentials: true");
}
header("Access-Control-Allow-Methods: DELETE");        /* NOTE: change the allow method for each single api */
header("Access-Control-Allow-Headers: Content-Type");

require_once __DIR__ . '/../utils/ApiResponse.php';
require_once __DIR__ . '/../utils/Database.php';
require_once __DIR__ . '/../utils/utils.php';

use App\Response\ApiResponse;
use App\Database\Database;

function handleRequest() {
    try {
        session_start();
        verifyMethods(['DELETE']);

        // 获取备份文件 ID
        $backupId = $_GET['backupId'] ?? null;
        if (!$backupId) {
            throw new \Exception("缺少 backupId 参数", 400);
        }

        // 定义备份目录
        $backupDir = __DIR__ . '/../backups';

        // 验证备份目录是否存在
        if (!is_dir($backupDir)) {
            throw new \Exception("备份目录不存在", 404);
        }

        // 根据备份文件 ID 找到对应的备份文件
        $backupFile = null;
        $files = scandir($backupDir);
        foreach ($files as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            // 简单验证备份文件名，假设文件名包含备份 ID
            if (strpos($file, $backupId) !== false) {
                $backupFile = $file;
                break;
            }
        }

        if (!$backupFile) {
            throw new \Exception("找不到备份文件", 404);
        }

        // 文件路径
        $filePath = "{$backupDir}/{$backupFile}";

        // 检查文件是否存在
        if (!file_exists($filePath)) {
            throw new \Exception("备份文件不存在", 404);
        }

        // 删除文件
        if (!unlink($filePath)) {
            throw new \Exception("无法删除备份文件", 500);
        }

        // 成功返回
        echo ApiResponse::success(["message" => "备份文件已删除"])->toJson();

    } catch (\Exception $e) {
        // 错误处理
        echo ApiResponse::error($e->getCode(), $e->getMessage())->toJson();
    }
}

handleRequest();
