<?php
/**
 * @file apis/BackupDatabaseApi.php
 * @brief API for backing up the database
 * @author Your Name
 * @date 2024-11-14
 */

/* set the response header to JSON */
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

require_once __DIR__ . '/../utils/ApiResponse.php';
require_once __DIR__ . '/../utils/Database.php';
require_once __DIR__ . '/../utils/utils.php';

use App\Response\ApiResponse;
use App\Database\Database;

/* the function to handle the request */
function handleRequest() {
    try {
        /* use verifyMethods function in utils/utils.php */
        session_start();
        /* TODO: verify the user's authority or UserType */
        if ($_SESSION["UserType"] !== "admin") {
            //throw new \Exception("operation not permitted for current user", 403);
        }
        /* TODO: custom your own permitted request methods */
        verifyMethods(['POST']);

        // 引入数据库连接信息
        include '../db_connection.php';

        // 备份数据库
        $backupDir = __DIR__ . '/../../backup';
        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0777, true);
        }
        $backupFile = $backupDir . '/' . $dbname . '_backup_' . date("Y-m-d-H-i-s") . '.sql';
        $command = "mysqldump --user={$username} --password={$password} --host={$servername} {$dbname} > {$backupFile}";

        system($command, $output);

        if ($output == 0) {
            $responseData = [
                'message' => "备份成功！备份文件已保存到 {$backupFile}",
                'backup_file' => $backupFile
            ];
            echo ApiResponse::success($responseData)->toJson();
        } else {
            throw new \Exception("备份失败！", 500);
        }
    } catch (\Exception $e) {
        /* return fail response */
        echo ApiResponse::error($e->getCode(), $e->getMessage())->toJson();
    }
}

/* call the request handling function */
handleRequest();