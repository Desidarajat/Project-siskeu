<?php
require_once 'core/Middleware.php';
require_once 'models/Pembayaran.php';
require_once 'models/TransaksiPembayaran.php';

class InputPembayaranController
{
    public function index()
    {
        Middleware::onlyAdmin();

        $data = Pembayaran::getAll();

        require 'views/admin/input_pembayaran.php';
    }

    public function form()
    {
        Middleware::onlyAdmin();

        $id = $_GET['id'];

        $data = Pembayaran::getById($id);

        require 'views/admin/form_input_pembayaran.php';
    }

    public function simpan()
    {
        Middleware::onlyAdmin();

        $pembayaran_id = $_POST['pembayaran_id'];
        $jumlah_bayar  = $_POST['jumlah_bayar'];

        TransaksiPembayaran::tambah(
            $pembayaran_id,
            $jumlah_bayar,
            $_SESSION['user']['id']
        );
        $totalBayar = TransaksiPembayaran::getTotalBayar($pembayaran_id);

$tagihan = Pembayaran::getById($pembayaran_id);

if ($totalBayar['total'] >= $tagihan['jumlah_bayar']) {

    Pembayaran::updateStatusLunas($pembayaran_id);

}

        header("Location:index.php?controller=InputPembayaran&action=index");
        exit;
    }
    public function riwayat()
{
    if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
        header("Location:index.php");
        exit;
    }

    $id = $_GET['id'];

    $riwayat = TransaksiPembayaran::getRiwayat($id);

    $data = Pembayaran::getById($id);

    require 'views/admin/riwayat_cicilan.php';
}
}