<?php
/**
 * @file apis/register.php
 * @brief the register api
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

function getNewUserId($db) {
    try {
        $stmt = $db->prepare(
            "SELECT
                COALESCE(MAX(user_id), 0)
            FROM
                user"
        );
        $stmt->execute();

        $maxId = $stmt->fetchColumn() + 1;

        return $maxId;
    } catch (\PDOException $e) {
        throw new \Exception ("Database query failed: " . $e->getMessage(), 500);
    }
}

function registerPatient($db, $userName, $userAcc, $userCell, $userPassword, $userGender, $userAge) {
    try {
        $userId = getNewUserId($db);
        $db->beginTransaction();
        $stmt = $db->prepare(
            "INSERT INTO user (user_id, user_acc, pass_hash, user_auth)
            VALUES (:user_id, :user_acc, :pass_hash, 4)"
        );
        $stmt->bindParam(':user_id', $userId, \PDO::PARAM_INT);
        $stmt->bindParam(':user_acc', $userAcc, \PDO::PARAM_STR);
        $stmt->bindParam(':pass_hash', $userPassword, \PDO::PARAM_STR);
        $stmt->execute();

        $stmt = $db->prepare(
            "INSERT INTO user_info (user_id, user_name, user_cell, user_gender, user_age)
            VALUES (:user_id, :name, :cell, :gender, :age)"
        );
        $stmt->bindParam(':user_id', $userId, \PDO::PARAM_INT);
        $stmt->bindParam(':name', $userName, \PDO::PARAM_STR);
        $stmt->bindParam(':cell', $userCell, \PDO::PARAM_STR);
        if ($userGender == 'male' || $userGender == '男') {
            $stmt->bindValue(':gender', 1, \PDO::PARAM_INT);
        } else if ($userGender == 'female' || $userGender == '女') {
            $stmt->bindValue(':gender', 0, \PDO::PARAM_INT);
        } else {
            $stmt->bindValue(':gender', 2, \PDO::PARAM_INT);
        }
        $stmt->bindParam(':age', $userAge, \PDO::PARAM_INT);
        $stmt->execute();
        $db->commit();
    } catch (\PDOException $e) {
        $db->rollBack();
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
        $in_user_cell = $_POST['cellphone'];
        $in_user_acc = $_POST['account'];
        $in_user_name = $_POST['name'];
        $in_password = $_POST['password'];
        $in_gender = $_POST['gender'];
        $in_age = $_POST['age'];

        /* verify the arguments */
        if (empty($in_user_cell) || empty($in_user_name) || empty($in_password)) {
            throw new \Exception("empty field", 400);
        }

        /* query the max user id  */
        $newPatientId = getNewUserId($db);
        /* NOTE: may need error handling */

        $in_psd_hash = password_hash($in_password, PASSWORD_DEFAULT);

        /* insert the new user */
        registerPatient($db, $newPatientId, $in_user_name, $in_user_cell, $in_psd_hash, $in_gender, $in_age);

        /* return success response */
        echo ApiResponse::success($newPatientId)->toJson();
    } catch (\Exception $e) {
        /* return fail response */
        echo ApiResponse::error($e->getCode(), $e->getMessage())->toJson();
    }
}

/* call the request handling function */
handleRequest();