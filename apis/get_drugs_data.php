<?php
/**
 * @file apis/user_manage/get_user_data.php
 * @brief get the users' data of the tree user_type
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

function fetchDrugData($db) {
    try {
        $stmt = $db->prepare(
            "SELECT
                DrugID as drug_id,
                DrugName as drug_name,
                Price as drug_price,
                StockQuantity as storage
            FROM
                drugs
            ORDER BY
                DrugID ASC;"
        );
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    } catch (\PDOException $e) {
        throw new \Exception("Database query failed: ". $e->getMessage(), 500);
        return null;
    }
}

/* the function to handle the request */
function handleRequest() {
    try {
        /* use verifyMethods function in utils/utils.php */
        session_start();

        verifyMethods(['GET']);

        $db = initializeDatabase();

        $ret = fetchDrugData($db);
        /* return success response */
        echo ApiResponse::success($ret)->toJson();
    } catch (\Exception $e) {
        /* return fail response */
        echo ApiResponse::error($e->getCode(), $e->getMessage())->toJson();
    }
}

/* call the request handling function */
handleRequest();
