<?php
require_once 'models/Pembayaran.php';
require_once __DIR__ . '/../vendor/autoload.php';
require_once 'controllers/NotifikasiController.php';
require_once 'models/Log.php';

use Dompdf\Dompdf;
use Dompdf\Options;

class PembayaranController {

    // ==========================================
    // HALAMAN DATA PEMBAYARAN
    // ==========================================
    public function index() {

        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
            header("Location: index.php");
            exit;
        }

        $data = Pembayaran::getAll();

        require_once __DIR__ . '/../views/admin/pembayaran.php';
    }

    // ==========================================
    // HALAMAN TAMBAH PEMBAYARAN
    // ==========================================
    public function tambah() {

        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
            header("Location: index.php");
            exit;
        }

        require_once __DIR__ . '/../views/admin/tambah_pembayaran.php';
    }

    // ==========================================
    // EXPORT PDF
    // ==========================================
    // ==========================================
// EXPORT PDF
// ==========================================
public function exportPDF()
{
    $bulan = $_GET['bulan'] ?? '';
    $tahun = $_GET['tahun'] ?? '';

    $data = Pembayaran::filterByTanggal($bulan, $tahun);

    $total = 0;

    $html = '
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
        }

        .kop {
            text-align: center;
            border-bottom: 3px solid black;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        .kop h2 {
            margin: 0;
        }

        .kop p {
            margin: 2px 0;
            font-size: 12px;
        }

        .periode {
            margin-bottom: 15px;
            font-weight: bold;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            background-color: #34495e;
            color: white;
            padding: 8px;
            border: 1px solid #000;
        }

        td {
            padding: 6px;
            border: 1px solid #000;
            text-align: center;
        }

        .total-row {
            font-weight: bold;
            background-color: #ecf0f1;
        }

        .ttd {
            margin-top: 60px;
            width: 100%;
            text-align: right;
        }

        .ttd p {
            margin: 5px 0;
        }
    </style>

    <div class="kop">
        <h2>SMK PERMATA NEGERI</h2>
        <p>Jl. Darajat kp. Kebon Kolot Ds. Padaawas</p>
        <p>Email: smkcontoh@email.com | Telp: 08123456789</p>
    </div>

    <div class="periode">
        Periode Laporan: Bulan '.$bulan.' Tahun '.$tahun.'
    </div>

    <table>
        <tr>
            <th>No</th>
            <th>Nama Siswa</th>
            <th>Jenis Pembayaran</th>
            <th>Tanggal</th>
            <th>Status</th>
            <th>Jumlah (Rp)</th>
        </tr>
    ';

    $no = 1;

    foreach ($data as $row) {

        $total += $row['jumlah_bayar'];

        $html .= '
        <tr>
            <td>'.$no++.'</td>
            <td>'.$row['nama_siswa'].'</td>
            <td>'.$row['nama_pembayaran'].'</td>
            <td>'.$row['tanggal_bayar'].'</td>
            <td>'.$row['status'].'</td>
            <td>'.number_format($row['jumlah_bayar'],0,',','.').'</td>
        </tr>';
    }

    $html .= '
        <tr class="total-row">
            <td colspan="6">TOTAL PEMASUKAN</td>
            <td>'.number_format($total,0,',','.').'</td>
        </tr>
    </table>

    <div class="ttd">
        <p>Kota Contoh, '.date('d F Y').'</p>
        <p>Kepala Sekolah</p>
        <br><br><br>
        <p><b>(_____________________)</b></p>
    </div>
    ';

    $options = new Options();
    $options->set('isRemoteEnabled', true);

    $dompdf = new Dompdf($options);

    $dompdf->loadHtml($html);

    $dompdf->setPaper('A4', 'landscape');

    $dompdf->render();

    $dompdf->stream(
        "Laporan_Pembayaran.pdf",
        ["Attachment" => true]
    );
}
    // ==========================================
    // VERIFIKASI PEMBAYARAN
    // ==========================================
    public function verify()
{
    Middleware::onlyAdmin();

    $id = $_GET['id'] ?? 0;

    $model = new Pembayaran();

    // ubah status jadi lunas
    $model->verify($id);

    // ======================================
    // LOG AKTIVITAS
    // ======================================
    $log = new Log();

    $log->catat(
        $_SESSION['user']['id'],
        'Memverifikasi pembayaran'
    );

    // kirim notifikasi WA
    NotifikasiController::pembayaranLunas($id);

    header("Location: index.php?controller=Pembayaran&action=index");
    exit;
}

    // ==========================================
    // TOLAK PEMBAYARAN
    // ==========================================
    public function reject()
    {
        Middleware::onlyAdmin();

        $id = $_GET['id'] ?? 0;

        $model = new Pembayaran();

        $model->reject($id);

        header("Location: index.php?controller=Pembayaran&action=index");
        exit;
    }

    // ==========================================
    // SIMPAN TAGIHAN / PEMBAYARAN
    // ==========================================
    public function store()
    {
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
            header("Location: index.php");
            exit;
        }

        $model = new Pembayaran();

        // ======================================
        // SIMPAN DATA PEMBAYARAN
        // ======================================
        $model->insert($_POST);
// ======================================
// LOG AKTIVITAS
// ======================================
$log = new Log();

$log->catat(
    $_SESSION['user']['id'],
    'Menambahkan tagihan pembayaran'
);
        // ======================================
        // KONEKSI DATABASE
        // ======================================
        $config = require 'config/database.php';

        $conn = new mysqli(
            $config['host'],
            $config['username'],
            $config['password'],
            $config['dbname']
        );

        // ======================================
        // AMBIL DATA SISWA
        // ======================================
        $id_siswa = $_POST['id_siswa'];

        $query = $conn->query("
            SELECT nama, no_wa
            FROM siswa
            WHERE id = '$id_siswa'
        ");

        $siswa = $query->fetch_assoc();

        // ======================================
        // DATA SISWA
        // ======================================
        $nama  = $siswa['nama'];
        $no_hp = $siswa['no_wa'];

        // ======================================
        // DATA TAGIHAN
        // ======================================
        $jenis         = $_POST['nama_pembayaran'];
        $nominal       = number_format($_POST['jumlah_bayar'], 0, ',', '.');
        $jatuh_tempo   = $_POST['jatuh_tempo'];

        // ======================================
        // PESAN WHATSAPP
        // ======================================
        $pesan = "📢 TAGIHAN PEMBAYARAN\n\n"
            . "Halo $nama,\n\n"
            . "Tagihan pembayaran *$jenis* sebesar "
            . "Rp $nominal telah diterbitkan.\n\n"
            . "📅 Jatuh tempo: $jatuh_tempo\n\n"
            . "Mohon segera melakukan pembayaran.\n\n"
            . "Terima kasih.\n"
            . "— Admin Sekolah";

        // ======================================
        // KIRIM WHATSAPP
        // ======================================
        NotifikasiController::kirimTagihan($no_hp, $pesan);

        header("Location: index.php?controller=Pembayaran&action=index");

        exit;
    }
}