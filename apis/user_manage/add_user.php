<?php
/**
 * @file apis/user_manage/add_user.php
 * @brief add a user(admin/doctor/patient)
 * @author xingfen
 * @date 2025-04-13
 */

/* set the response header to JSON */
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: POST");        /* NOTE: change the allow method for each single api */
header("Access-Control-Allow-Headers: Content-Type");

require_once __DIR__ . '/../utils/ApiResponse.php';
require_once __DIR__ . '/../utils/Database.php';
require_once __DIR__ . '/../utils/utils.php';

use App\Response\ApiResponse;
use App\Database\Database;

function add_admin($db, $user_name, $user_cell, $user_password) {
    $new_id = getNewUserId($db, 'admin');
    try {
        $stmt = $db->prepare(
            "INSERT INTO
                users (UserId, UserType, Username, UserCell, PasswordHash)
            VALUES
                (:new_id, 'admin', :user_name, :user_cell, :user_pass);"
        );
        $stmt->bindParam(':new_id', $new_id, \PDO::PARAM_INT);
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
        $new_id = getNewUserId($db, 'doctor');
        $stmt = $db->prepare(
            "INSERT INTO
                users (UserId, Username, UserCell, PasswordHash, UserType)
            VALUES
                (:userId, :userName, :userCell, :userPassword, 'doctor')"
        );
        $stmt->bindParam(':userId', $new_id, \PDO::PARAM_INT);
        $stmt->bindParam(':userName', $user_name, \PDO::PARAM_STR);
        $stmt->bindParam(':userCell', $user_cell, \PDO::PARAM_STR);
        $stmt->bindParam(':userPassword', $user_password, \PDO::PARAM_STR);
        $stmt->execute();

        $stmt = $db->prepare(
            "INSERT INTO doctors (DoctorID, FullName, DepartmentID)
                VALUES (
                    :userId,
                    :userName,
                    (SELECT DepartmentID FROM departments WHERE Department = :dep_name -- 查询部门 ID
                )
            );"
        );
        $stmt->bindParam(':userId', $new_id, \PDO::PARAM_INT);
        $stmt->bindParam(':userName', $user_name, \PDO::PARAM_STR);
        $stmt->bindParam(':dep_name', $doc_dep, \PDO::PARAM_STR);
        $stmt->execute();
    } catch (\PDOException $e) {
        throw new \Exception("Database query failed: ". $e->getMessage(), 500);
    }
}

function add_patient($db, $user_name, $user_cell, $user_password, $patient_gender, $patient_age) {
    try {
        $new_id = getNewUserId($db, 'patient');
        $stmt = $db->prepare(
            "INSERT INTO
                users (UserId, Username, UserCell, PasswordHash, UserType)
            VALUES
                (:userId, :userName, :userCell, :userPassword, 'patient')"
        );
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
            throw new \Exception("user not logged in or operation not permitted for current user", 401);
        }

        if (empty($_POST['user_type']) ||
            empty($_POST['name']) ||
            empty($_POST['cellphone']) ||
            empty($_POST['password'])) {
            throw new \Exception("empty field", 400);
        }

        if ($_POST['user_type'] == 'doctor' && empty($_POST['doc_dep'])) {
            throw new \Exception("empty field: doc_dep", 400);
        } else if ($_POST['user_type'] == 'patient' && (empty($_POST['patient_gender']) || empty($_POST['patient_age']))) {
            throw new \Exception("empty field: patient_gender or patient_age", 400);
        }

        $db = initializeDatabase();

        /* check if the user already exists */
        // $duplicateCell = checkDuplicateCell($db, $_POST['cellphone']);
        if (checkDuplicateCell($db, $_POST['cellphone'])) {
            throw new \Exception("duplicate cellphone", 400);
        }

        $in_psd_hash = password_hash($_POST['password'], PASSWORD_DEFAULT);

        if ($_POST['user_type'] == 'admin') {
            add_admin($db, $_POST['name'], $_POST['cellphone'], $in_psd_hash);
        } else if ($_POST['user_type'] == 'doctor') {
            add_doctor($db, $_POST['name'], $_POST['cellphone'], $in_psd_hash, $_POST['doc_dep']);
        } else if ($_POST['user_type'] == 'patient') {
            add_patient($db, $_POST['name'], $_POST['cellphone'], $in_psd_hash, $_POST['patient_gender'], $_POST['patient_age']);
        }


        /* return success response */
        echo ApiResponse::success($_POST['user_type'])->toJson();
    } catch (\Exception $e) {
        /* return fail response */
        echo ApiResponse::error($e->getCode(), $e->getMessage())->toJson();
    }
}

/* call the request handling function */
handleRequest();
