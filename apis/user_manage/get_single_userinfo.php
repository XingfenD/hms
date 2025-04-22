<?php
/**
 * @file apis/user_manage/get_user_data.php
 * @brief get the users' data of the three user_type
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
header("Access-Control-Allow-Methods: GET");        /* NOTE: change the allow method for each single api */
header("Access-Control-Allow-Headers: Content-Type");

require_once __DIR__ . '/../utils/ApiResponse.php';
require_once __DIR__ . '/../utils/Database.php';
require_once __DIR__ . '/../utils/utils.php';

use App\Response\ApiResponse;
use App\Database\Database;

/* the function to handle the request */
function handleRequest() {
    try {
        /* use verifyMethods function in utils/utils.php */
        session_start();

        verifyMethods(['GET']);

        if (!isset($_SESSION['UserType'])/* || $_SESSION['UserType'] != "admin"*/) {
            throw new \Exception("user not logged in or operation not permitted for current user", 401);
        }

        if (!isset($_GET['user_id'])) {
            throw new \Exception("empty field: user_id", 401);
        }

        $db = initializeDatabase();

        $stmt = $db->prepare("
            SELECT
                UserID, UserAccount, Username, UserType, UserCell
            FROM
                users
            WHERE UserID = :user_id;
        ");
        $stmt->bindParam(':user_id', $_GET['user_id'], \PDO::PARAM_INT);
        $stmt->execute();
        $ret = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        /* return success response */
        echo ApiResponse::success($ret)->toJson();
    } catch (\Exception $e) {
        /* return fail response */
        echo ApiResponse::error($e->getCode(), $e->getMessage())->toJson();
    }
}

/* call the request handling function */
handleRequest();
