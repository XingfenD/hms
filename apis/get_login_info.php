<?php
/**
 * @file apis/get_login_info.php
 * @brief get current login user's user_id and user_type
 * @author xingfen
 * @date 2025-04-13
 */

/* set the response header to JSON */
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
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

        if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type'])) {
            throw new \Exception("user not login", 401);
        }

        $ss_data = Array(
            'user_id' => $_SESSION['user_id'],
            'user_type' => $_SESSION['user_type']
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
