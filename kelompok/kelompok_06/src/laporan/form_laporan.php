<?php
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
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Laporan Pengaduan - Sistem Transportasi</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
        }

        .card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            padding: 40px;
            margin-top: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        .header h1 {
            color: #333;
            font-size: 28px;
            margin-bottom: 10px;
        }

        .header p {
            color: #666;
            font-size: 14px;
        }

        .user-info {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 30px;
            border-left: 4px solid #667eea;
        }

        .user-info p {
            color: #555;
            font-size: 14px;
            margin: 5px 0;
        }

        .user-info strong {
            color: #333;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            color: #333;
            font-weight: 600;
            margin-bottom: 8px;
            font-size: 15px;
        }

        .form-group label .required {
            color: #e74c3c;
        }

        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 15px;
            transition: all 0.3s ease;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .form-control:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        textarea.form-control {
            resize: vertical;
            min-height: 150px;
        }

        .char-counter {
            text-align: right;
            color: #999;
            font-size: 13px;
            margin-top: 5px;
        }

        .btn-group {
            display: flex;
            gap: 15px;
            margin-top: 30px;
        }

        .btn {
            flex: 1;
            padding: 14px 25px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-align: center;
            text-decoration: none;
            display: inline-block;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background: #5a6268;
            transform: translateY(-2px);
        }

        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: none;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border-left: 4px solid #28a745;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border-left: 4px solid #dc3545;
        }

        .alert-warning {
            background: #fff3cd;
            color: #856404;
            border-left: 4px solid #ffc107;
        }

        .form-help {
            font-size: 13px;
            color: #666;
            margin-top: 5px;
        }

        @media (max-width: 768px) {
            .card {
                padding: 25px;
            }

            .btn-group {
                flex-direction: column;
            }

            .header h1 {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="header">
                <h1>📝 Form Laporan Pengaduan</h1>
                <p>Laporkan masalah atau keluhan terkait layanan transportasi</p>
            </div>

            <div class="user-info">
                <p><strong>Nama:</strong> <?php echo htmlspecialchars($_SESSION['nama']); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($_SESSION['email']); ?></p>
            </div>

            <div id="alertBox" class="alert"></div>

            <form id="formLaporan" method="POST" action="proses_laporan.php">
                <div class="form-group">
                    <label for="judul_laporan">
                        Judul Laporan <span class="required">*</span>
                    </label>
                    <input 
                        type="text" 
                        class="form-control" 
                        id="judul_laporan" 
                        name="judul_laporan" 
                        placeholder="Contoh: Bus Terlambat, AC Rusak, Kursi Tidak Nyaman" 
                        required
                        maxlength="255"
                    >
                    <div class="form-help">Maksimal 255 karakter</div>
                </div>

                <div class="form-group">
                    <label for="isi_laporan">
                        Isi Laporan <span class="required">*</span>
                    </label>
                    <textarea 
                        class="form-control" 
                        id="isi_laporan" 
                        name="isi_laporan" 
                        placeholder="Jelaskan detail laporan Anda, termasuk waktu kejadian, nomor bus/transportasi, dan informasi penting lainnya..." 
                        required
                        maxlength="2000"
                    ></textarea>
                    <div class="char-counter">
                        <span id="charCount">0</span> / 2000 karakter
                    </div>
                    <div class="form-help">Jelaskan dengan detail agar laporan dapat ditindaklanjuti dengan baik</div>
                </div>

                <div class="btn-group">
                    <button type="submit" class="btn btn-primary">
                        Kirim Laporan
                    </button>
                    <a href="list_laporan_user.php" class="btn btn-secondary">
                        Batal
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Character counter untuk textarea
        const isiLaporan = document.getElementById('isi_laporan');
        const charCount = document.getElementById('charCount');

        isiLaporan.addEventListener('input', function() {
            const count = this.value.length;
            charCount.textContent = count;
            
            if (count > 1800) {
                charCount.style.color = '#e74c3c';
            } else if (count > 1500) {
                charCount.style.color = '#f39c12';
            } else {
                charCount.style.color = '#999';
            }
        });

        // Form validation
        const form = document.getElementById('formLaporan');
        const alertBox = document.getElementById('alertBox');

        form.addEventListener('submit', function(e) {
            const judul = document.getElementById('judul_laporan').value.trim();
            const isi = document.getElementById('isi_laporan').value.trim();

            // Validasi judul
            if (judul.length < 5) {
                e.preventDefault();
                showAlert('Judul laporan minimal 5 karakter', 'error');
                return false;
            }

            // Validasi isi
            if (isi.length < 20) {
                e.preventDefault();
                showAlert('Isi laporan minimal 20 karakter untuk memberikan detail yang cukup', 'error');
                return false;
            }

            // Validasi karakter khusus berbahaya
            const dangerousChars = /<script|javascript:|onerror=/gi;
            if (dangerousChars.test(judul) || dangerousChars.test(isi)) {
                e.preventDefault();
                showAlert('Karakter tidak diperbolehkan terdeteksi dalam laporan', 'error');
                return false;
            }
        });

        function showAlert(message, type) {
            alertBox.textContent = message;
            alertBox.className = 'alert alert-' + type;
            alertBox.style.display = 'block';
            
            // Scroll to alert
            alertBox.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            
            // Auto hide after 5 seconds
            setTimeout(function() {
                alertBox.style.display = 'none';
            }, 5000);
        }

        // Check for success/error message from URL
        window.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            const status = urlParams.get('status');
            const message = urlParams.get('message');

            if (status && message) {
                showAlert(decodeURIComponent(message), status);
                // Clean URL
                window.history.replaceState({}, document.title, window.location.pathname);
            }
        });
    </script>
</body>
</html>
