<?php
require_once 'models/Pembayaran.php';
require_once 'models/Log.php';
require_once __DIR__ . '/../core/Middleware.php';
require_once 'controllers/NotifikasiController.php';

class VerifikasiController
{
    public function index()
    {
        Middleware::onlyAdmin();
        $data = Pembayaran::getPending();
        require_once __DIR__ . '/../views/admin/verifikasi.php';
    }

    public function approve()
    {
        Middleware::onlyAdmin();
        $id = $_GET['id'] ?? 0;

        // ✔️ Verifikasi pembayaran
        Pembayaran::verify($id);
        // ======================================
// LOG AKTIVITAS
// ======================================
$log = new Log();

$log->catat(
    $_SESSION['user']['id'],
    'Menyetujui verifikasi pembayaran'
);

        // 🔔 TAMBAHAN: kirim notifikasi ke orang tua (WA simulasi + email)
        NotifikasiController::pembayaranLunas($id);

        header("Location: index.php?controller=verifikasi&action=index");
        exit;
    }

    public function reject()
    {
        Middleware::onlyAdmin();
        $id = $_GET['id'] ?? 0;

        Pembayaran::reject($id);
        // ======================================
// LOG AKTIVITAS
// ======================================
$log = new Log();

$log->catat(
    $_SESSION['user']['id'],
    'Menolak verifikasi pembayaran'
);

        header("Location: index.php?controller=verifikasi&action=index");
        exit;
    }
}