<?php

require_once 'models/User.php';
require_once 'models/Log.php';

class AuthController
{
    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    // ==============================
    // HALAMAN LOGIN
    // ==============================
    public function login()
    {
        // Jika sudah login, redirect sesuai role
        if (isset($_SESSION['user'])) {

            if ($_SESSION['user']['role'] === 'admin') {
                header("Location: index.php?controller=AdminDashboard&action=index");
            } else {
                header("Location: index.php?controller=SiswaDashboard&action=index");
            }
            exit;
        }

        require 'views/auth/login.php';
    }

    // ==============================
    // PROSES LOGIN
    // ==============================
    public function prosesLogin()
    {
        $username = trim($_POST['username'] ?? '');
        $password = trim($_POST['password'] ?? '');

        // Validasi input kosong
        if ($username === '' || $password === '') {
            $_SESSION['error'] = "Username dan password wajib diisi";
            header("Location: index.php?controller=Auth&action=login");
            exit;
        }

        $model = new User();
        $user = $model->cekLogin($username, $password);

        // Jika login gagal
        if (!$user) {
            $_SESSION['error'] = "Username atau password salah";
            header("Location: index.php?controller=Auth&action=login");
            exit;
        }

        // Regenerate session (security)
        session_regenerate_id(true);

        // Simpan data user ke session
        $_SESSION['user'] = [
            'id' => $user['id'],
            'username' => $user['username'],
            'siswa_id' => $user['siswa_id'], // penting untuk dashboard siswa
            'role' => $user['role'],
            'must_change_password' => $user['must_change_password']
        ];

        // ==============================
        // LOG AKTIVITAS LOGIN
        // ==============================
        $log = new Log();
        $log->catat($user['id'], 'Login ke sistem');

        // Redirect sesuai role
        if ($user['role'] === 'admin') {
            header("Location: index.php?controller=AdminDashboard&action=index");
            exit;
        }

        if ($user['role'] === 'siswa') {

            if ($user['must_change_password'] == 1) {
                header("Location: index.php?controller=Auth&action=changePassword");
            } else {
                header("Location: index.php?controller=SiswaDashboard&action=index");
            }
            exit;
        }

        // Jika role tidak dikenali
        $_SESSION['error'] = "Role tidak dikenali";
        header("Location: index.php?controller=Auth&action=login");
        exit;
    }

    // ==============================
    // HALAMAN GANTI PASSWORD
    // ==============================
    public function changePassword()
    {
        if (!isset($_SESSION['user'])) {
            header("Location: index.php?controller=Auth&action=login");
            exit;
        }

        require 'views/auth/change_password.php';
    }

    // ==============================
    // UPDATE PASSWORD
    // ==============================
    public function updatePassword()
    {
        if (!isset($_SESSION['user'])) {
            header("Location: index.php?controller=Auth&action=login");
            exit;
        }

        $password = trim($_POST['password'] ?? '');

        if ($password === '') {
            $_SESSION['error'] = "Password tidak boleh kosong";
            header("Location: index.php?controller=Auth&action=changePassword");
            exit;
        }

        $model = new User();
        $user_id = $_SESSION['user']['id'];

        // Update password (MD5 sesuai sistem kamu sekarang)
        $model->updatePassword($user_id, $password);

        // ==============================
        // LOG AKTIVITAS GANTI PASSWORD
        // ==============================
        $log = new Log();
        $log->catat($user_id, 'Mengganti password akun');

        // Update session
        $_SESSION['user']['must_change_password'] = 0;

        header("Location: index.php?controller=SiswaDashboard&action=index");
        exit;
    }

    // ==============================
    // LOGOUT
    // ==============================
    public function logout()
    {
        // ==============================
        // LOG AKTIVITAS LOGOUT
        // ==============================
        if (isset($_SESSION['user'])) {

            $log = new Log();
            $log->catat($_SESSION['user']['id'], 'Logout dari sistem');
        }

        $_SESSION = [];
        session_destroy();

        header("Location: index.php?controller=Auth&action=login");
        exit;
    }
}