<?php
/**
 * @file utils/utils.php
 * @brief utility functions for API
 * @author xingfen
 * @date 2025-04-13
 */

require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/ApiResponse.php';

use App\Response\ApiResponse;
use App\Database\Database;

/**
 * Initialize the database connection
 *
 * @return \PDO|null Database connection instance, or null on failure
 */
function initializeDatabase() {
    try {
        $database = new Database();
        return $database->connect();
    } catch (\Exception $e) {
        error_log("Failed to initialize database: " . $e->getMessage());
        die(json_encode(ApiResponse::error(500, "database initialization failed")->toJson()));
    }
}

/**
 * Verify the request method
 *
 * @param array $supportedMethods List of supported HTTP methods
 * @throws \Exception If the request method is not supported
 */
function verifyMethods($supportedMethods) {
    $requestMethod = $_SERVER['REQUEST_METHOD'];
    if (!in_array($requestMethod, $supportedMethods)) {
        throw new \Exception("method not supported for current api", 405);
    }
}
