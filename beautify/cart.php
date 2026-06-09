<?php
session_start();
include 'koneksi1.php';

// Sementara pakai user_id = 1 dulu (nanti diganti $_SESSION['user_id'])
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 1;

// ─── TAMBAH KE KERANJANG ───
if (isset($_GET['action']) && $_GET['action'] == 'tambah') {
    $product_id = $_GET['product_id'];
    $quantity   = 1;

    // Cek apakah produk sudah ada di keranjang
    $cek = $conn->prepare("SELECT * FROM cart WHERE users_id = ? AND product_id = ?");
    $cek->bind_param("ii", $user_id, $product_id);
    $cek->execute();
    $hasil = $cek->get_result();

    if ($hasil->num_rows > 0) {
        // Kalau sudah ada → tambah quantity
        $conn->query("UPDATE cart SET quantity = quantity + 1 
                      WHERE users_id = $user_id AND product_id = $product_id");
    } else {
        // Kalau belum ada → insert baru
        $conn->query("INSERT INTO cart (users_id, product_id, quantity) 
                      VALUES ($user_id, $product_id, $quantity)");
    }

    header("Location: cart.php");
    exit;
}

// ─── KURANG QTY ───
if (isset($_GET['action']) && $_GET['action'] == 'kurang') {
    $cart_id = $_GET['cart_id'];

    // Cek qty sekarang
    $cek = $conn->query("SELECT quantity FROM cart WHERE cart_id = $cart_id AND users_id = $user_id");
    $row = $cek->fetch_assoc();

    if ($row['quantity'] <= 1) {
        // Kalau qty sudah 1, hapus saja
        $conn->query("DELETE FROM cart WHERE cart_id = $cart_id AND users_id = $user_id");
    } else {
        // Kurangi qty
        $conn->query("UPDATE cart SET quantity = quantity - 1 WHERE cart_id = $cart_id AND users_id = $user_id");
    }
    header("Location: cart.php");
    exit;
}

// ─── TAMBAH QTY ───
if (isset($_GET['action']) && $_GET['action'] == 'tambah_qty') {
    $cart_id = $_GET['cart_id'];
    $conn->query("UPDATE cart SET quantity = quantity + 1 WHERE cart_id = $cart_id AND users_id = $user_id");
    header("Location: cart.php");
    exit;
}

// ─── HAPUS DARI KERANJANG ───
if (isset($_GET['action']) && $_GET['action'] == 'hapus') {
    $cart_id = $_GET['cart_id'];
    $conn->query("DELETE FROM cart WHERE cart_id = $cart_id AND users_id = $user_id");
    header("Location: cart.php");
    exit;
}

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
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keranjang Belanja – Beautify</title>
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

        /* HEADER */
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
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .back-btn:hover { opacity: 0.8; }

        /* CONTAINER */
        .container {
            max-width: 1100px;
            margin: 30px auto;
            padding: 0 20px;
            display: grid;
            grid-template-columns: 1fr 340px;
            gap: 20px;
        }

        /* CARD */
        .card {
            background: white;
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 1px 4px rgba(0,0,0,0.06);
        }
        .card-title {
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 20px;
            padding-bottom: 12px;
            border-bottom: 1px solid var(--border);
        }

        /* CART ITEM */
        .cart-item {
            display: flex;
            gap: 16px;
            padding: 16px 0;
            border-bottom: 1px solid var(--border);
            align-items: center;
        }
        .cart-item:last-child { border-bottom: none; }
        .item-img {
            width: 80px; height: 80px;
            border-radius: 8px;
            background: #fafafa;
            object-fit: cover;
            flex-shrink: 0;
        }
        .item-info { flex: 1; }
        .item-brand { font-size: 11px; color: var(--text-muted); margin-bottom: 4px; }
        .item-name { font-size: 14px; font-weight: 600; margin-bottom: 6px; }
        .item-price { font-size: 16px; font-weight: 700; color: var(--pink); }
        .item-subtotal { font-size: 12px; color: var(--text-muted); margin-top: 2px; }
        .qty-control {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-top: 8px;
        }
        .qty-btn {
            width: 28px; height: 28px;
            border-radius: 6px;
            border: 1px solid var(--border);
            background: #f9f9f9;
            cursor: pointer;
            font-size: 16px;
            font-weight: 700;
            display: flex; align-items: center; justify-content: center;
            text-decoration: none;
            color: var(--text);
            transition: background 0.2s;
        }
        .qty-btn:hover { background: var(--pink-light); border-color: var(--pink); }
        .qty-num { font-size: 14px; font-weight: 700; min-width: 24px; text-align: center; }
        .btn-hapus {
            color: var(--pink);
            text-decoration: none;
            font-size: 12px;
            font-weight: 600;
            margin-top: 6px;
            display: inline-block;
        }
        .btn-hapus:hover { text-decoration: underline; }

        /* EMPTY CART */
        .empty-cart {
            text-align: center;
            padding: 60px 0;
            color: var(--text-muted);
        }
        .empty-cart .icon { font-size: 60px; display: block; margin-bottom: 16px; }
        .empty-cart p { font-size: 16px; font-weight: 600; margin-bottom: 8px; }
        .empty-cart small { font-size: 13px; }
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

        /* SUMMARY */
        .summary-row {
            display: flex;
            justify-content: space-between;
            font-size: 13px;
            color: var(--text-muted);
            margin-bottom: 10px;
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
        .btn-checkout {
            display: block;
            width: 100%;
            background: var(--pink);
            color: white;
            text-align: center;
            padding: 14px;
            border-radius: 10px;
            font-size: 15px;
            font-weight: 700;
            text-decoration: none;
            transition: background 0.2s;
        }
        .btn-checkout:hover { background: #e07880; }
        .note-ongkir {
            font-size: 12px;
            color: var(--text-muted);
            text-align: center;
            margin-top: 10px;
        }

        @media (max-width: 768px) {
            .container { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

<!-- HEADER -->
<header>
    <div class="header-inner">
        <a href="in2.php" class="logo">Beauti<span>fy</span></a>
        <a href="in2.php" class="back-btn">← Lanjut Belanja</a>
    </div>
</header>

<!-- KONTEN -->
<div class="container">

    <!-- KIRI: LIST PRODUK -->
    <div class="card">
        <div class="card-title">🛒 Keranjang Belanja
            <span style="font-size:13px;font-weight:400;color:var(--text-muted);margin-left:8px;">
                (<?= count($cart_data) ?> produk)
            </span>
        </div>

        <?php if (count($cart_data) == 0): ?>
        <div class="empty-cart">
            <span class="icon">🛒</span>
            <p>Keranjang kamu masih kosong</p>
            <small>Yuk tambahkan produk favorit kamu!</small>
            <br>
            <a href="in2.php" class="btn-belanja">Mulai Belanja</a>
        </div>

        <?php else: ?>
        <?php foreach ($cart_data as $item): ?>
        <div class="cart-item">
            <img class="item-img"
                src="https://images.unsplash.com/photo-1596462502278-27bfdc403348?auto=format&fit=crop&w=200&q=80"
                alt="<?= htmlspecialchars($item['product_name']) ?>">
            <div class="item-info">
                <div class="item-brand"><?= htmlspecialchars($item['brand']) ?></div>
                <div class="item-name"><?= htmlspecialchars($item['product_name']) ?></div>
                <div class="item-price">Rp <?= number_format($item['price'], 0, ',', '.') ?></div>
                <div class="item-subtotal">Subtotal: Rp <?= number_format($item['subtotal'], 0, ',', '.') ?></div>
                <div class="qty-control">
                    <a href="cart.php?action=kurang&cart_id=<?= $item['cart_id'] ?>" class="qty-btn">−</a>
                    <span class="qty-num"><?= $item['quantity'] ?></span>
                    <a href="cart.php?action=tambah_qty&cart_id=<?= $item['cart_id'] ?>" class="qty-btn">+</a>
                </div>
                <a href="cart.php?action=hapus&cart_id=<?= $item['cart_id'] ?>" class="btn-hapus"
                   onclick="return confirm('Hapus produk ini dari keranjang?')">🗑 Hapus</a>
            </div>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- KANAN: RINGKASAN -->
    <div>
        <div class="card">
            <div class="card-title">📋 Ringkasan Belanja</div>
            <div class="summary-row">
                <span>Total Produk</span>
                <span><?= count($cart_data) ?> item</span>
            </div>
            <div class="summary-row">
                <span>Subtotal</span>
                <span>Rp <?= number_format($total, 0, ',', '.') ?></span>
            </div>
            <div class="summary-row">
                <span>Ongkos Kirim</span>
                <span style="color:#B6BB79;font-weight:600;">
                    <?= $total >= 50000 ? 'GRATIS' : 'Rp 15.000' ?>
                </span>
            </div>
            <div class="summary-total">
                <span>Total</span>
                <span style="color:var(--pink);">
                    Rp <?= number_format($total >= 50000 ? $total : $total + 15000, 0, ',', '.') ?>
                </span>
            </div>
            <?php if (count($cart_data) > 0): ?>
            <a href="checkout.php" class="btn-checkout">Lanjut ke Checkout →</a>
            <?php else: ?>
            <button class="btn-checkout" style="opacity:0.5;cursor:not-allowed;" disabled>Lanjut ke Checkout →</button>
            <?php endif; ?>
            <p class="note-ongkir">🚚 Gratis ongkir untuk pembelian ≥ Rp 50.000</p>
        </div>
    </div>

</div>

</body>
</html>