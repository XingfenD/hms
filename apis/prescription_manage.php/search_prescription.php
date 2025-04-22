<?php
/**
 * @file apis/GetPendingPrescriptions.php
 * @brief 获取所有待发药的处方详细信息列表（含患者、医生、药品等）
 */

header('Content-Type: application/json');
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
$allowedOrigins = ['http://localhost:5173'];
if (in_array($origin, $allowedOrigins)) {
    header("Access-Control-Allow-Origin: $origin");
    header("Access-Control-Allow-Credentials: true");
}
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type");

require_once __DIR__ . '/utils/ApiResponse.php';
require_once __DIR__ . '/utils/Database.php';
require_once __DIR__ . '/utils/utils.php';

use App\Response\ApiResponse;

function handleRequest() {
    try {
        session_start();

        if ($_SESSION["UserType"] !== "admin") {
            throw new \Exception("operation not permitted for current user", 401);
        }

        verifyMethods(['GET']);
        $db = initializeDatabase();

        $sql = "
            SELECT 
                pr.pres_rcd_id,
                pr.pres_id,
                pr.status_code,
                pr.oper_amount,
                pr.oper_time AS prescription_time,
                
                d.drug_id,
                d.drug_name,
                d.drug_price,
                d.drug_store,
                (pr.oper_amount * d.drug_price) AS total_price,

                ap.ap_id,
                ap.ap_date,
                ap.ap_time,
                ap.ap_status,
                ap.ap_result,

                pat.user_id AS patient_id,
                pat.user_name AS patient_name,
                pat.user_cell AS patient_phone,
                pat.user_gender AS patient_gender,
                pat.user_age AS patient_age,

                doc.user_id AS doctor_id,
                doc.user_name AS doctor_name,
                doc.user_cell AS doctor_phone,
                doc.user_gender AS doctor_gender,
                doc.user_age AS doctor_age

            FROM prescription_record pr
            JOIN appointment ap ON pr.ap_id = ap.ap_id
            JOIN user_info pat ON ap.pat_id = pat.user_id
            JOIN user_info doc ON ap.doc_id = doc.user_id
            JOIN drug_def d ON pr.drug_id = d.drug_id

            WHERE pr.status_code = 1
            ORDER BY pr.oper_time DESC
        ";

        $stmt = $db->prepare($sql);
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo ApiResponse::success($results)->toJson();

    } catch (\Exception $e) {
        echo ApiResponse::error($e->getCode(), $e->getMessage())->toJson();
    }
}

handleRequest();
