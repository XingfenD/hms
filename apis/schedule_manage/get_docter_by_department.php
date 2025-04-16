<?php
/**
 * @file apis/GetDoctorsByDepartment.php
 * @brief API - 获取指定科室的医生列表
 * @author weichuanbo
 * @date 2025-04-14
 */

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

/**
 * 根据科室 ID 查询医生
 * 
 * @param \PDO $db
 * @param int $departmentId
 * @return array|null
 */
function fetchDoctorsByDepartment($db, $departmentId) {
    try {
        $sql = "SELECT doctors.DoctorID, doctors.FullName, departments.Department
                FROM doctors
                INNER JOIN departments ON doctors.DepartmentID = departments.DepartmentID
                WHERE departments.DepartmentID = :department_id
                ORDER BY doctors.DoctorID ASC";

        $stmt = $db->prepare($sql);
        $stmt->bindParam(':department_id', $departmentId, PDO::PARAM_INT);
        $stmt->execute();

        $doctors = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $doctors;
    } catch (\PDOException $e) {
        throw new \Exception("Database query failed: " . $e->getMessage(), 500);
    }
}

function handleRequest() {
    try {
        session_start();

        // 权限验证：仅限管理员
        if ($_SESSION["UserType"] !== "admin") {
            throw new \Exception("operation not permitted for current user", 403);
        }

        verifyMethods(['GET']);

        $db = initializeDatabase();

        $departmentId = isset($_GET['department_id']) ? intval($_GET['department_id']) : null;

        if (empty($departmentId)) {
            throw new \Exception("department_id 参数不能为空", 400);
        }

        $doctorList = fetchDoctorsByDepartment($db, $departmentId);

        if (empty($doctorList)) {
            throw new \Exception("未找到该科室的医生", 404);
        }

        echo ApiResponse::success($doctorList)->toJson();
    } catch (\Exception $e) {
        echo ApiResponse::error($e->getCode(), $e->getMessage())->toJson();
    }
}

handleRequest();
