<?php
/**
 * @file apis/GetDoctorsByDepartment.php
 * @brief 获取指定科室下的医生信息
 * @date 2025-04-13
 */

header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type");

require_once __DIR__ . '/utils/ApiResponse.php';
require_once __DIR__ . '/utils/Database.php';
require_once __DIR__ . '/utils/utils.php';

use App\Response\ApiResponse;

/**
 * 根据科室 ID 获取该科室下的医生信息
 *
 * @param \PDO $db
 * @param int $departmentId
 * @return array|null
 */
function fetchDoctorsByDepartment($db, $departmentId) {
    try {
        $stmt = $db->prepare("
            SELECT d.DoctorID, d.FullName, dept.Department
            FROM doctors d
            INNER JOIN departments dept ON d.DepartmentID = dept.DepartmentID
            WHERE d.DepartmentID = :deptId
            ORDER BY d.DoctorID ASC
        ");
        $stmt->bindParam(':deptId', $departmentId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (\PDOException $e) {
        throw new \Exception("数据库查询失败: " . $e->getMessage(), 500);
    }
}

function handleRequest() {
    try {
        session_start();

	    //测试的时候手动设置为管理员权限
	    $_SESSION["UserType"] = "admin";

        // 权限校验（可根据需要放开）
        if ($_SESSION["UserType"] !== "admin") {
            throw new \Exception("当前用户无权限执行该操作", 403);
        }

        verifyMethods(['GET']);
        $db = initializeDatabase();

        // 获取参数
        $departmentId = isset($_GET['department_id']) ? intval($_GET['department_id']) : null;

        if (empty($departmentId)) {
            throw new \Exception("department_id 参数不能为空", 400);
        }

        // 获取医生数据
        $doctors = fetchDoctorsByDepartment($db, $departmentId);

        if (!$doctors) {
            throw new \Exception("该科室下暂无医生", 404);
        }

        echo ApiResponse::success($doctors)->toJson();
    } catch (\Exception $e) {
        echo ApiResponse::error($e->getCode(), $e->getMessage())->toJson();
    }
}

handleRequest();
