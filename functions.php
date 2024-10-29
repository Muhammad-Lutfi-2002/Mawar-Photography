<?php
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function validate_username($username) {
    return preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username);
}

function validate_password($password) {
    return strlen($password) >= 6;
}

function format_currency($amount) {
    return 'Rp ' . number_format($amount, 0, ',', '.');
}
?>