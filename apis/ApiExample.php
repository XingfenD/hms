<?php
/**
 * @file apis/ApiExample.php
 * @brief example of API implementation
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

/* NOTE: define your own database query function here to replace fetchUserData()  */
/**
 * get the user data
 *
 * @param \PDO $db instance of database connection
 * @param int $userId user id
 * @return array|null user data array, return null if empty
 */
function fetchUserData($db, $userId) {
    try {
        $stmt = $db->prepare("SELECT UserId, Username, UserType FROM users WHERE UserId = :id");
        $stmt->bindParam(':id', $userId, \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(\PDO::FETCH_ASSOC);
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
        /* TODO: verify the user's authority or UserType */
        if ($_SESSION["UserType"] !== "admin") {
            throw new \Exception("operation not permitted for current user", 403);
        }
        /* TODO: custom your own permitted request methods */
        verifyMethods(['GET']);


        /* get the request arguments */
        $userId = isset($_GET['user_id']) ? intval($_GET['user_id']) : null;

        /* verify the arguments */
        if (empty($userId)) {
            throw new \Exception("user_id can't be null", 400);
        }

        /* use initializeDatabase() function in utils/utils.php */
        /* initialize the database connection */
        $db = initializeDatabase();

        /* query the user_data */
        $userData = fetchUserData($db, $userId);

        if (!$userData) {
            throw new \Exception("user doesn't exist", 404);
        }

        /* return success response */
        echo ApiResponse::success($userData)->toJson();
    } catch (\Exception $e) {
        /* return fail response */
        echo ApiResponse::error($e->getCode(), $e->getMessage())->toJson();
    }
}

/* call the request handling function */
handleRequest();