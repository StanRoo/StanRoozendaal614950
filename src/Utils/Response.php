<?php

namespace App\Utils;

class Response {
    public static function json($status, $message, $data = null) {
        http_response_code($status);
        echo json_encode(["message" => $message, "data" => $data]);
        exit();
    }
}