<?php
/**
 * @file apis/user_manage/add_user.php
 * @brief add a user(admin/doctor/patient)
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

require_once __DIR__ . '/../utils/ApiResponse.php';
require_once __DIR__ . '/../utils/Database.php';
require_once __DIR__ . '/../utils/utils.php';

use App\Response\ApiResponse;
use App\Database\Database;

function getNewUserID($db) {
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

function add_user($db, $user, $userName, $userAcc, $userCell, $userPassword, $userGender, $userAge, $userType) {
    $newUserID = getNewUserID($db);
    try {
        $db->beginTransaction();
        $stmt = $db->prepare(
            "INSERT INTO user (user_id, user_acc, pass_hash, user_auth)
            VALUES (
                :user_id,
                :user_acc,
                :pass_hash,
                (SELECT auth_id FROM authdef WHERE auth_name = :user_type));"
        );
        $stmt->bindParam(':user_id', $newUserID, \PDO::PARAM_INT);
        $stmt->bindParam(':user_acc', $userAcc, \PDO::PARAM_STR);
        $stmt->bindParam(':pass_hash', $userPassword, \PDO::PARAM_STR);
        $stmt->bindParam(':user_type', $userType, \PDO::PARAM_STR);
        $stmt->bindParam(':user_auth', $userPassword, \PDO::PARAM_STR);
        $stmt->execute();

        $stmt = $db->prepare(
            "INSERT INTO user_info (user_id, user_name, user_cell, user_gender, user_age)
            VALUES (:user_id, :name, :cell, :gender, :age)"
        );
        $stmt->bindParam(':user_id', $newUserID, \PDO::PARAM_INT);
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

        if (!isset($_SESSION['UserType']) || $_SESSION['UserType'] != "admin") {
            throw new \Exception("user not logged in or operation not permitted for current user", 401);
        }

        if (empty($_POST['user_type']) ||
            empty($_POST['user_acc']) ||
            empty($_POST['name']) ||
            empty($_POST['cellphone']) ||
            empty($_POST['password']) ||
            empty($_POST['gender']) ||
            empty($_POST['age'])) {
            throw new \Exception("empty field", 400);
        }

        if ($_POST['user_type'] == '医生' && empty($_POST['doc_dep'])) {
            throw new \Exception("empty field: doc_dep", 400);
        }

        $db = initializeDatabase();

        $db_user_id = getNewUserId($db);

        try {
            $db->beginTransaction();
            $in_user_id = getNewUserId($db);
            $db->beginTransaction();
            $stmt = $db->prepare(
                "INSERT INTO user (user_id, user_acc, pass_hash, user_auth)
                VALUES (:user_id, :user_acc, :pass_hash, 4)"
            );
            $stmt->bindParam(':user_id', $db_user_id, \PDO::PARAM_INT);
            $stmt->bindValue(':user_acc', $userCell, \PDO::PARAM_STR);
            $stmt->bindParam(':user_acc', $userCell, \PDO::PARAM_STR);
            $stmt->bindParam(':pass_hash', $userPassword, \PDO::PARAM_STR);
            $stmt->execute();
        }

        $in_psd_hash = password_hash($_POST['password'], PASSWORD_DEFAULT);


        /* return success response */
        echo ApiResponse::success($_POST['user_type'])->toJson();
    } catch (\Exception $e) {
        /* return fail response */
        echo ApiResponse::error($e->getCode(), $e->getMessage())->toJson();
    }
}

/* call the request handling function */
handleRequest();
