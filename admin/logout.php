<?php
/**
 * IF Barber — Admin Logout
 * Mengakhiri sesi administrator dan mengarahkan ke halaman login
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

logout();

// Set flash message
session_start(); // Start new session to store flash message
setFlash('success', 'Anda telah berhasil logout.');

redirect(BASE_URL . '/admin/login.php');
?>
