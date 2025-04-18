<?php
/**
 * @file apis/delete_user_api.php
 * @brief the API for deleting a user from the users table
 * @author xvjie
 * @date 2025-04-15
 */

/* set the response header to JSON */
header('Content-Type: application/json');
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
$allowedOrigins = ['http://localhost:5173'];
if (in_array($origin, $allowedOrigins)) {
    header("Access-Control-Allow-Origin: $origin");
    header("Access-Control-Allow-Credentials: true");
}
header("Access-Control-Allow-Methods: POST");        /* 修改为 POST 请求方法 */
header("Access-Control-Allow-Headers: Content-Type");

require_once __DIR__ . '/../utils/ApiResponse.php';
require_once __DIR__ . '/../utils/Database.php';
require_once __DIR__ . '/../utils/utils.php';

use App\Response\ApiResponse;
use App\Database\Database;

/**
 * Delete a user from the user and related tables
 *
 * @param \PDO $db instance of database connection
 * @param int $userId user id
 * @throws \Exception if the database query fails
 */
function deleteUser($db, $userId) {
    try {
        // 开始事务
        $db->beginTransaction();

        // 检查用户是否存在
        $checkStmt = $db->prepare("SELECT * FROM user WHERE user_id = :userId");
        $checkStmt->bindParam(':userId', $userId, \PDO::PARAM_INT);
        $checkStmt->execute();

        if ($checkStmt->rowCount() === 0) {
            throw new \Exception("User not found", 404);
        }

        // 获取用户权限
        $userAuth = $checkStmt->fetch(\PDO::FETCH_ASSOC)['user_auth'];

        // 删除用户信息
        $deleteStmt = $db->prepare("DELETE FROM user WHERE user_id = :userId");
        $deleteStmt->bindParam(':userId', $userId, \PDO::PARAM_INT);
        $deleteStmt->execute();

        // 根据用户权限删除相关表中的信息
        if ($userAuth === 3) { // 医生权限
            // 删除 doctor 表中的信息
            $deleteDoctorStmt = $db->prepare("DELETE FROM doctor WHERE doc_uid = :userId");
            $deleteDoctorStmt->bindParam(':userId', $userId, \PDO::PARAM_INT);
            $deleteDoctorStmt->execute();
        }

        // 由于 user_info 表的外键设置为 ON DELETE CASCADE，删除 user 表记录时会自动删除关联的 user_info 记录

        // 提交事务
        $db->commit();
    } catch (\PDOException $e) {
        // 回滚事务
        $db->rollBack();
        throw new \Exception("Database query failed: ". $e->getMessage(), 500);
    }
}

/* the function to handle the request */
function handleRequest() {
    try {
        /* use verifyMethods function in utils/utils.php */
        session_start();

        // 验证用户权限
        if ($_SESSION["UserType"] !== "admin") {
            throw new \Exception("operation not permitted for current user", 403);
        }

        verifyMethods(['POST']); // 修改为验证 POST 请求方法

        /* use initializeDatabase() function in utils/utils.php */
        /* initialize the database connection */
        $db = initializeDatabase();

        // Get the user ID from the POST request
        $userId = isset($_POST['userId']) ? intval($_POST['userId']) : null;

        /* verify the arguments */
        if (empty($userId)) {
            throw new \Exception("userId can't be null", 400);
        }

        /* delete the user */
        deleteUser($db, $userId);

        /* return success response */
        echo ApiResponse::success(["message" => "用户删除成功"])->toJson();
    } catch (\Exception $e) {
        /* return fail response */
        echo ApiResponse::error($e->getCode(), $e->getMessage())->toJson();
    }
}

/* call the request handling function */
handleRequest();
