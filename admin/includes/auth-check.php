<?php
if (!function_exists('isAdmin')) {
    function isAdmin() {
        return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
    }
}

if (!isAdmin()) {
    header('Location: /admin/login.php');
    exit;
}
?>
