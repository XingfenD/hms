<?php
/**
 * @file apis/user_manage/get_user_data.php
 * @brief get the users' data of the three user_type
 * @author xingfen
 * @date 2025-04-13
 */

/* set the response header to JSON */
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

function update_u($db, $user_id, $user_dict) {
    try {
        $sql = "UPDATE user SET ";
        $params = [];
        $updates = [];
        foreach ($user_dict as $field => $value) {
            $updates[] = "$field = :$field";
            $params[":$field"] = $value;
        }
        if (empty($params)) {
            return;
        }
        $sql .= implode(", ", $updates);
        $sql .= " WHERE user_id = :user_id";
        $params[':user_id'] = $user_id;
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
    } catch (\PDOException $e) {
        throw new \Exception ("Database query failed: " . $e->getMessage(), 500);
    }
}

function update_ui($db, $user_id, $ui_dict) {
    try {
        $sql = "UPDATE user_info SET ";
        $params = [];
        $updates = [];
        foreach ($ui_dict as $field => $value) {
            $updates[] = "$field = :$field";
            $params[":$field"] = $value;
        }
        if (empty($params)) {
            return;
        }
        $sql .= implode(", ", $updates);
        $sql .= " WHERE user_id = :user_id";
        $params[':user_id'] = $user_id;
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
    } catch (\PDOException $e) {
        throw new \Exception ("Database query failed: " . $e->getMessage(), 500);
    }
}

function update_d($db, $user_id, $doc_dict) {
    try {
        $sql = "UPDATE doctor SET ";
        $params = [];
        $updates = [];
        foreach ($doc_dict as $field => $value) {
            $updates[] = "$field = :$field";
            $params[":$field"] = $value;
        }
        if (empty($params)) {
            return;
        }
        $sql .= implode(", ", $updates);
        $sql .= " WHERE doc_uid = :user_id";
        $params[':user_id'] = $user_id;
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
    } catch (\PDOException $e) {
        throw new \Exception ("Database query failed: " . $e->getMessage(), 500);
    }
}

function get_dep_id($db, $name) {
    try {
        $stmt = $db->prepare("SELECT dep_id FROM department WHERE dep_name = :name");
        $stmt->bindParam(':name', $name, \PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetchColumn();
    } catch (\PDOException $e) {
        throw new \Exception ("Database query failed: " . $e->getMessage(), 500);
    } catch (\Exception $e) {
        throw new \Exception ("Unknown exception: " . $e->getMessage(), 500);
    }
}

function get_title_id($db, $name) {
    try {
        $stmt = $db->prepare("SELECT title_id FROM doc_title WHERE title_name = :name");
        $stmt->bindParam(':name', $name, \PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetchColumn();
    } catch (\PDOException $e) {
        throw new \Exception ("Database query failed: " . $e->getMessage(), 500);
    } catch (\Exception $e) {
        throw new \Exception ("Unknown exception: " . $e->getMessage(), 500);
    }
}

function get_auth_id($db, $name) {
    try {
        $stmt = $db->prepare("SELECT auth_id FROM auth_def WHERE auth_name = :name");
        $stmt->bindParam(':name', $name, \PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetchColumn();
    } catch (\PDOException $e) {
        throw new \Exception ("Database query failed: " . $e->getMessage(), 500);
    } catch (\Exception $e) {
        throw new \Exception ("Unknown exception: " . $e->getMessage(), 500);
    }
}

function get_gender_id($name) {
    if ($name == '男' || $name == 'male') {
        return 1;
    } else if ($name == '女' || $name == 'female') {
        return 0;
    } else {
        return 2;
    }
}

/* the function to handle the request */
function handleRequest() {
    try {
        /* use verifyMethods function in utils/utils.php */
        session_start();

        verifyMethods(['POST']);

        if (!isset($_SESSION['UserType']) || $_SESSION['UserType'] != "admin") {
            throw new \Exception("user not logged in or operation not permitted for current user", 401);
        }

        if (!isset($_POST['user_id']) || empty($_POST['user_id'])) {
            throw new \Exception("empty field: user_id", 401);
        }

        $user_fields = Array(
            'new_acc' => 'user_acc',
            'new_psd' => 'pass_hash'
            // ,'new_auth' => 'user_auth'
        );
        $ui_fields = Array(
            'new_cell' => 'user_cell',
            'new_name' => 'user_name',
            'new_age' => 'user_age',
            'new_gender' => 'user_gender'
        );
        $doc_fields = Array(
            'new_title' => 'title_id',
            'new_dep' => 'dep_id'
        );
        $received_u = Array();
        $received_ui = Array();
        $received_d = Array();

        $db = initializeDatabase();
        $db->beginTransaction();
        try {
            foreach ($user_fields as $k => $v) {
                if (isset($_POST[$k]) && !empty($_POST[$k])) {
                    if($v == 'pass_hash') {
                        $received_u[$v] = password_hash($_POST[$k], PASSWORD_DEFAULT);
                    } else if ($v == 'user_auth') {
                        $received_u[$v] = get_auth_id($db, $_POST[$k]);
                    } else {
                        $received_u[$v] = $_POST[$k];
                    }
                }
            }
            foreach ($ui_fields as $k => $v) {
                if (isset($_POST[$k]) && !empty($_POST[$k])) {
                    if ($v == 'user_gender') {
                        $received_ui[$v] = get_gender_id($_POST[$k]);
                    } else {
                        $received_ui[$v] = $_POST[$k];
                    }
                }
            }
            foreach ($doc_fields as $k => $v) {
                if (isset($_POST[$k]) && !empty($_POST[$k])) {
                    if ($v == 'title_id') {
                        $received_d[$v] = get_title_id($db, $_POST[$k]);
                    } else if ($v == 'dep_id') {
                        $received_d[$v] = get_dep_id($db, $_POST[$k]);
                    } else {
                        $received_d[$v] = $_POST[$k];
                    }
                }
            }

            update_u($db, $_POST['user_id'], $received_u);
            update_ui($db, $_POST['user_id'], $received_ui);
            update_d($db, $_POST['user_id'], $received_d);
            $db->commit();
        } catch (\PDOException $e) {
            $db->rollback();
            throw new \Exception ("Database query failed: " . $e->getMessage(), 500);
        }
        /* return success response */
        echo ApiResponse::success()->toJson();
    } catch (\Exception $e) {
        /* return fail response */
        echo ApiResponse::error($e->getCode(), $e->getMessage())->toJson();
    }
}

/* call the request handling function */
handleRequest();
