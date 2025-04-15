<?php
/**
 * @file apis/logout.php
 * @brief the logout api
 * @author xingfen
 * @date 2025-04-13
 */

/* set the response header to JSON */
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Credentials: true");
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

        session_unset();

        session_destroy();
        /* return success response */
        echo ApiResponse::success("logout success")->toJson();
    } catch (\Exception $e) {
        /* return fail response */
        echo ApiResponse::error($e->getCode(), $e->getMessage())->toJson();
    }
}

/* call the request handling function */
handleRequest();
