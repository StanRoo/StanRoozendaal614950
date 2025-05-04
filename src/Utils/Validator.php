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
            return "Username can only contain letters, numbers, dashes (-), and underscores (_).";
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
        } else {
            if (strlen($password) < 8) {
                return "Password must be at least 8 characters long.";
            }
            if (!preg_match("/[A-Z]/", $password)) {
                return "Password must contain at least one uppercase letter.";
            }
            if (!preg_match("/\d/", $password)) {
                return "Password must contain at least one number.";
            }
            if (!preg_match("/[\W_]/", $password)) {
                return "Password must contain at least one special character.";
            }
        }
    
        return true;
    }

    public static function validateCardName($name) {
        if (empty($name)) {
            return "Card name is required.";
        }

        if (strlen($name) < 3 || strlen($name) > 50) {
            return "Card name must be between 3 and 50 characters.";
        }

        if (!preg_match("/^[a-zA-Z0-9\s-]+$/", $name)) {
            return "Card name can only contain letters, numbers, spaces, and dashes (-).";
        }

        return true;
    }

    public static function validatePrice($price) {
        if (!is_numeric($price) || $price <= 0) {
            return "Price must be a positive number.";
        }

        return true;
    }
}