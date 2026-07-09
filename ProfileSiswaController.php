<?php

require_once 'models/Siswa.php';
require_once 'models/User.php';

class ProfileSiswaController
{
    public function index()
    {
        $id_siswa = $_SESSION['user']['siswa_id'];

        $siswa = Siswa::getById($id_siswa);

        require 'views/siswa/profile.php';
    }

    public function ubahPassword()
    {
        require 'views/siswa/ubah_password.php';
    }

    public function simpanPassword()
    {
        $id_user = $_SESSION['user']['id'];

        $password_lama = $_POST['password_lama'];
        $password_baru = $_POST['password_baru'];
        $konfirmasi = $_POST['konfirmasi_password'];

        $userModel = new User();

        $user = $userModel->getById($id_user);

        if ($user['password'] != md5($password_lama)) {

            $_SESSION['error'] = 'Password lama salah';

            header("Location:index.php?controller=ProfileSiswa&action=ubahPassword");
            exit;
        }

        if ($password_baru != $konfirmasi) {

            $_SESSION['error'] = 'Konfirmasi password tidak cocok';

            header("Location:index.php?controller=ProfileSiswa&action=ubahPassword");
            exit;
        }

        $userModel->updatePassword(
            $id_user,
            $password_baru
        );

        $_SESSION['success'] = 'Password berhasil diubah';

        header("Location:index.php?controller=ProfileSiswa&action=index");
        exit;
    }
    public function uploadFoto()
{
    $id_siswa = $_SESSION['user']['siswa_id'];

    if(isset($_FILES['foto']))
    {
        $file = $_FILES['foto'];

        $namaFile =
            time() . "_" .
            $file['name'];

        move_uploaded_file(
            $file['tmp_name'],
            'uploads/profil/' . $namaFile
        );

        Siswa::updateFoto(
            $id_siswa,
            $namaFile
        );
    }

    header(
        "Location:index.php?controller=ProfileSiswa&action=index"
    );
}
}