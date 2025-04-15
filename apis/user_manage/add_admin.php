<?php
/**
 * @file apis/user_manage/add_user.php
 * @brief get the users' data of the tree user_type
 * @author xingfen
 * @date 2025-04-13
 */

/* set the response header to JSON */
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");        /* NOTE: change the allow method for each single api */
header("Access-Control-Allow-Headers: Content-Type");

require_once __DIR__ . '/../utils/ApiResponse.php';
require_once __DIR__ . '/../utils/Database.php';
require_once __DIR__ . '/../utils/utils.php';

use App\Response\ApiResponse;
use App\Database\Database;

function add_admin($db, $user_name, $user_cell, $user_password) {
    $new_id = getNewUserId($db, 'admin');
    try {
        $stmt = $db->prepare(
            "INSERT INTO
                users (UserId, UserType, Username, UserCell, PasswordHash)
            VALUES
                (:new_id, 'admin', :user_name, :user_cell, :user_pass);"
        );
        $stmt->bindParam(':new_id', $new_id, \PDO::PARAM_INT);
        $stmt->bindParam(':user_name', $user_name, \PDO::PARAM_STR);
        $stmt->bindParam(':user_cell', $user_cell, \PDO::PARAM_STR);
        $stmt->bindParam(':user_pass', $user_password, \PDO::PARAM_STR);
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

        if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] != "admin") {
            throw new \Exception("user not logged in or operation not permitted current user", 401);
        }

        if (empty($_POST['name']) ||
            empty($_POST['cellphone']) ||
            empty($_POST['password'])) {
            throw new \Exception("empty field", 400);
        }

        $db = initializeDatabase();

        /* check if the user already exists */
        // $duplicateCell = checkDuplicateCell($db, $_POST['cellphone']);
        if (checkDuplicateCell($db, $_POST['cellphone'])) {
            throw new \Exception("duplicate cellphone", 400);
        }

        $in_psd_hash = password_hash($_POST['password'], PASSWORD_DEFAULT);

        add_admin($db, $_POST['name'], $_POST['cellphone'], $in_psd_hash);

        /* return success response */
        echo ApiResponse::success("add admin success")->toJson();
    } catch (\Exception $e) {
        /* return fail response */
        echo ApiResponse::error($e->getCode(), $e->getMessage())->toJson();
    }
}

/* call the request handling function */
handleRequest();
