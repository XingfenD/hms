<?php
/**
 * @file apis/GetPendingPrescriptions.php
 * @brief 获取所有待发药的处方详细信息列表（含患者、医生、药品等）
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
function handleRequest() {
    try {
        session_start();

        if ($_SESSION["UserType"] !== "admin" && $_SESSION["UserType"] !== "药房管理员" && $_SESSION["UserType"] !== "医生") {
            throw new \Exception("operation not permitted for current user", 401);
        }

        verifyMethods(['GET']);
        $db = initializeDatabase();

        $stmt = $db->prepare(
            "SELECT
                AP_ID.ap_id AS ap_id,
                ps.pres_id,
                aps.DoctorName AS doc_name,
                aps.PatientName AS pat_name,
                aps.Title AS doc_title,
                drug_def.drug_name,
                ps.payed_amount,
                ps.recipe_amount,
                ps.total_amount
            FROM
                prescriptions AS ps
            LEFT JOIN (
                SELECT
                    prescription_record.ap_id,
                    prescription_record.pres_id
                FROM
                    prescription_record
                LIMIT 1
            ) AS AP_ID
            ON
                AP_ID.pres_id = ps.pres_id
            LEFT JOIN
                appointments AS aps
            ON
                aps.AppointmentID = AP_ID.ap_id
            LEFT JOIN
                drug_def
            ON
                drug_def.drug_id = ps.drug_id
            "
        );
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo ApiResponse::success($results)->toJson();

    } catch (\Exception $e) {
        echo ApiResponse::error($e->getCode(), $e->getMessage())->toJson();
    }
}

handleRequest();
