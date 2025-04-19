<?php
/**
 * @file apis/login.php
 * @brief the login api
 * @author xingfen
 * @date 2025-04-13
 */

/* set the response header to JSON */
header('Content-Type: application/json');
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
$allowedOrigins = ['http://localhost:5173'];
if (in_array($origin, $allowedOrigins)) {
    header("Access-Control-Allow-Origin: $origin");
    header("Access-Control-Allow-Credentials: true");
}
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
    }
}

function fetchLoginInfoByUserAcc($db, $user_acc, $user_type) {
    try {
        $stmt = $db->prepare(
            "SELECT
                u.user_id AS UserId,
                u.pass_hash AS PasswordHash,
                ad.auth_name AS UserType
            FROM user AS u
            LEFT JOIN auth_def as ad
            ON u.user_auth = ad.auth_id
            WHERE 
                u.user_acc = :in_acc
                AND ad.auth_name = :in_auth"
        );
        $stmt->bindParam(':in_acc', $user_acc, \PDO::PARAM_STR);
        $stmt->bindParam(':in_auth', $user_type, \PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    } catch (\PDOException $e) {
        throw new \Exception ("Database query failed: ". $e->getMessage(), 500);
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
        $db_userInfo2 = fetchLoginInfoByUserAcc($db, $in_user_cell, $in_user_type);
        $db_final_user_info = (!$db_userInfo)? $db_userInfo2: $db_userInfo;

        if (!$db_final_user_info) {
            throw new \Exception("user doesn't exist", 404);
        } else if (count($db_final_user_info) > 1) {
            throw new \Exception("duplicate cellphone or name", 500);
        }

        $db_psd_hash = $db_final_user_info[0]['PasswordHash'];
        $db_user_type = $db_final_user_info[0]['UserType'];
        $db_user_id = $db_final_user_info[0]['UserId'];

        if (!password_verify($in_password, $db_psd_hash)) {
            throw new \Exception("cellphone or password error", 401);
        }

        /* TODO: the logic below is possible to be changed */
        $_SESSION["UserID"] = $db_user_id;
        $_SESSION["UserType"] = $db_user_type;

        /* return success response */
        echo ApiResponse::success("login success")->toJson();
    } catch (\Exception $e) {
        /* return fail response */
        echo ApiResponse::error($e->getCode(), $e->getMessage())->toJson();
    }
}

/* call the request handling function */
handleRequest();
