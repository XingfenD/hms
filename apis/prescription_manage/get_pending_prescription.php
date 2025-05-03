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
            //throw new \Exception("operation not permitted for current user", 401);
        }

        verifyMethods(['GET']);
        $db = initializeDatabase();

        $stmt = $db->prepare(
            "SELECT
                AP_ID.ap_id,
                AP_ID.pres_id,
                AP_ID.status_code,
                AP_ID.oper_time,
                ps.pres_id,
                aps.DoctorName AS doc_name,
                aps.PatientName AS pat_name,
                aps.Title AS doc_title,
                drug_def.drug_name,
                ps.payed_amount,
                ps.recipe_amount,
                ps.total_amount,
                pres_table.DrugSumPrice AS sum_price
            FROM
                prescriptions AS ps
            LEFT JOIN (
                SELECT
                    pr1.ap_id,
                    pr1.pres_id,
                    pr1.status_code,
                    pr1.oper_time
                FROM
                    prescription_record pr1
                JOIN (
                    SELECT
                        ap_id,
                        pres_id,
                        MAX(status_code) AS max_status_code
                    FROM
                        prescription_record
                    GROUP BY
                        ap_id,
                        pres_id
                ) pr2
                ON
                    pr1.ap_id = pr2.ap_id AND
                    pr1.pres_id = pr2.pres_id AND
                    pr1.status_code = pr2.max_status_code
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
            LEFT JOIN
                prescription_table AS pres_table
            ON
                pres_table.PrescriptionID = ps.pres_id;
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