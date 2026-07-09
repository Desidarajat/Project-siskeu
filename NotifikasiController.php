<?php
class NotifikasiController {

    // 🔹 KONEKSI DATABASE
    private static function getConn() {
        $config = require 'config/database.php';

        $conn = mysqli_connect(
            $config['host'],
            $config['username'],
            $config['password'],
            $config['dbname']
        );

        if (!$conn) {
            die("Koneksi gagal: " . mysqli_connect_error());
        }

        return $conn;
    }

    // 🔹 FORMAT NOMOR WA (PENTING)
    private static function formatNomor($no_hp) {
        // hapus spasi
        $no_hp = str_replace(' ', '', $no_hp);

        // jika diawali 0 → ubah ke 62
        if (substr($no_hp, 0, 1) == "0") {
            $no_hp = "62" . substr($no_hp, 1);
        }

        return $no_hp;
    }

    // 🔹 NOTIFIKASI PEMBAYARAN LUNAS
    public static function pembayaranLunas($pembayaran_id) {
        $data = Pembayaran::detail($pembayaran_id);

        if (!$data) return;

        $nama     = $data['nama_siswa'];
        $no_hp    = self::formatNomor($data['no_hp']);
        $siswa_id = $data['siswa_id'];
        $jenis    = $data['jenis_pembayaran'] ?? 'Pembayaran';

        $pesan = "Yth. $nama,\n\nDengan hormat, kami informasikan bahwa pembayaran $jenis telah diterima.\nStatus: LUNAS.\n\nTerima kasih atas perhatian dan kerja samanya.\n\n— Admin Sekolah";

        // 🔹 Simpan ke database (WAJIB)
        self::kirimWA_simulasi($siswa_id, $no_hp, $pesan);

        // 🔹 Kirim ke WhatsApp REAL
        self::kirimWA_real($no_hp, $pesan);
    }
    
    // 🔹 NOTIFIKASI TAGIHAN BARU
    public static function kirimTagihan($no_hp, $pesan) {

        // format nomor
        $no_hp = self::formatNomor($no_hp);

        // kirim WA real
        self::kirimWA_real($no_hp, $pesan);
    }
    // 🔹 WHATSAPP REAL (FONNTE)
private static function kirimWA_real($no_hp, $pesan)
{
    $token = "8tC2FEzLRgu1hNU8bFtS";

    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => "https://api.fonnte.com/send",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => array(
            'target' => $no_hp,
            'message' => $pesan,
        ),
        CURLOPT_HTTPHEADER => array(
            "Authorization: $token"
        ),
    ));

    $response = curl_exec($curl);

    if (curl_errno($curl)) {
        error_log('Curl error: ' . curl_error($curl));
    }

    curl_close($curl);

    return $response;
}
    // 🔹 SIMPAN KE DATABASE
    private static function kirimWA_simulasi($siswa_id, $no_hp, $pesan) {
        $conn = self::getConn();

        if (empty($no_hp)) return;

        $query = "INSERT INTO notifikasi (siswa_id, no_hp, pesan, status, created_at)
                  VALUES ('$siswa_id', '$no_hp', '$pesan', 'terkirim', NOW())";

        mysqli_query($conn, $query);
    }
    // 🔹 AMBIL SEMUA NOTIFIKASI (UNTUK ADMIN)
public static function getAllNotifikasi() {
    $conn = self::getConn();

    $query = "SELECT * FROM notifikasi ORDER BY created_at DESC";
    $result = mysqli_query($conn, $query);

    $data = [];
    while($row = mysqli_fetch_assoc($result)) {
        $data[] = $row;
    }

    return $data;
}
}