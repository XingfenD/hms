<?php
/**
 * @file apis/register.php
 * @brief the register api
 * @author xingfen
 * @date 2025-04-13
 */

/* set the response header to JSON */
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");        /* NOTE: change the allow method for each single api */
header("Access-Control-Allow-Headers: Content-Type");

require_once __DIR__ . '/utils/ApiResponse.php';
require_once __DIR__ . '/utils/Database.php';
require_once __DIR__ . '/utils/utils.php';

use App\Response\ApiResponse;
use App\Database\Database;

function registerPatient($db, $userId, $userName, $userCell, $userPassword, $userGender, $userAge) {
    try {
        $stmt = $db->prepare("INSERT INTO users (UserId, UserName, UserCell, PasswordHash, UserType) VALUES (:userId, :userName, :userCell, :userPassword, 'patient')");
        $stmt->bindParam(':userId', $userId, \PDO::PARAM_INT);
        $stmt->bindParam(':userName', $userName, \PDO::PARAM_STR);
        $stmt->bindParam(':userCell', $userCell, \PDO::PARAM_STR);
        $stmt->bindParam(':userPassword', $userPassword, \PDO::PARAM_STR);
        $stmt->execute();

        $stmt = $db->prepare("INSERT INTO patients (PatientId, FullName, Gender, Age) VALUES (:userId, :userName, :userGender, :userAge)");
        $stmt->bindParam(':userId', $userId, \PDO::PARAM_INT);
        $stmt->bindParam(':userName', $userName, \PDO::PARAM_STR);
        $stmt->bindParam(':userGender', $userGender, \PDO::PARAM_STR);
        $stmt->bindParam(':userAge', $userAge, \PDO::PARAM_INT);
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

        /* use initializeDatabase() function in utils/utils.php */
        /* initialize the database connection */
        $db = initializeDatabase();
        $in_user_cell = $_POST['cellphone'];
        $in_user_name = $_POST['name'];
        $in_password = $_POST['password'];
        $in_gender = $_POST['gender'];
        $in_age = $_POST['age'];

        /* verify the arguments */
        if (empty($in_user_cell) || empty($in_user_name) || empty($in_password)) {
            throw new \Exception("empty field", 400);
        }

        /* query the max user id  */
        $newPatientId = getNewUserId($db, 'patient');
        /* NOTE: may need error handling */

        $in_psd_hash = password_hash($in_password, PASSWORD_DEFAULT);

        /* check if the user already exists */
        // $duplicateCell = checkDuplicateCell($db, $in_user_cell);
        if (checkDuplicateCell($db, $in_user_cell)) {
            throw new \Exception("duplicate cellphone", 400);
        }

        /* insert the new user */
        registerPatient($db, $newPatientId, $in_user_name, $in_user_cell, $in_psd_hash, $in_gender, $in_age);

        /* return success response */
        echo ApiResponse::success($newPatientId)->toJson();
    } catch (\Exception $e) {
        /* return fail response */
        echo ApiResponse::error($e->getCode(), $e->getMessage())->toJson();
    }
}

/* call the request handling function */
handleRequest();