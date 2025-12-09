<?php
/**
 * File: proses_laporan.php
 * Deskripsi: Backend untuk memproses pengiriman laporan pengaduan
 * Anggota: Nabila Salwa (Anggota 3)
 */

// Memulai session
session_start();

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

// Cek apakah user adalah user biasa (bukan admin)
if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
    header("Location: ../admin/dashboard.php");
    exit();
}

// Cek apakah request method adalah POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: form_laporan.php?status=error&message=" . urlencode("Metode request tidak valid"));
    exit();
}

// Include file koneksi database
require_once('../config/koneksi.php');

// Ambil data dari form
$user_id = $_SESSION['user_id'];
$judul_laporan = trim($_POST['judul_laporan']);
$isi_laporan = trim($_POST['isi_laporan']);

// Array untuk menyimpan error
$errors = [];

// Validasi Judul Laporan
if (empty($judul_laporan)) {
    $errors[] = "Judul laporan tidak boleh kosong";
} elseif (strlen($judul_laporan) < 5) {
    $errors[] = "Judul laporan minimal 5 karakter";
} elseif (strlen($judul_laporan) > 255) {
    $errors[] = "Judul laporan maksimal 255 karakter";
}

// Validasi Isi Laporan
if (empty($isi_laporan)) {
    $errors[] = "Isi laporan tidak boleh kosong";
} elseif (strlen($isi_laporan) < 20) {
    $errors[] = "Isi laporan minimal 20 karakter";
} elseif (strlen($isi_laporan) > 2000) {
    $errors[] = "Isi laporan maksimal 2000 karakter";
}

// Validasi karakter berbahaya (XSS Prevention)
$dangerous_patterns = ['<script', 'javascript:', 'onerror=', 'onclick=', 'onload='];
foreach ($dangerous_patterns as $pattern) {
    if (stripos($judul_laporan, $pattern) !== false || stripos($isi_laporan, $pattern) !== false) {
        $errors[] = "Karakter tidak diperbolehkan terdeteksi dalam laporan";
        break;
    }
}

// Jika ada error, redirect kembali ke form
if (!empty($errors)) {
    $error_message = implode(", ", $errors);
    header("Location: form_laporan.php?status=error&message=" . urlencode($error_message));
    exit();
}

// Sanitasi input untuk keamanan
$judul_laporan = htmlspecialchars($judul_laporan, ENT_QUOTES, 'UTF-8');
$isi_laporan = htmlspecialchars($isi_laporan, ENT_QUOTES, 'UTF-8');

// Prepared statement untuk mencegah SQL Injection
$query = "INSERT INTO laporan (user_id, judul_laporan, isi_laporan, status, tanggal) 
          VALUES (?, ?, ?, 'pending', NOW())";

try {
    // Persiapkan statement
    if ($stmt = mysqli_prepare($conn, $query)) {
        // Bind parameter
        mysqli_stmt_bind_param($stmt, "iss", $user_id, $judul_laporan, $isi_laporan);
        
        // Eksekusi query
        if (mysqli_stmt_execute($stmt)) {
            // Ambil ID laporan yang baru dibuat
            $laporan_id = mysqli_insert_id($conn);
            
            // Tutup statement
            mysqli_stmt_close($stmt);
            
            // Redirect ke halaman list dengan pesan sukses
            header("Location: list_laporan_user.php?status=success&message=" . urlencode("Laporan berhasil dikirim! ID Laporan: #" . $laporan_id));
            exit();
        } else {
            // Error saat eksekusi
            mysqli_stmt_close($stmt);
            throw new Exception("Gagal menyimpan laporan ke database");
        }
    } else {
        throw new Exception("Gagal mempersiapkan query");
    }
} catch (Exception $e) {
    // Log error (dalam production, gunakan error_log)
    error_log("Error proses_laporan.php: " . $e->getMessage());
    
    // Redirect dengan pesan error
    header("Location: form_laporan.php?status=error&message=" . urlencode("Terjadi kesalahan sistem. Silakan coba lagi."));
    exit();
} finally {
    // Tutup koneksi database
    if (isset($conn)) {
        mysqli_close($conn);
    }
}
?>
