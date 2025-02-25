<?php
$plainTextPassword = "Welkom#01!"; 
$hashedPassword = password_hash($plainTextPassword, PASSWORD_DEFAULT);
echo "Hashed Password: " . $hashedPassword;
?>