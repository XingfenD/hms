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

function fetchLatestPatientId($db) {
    try {
        // 查询 user 表中最大的 UserId
        $stmt = $db->prepare("SELECT MAX(UserId) AS MaxUserId FROM users");
        $stmt->execute();

        // 返回结果
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    } catch (\PDOException $e) {
        throw new \Exception("Database query failed: " . $e->getMessage(), 500);
    }
}

function checkDuplicateCell($db, $userCell) {
    try {
        $stmt = $db->prepare("SELECT COUNT(*) AS Count FROM users WHERE UserCell = :userCell");
        $stmt->bindParam(':userCell', $userCell, \PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    } catch (\PDOException $e) {
        throw new \Exception("Database query failed: ". $e->getMessage(), 500);
    }
}

function registerPatient($db, $userId, $userName, $userCell, $userPassword) {
    try {
        $stmt = $db->prepare("INSERT INTO users (UserId, UserName, UserCell, PasswordHash, UserType) VALUES (:userId, :userName, :userCell, :userPassword, 'patient')");
        $stmt->bindParam(':userId', $userId, \PDO::PARAM_INT);
        $stmt->bindParam(':userName', $userName, \PDO::PARAM_STR);
        $stmt->bindParam(':userCell', $userCell, \PDO::PARAM_STR);
        $stmt->bindParam(':userPassword', $userPassword, \PDO::PARAM_STR);
        $stmt->execute();
    } catch (\PDOException $e) {
        throw new \Exception("Database query failed: ". $e->getMessage(), 500);
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
        $in_user_cell= $_POST['cellphone'];
        $in_user_name = $_POST['name'];
        $in_password = $_POST['password'];

        /* verify the arguments */
        if (empty($in_user_cell) || empty($in_user_name) || empty($in_password)) {
            throw new \Exception("empty field", 400);
        }

        /* query the max user id  */
        $latestPatientId = fetchLatestPatientId($db);
        $newPatientId = 1;
        if (count($latestPatientId) > 0 && $latestPatientId['MaxUserId'] !== null) {
            $newPatientId = $latestPatientId['MaxUserId'] + 1;
        }
        /* NOTE: may need error handling */

        $in_psd_hash = password_hash($in_password, PASSWORD_DEFAULT);

        /* check if the user already exists */
        $duplicateCell = checkDuplicateCell($db, $in_user_cell);
        if ($duplicateCell['Count'] > 0) {
            throw new \Exception("duplicate cellphone", 400);
        }

        /* insert the new user */
        registerPatient($db, $newPatientId, $in_user_name, $in_user_cell, $in_psd_hash);

        /* return success response */
        echo ApiResponse::success("register success")->toJson();
    } catch (\Exception $e) {
        /* return fail response */
        echo ApiResponse::error($e->getCode(), $e->getMessage())->toJson();
    }
}

/* call the request handling function */
handleRequest();