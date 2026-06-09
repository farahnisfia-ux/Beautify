<?php
session_start();
include 'koneksi.php';

$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 1;

// ─── AMBIL ISI KERANJANG ───
$stmt = $conn->prepare("
    SELECT c.cart_id, c.quantity, p.product_name, p.price, p.brand
    FROM cart c
    JOIN products p ON c.product_id = p.id_product
    WHERE c.users_id = ?
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$items = $stmt->get_result();

$total = 0;
$cart_data = [];
while ($row = $items->fetch_assoc()) {
    $row['subtotal'] = $row['price'] * $row['quantity'];
    $total += $row['subtotal'];
    $cart_data[] = $row;
}

// Kalau keranjang kosong, redirect ke cart
if (count($cart_data) == 0) {
    header("Location: cart.php");
    exit;
}

$ongkir = $total >= 50000 ? 0 : 15000;
$total_bayar = $total + $ongkir;

// ─── PROSES CHECKOUT ───
$sukses = false;
if (isset($_POST['bayar'])) {
    // Simpan ke tabel orders
    $stmt2 = $conn->prepare("INSERT INTO orders (users_id, total_price) VALUES (?, ?)");
    $stmt2->bind_param("id", $user_id, $total_bayar);
    $stmt2->execute();

    // Kosongkan keranjang
    $conn->query("DELETE FROM cart WHERE users_id = $user_id");

    $sukses = true;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout – Beautify</title>
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
            max-width: 1100px;
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
            max-width: 1100px;
            margin: 30px auto;
            padding: 0 20px;
            display: grid;
            grid-template-columns: 1fr 340px;
            gap: 20px;
        }
        .card {
            background: white;
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 1px 4px rgba(0,0,0,0.06);
            margin-bottom: 16px;
        }
        .card-title {
            font-size: 16px;
            font-weight: 700;
            margin-bottom: 18px;
            padding-bottom: 12px;
            border-bottom: 1px solid var(--border);
        }

        /* FORM */
        .form-group { margin-bottom: 14px; }
        .form-group label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 6px;
            color: var(--text);
        }
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 10px 14px;
            border: 1px solid var(--border);
            border-radius: 8px;
            font-size: 13px;
            font-family: inherit;
            color: var(--text);
            background: white;
            outline: none;
            transition: border 0.2s;
        }
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            border-color: var(--pink);
        }
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }

        /* PAYMENT METHOD */
        .payment-options {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }
        .payment-option {
            border: 2px solid var(--border);
            border-radius: 8px;
            padding: 12px;
            cursor: pointer;
            transition: border 0.2s, background 0.2s;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            font-weight: 600;
        }
        .payment-option input[type="radio"] { accent-color: var(--pink); }
        .payment-option:has(input:checked) {
            border-color: var(--pink);
            background: #FFF0F1;
        }

        /* ORDER SUMMARY */
        .order-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid var(--border);
            font-size: 13px;
        }
        .order-item:last-child { border-bottom: none; }
        .order-item-name { color: var(--text); font-weight: 500; }
        .order-item-qty { color: var(--text-muted); font-size: 11px; }
        .order-item-price { font-weight: 700; color: var(--pink); }

        .summary-row {
            display: flex;
            justify-content: space-between;
            font-size: 13px;
            color: var(--text-muted);
            margin-bottom: 8px;
        }
        .summary-total {
            display: flex;
            justify-content: space-between;
            font-size: 18px;
            font-weight: 700;
            color: var(--text);
            margin: 14px 0;
            padding-top: 14px;
            border-top: 2px solid var(--border);
        }
        .btn-bayar {
            display: block;
            width: 100%;
            background: var(--pink);
            color: white;
            text-align: center;
            padding: 14px;
            border-radius: 10px;
            font-size: 15px;
            font-weight: 700;
            border: none;
            cursor: pointer;
            transition: background 0.2s;
        }
        .btn-bayar:hover { background: #e07880; }

        /* SUKSES */
        .sukses-box {
            text-align: center;
            padding: 60px 20px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 1px 4px rgba(0,0,0,0.06);
            max-width: 500px;
            margin: 40px auto;
        }
        .sukses-icon { font-size: 70px; display: block; margin-bottom: 16px; }
        .sukses-box h2 { font-size: 24px; font-weight: 700; margin-bottom: 8px; color: var(--text); }
        .sukses-box p { font-size: 14px; color: var(--text-muted); margin-bottom: 24px; }
        .btn-lanjut {
            display: inline-block;
            background: var(--pink);
            color: white;
            padding: 12px 28px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 700;
            font-size: 14px;
            margin: 6px;
        }
        .btn-lanjut:hover { background: #e07880; }
        .btn-riwayat {
            display: inline-block;
            background: var(--secondary);
            color: var(--secondary-text);
            padding: 12px 28px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 700;
            font-size: 14px;
            margin: 6px;
        }

        @media (max-width: 768px) {
            .container { grid-template-columns: 1fr; }
            .form-row { grid-template-columns: 1fr; }
            .payment-options { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

<header>
    <div class="header-inner">
        <a href="index.php" class="logo">Beauti<span>fy</span></a>
        <a href="cart.php" class="back-btn">← Kembali ke Keranjang</a>
    </div>
</header>

<?php if ($sukses): ?>
<!-- HALAMAN SUKSES -->
<div class="sukses-box">
    <span class="sukses-icon">🎉</span>
    <h2>Pesanan Berhasil!</h2>
    <p>Terima kasih sudah belanja di Beautify!<br>Pesananmu sedang diproses dan akan segera dikirim.</p>
    <a href="index.php" class="btn-lanjut">🛍 Belanja Lagi</a>
    <a href="riwayat_pesanan.php" class="btn-riwayat">📦 Lihat Pesanan</a>
</div>

<?php else: ?>
<!-- FORM CHECKOUT -->
<div class="container">

    <!-- KIRI: FORM -->
    <div>
        <!-- Alamat Pengiriman -->
        <div class="card">
            <div class="card-title">📍 Alamat Pengiriman</div>
            <form method="POST">
                <div class="form-row">
                    <div class="form-group">
                        <label>Nama Lengkap</label>
                        <input type="text" name="nama" placeholder="Nama penerima" required>
                    </div>
                    <div class="form-group">
                        <label>No. Telepon</label>
                        <input type="text" name="telepon" placeholder="08xxxxxxxxxx" required>
                    </div>
                </div>
                <div class="form-group">
                    <label>Alamat Lengkap</label>
                    <textarea name="alamat" rows="3" placeholder="Jl. nama jalan, No. rumah..." required></textarea>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Kota</label>
                        <input type="text" name="kota" placeholder="Surabaya" required>
                    </div>
                    <div class="form-group">
                        <label>Kode Pos</label>
                        <input type="text" name="kodepos" placeholder="60111" required>
                    </div>
                </div>

                <!-- Metode Pembayaran -->
                <div class="card-title" style="margin-top:20px;">💳 Metode Pembayaran</div>
                <div class="payment-options">
                    <label class="payment-option">
                        <input type="radio" name="pembayaran" value="gopay" checked>GoPay
                    </label>
                    <label class="payment-option">
                        <input type="radio" name="pembayaran" value="ovo">OVO
                    </label>
                    <label class="payment-option">
                        <input type="radio" name="pembayaran" value="dana">Dana
                    </label>
                    <label class="payment-option">
                        <input type="radio" name="pembayaran" value="transfer">Transfer Bank
                    </label>
                </div>

                <!-- Tombol Bayar (ada di dalam form) -->
                <button type="submit" name="bayar" class="btn-bayar" style="margin-top:20px;">
                    Bayar Sekarang – Rp <?= number_format($total_bayar, 0, ',', '.') ?>
                </button>
            </form>
        </div>
    </div>

    <!-- KANAN: RINGKASAN -->
    <div>
        <div class="card">
            <div class="card-title">🧾 Ringkasan Pesanan</div>
            <?php foreach ($cart_data as $item): ?>
            <div class="order-item">
                <div>
                    <div class="order-item-name"><?= htmlspecialchars($item['product_name']) ?></div>
                    <div class="order-item-qty"><?= $item['quantity'] ?>x · <?= htmlspecialchars($item['brand']) ?></div>
                </div>
                <div class="order-item-price">Rp <?= number_format($item['subtotal'], 0, ',', '.') ?></div>
            </div>
            <?php endforeach; ?>

            <div style="margin-top:14px;">
                <div class="summary-row">
                    <span>Subtotal</span>
                    <span>Rp <?= number_format($total, 0, ',', '.') ?></span>
                </div>
                <div class="summary-row">
                    <span>Ongkos Kirim</span>
                    <span style="color:#B6BB79;font-weight:600;">
                        <?= $ongkir == 0 ? 'GRATIS' : 'Rp ' . number_format($ongkir, 0, ',', '.') ?>
                    </span>
                </div>
                <div class="summary-total">
                    <span>Total Bayar</span>
                    <span style="color:var(--pink);">Rp <?= number_format($total_bayar, 0, ',', '.') ?></span>
                </div>
            </div>
        </div>
    </div>

</div>
<?php endif; ?>

</body>
</html>