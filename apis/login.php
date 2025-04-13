<?php
/**
 * @file apis/login.php
 * @brief the login api
 * @author xingfen
 * @date 2025-04-13
 */

/* set the response header to JSON */
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");        /* NOTE: change the allow method for each single api */
header("Access-Control-Allow-Headers: Content-Type");

require_once __DIR__ . '/utils/ApiResponse.php';
require_once __DIR__ . '/utils/Database.php';
require_once __DIR__ . '/utils/utils.php';

use App\Response\ApiResponse;
use App\Database\Database;

/**
 * get the user data
 *
 * @param \PDO $db instance of database connection
 * @param string $user_cell user cellphone
 * @return array|null user data array, return null if empty
 */
function fetchLoginInfoByCellphone($db, $user_cell, $user_type) {
    try {
        $stmt = $db->prepare("SELECT UserId, PasswordHash, UserType FROM users WHERE UserCell = :cell AND UserType = :user_type");
        $stmt->bindParam(':cell', $user_cell, \PDO::PARAM_STR);
        $stmt->bindParam(':user_type', $user_type, \PDO::PARAM_STR);
        $stmt->execute();

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    } catch (\PDOException $e) {
        throw new \Exception ("Database query failed: " . $e->getMessage(), 500);
        return null;
    }
}

function fetchLoginInfoByUsername($db, $user_name, $user_type) {
    try {
        $stmt = $db->prepare("SELECT UserId, PasswordHash, UserType FROM users WHERE Username = :name AND UserType = :user_type");
        $stmt->bindParam(':name', $user_name, \PDO::PARAM_STR);
        $stmt->bindParam(':user_type', $user_type, \PDO::PARAM_STR);
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
        $in_user_cell = $_POST['cellphone'];    /* cellphone or user_name */
        $in_password = $_POST['password'];
        $in_user_type = $_POST['user_type'];

        /* verify the arguments */
        if (empty($in_user_cell) || empty($in_password) || empty($in_user_type)) {
            throw new \Exception("empty field", 400);
        }

        /* query the login info */
        $db_userInfo = fetchLoginInfoByCellphone($db, $in_user_cell, $in_user_type);
        $db_userInfo2 = fetchLoginInfoByUserName($db, $in_user_cell, $in_user_type);
        // if (!$db_userInfo && !$db_userInfo2) {
        //     throw new \Exception("user doesn't exist", 404);
        // } else if (count($db_userInfo) + count($db_userInfo2) > 1) {
        //     throw new \Exception("duplicate cellphone or name", 500);
        // }

        $db_final_user_info = (!$db_userInfo)? $db_userInfo2: $db_userInfo;

        if (!$db_final_user_info) {
            throw new \Exception("user doesn't exist", 404);
        } else if (count($db_final_user_info) > 1) {
            throw new \Exception("duplicate cellphone or name", 500);
        }

        $db_psd_hash = $db_final_user_info[0]["PasswordHash"];
        $db_user_type = $db_final_user_info[0]["UserType"];
        $db_user_id = $db_final_user_info[0]["UserId"];

        if (!password_verify($in_password, $db_psd_hash)) {
            throw new \Exception("cellphone or password error", 401);
        }

        /* TODO: the logic below is possible to be changed */
        switch ($db_user_type) {
            case 'doctor':
                $_SESSION["doctor_login"] = $db_user_id;
                break;
            case 'patient':
                $_SESSION["patient_login"] = $db_user_id;
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
