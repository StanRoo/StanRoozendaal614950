<?php
$plainTextPassword = "Welkom#01!"; // Replace with actual password
$hashedPassword = password_hash($plainTextPassword, PASSWORD_DEFAULT);
echo "Hashed Password: " . $hashedPassword;
?>