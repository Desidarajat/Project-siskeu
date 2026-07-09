<?php

class AdminDashboardController {

    public function index() {

        // 1. Cek login admin
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
            header("Location: index.php");
            exit;
        }

        // 2. Ambil data sederhana dari model
        require_once 'models/Pembayaran.php';

        $totalPemasukan = Pembayaran::totalPemasukanSederhana();
        $totalSiswa     = Pembayaran::totalSiswa();
        $totalLunas     = Pembayaran::totalSiswaLunas();
        $totalBelum     = Pembayaran::totalSiswaBelum();

        $grafikBulanan     = Pembayaran::grafikBulanan();
        $grafikPerKelas    = Pembayaran::grafikPerKelas();
        $statusPembayaran  = Pembayaran::statusPembayaran();

        // 3. Load layout lengkap
        require_once 'views/layouts/header.php';
        require_once 'views/layouts/sidebar.php';
        require_once 'views/admin/dashboard.php';
        require_once 'views/layouts/footer.php';
    }
    public function __construct()
{

    if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
        header("Location: index.php?controller=Auth&action=login");
        exit;
    }
}
public function simpanJenis()
{
    require_once 'models/JenisPembayaran.php';
    require_once 'models/Pembayaran.php';
    require_once 'models/Siswa.php';

    $nama     = $_POST['nama_pembayaran'];
    $nominal  = $_POST['nominal'];
    $tipe     = $_POST['tipe'];
    $deadline = $_POST['deadline'];

    $jenisModel = new JenisPembayaran();

    // 1️⃣ Simpan jenis pembayaran
    $jenisModel->create([
        'nama_pembayaran' => $nama,
        'nominal' => $nominal,
        'tipe' => $tipe,
        'deadline' => $deadline
    ]);

    // 2️⃣ Ambil ID terakhir
    $db = Database::connect();
    $id_jenis = mysqli_insert_id($db);

    // 3️⃣ Ambil semua siswa aktif
    $result = mysqli_query($db, "SELECT id FROM siswa WHERE status='aktif'");
    $semuaSiswa = mysqli_fetch_all($result, MYSQLI_ASSOC);

    // 4️⃣ Insert tagihan
    foreach ($semuaSiswa as $siswa) {
        Pembayaran::insertTagihan($siswa['id'], $id_jenis, $nominal);
    }

    header("Location: index.php?controller=AdminDashboard&action=simpanJenis");
}
}
