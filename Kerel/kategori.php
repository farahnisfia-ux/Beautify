<?php
include 'koneksi.php';

$cat = $_GET['cat'] ?? '';

// Mapping URL slug ke nama kategori di database
$catMap = [
    'complexion'   => 'Complexion',
    'lip-products' => 'Lip Products',
    'eye-makeup'   => 'Eye Makeup',
    'eyebrow'      => 'Eyebrow',
];

// Emoji per kategori
$catEmoji = [
    'complexion'   => '✨',
    'lip-products' => '💄',
    'eye-makeup'   => '👁',
    'eyebrow'      => '🤎',
    'flash-sale'   => '⚡',
    'best-seller'  => '🏆',
];

$catName     = $catMap[$cat] ?? null;
$pageTitle   = $catName ?? ucwords(str_replace('-', ' ', $cat));
$pageEmoji   = $catEmoji[$cat] ?? '🛍';

if ($catName) {
    // Filter berdasarkan kategori spesifik
    $stmt = $conn->prepare("
        SELECT p.*, c.category_name
        FROM products p
        JOIN categories c ON p.category_id = c.id_category
        WHERE c.category_name = ?
        ORDER BY p.id_product DESC
    ");
    $stmt->bind_param('s', $catName);
} else {
    // Semua produk (untuk flash-sale, best-seller, dll)
    $stmt = $conn->prepare("
        SELECT p.*, c.category_name
        FROM products p
        JOIN categories c ON p.category_id = c.id_category
        ORDER BY p.id_product DESC
    ");
}

$stmt->execute();
$result = $stmt->get_result();
$totalProduk = $result->num_rows;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?> – Beautify</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Fraunces:ital,wght@0,300;0,600;1,300&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        :root {
            --pink: #F297A0;
            --pink-light: #F9D0CE;
            --orange: #F297A0;
            --bg: #F3EBD8;
            --white: #FFFFFF;
            --text: #3B2A2B;
            --text-muted: #8A7070;
            --border: #EDD9CC;
            --card-radius: 10px;
            --secondary: #DCDFBA;
            --secondary-text: #5A5E3A;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: var(--bg);
            color: var(--text);
            font-size: 14px;
        }

        /* ─── TOPBAR ─── */
        .topbar { background: var(--pink); color: white; font-size: 12px; padding: 6px 0; }
        .topbar-inner { max-width: 1280px; margin: auto; padding: 0 16px; display: flex; justify-content: space-between; align-items: center; }
        .topbar a { color: rgba(255,255,255,0.85); text-decoration: none; }
        .topbar a:hover { color: white; text-decoration: underline; }
        .topbar-links { display: flex; gap: 16px; align-items: center; }
        .topbar-links span { opacity: 0.5; }

        /* ─── HEADER ─── */
        header { background: var(--pink); position: sticky; top: 0; z-index: 100; box-shadow: 0 2px 8px rgba(0,0,0,0.15); }
        .header-inner { max-width: 1280px; margin: auto; padding: 12px 16px; display: flex; align-items: center; gap: 16px; }
        .logo { font-family: 'Fraunces', serif; font-size: 28px; font-weight: 600; color: white; white-space: nowrap; letter-spacing: -0.5px; text-decoration: none; }
        .logo span { font-style: italic; font-weight: 300; }
        .search-bar { flex: 1; display: flex; background: white; border-radius: 4px; overflow: hidden; height: 40px; }
        .search-bar input { flex: 1; border: none; outline: none; padding: 0 14px; font-size: 14px; font-family: inherit; }
        .search-bar button { background: var(--orange); border: none; color: white; padding: 0 20px; cursor: pointer; display: flex; align-items: center; transition: background 0.2s; }
        .search-bar button:hover { background: #e07880; }
        .header-actions { display: flex; align-items: center; gap: 20px; color: white; }
        .header-action-btn { display: flex; flex-direction: column; align-items: center; gap: 3px; cursor: pointer; color: white; text-decoration: none; font-size: 11px; position: relative; }
        .header-action-btn svg { width: 22px; height: 22px; }
        .cart-badge { position: absolute; top: -6px; right: -8px; background: #DCDFBA; color: #5A5E3A; font-size: 10px; font-weight: 700; width: 18px; height: 18px; border-radius: 50%; display: flex; align-items: center; justify-content: center; }

        /* ─── PROFILE DROPDOWN ─── */
        .profile-wrapper { position: relative; }
        .profile-trigger { display: flex; flex-direction: column; align-items: center; gap: 3px; cursor: pointer; color: white; font-size: 11px; user-select: none; }
        .profile-trigger svg { width: 22px; height: 22px; }
        .profile-dropdown { display: none; position: absolute; top: calc(100% + 14px); right: -10px; background: white; border-radius: 12px; box-shadow: 0 8px 32px rgba(0,0,0,0.18); min-width: 190px; padding: 8px 0; z-index: 300; color: var(--text); animation: dropFade 0.18s ease; }
        @keyframes dropFade { from { opacity:0; transform: translateY(-6px); } to { opacity:1; transform: translateY(0); } }
        .profile-dropdown.open { display: block; }
        .profile-dropdown::before { content: ''; position: absolute; top: -6px; right: 22px; width: 12px; height: 12px; background: white; transform: rotate(45deg); box-shadow: -2px -2px 5px rgba(0,0,0,0.06); z-index: -1; }
        .profile-dropdown a { display: flex; align-items: center; gap: 10px; padding: 10px 18px; font-size: 13px; color: var(--text); text-decoration: none; font-weight: 500; transition: background 0.15s, color 0.15s; }
        .profile-dropdown a:hover { background: #FFF0F1; color: var(--pink); }
        .profile-dropdown .dropdown-divider { margin: 6px 0; border: none; border-top: 1px solid #F3EBD8; }
        .profile-dropdown .logout-link { color: var(--pink); }

        /* ─── NAV ─── */
        nav.category-nav { background: white; border-bottom: 1px solid var(--border); }
        .nav-inner { max-width: 1280px; margin: auto; padding: 0 16px; display: flex; gap: 0; }
        .nav-inner a { display: block; padding: 12px 16px; text-decoration: none; color: var(--text); font-size: 13px; font-weight: 500; border-bottom: 2px solid transparent; white-space: nowrap; transition: all 0.2s; }
        .nav-inner a:hover, .nav-inner a.active { color: var(--pink); border-bottom-color: var(--pink); }

        /* ─── CONTAINER ─── */
        .container { max-width: 1280px; margin: auto; padding: 16px; }

        /* ─── BREADCRUMB ─── */
        .breadcrumb { display: flex; align-items: center; gap: 6px; margin-bottom: 14px; font-size: 13px; }
        .breadcrumb a { color: var(--text-muted); text-decoration: none; }
        .breadcrumb a:hover { color: var(--pink); }
        .breadcrumb .sep { color: var(--text-muted); }
        .breadcrumb .current { font-weight: 600; color: var(--text); }

        /* ─── CATEGORY HEADER ─── */
        .cat-header {
            background: linear-gradient(135deg, #F297A0 0%, #F9D0CE 60%, #F3EBD8 100%);
            border-radius: var(--card-radius);
            padding: 28px 32px;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 20px;
        }
        .cat-header-icon { font-size: 48px; line-height: 1; }
        .cat-header-text h1 { font-family: 'Fraunces', serif; font-size: 32px; font-weight: 600; color: #3B2A2B; margin-bottom: 4px; }
        .cat-header-text p { font-size: 13px; color: #7A5A5C; }

        /* ─── FILTER BAR ─── */
        .filter-bar {
            background: white;
            border-radius: var(--card-radius);
            padding: 12px 16px;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
            box-shadow: 0 1px 4px rgba(0,0,0,0.06);
        }
        .filter-label { font-size: 13px; font-weight: 600; color: var(--text-muted); }
        .filter-btn {
            padding: 6px 14px;
            border-radius: 20px;
            border: 1px solid var(--border);
            background: white;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            font-family: inherit;
            color: var(--text);
            transition: all 0.15s;
        }
        .filter-btn:hover, .filter-btn.active { background: var(--pink); color: white; border-color: var(--pink); }
        .result-count { margin-left: auto; font-size: 12px; color: var(--text-muted); }

        /* ─── SECTION PANEL ─── */
        .section-panel { background: white; border-radius: var(--card-radius); padding: 18px 16px; margin-bottom: 16px; box-shadow: 0 1px 4px rgba(0,0,0,0.06); }
        .section-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px; }
        .section-title { display: flex; align-items: center; gap: 12px; font-weight: 700; font-size: 18px; }

        /* ─── PRODUCT GRID ─── */
        .product-grid { display: grid; grid-template-columns: repeat(5, 1fr); gap: 12px; }
        .product-card { background: white; border-radius: var(--card-radius); overflow: hidden; box-shadow: 0 1px 4px rgba(0,0,0,0.06); transition: box-shadow 0.25s, transform 0.25s; cursor: pointer; position: relative; }
        .product-card:hover { box-shadow: 0 4px 20px rgba(0,0,0,0.12); transform: translateY(-3px); }
        .product-img-wrap { position: relative; background: #FAFAFA; aspect-ratio: 1; overflow: hidden; }
        .product-img-wrap img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.35s; }
        .product-card:hover .product-img-wrap img { transform: scale(1.06); }

        .cart-quick-btn { position: absolute; top: 8px; right: 8px; background: rgba(255,255,255,0.92); border: none; border-radius: 50%; width: 34px; height: 34px; display: flex; align-items: center; justify-content: center; cursor: pointer; font-size: 16px; opacity: 0; transition: opacity 0.2s, background 0.2s, transform 0.15s; box-shadow: 0 2px 6px rgba(0,0,0,0.12); }
        .product-card:hover .cart-quick-btn { opacity: 1; }
        .cart-quick-btn:hover { background: var(--pink); transform: scale(1.1); }
        .cart-quick-btn.added { background: var(--pink); opacity: 1; }

        .badge-label { position: absolute; top: 8px; left: 8px; padding: 3px 8px; border-radius: 3px; font-size: 11px; font-weight: 700; }
        .badge-label.sale { background: var(--pink); color: white; }
        .badge-label.star { background: var(--secondary); color: var(--secondary-text); }

        .product-info { padding: 10px 12px 12px; }
        .product-brand { font-size: 11px; color: var(--text-muted); margin-bottom: 4px; }
        .product-name { font-size: 13px; color: var(--text); line-height: 1.45; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; min-height: 38px; margin-bottom: 6px; }
        .price-row { margin-top: 4px; }
        .price-original { font-size: 11px; color: #AAAAAA; text-decoration: line-through; }
        .price-main { font-size: 16px; font-weight: 700; color: var(--pink); line-height: 1.2; }
        .discount-tag { display: inline-block; background: #F9D0CE; color: #b5606b; font-size: 11px; font-weight: 700; padding: 2px 5px; border-radius: 3px; margin-left: 4px; }
        .product-meta { display: flex; align-items: center; justify-content: space-between; margin-top: 6px; }
        .rating { font-size: 11px; color: #FAAF00; display: flex; align-items: center; gap: 2px; }
        .rating span { color: var(--text-muted); font-size: 11px; }
        .location-tag { font-size: 11px; color: var(--text-muted); }

        /* ─── ADMIN ACTIONS ─── */
        .admin-actions { display: flex; gap: 6px; padding: 8px 12px 10px; border-top: 1px solid var(--border); opacity: 0; transition: opacity 0.2s; }
        .product-card:hover .admin-actions { opacity: 1; }
        .btn-edit, .btn-delete { flex: 1; text-align: center; padding: 7px 4px; border-radius: 5px; font-size: 12px; font-weight: 600; text-decoration: none; }
        .btn-edit { background: #DCDFBA; color: #5A5E3A; border: 1px solid #c8cba0; }
        .btn-edit:hover { background: #c8cba0; }
        .btn-delete { background: var(--pink); color: white; border: none; cursor: pointer; }
        .btn-delete:hover { background: #e07880; }

        /* ─── EMPTY STATE ─── */
        .empty-state { text-align: center; padding: 60px 20px; color: var(--text-muted); }
        .empty-state .es-icon { font-size: 56px; display: block; margin-bottom: 16px; }
        .empty-state h3 { font-size: 18px; font-weight: 700; color: var(--text); margin-bottom: 8px; }
        .empty-state p { font-size: 13px; margin-bottom: 20px; }
        .btn-back { display: inline-flex; align-items: center; gap: 6px; background: var(--pink); color: white; padding: 10px 22px; border-radius: 20px; font-weight: 700; font-size: 13px; text-decoration: none; transition: background 0.2s; }
        .btn-back:hover { background: #e07880; }

        /* ─── CART SIDEBAR ─── */
        .cart-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.4); z-index: 200; }
        .cart-overlay.open { display: block; }
        .cart-sidebar { position: fixed; top: 0; right: -400px; width: 360px; height: 100vh; background: white; z-index: 201; display: flex; flex-direction: column; box-shadow: -4px 0 24px rgba(0,0,0,0.12); transition: right 0.3s ease; }
        .cart-sidebar.open { right: 0; }
        .cart-header-side { padding: 16px 20px; border-bottom: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between; }
        .cart-header-side h3 { font-size: 16px; font-weight: 700; }
        .btn-close-cart { background: none; border: none; font-size: 22px; cursor: pointer; color: var(--text-muted); }
        .btn-close-cart:hover { color: var(--text); }
        .cart-items-list { flex: 1; overflow-y: auto; padding: 12px 16px; }
        .cart-item-row { display: flex; gap: 12px; padding: 10px 0; border-bottom: 1px solid var(--border); align-items: center; }
        .cart-item-row img { width: 60px; height: 60px; object-fit: cover; border-radius: 8px; flex-shrink: 0; }
        .cart-item-details { flex: 1; min-width: 0; }
        .cart-item-details .item-name { font-size: 12px; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; margin-bottom: 2px; }
        .cart-item-details .item-brand { font-size: 10px; color: var(--text-muted); }
        .cart-item-details .item-price { font-size: 13px; font-weight: 700; color: var(--pink); margin-top: 2px; }
        .qty-control { display: flex; align-items: center; gap: 6px; margin-top: 5px; }
        .qty-control button { width: 22px; height: 22px; border-radius: 4px; border: 1px solid var(--border); background: #f9f9f9; cursor: pointer; font-size: 14px; font-weight: 700; display: flex; align-items: center; justify-content: center; }
        .qty-control button:hover { background: #F9D0CE; border-color: var(--pink); }
        .qty-control .qty-num { font-size: 13px; font-weight: 700; min-width: 20px; text-align: center; }
        .btn-remove-item { background: none; border: none; color: #ccc; cursor: pointer; font-size: 18px; padding: 4px; transition: color 0.2s; }
        .btn-remove-item:hover { color: var(--pink); }
        .cart-footer-side { padding: 14px 20px; border-top: 1px solid var(--border); }
        .cart-subtotal { display: flex; justify-content: space-between; font-size: 12px; color: var(--text-muted); margin-bottom: 4px; }
        .cart-total-row { display: flex; justify-content: space-between; font-size: 16px; font-weight: 700; margin-bottom: 14px; }
        .btn-checkout-main { display: block; width: 100%; background: var(--pink); color: white; text-align: center; padding: 12px; border-radius: 8px; font-size: 14px; font-weight: 700; text-decoration: none; border: none; cursor: pointer; transition: background 0.2s; }
        .btn-checkout-main:hover { background: #e07880; }
        .empty-cart-msg { text-align: center; padding: 50px 0; color: var(--text-muted); }
        .empty-cart-msg .ec-icon { font-size: 44px; display: block; margin-bottom: 10px; }
        .empty-cart-msg p { font-size: 13px; }
        .empty-cart-msg small { font-size: 11px; margin-top: 4px; display: block; }

        /* ─── FOOTER ─── */
        footer { background: white; border-top: 1px solid var(--border); margin-top: 32px; }
        .footer-main { max-width: 1280px; margin: auto; padding: 32px 16px; display: grid; grid-template-columns: 1.5fr 1fr 1fr 1fr; gap: 32px; }
        .footer-brand p { font-size: 12px; color: var(--text-muted); line-height: 1.7; }
        .footer-col h4 { font-size: 13px; font-weight: 700; margin-bottom: 14px; }
        .footer-col a { display: block; font-size: 12px; color: var(--text-muted); text-decoration: none; margin-bottom: 8px; }
        .footer-col a:hover { color: var(--pink); }
        .footer-bottom { border-top: 1px solid var(--border); padding: 14px 16px; text-align: center; font-size: 12px; color: var(--text-muted); max-width: 1280px; margin: auto; }
        .payment-icons { display: flex; gap: 8px; flex-wrap: wrap; margin-top: 12px; }
        .pay-tag { background: #F9D0CE; border: 1px solid #f0b8bc; padding: 4px 10px; border-radius: 4px; font-size: 11px; font-weight: 600; color: #b5606b; }

        @media (max-width: 1024px) { .product-grid { grid-template-columns: repeat(4, 1fr); } }
        @media (max-width: 768px) { .product-grid { grid-template-columns: repeat(2, 1fr); } .footer-main { grid-template-columns: 1fr 1fr; } .cart-sidebar { width: 100%; right: -100%; } }
    </style>
</head>
<body>

<!-- ─── TOPBAR ─── -->
<div class="topbar">
    <div class="topbar-inner">
        <div class="topbar-links">
            <a href="#">Bantuan</a>
            <span>|</span>
            <a href="#">🔔 Notifikasi</a>
            <span>|</span>
            <a href="login.php">Masuk / Daftar</a>
        </div>
    </div>
</div>

<!-- ─── HEADER ─── -->
<header>
    <div class="header-inner">
        <a href="index.php" class="logo">Beauti<span>fy</span></a>

        <div class="search-bar">
            <input type="text" placeholder="Cari produk, merek, kategori...">
            <button>
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="width:18px;height:18px;">
                    <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                </svg>
            </button>
        </div>

        <div class="header-actions">
            <a href="#" class="header-action-btn" onclick="openCart(); return false;">
                <div style="position:relative;">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/>
                        <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
                    </svg>
                    <span class="cart-badge" id="cartBadge">0</span>
                </div>
                <span>Keranjang</span>
            </a>

            <div class="profile-wrapper" id="profileWrapper">
                <div class="profile-trigger" onclick="toggleProfile()">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>
                    </svg>
                    <span>Akun</span>
                </div>
                <div class="profile-dropdown" id="profileDropdown">
                    <a href="profil.php">👤 &nbsp;Profil Saya</a>
                    <a href="pesanan.php">📦 &nbsp;Pesanan Saya</a>
                    <a href="wishlist.php">❤️ &nbsp;Wishlist</a>
                    <a href="pengaturan.php">⚙️ &nbsp;Pengaturan</a>
                    <hr class="dropdown-divider">
                    <a href="login.php" class="logout-link">🚪 &nbsp;Masuk / Daftar</a>
                </div>
            </div>
        </div>
    </div>
</header>

<!-- ─── CATEGORY NAV ─── -->
<nav class="category-nav">
    <div class="nav-inner">
        <a href="index.php">Home</a>
        <a href="kategori.php?cat=flash-sale" <?= $cat=='flash-sale' ? 'class="active"' : '' ?>>Flash Sale</a>
        <a href="kategori.php?cat=best-seller" <?= $cat=='best-seller' ? 'class="active"' : '' ?>>Best Seller</a>
        <a href="kategori.php?cat=complexion" <?= $cat=='complexion' ? 'class="active"' : '' ?>>Complexion</a>
        <a href="kategori.php?cat=lip-products" <?= $cat=='lip-products' ? 'class="active"' : '' ?>>Lip Products</a>
        <a href="kategori.php?cat=eye-makeup" <?= $cat=='eye-makeup' ? 'class="active"' : '' ?>>Eye Makeup</a>
        <a href="kategori.php?cat=eyebrow" <?= $cat=='eyebrow' ? 'class="active"' : '' ?>>Eyebrow</a>
    </div>
</nav>

<div class="container">

    <!-- ─── BREADCRUMB ─── -->
    <div class="breadcrumb">
        <a href="index.php">🏠 Home</a>
        <span class="sep">›</span>
        <span class="current"><?= htmlspecialchars($pageTitle) ?></span>
    </div>

    <!-- ─── CATEGORY HEADER ─── -->
    <div class="cat-header">
        <div class="cat-header-icon"><?= $pageEmoji ?></div>
        <div class="cat-header-text">
            <h1><?= htmlspecialchars($pageTitle) ?></h1>
            <p><?= $totalProduk ?> produk tersedia · Gratis ongkir min. Rp 50.000</p>
        </div>
    </div>

    <!-- ─── FILTER BAR ─── -->
    <div class="filter-bar">
        <span class="filter-label">Urutkan:</span>
        <button class="filter-btn active" onclick="setFilter(this)">Terbaru</button>
        <button class="filter-btn" onclick="setFilter(this)">Terlaris</button>
        <button class="filter-btn" onclick="setFilter(this)">Harga ↑</button>
        <button class="filter-btn" onclick="setFilter(this)">Harga ↓</button>
        <button class="filter-btn" onclick="setFilter(this)">Rating Tertinggi</button>
        <span class="result-count"><?= $totalProduk ?> produk ditemukan</span>
    </div>

    <!-- ─── PRODUCTS ─── -->
    <div class="section-panel">
        <?php if ($totalProduk == 0): ?>
        <div class="empty-state">
            <span class="es-icon">🔍</span>
            <h3>Produk tidak ditemukan</h3>
            <p>Belum ada produk di kategori <strong><?= htmlspecialchars($pageTitle) ?></strong> saat ini.</p>
            <a href="index.php" class="btn-back">← Kembali ke Beranda</a>
        </div>
        <?php else: ?>
        <div class="product-grid">
            <?php while($data = $result->fetch_assoc()):
                $hargaCoret   = $data['price'] + ($data['price'] * 0.15);
                $disc         = 15;
                $isStarSeller = $data['stock'] > 15;
                $sold         = rand(100, 5000);
                $rating       = number_format(rand(40, 50) / 10, 1);
                $badgeClass   = $isStarSeller ? 'star' : 'sale';
                $badgeText    = $isStarSeller ? '⭐ Star Seller' : '-' . $disc . '%';
                $nameSafe     = addslashes(htmlspecialchars($data['product_name']));
                $brandSafe    = addslashes(htmlspecialchars($data['brand']));
            ?>
            <div class="product-card">
                <div class="product-img-wrap">
                    <img
                        src="https://images.unsplash.com/photo-1596462502278-27bfdc403348?auto=format&fit=crop&w=400&q=80"
                        alt="<?= htmlspecialchars($data['product_name']); ?>"
                        loading="lazy"
                    >
                    <span class="badge-label <?= $badgeClass; ?>"><?= $badgeText; ?></span>
                    <button
                        class="cart-quick-btn"
                        title="Tambah ke Keranjang"
                        data-id="<?= $data['id_product'] ?>"
                        onclick="addToCart(
                            <?= $data['id_product'] ?>,
                            '<?= $nameSafe ?>',
                            '<?= $brandSafe ?>',
                            <?= $data['price'] ?>,
                            'https://images.unsplash.com/photo-1596462502278-27bfdc403348?auto=format&fit=crop&w=400&q=80'
                        )"
                    >🛒</button>
                </div>

                <div class="product-info">
                    <div class="product-brand"><?= htmlspecialchars($data['brand']); ?></div>
                    <div class="product-name"><?= htmlspecialchars($data['product_name']); ?></div>
                    <div class="price-row">
                        <?php if(!$isStarSeller): ?>
                        <div class="price-original">Rp <?= number_format($hargaCoret, 0, ',', '.'); ?></div>
                        <?php endif; ?>
                        <div>
                            <span class="price-main">Rp <?= number_format($data['price'], 0, ',', '.'); ?></span>
                            <?php if(!$isStarSeller): ?>
                            <span class="discount-tag">-<?= $disc; ?>%</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="product-meta">
                        <div class="rating">
                            ★ <?= $rating; ?>
                            <span>| <?= number_format($sold, 0, ',', '.'); ?> terjual</span>
                        </div>
                        <div class="location-tag">Surabaya</div>
                    </div>
                    <div style="margin-top:6px;">
                        <span style="background:#F9D0CE;color:#b5606b;font-size:10px;font-weight:700;padding:2px 6px;border-radius:3px;"><?= htmlspecialchars($data['category_name']); ?></span>
                        <span style="background:#DCDFBA;color:#5A5E3A;font-size:10px;font-weight:700;padding:2px 6px;border-radius:3px;margin-left:4px;">Official</span>
                    </div>
                </div>

                <div class="admin-actions">
                    <a href="edit_produk.php?id=<?= $data['id_product']; ?>" class="btn-edit">✏ Edit</a>
                    <a href="hapus_produk.php?id=<?= $data['id_product']; ?>" onclick="return confirm('Hapus produk ini?')" class="btn-delete">🗑 Hapus</a>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
        <?php endif; ?>
    </div>

</div><!-- /container -->

<!-- ─── CART OVERLAY ─── -->
<div class="cart-overlay" id="cartOverlay" onclick="closeCart()"></div>

<!-- ─── CART SIDEBAR ─── -->
<div class="cart-sidebar" id="cartSidebar">
    <div class="cart-header-side">
        <h3>🛒 Keranjang Belanja</h3>
        <button class="btn-close-cart" onclick="closeCart()">✕</button>
    </div>
    <div class="cart-items-list" id="cartItemsList">
        <div class="empty-cart-msg">
            <span class="ec-icon">🛒</span>
            <p>Keranjang masih kosong</p>
            <small>Yuk tambahkan produk favorit kamu!</small>
        </div>
    </div>
    <div class="cart-footer-side" id="cartFooter" style="display:none;">
        <div class="cart-subtotal">
            <span id="cartItemCount">0 produk</span>
            <span>Subtotal</span>
        </div>
        <div class="cart-total-row">
            <span>Total</span>
            <span id="cartTotalPrice">Rp 0</span>
        </div>
        <a href="checkout.php" class="btn-checkout-main">Lanjut ke Checkout →</a>
    </div>
</div>

<!-- ─── FOOTER ─── -->
<footer>
    <div class="footer-main">
        <div class="footer-brand">
            <a href="index.php" class="logo" style="color:#F297A0;font-size:24px;display:block;margin-bottom:10px;">Beauti<span>fy</span></a>
            <p>Platform marketplace kecantikan terpercaya di Indonesia. Temukan produk beauty premium dengan harga terbaik dan pengiriman cepat ke seluruh Indonesia.</p>
            <div class="payment-icons">
                <span class="pay-tag">GoPay</span>
                <span class="pay-tag">OVO</span>
                <span class="pay-tag">Dana</span>
                <span class="pay-tag">BCA</span>
                <span class="pay-tag">BRI</span>
                <span class="pay-tag">Mandiri</span>
            </div>
        </div>
        <div class="footer-col">
            <h4>Layanan Pelanggan</h4>
            <a href="#">Pusat Bantuan</a>
            <a href="#">Cara Belanja</a>
            <a href="#">Lacak Pesanan</a>
            <a href="#">Kebijakan Retur</a>
            <a href="#">Hubungi Kami</a>
        </div>
        <div class="footer-col">
            <h4>Tentang Beautify</h4>
            <a href="#">Tentang Kami</a>
            <a href="#">Blog Kecantikan</a>
            <a href="#">Karir</a>
            <a href="#">Press</a>
            <a href="#">Kebijakan Privasi</a>
        </div>
        <div class="footer-col">
            <h4>Untuk Penjual</h4>
            <a href="#">Daftar Jadi Seller</a>
            <a href="#">Panduan Seller</a>
            <a href="#">Flash Sale Program</a>
            <a href="#">Iklan di Beautify</a>
        </div>
    </div>
    <div class="footer-bottom">
        © 2026 Beautify Marketplace. Hak Cipta Dilindungi. | 🇮🇩 Indonesia
    </div>
</footer>

<script>
// ─── CART STATE ───
let cart = JSON.parse(sessionStorage.getItem('beautify_cart') || '[]');

function saveCart() { sessionStorage.setItem('beautify_cart', JSON.stringify(cart)); }
function formatRp(n) { return 'Rp ' + parseInt(n).toLocaleString('id-ID'); }

function openCart() {
    document.getElementById('cartSidebar').classList.add('open');
    document.getElementById('cartOverlay').classList.add('open');
    renderCart();
}
function closeCart() {
    document.getElementById('cartSidebar').classList.remove('open');
    document.getElementById('cartOverlay').classList.remove('open');
}

function addToCart(id, name, brand, price, img) {
    const existing = cart.find(c => c.id == id);
    if (existing) { existing.qty++; }
    else { cart.push({ id, name, brand, price: parseInt(price), img, qty: 1 }); }
    saveCart(); updateCartBadge(); renderCart(); openCart();
    const btn = document.querySelector(`.cart-quick-btn[data-id="${id}"]`);
    if (btn) {
        btn.textContent = '✓'; btn.classList.add('added');
        setTimeout(() => { btn.textContent = '🛒'; btn.classList.remove('added'); }, 1200);
    }
}

function changeQty(id, delta) {
    const item = cart.find(c => c.id == id);
    if (!item) return;
    item.qty += delta;
    if (item.qty <= 0) cart = cart.filter(c => c.id != id);
    saveCart(); updateCartBadge(); renderCart();
}
function removeItem(id) {
    cart = cart.filter(c => c.id != id);
    saveCart(); updateCartBadge(); renderCart();
}

function updateCartBadge() {
    const total = cart.reduce((s, c) => s + c.qty, 0);
    document.getElementById('cartBadge').textContent = total;
}

function renderCart() {
    const list   = document.getElementById('cartItemsList');
    const footer = document.getElementById('cartFooter');
    if (cart.length === 0) {
        list.innerHTML = `<div class="empty-cart-msg"><span class="ec-icon">🛒</span><p>Keranjang masih kosong</p><small>Yuk tambahkan produk favorit kamu!</small></div>`;
        footer.style.display = 'none'; return;
    }
    const totalQty   = cart.reduce((s, c) => s + c.qty, 0);
    const totalPrice = cart.reduce((s, c) => s + c.price * c.qty, 0);
    document.getElementById('cartItemCount').textContent  = totalQty + ' produk';
    document.getElementById('cartTotalPrice').textContent = formatRp(totalPrice);
    footer.style.display = 'block';
    list.innerHTML = cart.map(item => `
        <div class="cart-item-row">
            <img src="${item.img}" alt="${item.name}">
            <div class="cart-item-details">
                <div class="item-name">${item.name}</div>
                <div class="item-brand">${item.brand}</div>
                <div class="item-price">${formatRp(item.price)}</div>
                <div class="qty-control">
                    <button onclick="changeQty(${item.id}, -1)">−</button>
                    <span class="qty-num">${item.qty}</span>
                    <button onclick="changeQty(${item.id}, +1)">+</button>
                </div>
            </div>
            <button class="btn-remove-item" onclick="removeItem(${item.id})">✕</button>
        </div>
    `).join('');
}

// ─── PROFILE DROPDOWN ───
function toggleProfile() {
    document.getElementById('profileDropdown').classList.toggle('open');
}
document.addEventListener('click', function(e) {
    const wrapper = document.getElementById('profileWrapper');
    if (wrapper && !wrapper.contains(e.target)) {
        document.getElementById('profileDropdown').classList.remove('open');
    }
});

// ─── FILTER BUTTON ───
function setFilter(btn) {
    document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
}

// ─── INIT ───
updateCartBadge();
</script>

</body>
</html>