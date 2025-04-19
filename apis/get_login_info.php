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
header("Access-Control-Allow-Methods: GET");        /* NOTE: change the allow method for each single api */
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

        verifyMethods(['GET']);

        if (!isset($_SESSION['UserID']) || !isset($_SESSION['UserType'])) {
            throw new \Exception("user not login", 401);
        }

        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $client_ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } elseif (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $client_ip = $_SERVER['HTTP_CLIENT_IP'];
        } else {
            $client_ip = $_SERVER['REMOTE_ADDR'];
        }

        // Get client port
        $client_port = $_SERVER['REMOTE_PORT'];

        $ss_data = Array(
            'user_id' => $_SESSION['UserID'],
            'user_type' => $_SESSION['UserType'],
            'client_ip' => $client_ip,
            'client_port' => $client_port
        );
        /* return success response */
        echo ApiResponse::success($ss_data)->toJson();
    } catch (\Exception $e) {
        /* return fail response */
        echo ApiResponse::error($e->getCode(), $e->getMessage())->toJson();
    }
}

/* call the request handling function */
handleRequest();
