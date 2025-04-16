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
header("Access-Control-Allow-Methods: DELETE");        /* NOTE: change the allow method for each single api */
header("Access-Control-Allow-Headers: Content-Type");

require_once __DIR__ . '/../utils/ApiResponse.php';
require_once __DIR__ . '/../utils/Database.php';
require_once __DIR__ . '/../utils/utils.php';

use App\Response\ApiResponse;
use App\Database\Database;

/**
 * Delete a user from the users table
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
        $checkStmt = $db->prepare("SELECT * FROM users WHERE UserId = :userId");
        $checkStmt->bindParam(':userId', $userId, \PDO::PARAM_INT);
        $checkStmt->execute();

        if ($checkStmt->rowCount() === 0) {
            throw new \Exception("User not found", 404);
        }

        // 获取用户类型
        $userType = $checkStmt->fetch(\PDO::FETCH_ASSOC)['UserType'];

        // 删除用户信息
        $deleteStmt = $db->prepare("DELETE FROM users WHERE UserId = :userId");
        $deleteStmt->bindParam(':userId', $userId, \PDO::PARAM_INT);
        $deleteStmt->execute();

        // 根据用户类型删除相关表中的信息
        if ($userType === "doctor") {
            $deleteRelatedStmt = $db->prepare("DELETE FROM doctors WHERE DoctorID = :userId");
            $deleteRelatedStmt->bindParam(':userId', $userId, \PDO::PARAM_INT);
            $deleteRelatedStmt->execute();
        } elseif ($userType === "patient") {
            $deleteRelatedStmt = $db->prepare("DELETE FROM patients WHERE PatientID = :userId");
            $deleteRelatedStmt->bindParam(':userId', $userId, \PDO::PARAM_INT);
            $deleteRelatedStmt->execute();
        }

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

        verifyMethods(['DELETE']);

        /* use initializeDatabase() function in utils/utils.php */
        /* initialize the database connection */
        $db = initializeDatabase();

        // Get the user ID from the request
        parse_str(file_get_contents("php://input"), $deleteData);
        $userId = isset($deleteData['userId']) ? intval($deleteData['userId']) : null;

        /* verify the arguments */
        if (empty($userId)) {
            throw new \Exception("userId can't be null", 400);
        }

        /* delete the user */
        deleteUser($db, $userId);

        /* return success response */
        echo ApiResponse::success("User deleted successfully")->toJson();
    } catch (\Exception $e) {
        /* return fail response */
        echo ApiResponse::error($e->getCode(), $e->getMessage())->toJson();
    }
}

/* call the request handling function */
handleRequest();