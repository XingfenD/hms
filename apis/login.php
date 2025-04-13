<?php
/**
 * @file apis/login.php
 * @brief the login api
 * @author xingfen
 * @date 2025-04-13
 */

/* set the response header to JSON */
header('Content-Type: application/json');

require_once __DIR__ . '/utils/ApiResponse.php';
require_once __DIR__ . '/utils/Database.php';
require_once __DIR__ . '/utils/utils.php';

use App\Response\ApiResponse;
use App\Database\Database;

/**
 * get the user data
 *
 * @param \PDO $db instance of database connection
 * @param int $userId user id
 * @return array|null user data array, return null if empty
 */
function fetchLoginInfo($db, $userId) {
    try {
        $stmt = $db->prepare("SELECT PasswordHash, UserType FROM users WHERE UserId = :id");
        $stmt->bindParam(':id', $userId, \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    } catch (\PDOException $e) {
        throw new \Exception ("Database query failed: " . $e->getMessage(), 500);
        return null;
    }
}


/* the function to handle the request */
function handleRequest() {
    try {
        /* use verifyMethods function in utils/utils.php */
        session_start();

        verifyMethods(['POST']);

        /* use initializeDatabase() function in utils/utils.php */
        /* initialize the database connection */
        $db = initializeDatabase();
        $userId = $_POST['user_id'];
        $password = $_POST['password'];

        /* verify the arguments */
        if (empty($userId) || empty($password)) {
            throw new \Exception("user_id or password can't be null", 400);
        }

        /* query the login info */
        $userInfo = fetchLoginInfo($db, $userId);

        if (!$userInfo) {
            throw new \Exception("user doesn't exist", 404);
        } else if (count($userInfo) != 1) {
            throw new \Exception("duplicate user_id", 500);
        }

        $db_psd_hash = $userInfo[0]["PasswordHash"];
        $db_user_type = $userInfo[0]["UserType"];

        if (!password_verify($password, $db_psd_hash)) {
            throw new \Exception("use_id or password error", 401);
        }

        /* TODO: the logic below is possible to be changed */
        switch ($db_user_type) {
            case 'doctor':
                $_SESSION["doctor_login"] = $user_id;
                break;
            case 'patient':
                $_SESSION["patient_login"] = $user_id;
                break;
            case 'admin':
                $_SESSION["UserType"] = "admin";
                break;
            default:
                throw new \Exception("unknown user_type", 500);
        }
        /* return success response */
        echo ApiResponse::success(null)->toJson();
    } catch (\Exception $e) {
        /* return fail response */
        echo ApiResponse::error($e->getCode(), $e->getMessage())->toJson();
    }
}

/* call the request handling function */
handleRequest();