<?php

class KelasController
{
    public function index()
    {
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
            header("Location: index.php");
            exit;
        }

        require_once __DIR__ . '/../models/Kelas.php';
        $kelas = Kelas::getAll();

        include __DIR__ . '/../views/admin/kelas.php';
    }

    public function create()
    {
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
            header("Location: index.php");
            exit;
        }

        include __DIR__ . '/../views/admin/form_kelas.php';
    }

    public function store()
    {
        require_once __DIR__ . '/../models/Kelas.php';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nama_kelas = $_POST['nama_kelas'] ?? '';
            $wali_kelas = $_POST['wali_kelas'] ?? null;

            Kelas::create($nama_kelas, $wali_kelas);
        }

        header("Location: index.php?controller=kelas&action=index");
        exit;
    }

    public function edit()
    {
        require_once __DIR__ . '/../models/Kelas.php';

        $id = $_GET['id'] ?? 0;
        $kelas = Kelas::find($id);

        include __DIR__ . '/../views/admin/form_kelas.php';
    }

    public function update()
    {
        require_once __DIR__ . '/../models/Kelas.php';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'];
            $nama_kelas = $_POST['nama_kelas'];
            $wali_kelas = $_POST['wali_kelas'];

            Kelas::update($id, $nama_kelas, $wali_kelas);
        }

        header("Location: index.php?controller=kelas&action=index");
        exit;
    }

    public function delete()
    {
        require_once __DIR__ . '/../models/Kelas.php';

        $id = $_GET['id'] ?? 0;
        Kelas::delete($id);

        header("Location: index.php?controller=kelas&action=index");
        exit;
    }
}