<?php
/**
 * File: admin_list.php
 * Deskripsi: Halaman untuk admin melihat semua laporan pengaduan
 * Anggota: Nabila Salwa (Anggota 3)
 */

// Memulai session
session_start();

// TEMPORARY: Disable login check for testing
// Set dummy admin session
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 1;
    $_SESSION['username'] = 'admin';
    $_SESSION['nama'] = 'Admin';
    $_SESSION['email'] = 'admin@silatium.com';
    $_SESSION['role'] = 'admin';
}

// Include file koneksi database
require_once('../config/koneksi.php');

// Ambil parameter filter status (opsional)
$filter_status = isset($_GET['status']) ? $_GET['status'] : 'all';

// Query untuk statistik
$stats_query = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
    SUM(CASE WHEN status = 'diproses' THEN 1 ELSE 0 END) as diproses,
    SUM(CASE WHEN status = 'selesai' THEN 1 ELSE 0 END) as selesai,
    SUM(CASE WHEN status = 'ditolak' THEN 1 ELSE 0 END) as ditolak
    FROM laporan";
$stats_result = mysqli_query($conn, $stats_query);
$stats = mysqli_fetch_assoc($stats_result);

// Query untuk mengambil semua laporan dengan join ke tabel users
$query = "SELECT l.*, u.nama as nama_user, u.email as email_user 
          FROM laporan l 
          LEFT JOIN users u ON l.user_id = u.id";

// Tambahkan filter status jika dipilih
if ($filter_status !== 'all') {
    $query .= " WHERE l.status = ?";
}

$query .= " ORDER BY l.tanggal DESC";

// Persiapkan dan eksekusi query
$laporan_list = [];
try {
    if ($filter_status !== 'all') {
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "s", $filter_status);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
    } else {
        $result = mysqli_query($conn, $query);
    }
    
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $laporan_list[] = $row;
        }
    }
} catch (Exception $e) {
    error_log("Error admin_list.php: " . $e->getMessage());
    $error_message = "Terjadi kesalahan saat mengambil data laporan.";
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Laporan - Admin SILATIUM</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: #f8fafc;
            min-height: 100vh;
            color: #1e293b;
            display: flex;
        }

        /* Sidebar Styling */
        .sidebar {
            width: 260px;
            background: #1e293b;
            min-height: 100vh;
            padding: 24px 0;
            position: fixed;
            left: 0;
            top: 0;
        }

        .sidebar-header {
            padding: 0 24px 24px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .logo {
            font-size: 22px;
            font-weight: 700;
            background: linear-gradient(135deg, #60a5fa 0%, #a78bfa 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            letter-spacing: -0.5px;
        }

        .sidebar-menu {
            margin-top: 32px;
            padding: 0 12px;
        }

        .menu-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            color: #94a3b8;
            text-decoration: none;
            border-radius: 8px;
            margin-bottom: 4px;
            transition: all 0.2s ease;
            font-size: 14px;
            font-weight: 500;
        }

        .menu-item:hover {
            background: rgba(255, 255, 255, 0.05);
            color: #f1f5f9;
        }

        .menu-item.active {
            background: rgba(96, 165, 250, 0.1);
            color: #60a5fa;
        }

        .menu-icon {
            width: 20px;
            height: 20px;
        }

        /* Main Content */
        .main-content {
            margin-left: 260px;
            flex: 1;
            padding: 24px 40px;
        }

        .page-header {
            margin-bottom: 28px;
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
        }

        .page-title {
            font-size: 28px;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 2px;
            line-height: 1;
        }

        .page-subtitle {
            color: #64748b;
            font-size: 14px;
            line-height: 1.5;
        }

        /* Statistics Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 32px;
        }

        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 24px;
            border: 1px solid #e2e8f0;
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            transform: translateY(-2px);
        }

        .stat-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 12px;
        }

        .stat-label {
            font-size: 13px;
            color: #64748b;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .stat-icon {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .stat-icon.total {
            background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
        }

        .stat-icon.pending {
            background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);
        }

        .stat-icon.diproses {
            background: linear-gradient(135deg, #60a5fa 0%, #3b82f6 100%);
        }

        .stat-icon.selesai {
            background: linear-gradient(135deg, #34d399 0%, #10b981 100%);
        }

        .stat-icon.ditolak {
            background: linear-gradient(135deg, #f87171 0%, #ef4444 100%);
        }

        .stat-number {
            font-size: 32px;
            font-weight: 700;
            color: #0f172a;
        }

        /* Table Container */
        .table-wrapper {
            background: white;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            overflow: hidden;
        }

        .table-header {
            padding: 20px 24px;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .table-title {
            font-size: 18px;
            font-weight: 600;
            color: #0f172a;
        }

        .filter-select {
            padding: 8px 16px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            background: white;
            color: #475569;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .filter-select:hover {
            border-color: #60a5fa;
        }

        .filter-select:focus {
            outline: none;
            border-color: #60a5fa;
            box-shadow: 0 0 0 3px rgba(96, 165, 250, 0.1);
        }

        /* Table */
        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead {
            background: #f8fafc;
        }

        th {
            padding: 14px 24px;
            text-align: left;
            font-size: 12px;
            font-weight: 600;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        td {
            padding: 16px 24px;
            border-bottom: 1px solid #f1f5f9;
            font-size: 14px;
        }

        tbody tr {
            transition: background-color 0.2s ease;
        }

        tbody tr:hover {
            background-color: #f8fafc;
        }

        tbody tr:last-child td {
            border-bottom: none;
        }

        .user-email {
            color: #475569;
            font-weight: 500;
        }

        .laporan-title {
            color: #0f172a;
            font-weight: 600;
            max-width: 300px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        /* Status Badges */
        .status-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            letter-spacing: 0.3px;
        }

        .status-pending {
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
            color: #92400e;
        }

        .status-diproses {
            background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
            color: #1e40af;
        }

        .status-selesai {
            background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
            color: #065f46;
        }

        .status-ditolak {
            background: linear-gradient(135deg, #fecaca 0%, #fca5a5 100%);
            color: #991b1b;
        }

        .tanggal {
            color: #64748b;
            font-size: 13px;
        }

        /* Button */
        .btn-detail {
            padding: 8px 20px;
            background: linear-gradient(135deg, #60a5fa 0%, #3b82f6 100%);
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-block;
        }

        .btn-detail:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 60px 24px;
        }

        .empty-icon {
            width: 64px;
            height: 64px;
            margin: 0 auto 16px;
            opacity: 0.3;
        }

        .empty-text {
            color: #94a3b8;
            font-size: 15px;
        }

        @media (max-width: 1024px) {
            .sidebar {
                width: 220px;
            }

            .main-content {
                margin-left: 220px;
            }
        }

        @media (max-width: 768px) {
            .sidebar {
                display: none;
            }

            .main-content {
                margin-left: 0;
                padding: 20px;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            table {
                font-size: 13px;
            }

            th, td {
                padding: 12px 16px;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <div class="logo">SILATIUM</div>
        </div>
        <nav class="sidebar-menu">
            <a href="../admin/dashboard.php" class="menu-item">
                <svg class="menu-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                </svg>
                Dashboard
            </a>
            <a href="../transportasi/admin_transportasi.php" class="menu-item">
                <svg class="menu-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                </svg>
                Kelola Transportasi
            </a>
            <a href="admin_list.php" class="menu-item active">
                <svg class="menu-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                Kelola Laporan
            </a>
        </nav>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-title">Kelola Laporan</h1>
            <p class="page-subtitle">Kelola dan pantau semua laporan pengaduan dari pengguna</p>
        </div>

        <!-- Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-header">
                    <div>
                        <div class="stat-label">Total Laporan</div>
                        <div class="stat-number"><?php echo $stats['total']; ?></div>
                    </div>
                    <div class="stat-icon total">
                        <svg width="20" height="20" fill="white" viewBox="0 0 20 20">
                            <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/>
                            <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-header">
                    <div>
                        <div class="stat-label">Pending</div>
                        <div class="stat-number"><?php echo $stats['pending']; ?></div>
                    </div>
                    <div class="stat-icon pending">
                        <svg width="20" height="20" fill="white" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-header">
                    <div>
                        <div class="stat-label">Diproses</div>
                        <div class="stat-number"><?php echo $stats['diproses']; ?></div>
                    </div>
                    <div class="stat-icon diproses">
                        <svg width="20" height="20" fill="white" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M11.49 3.17c-.38-1.56-2.6-1.56-2.98 0a1.532 1.532 0 01-2.286.948c-1.372-.836-2.942.734-2.106 2.106.54.886.061 2.042-.947 2.287-1.561.379-1.561 2.6 0 2.978a1.532 1.532 0 01.947 2.287c-.836 1.372.734 2.942 2.106 2.106a1.532 1.532 0 012.287.947c.379 1.561 2.6 1.561 2.978 0a1.533 1.533 0 012.287-.947c1.372.836 2.942-.734 2.106-2.106a1.533 1.533 0 01.947-2.287c1.561-.379 1.561-2.6 0-2.978a1.532 1.532 0 01-.947-2.287c.836-1.372-.734-2.942-2.106-2.106a1.532 1.532 0 01-2.287-.947zM10 13a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-header">
                    <div>
                        <div class="stat-label">Selesai</div>
                        <div class="stat-number"><?php echo $stats['selesai']; ?></div>
                    </div>
                    <div class="stat-icon selesai">
                        <svg width="20" height="20" fill="white" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-header">
                    <div>
                        <div class="stat-label">Ditolak</div>
                        <div class="stat-number"><?php echo $stats['ditolak']; ?></div>
                    </div>
                    <div class="stat-icon ditolak">
                        <svg width="20" height="20" fill="white" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Table Wrapper -->
                    </div>
                    <div class="stat-icon selesai">
                        <svg width="20" height="20" fill="white" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Table Wrapper -->
        <div class="table-wrapper">
            <div class="table-header">
                <h2 class="table-title">Daftar Laporan</h2>
                <select class="filter-select" onchange="window.location.href='?status=' + this.value">
                    <option value="all" <?php echo $filter_status === 'all' ? 'selected' : ''; ?>>Semua Status</option>
                    <option value="pending" <?php echo $filter_status === 'pending' ? 'selected' : ''; ?>>Pending</option>
                    <option value="diproses" <?php echo $filter_status === 'diproses' ? 'selected' : ''; ?>>Diproses</option>
                    <option value="selesai" <?php echo $filter_status === 'selesai' ? 'selected' : ''; ?>>Selesai</option>
                    <option value="ditolak" <?php echo $filter_status === 'ditolak' ? 'selected' : ''; ?>>Ditolak</option>
                </select>
            </div>

            <?php if (empty($laporan_list)): ?>
                <div class="empty-state">
                    <svg class="empty-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <p class="empty-text">Tidak ada laporan yang tersedia</p>
                </div>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Judul Laporan</th>
                            <th>Status</th>
                            <th>Tanggal</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($laporan_list as $laporan): ?>
                            <tr>
                                <td>
                                    <div class="user-email"><?php echo htmlspecialchars($laporan['email_user']); ?></div>
                                </td>
                                <td>
                                    <div class="laporan-title"><?php echo htmlspecialchars($laporan['judul_laporan']); ?></div>
                                </td>
                                <td>
                                    <?php
                                    $status_class = 'status-' . $laporan['status'];
                                    $status_text = ucfirst($laporan['status']);
                                    ?>
                                    <span class="status-badge <?php echo $status_class; ?>">
                                        <?php echo $status_text; ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="tanggal"><?php echo date('d M Y', strtotime($laporan['tanggal'])); ?></div>
                                </td>
                                <td>
                                    <a href="admin_detail.php?id=<?php echo $laporan['id']; ?>" class="btn-detail">
                                        Detail
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>

<?php
// Tutup koneksi database
if (isset($conn)) {
    mysqli_close($conn);
}
?>
