<?php

namespace App\Utils;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use App\Config;
use App\Utils\Response;

class TokenHelper {
    public static function decodeToken($token) {
        try {
            return JWT::decode($token, new Key(Config::JWT_SECRET, 'HS256'));
        } catch (\Exception $e) {
            return null;
        }
    }
}