<?php

namespace App\Utils;

class Validator {
    
    public static function validateUsername($username) {
        if (empty($username)) {
            return "Username is required.";
        }

        if (strlen($username) < 3 || strlen($username) > 20) {
            return "Username must be between 3 and 20 characters.";
        }

        if (!preg_match("/^[a-zA-Z0-9_-]+$/", $username)) {
            return "Username can only contain letters, numbers, dashes, and underscores.";
        }

        return true;
    }

    public static function validateEmail($email) {
        if (empty($email)) {
            return "Email is required.";
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return "Invalid email format.";
        }

        return true;
    }

    public static function validatePassword($password) {
        if (empty($password)) {
            return "Password is required.";
        }

        if (!preg_match("/^(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/", $password)) {
            return "Password must be at least 8 characters long, with at least 1 uppercase letter, 1 number, and 1 special character.";
        }

        return true;
    }
}