<?php
/**
 * IF Barber — Authentication Helpers
 * Fungsi-fungsi untuk autentikasi admin dan member
 */

/**
 * Cek apakah admin sudah login
 */
function isAdminLoggedIn() {
    return isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']);
}

/**
 * Cek apakah member sudah login
 */
function isMemberLoggedIn() {
    return isset($_SESSION['member_id']) && !empty($_SESSION['member_id']);
}

/**
 * Paksa halaman hanya bisa diakses admin
 * Redirect ke login jika belum login
 */
function requireAdmin() {
    if (!isAdminLoggedIn()) {
        setFlash('error', 'Silakan login terlebih dahulu.');
        redirect(BASE_URL . '/admin/login.php');
    }
}

/**
 * Paksa halaman hanya bisa diakses member
 */
function requireMember() {
    if (!isMemberLoggedIn()) {
        setFlash('error', 'Silakan login sebagai member terlebih dahulu.');
        redirect(BASE_URL . '/member/login.php');
    }
}

/**
 * Login admin
 * @return bool
 */
function loginAdmin($conn, $username, $password) {
    $stmt = $conn->prepare("SELECT id, username, password, nama FROM admin WHERE username = ?");
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $admin = $result->fetch_assoc();
        if (password_verify($password, $admin['password'])) {
            session_regenerate_id(true); // Cegah session fixation
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];
            $_SESSION['admin_nama'] = $admin['nama'];
            $stmt->close();
            return true;
        }
    }
    $stmt->close();
    return false;
}

/**
 * Login member
 * @return bool
 */
function loginMember($conn, $email, $password) {
    $stmt = $conn->prepare("SELECT id, nama, email, no_hp, password FROM member WHERE email = ?");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $member = $result->fetch_assoc();
        if (password_verify($password, $member['password'])) {
            session_regenerate_id(true); // Cegah session fixation
            $_SESSION['member_id'] = $member['id'];
            $_SESSION['member_nama'] = $member['nama'];
            $_SESSION['member_email'] = $member['email'];
            $_SESSION['member_hp'] = $member['no_hp'];
            return true;
        }
    }
    $stmt->close();
    return false;
}

/**
 * Logout (admin/member)
 */
function logout() {
    session_unset();
    session_destroy();
}

/**
 * Register member baru
 * @return array ['success' => bool, 'message' => string]
 */
function registerMember($conn, $nama, $email, $no_hp, $password) {
    // Cek email sudah terdaftar
    $stmt = $conn->prepare("SELECT id FROM member WHERE email = ?");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        $stmt->close();
        return ['success' => false, 'message' => 'Email sudah terdaftar.'];
    }
    $stmt->close();

    // Hash password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Insert member
    $stmt = $conn->prepare("INSERT INTO member (nama, email, no_hp, password) VALUES (?, ?, ?, ?)");
    $stmt->bind_param('ssss', $nama, $email, $no_hp, $hashedPassword);

    if ($stmt->execute()) {
        $stmt->close();
        return ['success' => true, 'message' => 'Registrasi berhasil! Silakan login.'];
    }

    $stmt->close();
    return ['success' => false, 'message' => 'Terjadi kesalahan. Silakan coba lagi.'];
}
