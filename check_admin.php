<?php
function checkAdmin() {
    // Pastikan session sudah dimulai
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Cek apakah user sudah login dan role-nya admin
    if (!isset($_SESSION['login']) || 
        $_SESSION['login'] !== true || 
        !isset($_SESSION['role']) || 
        $_SESSION['role'] !== 'admin') {
        
        header("Location: home.php");
        exit();
    }
}
?>