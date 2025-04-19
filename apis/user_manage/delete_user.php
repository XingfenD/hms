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

function delete_user($db, $user_acc) {
    try {
        $db->beginTransaction();
        $stmt = $db->prepare("
            DELETE FROM user
            WHERE user_acc = :user_acc"
        );

        $stmt->bindParam(":user_acc", $user_acc);
        $stmt->execute();

        $db->commit();
    } catch (\PDOException $e) {
        $db->rollback();
        throw new \Exception ("Database query failed: " . $e->getMessage(), 500);
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

        $fields = ['account'];

        foreach ($fields as $field) {
            if (empty($_POST[$field])) {
                throw new \Exception("empty field: {$field}", 400);
            }
        }

        $db = initializeDatabase();

        delete_user($db, $_POST['account']);

        /* return success response */
        echo ApiResponse::success($_POST['account'])->toJson();
    } catch (\Exception $e) {
        /* return fail response */
        echo ApiResponse::error($e->getCode(), $e->getMessage())->toJson();
    }
}

/* call the request handling function */
handleRequest();
