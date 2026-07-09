<?php

require_once 'models/Pembayaran.php';

class SiswaDashboardController
{
    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Cek login & role siswa
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'siswa') {
            header("Location: index.php?controller=Auth&action=login");
            exit;
        }
    }

    public function index()
{
    $siswa_id = $_SESSION['user']['siswa_id'];

    $tagihan = Pembayaran::tagihanSiswa($siswa_id);

    require 'views/siswa/dashboard_siswa.php';
}
public function uploadBukti()
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        $pembayaran_id = $_POST['pembayaran_id'];
        $tanggal_bayar = date('Y-m-d H:i:s'); // REAL TIME SERVER

        $file = $_FILES['bukti']['name'];
        $tmp  = $_FILES['bukti']['tmp_name'];

        $folder = "uploads/";
        $nama_file = time() . "_" . $file;

        move_uploaded_file($tmp, $folder . $nama_file);

        // Update database
        Pembayaran::updateBukti(
            $pembayaran_id,
            $nama_file,
            $tanggal_bayar
        );

        header("Location: index.php?controller=SiswaDashboard&action=index");
        exit;
    }
}
public function riwayat()
{
    $siswa_id = $_SESSION['user']['siswa_id'];

    $bulan = $_GET['bulan'] ?? null;
    $tahun = $_GET['tahun'] ?? null;

    $riwayat = Pembayaran::riwayatSiswa($siswa_id, $bulan, $tahun);

    require 'views/siswa/riwayat_pembayaran.php';
}
public function cetakStruk()
{
    $id = $_GET['id'];

    $data = Pembayaran::getById($id);

    require_once 'views/siswa/cetak_struk.php';
}
}