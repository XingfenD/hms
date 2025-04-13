<?php
/**
 * @file apis/utils/ApiResponse.php
 * @brief defination of ApiResponse class, used to encapsulate API response data.
 * @author xingfen
 * @date 2025-04-13
 */

namespace App\Response;

class ApiResponse {
    private $code;
    private $message;
    private $data;

    /* construct function */
    public function __construct($code, $message, $data = null) {
        $this->code = $code;
        $this->message = $message;
        $this->data = $data;
    }

    /* success function */
    public static function success($data = null, $message = "request success") {
        return new self(200, $message, $data);
    }

    /* fail function */
    public static function error($code, $message, $data = null) {
        return new self($code, $message, $data);
    }

    /* make a json response */
    public function toJson() {
        echo json_encode([
            'code' => $this->code,
            'message' => $this->message,
            'data' => $this->data
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }
}
