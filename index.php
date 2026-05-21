<?php
require_once 'config.php';

if (isLoggedIn()) {
    if ($_SESSION['role'] === 'teacher') {
        header('Location: dashboard.php');
    } else {
        header('Location: student_view.php');
    }
} else {
    header('Location: login.php');
}
exit;
?>
