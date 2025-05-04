<?php
// 设置时区为北京时间
date_default_timezone_set('Asia/Shanghai');

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
 * 用备份文件恢复数据库
 * @param Database $database 数据库连接类实例
 * @param string $backupFileName 备份文件名
 * @return array 包含恢复结果信息的数组
 * @throws Exception 如果恢复过程中出现错误
 */
function restoreDatabase($database, $backupFileName) {
    // 备份目录
    $backupDir = realpath(__DIR__ . '/../../backup');
    $backupFilePath = $backupDir . '/' . $backupFileName;

    // 检查备份文件是否存在
    if (!file_exists($backupFilePath)) {
        throw new Exception("Backup file not found: $backupFileName", 404);
    }

    try {
        // 数据库名
        $databaseName = 'yiliao2';

        // 建立与数据库服务器的连接（不指定具体数据库）
        $db = $database->connectToServer();

        // 删除原有数据库
        $db->exec("DROP DATABASE IF EXISTS $databaseName");

        // 创建新的数据库
        $db->exec("CREATE DATABASE $databaseName");

        // 重新实例化 Database 类以连接到新创建的数据库
        $newDatabase = new Database();
        $newDb = $newDatabase->connect();

        // 读取备份文件内容
        $backupContent = file_get_contents($backupFilePath);

        // 分割 SQL 语句
        $statements = explode(';', $backupContent);

        // 执行每个 SQL 语句
        foreach ($statements as $statement) {
            $statement = trim($statement);
            if (!empty($statement)) {
                $newDb->exec($statement);
            }
        }

        // 获取恢复时间
        $restoreTime = date('Y-m-d H:i:s');

        return [
            'backupFileName' => $backupFileName,
            'restoreTime' => $restoreTime,
            'message' => 'Database restored successfully'
        ];
    } catch (PDOException $e) {
        throw new Exception("Database restore failed: " . $e->getMessage(), 500);
    }
}

/**
 * 处理请求的函数
 */
function handleRequest() {
    try {
        // 验证请求方法
        verifyMethods(['POST']);

        // 初始化数据库连接类实例
        $database = new Database();

        // 获取备份文件名
        $backupFileName = $_POST['backupFileName'] ?? null;
        if (!$backupFileName) {
            throw new Exception("Backup file name is required", 400);
        }

        // 执行恢复操作
        $restoreInfo = restoreDatabase($database, $backupFileName);

        // 返回成功响应
        echo ApiResponse::success($restoreInfo)->toJson();
    } catch (Exception $e) {
        // 返回失败响应
        echo ApiResponse::error($e->getCode(), $e->getMessage())->toJson();
    }
}

// 调用处理请求的函数
handleRequest();