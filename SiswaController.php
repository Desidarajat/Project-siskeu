<?php
require_once 'models/Siswa.php';
require_once 'models/Kelas.php';
require_once 'controllers/NotifikasiController.php';

class SiswaController {

    // ===============================
    // TAMPILKAN DATA
    // ===============================
    public function index() {

        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
            header("Location: index.php");
            exit;
        }

        $data = Siswa::getAll();
        require_once 'views/admin/siswa.php';
    }

    // ===============================
    // FORM TAMBAH
    // ===============================
    public function tambah() {

        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
            header("Location: index.php");
            exit;
        }

        // Ambil data kelas untuk dropdown
        $kelas = Kelas::getAll();

        require_once 'views/admin/tambah_siswa.php';
    }

    // ===============================
    // SIMPAN DATA
    // ===============================
    public function simpan() {

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $data = [
                'nis' => $_POST['nis'],
                'nama' => $_POST['nama'],
                'no_wa' => $_POST['no_wa'],
                'kelas_id' => $_POST['kelas_id'],
                'tahun_ajaran' => $_POST['tahun_ajaran'],
                'status' => $_POST['status']
            ];

            Siswa::insert($data);

            header("Location: index.php?controller=siswa&action=index");
            exit;
        }
    }

    // ===============================
    // FORM EDIT
    // ===============================
    public function edit() {

        $id = $_GET['id'];
        $siswa = Siswa::getById($id);
        $kelas = Kelas::getAll();

        require_once 'views/admin/edit_siswa.php';
    }

    // ===============================
    // UPDATE DATA
    // ===============================
    public function update() {

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $id = $_POST['id'];

            $data = [
                'nis' => $_POST['nis'],
                'nama' => $_POST['nama'],
                'no_wa' => $_POST['no_wa'],
                'kelas_id' => $_POST['kelas_id'],
                'tahun_ajaran' => $_POST['tahun_ajaran'],
                'status' => $_POST['status']
            ];

            Siswa::update($id, $data);

            header("Location: index.php?controller=siswa&action=index");
            exit;
        }
    }

    // ===============================
    // HAPUS DATA
    // ===============================
    public function delete() {

        $id = $_GET['id'];
        Siswa::delete($id);

        header("Location: index.php?controller=siswa&action=index");
        exit;
    }
}