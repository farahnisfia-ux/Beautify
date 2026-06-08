<?php
session_start();
include 'koneksi.php';

$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 1;

// Ambil semua pesanan user
$stmt = $conn->prepare("
    SELECT * FROM orders 
    WHERE users_id = ? 
    ORDER BY order_date DESC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$pesanan = $stmt->get_result();

$data_pesanan = [];
while ($row = $pesanan->fetch_assoc()) {
    $data_pesanan[] = $row;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Pesanan – Beautify</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Fraunces:ital,wght@0,300;0,600;1,300&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        :root {
            --pink: #F297A0;
            --pink-light: #F9D0CE;
            --bg: #F3EBD8;
            --text: #3B2A2B;
            --text-muted: #8A7070;
            --border: #EDD9CC;
            --secondary: #DCDFBA;
            --secondary-text: #5A5E3A;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: var(--bg); color: var(--text); }

        header {
            background: var(--pink);
            padding: 14px 0;
            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
        }
        .header-inner {
            max-width: 900px;
            margin: auto;
            padding: 0 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .logo {
            font-family: 'Fraunces', serif;
            font-size: 26px;
            font-weight: 600;
            color: white;
            text-decoration: none;
        }
        .logo span { font-style: italic; font-weight: 300; }
        .back-btn {
            color: white;
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
        }
        .back-btn:hover { opacity: 0.8; }

        .container {
            max-width: 900px;
            margin: 30px auto;
            padding: 0 20px;
        }
        .page-title {
            font-size: 22px;
            font-weight: 700;
            margin-bottom: 20px;
        }
        .card {
            background: white;
            border-radius: 12px;
            padding: 20px 24px;
            box-shadow: 0 1px 4px rgba(0,0,0,0.06);
            margin-bottom: 14px;
        }
        .pesanan-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
            padding-bottom: 12px;
            border-bottom: 1px solid var(--border);
        }
        .pesanan-id {
            font-size: 13px;
            font-weight: 700;
            color: var(--text);
        }
        .pesanan-tanggal {
            font-size: 12px;
            color: var(--text-muted);
            margin-top: 2px;
        }
        .badge-status {
            background: var(--secondary);
            color: var(--secondary-text);
            font-size: 11px;
            font-weight: 700;
            padding: 4px 12px;
            border-radius: 20px;
        }
        .pesanan-total {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 12px;
        }
        .total-label { font-size: 13px; color: var(--text-muted); }
        .total-amount {
            font-size: 18px;
            font-weight: 700;
            color: var(--pink);
        }
        .btn-detail {
            display: inline-block;
            background: var(--pink-light);
            color: var(--pink);
            padding: 6px 16px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 700;
            text-decoration: none;
            transition: background 0.2s;
        }
        .btn-detail:hover { background: var(--pink); color: white; }

        .empty-box {
            text-align: center;
            padding: 60px 20px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 1px 4px rgba(0,0,0,0.06);
        }
        .empty-box .icon { font-size: 60px; display: block; margin-bottom: 16px; }
        .empty-box p { font-size: 16px; font-weight: 600; margin-bottom: 8px; }
        .empty-box small { font-size: 13px; color: var(--text-muted); }
        .btn-belanja {
            display: inline-block;
            margin-top: 20px;
            background: var(--pink);
            color: white;
            padding: 10px 24px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 700;
            font-size: 14px;
        }
        .btn-belanja:hover { background: #e07880; }
    </style>
</head>
<body>

<header>
    <div class="header-inner">
        <a href="index.php" class="logo">Beauti<span>fy</span></a>
        <a href="index.php" class="back-btn">← Kembali Belanja</a>
    </div>
</header>

<div class="container">
    <div class="page-title">📦 Riwayat Pesanan</div>

    <?php if (count($data_pesanan) == 0): ?>
    <div class="empty-box">
        <span class="icon">📦</span>
        <p>Belum ada pesanan</p>
        <small>Yuk mulai belanja produk beauty favoritmu!</small>
        <br>
        <a href="index.php" class="btn-belanja">Mulai Belanja</a>
    </div>

    <?php else: ?>
    <?php foreach ($data_pesanan as $p): ?>
    <div class="card">
        <div class="pesanan-header">
            <div>
                <div class="pesanan-id">Pesanan #<?= str_pad($p['order_id'], 5, '0', STR_PAD_LEFT) ?></div>
                <div class="pesanan-tanggal">📅 <?= date('d M Y, H:i', strtotime($p['order_date'])) ?> WIB</div>
            </div>
            <span class="badge-status">✅ Diproses</span>
        </div>
        <div class="pesanan-total">
            <div>
                <div class="total-label">Total Pembayaran</div>
                <div class="total-amount">Rp <?= number_format($p['total_price'], 0, ',', '.') ?></div>
            </div>
            <a href="index.php" class="btn-detail">🛍 Beli Lagi</a>
        </div>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>

</div>

</body>
</html>