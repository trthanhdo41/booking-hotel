<?php
session_start();

// Xóa tất cả session admin
unset($_SESSION['admin_logged_in']);
unset($_SESSION['admin_id']);
unset($_SESSION['admin_username']);
unset($_SESSION['admin_role']);

// Chuyển hướng về trang đăng nhập
header('Location: login.php');
exit;
?>
