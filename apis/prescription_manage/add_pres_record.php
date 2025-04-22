<?php
/**
 * @file apis/DispenseDrug.php
 * @brief API for dispensing drug based on prescription record (with inventory check)
 * @author xing
 * @date 2025-04-13
 */

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

function handleRequest() {
    try {
        session_start();

        if ($_SESSION["UserType"] !== "admin" && $_SESSION["UserType"] !== "药房管理员" && "医生" !== $_SESSION["UserType"]) {
            throw new \Exception("operation not permitted for current user", 401);
        }

        verifyMethods(['POST']);
        $db = initializeDatabase();
        /* verify the arguments */
        $fields = ['appointment_id', 'drug_id', 'amount', 'oper_code'];

        foreach ($fields as $field) {
            if (!isset($_POST[$field]) && empty($_POST[$field])) {
                throw new \Exception("empty field: {$field}", 400);
            }
        }
        $db->beginTransaction();
        $stmt = $db->prepare("
            INSERT INTO prescription_record (ap_id, drug_id, oper_amount, status_code)
            VALUES (:ap_id, :drug_id, :amount, :oper_code)
        ");
        $stmt->bindParam(':ap_id', $_POST['appointment_id'], \PDO::PARAM_INT);
        $stmt->bindParam(':drug_id', $_POST['drug_id'], \PDO::PARAM_INT);
        $stmt->bindParam(':amount', $_POST['amount'], \PDO::PARAM_INT);
        $stmt->bindParam(':oper_code', $_POST['oper_code'], \PDO::PARAM_INT);
        $stmt->execute();
        $db->commit();

        echo ApiResponse::success()->toJson();

    } catch (\Exception $e) {
        if (isset($db) && $db->inTransaction()) {
            $db->rollBack();
        }
        echo ApiResponse::error($e->getCode(), $e->getMessage())->toJson();
    }
}

handleRequest();
