<?php
/**
 * @file apis/user_manage/add_user.php
 * @brief get the users' data of the tree user_type
 * @author xingfen
 * @date 2025-04-13
 */

/* set the response header to JSON */
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");        /* NOTE: change the allow method for each single api */
header("Access-Control-Allow-Headers: Content-Type");

require_once __DIR__ . '/../utils/ApiResponse.php';
require_once __DIR__ . '/../utils/Database.php';
require_once __DIR__ . '/../utils/utils.php';

use App\Response\ApiResponse;
use App\Database\Database;

function add_admin($db, $user_name, $user_cell, $user_password) {
    $new_id = getNewId($db, 'admin');
    try {
        $stmt = $db->prepare(
            "INSERT INTO
                users (UserId, UserType, UserName, UserCell, UserPassword)
            VALUES
                (new_id, 'admin', :user_name, :user_cell, :user_pass);"
        );
        $stmt->bindParam(':user_name', $user_name, \PDO::PARAM_STR);
        $stmt->bindParam(':user_cell', $user_cell, \PDO::PARAM_STR);
        $stmt->bindParam(':user_pass', $user_password, \PDO::PARAM_STR);
        $stmt->execute();
    } catch (\PDOException $e) {
        throw new \Exception("Database query failed: ". $e->getMessage(), 500);
    }
}

function add_doctor($db, $user_name, $user_cell, $user_password, $doc_dep) {
    try {
        $new_id = getNewId($db, 'doctor');
        $stmt = $db->prepare("INSERT INTO users (UserId, UserName, UserCell, PasswordHash, UserType) VALUES (:userId, :userName, :userCell, :userPassword, 'patient')");
        $stmt->bindParam(':userId', $new_id, \PDO::PARAM_INT);
        $stmt->bindParam(':userName', $user_name, \PDO::PARAM_STR);
        $stmt->bindParam(':userCell', $user_cell, \PDO::PARAM_STR);
        $stmt->bindParam(':userPassword', $user_password, \PDO::PARAM_STR);
        $stmt->execute();

        $stmt = $db->prepare(
                "INSERT INTO doctors (DoctorID, FullName, DepartmentID)
                    VALUES (
                        :userId,
                        :userName, -- 医生姓名
                        (SELECT DepartmentID FROM departments WHERE Department = '眼科' -- 查询部门 ID
                    )
                );"
            );
        $stmt->bindParam(':userId', $new_id, \PDO::PARAM_INT);
        $stmt->bindParam(':userName', $user_name, \PDO::PARAM_STR);
        $stmt->execute();
    } catch (\PDOException $e) {
        throw new \Exception("Database query failed: ". $e->getMessage(), 500);
    }
}

function add_patient($db, $user_name, $user_cell, $user_password, $patient_gender, $patient_age) {
    try {
        $new_id = getNewId($db, 'patient');
        $stmt = $db->prepare("INSERT INTO users (UserId, UserName, UserCell, PasswordHash, UserType) VALUES (:userId, :userName, :userCell, :userPassword, 'patient')");
        $stmt->bindParam(':userId', $new_id, \PDO::PARAM_INT);
        $stmt->bindParam(':userName', $user_name, \PDO::PARAM_STR);
        $stmt->bindParam(':userCell', $user_cell, \PDO::PARAM_STR);
        $stmt->bindParam(':userPassword', $user_password, \PDO::PARAM_STR);
        $stmt->execute();

        $stmt = $db->prepare("INSERT INTO patients (PatientId, FullName, Gender, Age) VALUES (:userId, :userName, :userGender, :userAge)");
        $stmt->bindParam(':userId', $new_id, \PDO::PARAM_INT);
        $stmt->bindParam(':userName', $user_name, \PDO::PARAM_STR);
        $stmt->bindParam(':userGender', $patient_gender, \PDO::PARAM_STR);
        $stmt->bindParam(':userAge', $patient_age, \PDO::PARAM_INT);
        $stmt->execute();
    } catch (\PDOException $e) {
        throw new \Exception("Database query failed: ". $e->getMessage(), 500);
    }
}

/* the function to handle the request */
function handleRequest() {
    try {
        /* use verifyMethods function in utils/utils.php */
        session_start();

        verifyMethods(['POST']);

        if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] != "admin") {
            throw new \Exception("user not logged in or operation not permitted current user", 401);
        }

        if (empty($_POST['user_type']) ||
            empty($_POST['user_name']) ||
            empty($_POST['user_cell']) ||
            empty($_POST['user_password'])) {
            throw new \Exception("empty field", 400);
        }

        if ($_POST['user_type'] == 'admin') {
            
        }

        $db = initializeDatabase();

        $ret = Array(
            "patients" => fetchPatientData($db),
            "doctors" => fetchDoctorData($db)
        );
        /* return success response */
        echo ApiResponse::success($ret)->toJson();
    } catch (\Exception $e) {
        /* return fail response */
        echo ApiResponse::error($e->getCode(), $e->getMessage())->toJson();
    }
}

/* call the request handling function */
handleRequest();
