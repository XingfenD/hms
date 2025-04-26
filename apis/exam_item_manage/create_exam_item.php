<?php
/**
 * @file apis/get_login_info.php
 * @brief get current login user's user_id and user_type
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

/* the function to handle the request */
function handleRequest() {
    try {
        /* use verifyMethods function in utils/utils.php */
        session_start();

        verifyMethods(['POST']);

        if (!isset($_SESSION['UserType']) || $_SESSION['UserType'] != 'admin') {
            throw new \Exception("user not logged in or operation not permitted for current user", 401);
        }

        if (empty($_POST['exam_name']) || empty($_POST['exam_price'])) {
            throw new \Exception("empty field", 400);
        }

        try {
            $db = initializeDatabase();
            $stmt = $db->prepare(
                "INSERT INTO exam_item (exam_name, exam_price) VALUES (:exam_name, :exam_price);";
            );
            $stmt->bindParam(':exam_name', $_POST['exam_name'], \PDO::PARAM_STR);
            $stmt->bindParam(':exam_price', $_POST['exam_price'], \PDO::PARAM_INT);
            $stmt->execute();

        } catch (\PDOException $e) {
            throw new \Exception("Database query failed: ". $e->getMessage(), 500);
        }


        /* return success response */
        echo ApiResponse::success()->toJson();
    } catch (\Exception $e) {
        /* return fail response */
        echo ApiResponse::error($e->getCode(), $e->getMessage())->toJson();
    }
}

/* call the request handling function */
handleRequest();
