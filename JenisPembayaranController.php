<?php
require_once 'models/JenisPembayaran.php';
require_once 'controllers/NotifikasiController.php';

class JenisPembayaranController {

    // ===============================
    // TAMPILKAN SEMUA DATA
    // ===============================
    public function index() {
        $model = new JenisPembayaran();
        $data = $model->getAll();
        require 'views/admin/JenisPembayaran.php';
    }

    // ===============================
    // TAMPILKAN FORM TAMBAH
    // ===============================
    public function tambah() {
        require 'views/admin/tambah_jenis_pembayaran.php';
    }
    // ===============================
    // PROSES SIMPAN DATA
    // ===============================

public function store()
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        if (
            empty($_POST['nama_pembayaran']) ||
            empty($_POST['nominal']) ||
            empty($_POST['tipe']) ||
            empty($_POST['deadline'])
        ) {
            die("Semua field wajib diisi!");
        }

        // =========================
        // SIMPAN JENIS PEMBAYARAN
        // =========================
        $model = new JenisPembayaran();
        $model->createWithTagihan($_POST);

        // =========================
        // KONEKSI DATABASE
        // =========================
        $config = require 'config/database.php';
        $conn = new mysqli(
            $config['host'],
            $config['username'],
            $config['password'],
            $config['dbname']
            );

        // =========================
        // AMBIL SEMUA SISWA
        // =========================
        $query = $conn->query("
            SELECT nama, no_wa
            FROM siswa
            WHERE no_wa IS NOT NULL
            AND no_wa != ''
        ");

        // =========================
        // DATA TAGIHAN
        // =========================
        $jenis = $_POST['nama_pembayaran'];
        $nominal = number_format($_POST['nominal'], 0, ',', '.');
        $deadline = $_POST['deadline'];

        // =========================
        // LOOP KIRIM WA
        // =========================
        while ($siswa = $query->fetch_assoc()) {

            $nama = $siswa['nama'];
            $no_hp = $siswa['no_wa'];

            $pesan = "📢 TAGIHAN PEMBAYARAN\n\n"
                . "Halo $nama,\n\n"
                . "Tagihan pembayaran *$jenis* sebesar "
                . "Rp $nominal telah diterbitkan.\n\n"
                . "📅 Deadline pembayaran: $deadline\n\n"
                . "Mohon segera melakukan pembayaran.\n\n"
                . "Via tranfer pada rek berikut : 433xxxx"
                . "atau secara langsung menghubungi pihak tata usaha sekolah"
                . "Terima kasih.\n"
                . "— Admin Sekolah";

            // KIRIM WHATSAPP
            NotifikasiController::kirimTagihan($no_hp, $pesan);
        }

        header("Location: index.php?controller=JenisPembayaran&action=index");
        exit;
    }
}

    // ===============================
    // HAPUS DATA
    // ===============================
    public function delete() {
        if (isset($_GET['id'])) {

            $id = $_GET['id'];
            $model = new JenisPembayaran();
            $model->delete($id);

            header("Location: index.php?controller=JenisPembayaran&action=index");
            exit;
        }
    }
    // ===============================
// TAMPILKAN FORM EDIT
// ===============================
public function edit()
{
    if (!isset($_GET['id'])) {
        die("ID tidak ditemukan");
    }

    $id = $_GET['id'];

    $model = new JenisPembayaran();
    $data = $model->getById($id);

    if (!$data) {
        die("Data tidak ditemukan");
    }

    require 'views/admin/edit_jenis_pembayaran.php';
}


// ===============================
// PROSES UPDATE DATA
// ===============================
public function update()
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        if (empty($_POST['id'])) {
            die("ID tidak ditemukan");
        }

        $id = $_POST['id'];

        $model = new JenisPembayaran();
        $success = $model->update($id, $_POST);

        if ($success) {
            header("Location: index.php?controller=JenisPembayaran&action=index");
            exit;
        } else {
            die("Gagal update data!");
        }
    }
}
}