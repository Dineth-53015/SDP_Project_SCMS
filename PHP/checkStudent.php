<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) || !isset($_SESSION['name']) || !isset($_SESSION['role'])) {
    header('Location: index.php');
    exit;
}

if ($_SESSION['role'] !== 'Student') {
    header('Location: index.php');
    exit;
}
?>