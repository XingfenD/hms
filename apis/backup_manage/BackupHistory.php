<?php
/**
 * @file apis/ApiDatabaseBackupHistory.php
 * @brief API for retrieving the database backup history
 * @author xing
 * @date 2025-04-14
 */

header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type");

require_once __DIR__ . '/utils/ApiResponse.php';
require_once __DIR__ . '/utils/Database.php';
require_once __DIR__ . '/utils/utils.php';

use App\Response\ApiResponse;
use App\Database\Database;

function handleRequest() {
    try {
        session_start();
        verifyMethods(['GET']);

        // 判断用户是否为管理员
        if ($_SESSION["UserType"] !== "admin") {
            throw new \Exception("操作不被当前用户允许", 403);
        }

        // 定义备份目录
        $backupDir = __DIR__ . '/../backups';

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
                'id' => uniqid(),  // 生成唯一的备份 ID
                'filename' => $file,
                'path' => $filePath,
                'size_kb' => round($fileSize / 1024, 2),  // 转换为 KB
                'backup_time' => date('Y-m-d H:i:s', $fileModifiedTime)
            ];
        }

        // 返回备份历史记录
        echo ApiResponse::success($backupHistory)->toJson();

    } catch (\Exception $e) {
        // 错误处理
        echo ApiResponse::error($e->getCode(), $e->getMessage())->toJson();
    }
}

handleRequest();
