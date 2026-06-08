<?php
include 'koneksi.php';

// ─── Ambil category_id dari URL ───
$category_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// ─── Ambil semua kategori untuk navbar & pills ───
$stmtCats = $conn->prepare("SELECT * FROM categories ORDER BY id_category ASC");
$stmtCats->execute();
$categories = $stmtCats->get_result()->fetch_all(MYSQLI_ASSOC);

// ─── Nama kategori aktif ───
$currentCategoryName = 'Semua Produk';
foreach ($categories as $cat) {
    if ($cat['id_category'] == $category_id) {
        $currentCategoryName = $cat['category_name'];
        break;
    }
}

// ─── Ambil produk sesuai kategori ───
if ($category_id > 0) {
    $stmtProd = $conn->prepare("
        SELECT p.*, c.category_name
        FROM products p
        JOIN categories c ON p.category_id = c.id_category
        WHERE p.category_id = ?
        ORDER BY p.id_product DESC
    ");
    $stmtProd->bind_param("i", $category_id);
} else {
    $stmtProd = $conn->prepare("
        SELECT p.*, c.category_name
        FROM products p
        JOIN categories c ON p.category_id = c.id_category
        ORDER BY p.id_product DESC
    ");
}
$stmtProd->execute();
$resultProd = $stmtProd->get_result();

// ─── Hitung total produk ───
if ($category_id > 0) {
    $stmtCount = $conn->prepare("SELECT COUNT(*) as total FROM products WHERE category_id = ?");
    $stmtCount->bind_param("i", $category_id);
} else {
    $stmtCount = $conn->prepare("SELECT COUNT(*) as total FROM products");
}
$stmtCount->execute();
$totalProducts = $stmtCount->get_result()->fetch_assoc()['total'];

// ─── Data icon & warna tiap kategori ───
$catMeta = [
    1 => ['icon' => '✨', 'pill_bg' => 'blue',   'hero' => 'cat-1', 'desc' => 'Foundation, concealer, powder & semua produk complexion favoritmu'],
    2 => ['icon' => '💄', 'pill_bg' => 'pink',   'hero' => 'cat-2', 'desc' => 'Lipstik, lip gloss, lip tint & koleksi lip products terlengkap'],
    3 => ['icon' => '👁',  'pill_bg' => 'purple', 'hero' => 'cat-3', 'desc' => 'Eyeshadow, eyeliner, mascara & produk eye makeup premium'],
    4 => ['icon' => '🤎', 'pill_bg' => 'yellow', 'hero' => 'cat-4', 'desc' => 'Pensil alis, pomade, eyebrow kit & semua perlengkapan alis'],
];

// ─── Gambar placeholder per kategori (pakai Unsplash) ───
$catImages = [
    1 => 'https://images.unsplash.com/photo-1617897903246-719242758050?auto=format&fit=crop&w=400&q=80',
    2 => 'https://images.unsplash.com/photo-1586495777744-4e6232bf4eed?auto=format&fit=crop&w=400&q=80',
    3 => 'https://images.unsplash.com/photo-1512496015851-a90fb38ba796?auto=format&fit=crop&w=400&q=80',
    4 => 'https://images.unsplash.com/photo-1522335789203-aabd1fc54bc9?auto=format&fit=crop&w=400&q=80',
];
$defaultImg = 'https://images.unsplash.com/photo-1596462502278-27bfdc403348?auto=format&fit=crop&w=400&q=80';

$heroClass = isset($catMeta[$category_id]) ? $catMeta[$category_id]['hero'] : 'cat-0';
$heroIcon  = isset($catMeta[$category_id]) ? $catMeta[$category_id]['icon'] : '🌸';
$heroDesc  = isset($catMeta[$category_id]) ? $catMeta[$category_id]['desc'] : 'Temukan semua produk beauty premium pilihan terbaik';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($currentCategoryName) ?> – Beautify</title>
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
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: var(--bg); color: var(--text); font-size: 14px; }

        /* TOPBAR */
        .topbar { background: var(--pink); color: white; font-size: 12px; padding: 6px 0; }
        .topbar-inner { max-width: 1280px; margin: auto; padding: 0 16px; display: flex; justify-content: space-between; align-items: center; }
        .topbar a { color: rgba(255,255,255,0.85); text-decoration: none; }
        .topbar a:hover { color: white; text-decoration: underline; }
        .topbar-links { display: flex; gap: 16px; align-items: center; }
        .topbar-links span { opacity: 0.5; }

        /* HEADER */
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

        /* NAV */
        nav.category-nav { background: white; border-bottom: 1px solid var(--border); }
        .nav-inner { max-width: 1280px; margin: auto; padding: 0 16px; display: flex; overflow-x: auto; }
        .nav-inner a { display: block; padding: 12px 16px; text-decoration: none; color: var(--text); font-size: 13px; font-weight: 500; border-bottom: 2px solid transparent; white-space: nowrap; transition: all 0.2s; }
        .nav-inner a:hover, .nav-inner a.active { color: var(--pink); border-bottom-color: var(--pink); }

        /* CONTAINER */
        .container { max-width: 1280px; margin: auto; padding: 16px; }

        /* BREADCRUMB */
        .breadcrumb { display: flex; align-items: center; gap: 6px; font-size: 12px; color: var(--text-muted); margin-bottom: 16px; }
        .breadcrumb a { color: var(--pink); text-decoration: none; }
        .breadcrumb a:hover { text-decoration: underline; }

        /* CATEGORY HERO */
        .category-hero { border-radius: 14px; overflow: hidden; margin-bottom: 20px; position: relative; height: 160px; display: flex; align-items: center; padding: 0 36px; }
        .cat-0 { background: linear-gradient(120deg, #F297A0 0%, #F9D0CE 60%, #F3EBD8 100%); }
        .cat-1 { background: linear-gradient(120deg, #cce7f5 0%, #E8F4FF 60%, #f0faff 100%); }
        .cat-2 { background: linear-gradient(120deg, #F297A0 0%, #F9D0CE 60%, #fff0ef 100%); }
        .cat-3 { background: linear-gradient(120deg, #d8c4f0 0%, #F3E8FF 60%, #faf5ff 100%); }
        .cat-4 { background: linear-gradient(120deg, #e8d8a0 0%, #FFFCE8 60%, #fffdf0 100%); }
        .hero-content .eyebrow { font-size: 10px; font-weight: 700; letter-spacing: 3px; text-transform: uppercase; opacity: 0.6; margin-bottom: 8px; }
        .hero-content h1 { font-family: 'Fraunces', serif; font-size: 36px; font-weight: 600; line-height: 1.1; color: #3B2A2B; margin-bottom: 6px; }
        .hero-content p { font-size: 13px; color: #7A5A5C; }
        .hero-big-icon { position: absolute; right: 40px; font-size: 90px; opacity: 0.18; user-select: none; }

        /* CATEGORY PILLS */
        .cat-pills { display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 20px; }
        .cat-pill-link { display: inline-flex; align-items: center; gap: 7px; padding: 9px 18px; border-radius: 25px; font-size: 13px; font-weight: 600; text-decoration: none; transition: all 0.2s; border: 2px solid var(--border); background: white; color: var(--text); }
        .cat-pill-link:hover { border-color: var(--pink); color: var(--pink); transform: translateY(-1px); box-shadow: 0 3px 10px rgba(242,151,160,0.2); }
        .cat-pill-link.active { background: var(--pink); color: white; border-color: var(--pink); box-shadow: 0 4px 14px rgba(242,151,160,0.4); }

        /* TOOLBAR */
        .toolbar { background: white; border-radius: var(--card-radius); padding: 12px 16px; display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px; box-shadow: 0 1px 4px rgba(0,0,0,0.06); flex-wrap: wrap; gap: 10px; }
        .toolbar-left { font-size: 13px; color: var(--text-muted); }
        .toolbar-left strong { color: var(--text); }
        .toolbar-right { display: flex; align-items: center; gap: 10px; }
        .sort-select { border: 1px solid var(--border); border-radius: 6px; padding: 6px 12px; font-size: 12px; font-family: inherit; background: white; color: var(--text); cursor: pointer; outline: none; }
        .sort-select:focus { border-color: var(--pink); }
        .btn-add { display: inline-flex; align-items: center; gap: 6px; background: var(--pink); color: white; padding: 7px 16px; border-radius: 5px; font-size: 12px; font-weight: 600; text-decoration: none; transition: background 0.2s; }
        .btn-add:hover { background: #e07880; }

        /* PRODUCT GRID */
        .product-grid { display: grid; grid-template-columns: repeat(5, 1fr); gap: 12px; }

        /* PRODUCT CARD */
        .product-card { background: white; border-radius: var(--card-radius); overflow: hidden; box-shadow: 0 1px 4px rgba(0,0,0,0.06); transition: box-shadow 0.25s, transform 0.25s; cursor: pointer; position: relative; animation: fadeUp 0.4s ease both; }
        @keyframes fadeUp { from { opacity: 0; transform: translateY(16px); } to { opacity: 1; transform: translateY(0); } }
        .product-card:hover { box-shadow: 0 6px 24px rgba(0,0,0,0.13); transform: translateY(-4px); }
        .product-img-wrap { position: relative; background: #FAFAFA; aspect-ratio: 1; overflow: hidden; }
        .product-img-wrap img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.4s; }
        .product-card:hover .product-img-wrap img { transform: scale(1.07); }
        .wishlist-btn { position: absolute; top: 8px; right: 8px; background: rgba(255,255,255,0.92); border: none; border-radius: 50%; width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; cursor: pointer; font-size: 16px; opacity: 0; transition: opacity 0.2s; box-shadow: 0 2px 6px rgba(0,0,0,0.1); }
        .product-card:hover .wishlist-btn { opacity: 1; }
        .badge-label { position: absolute; top: 8px; left: 8px; padding: 3px 8px; border-radius: 3px; font-size: 11px; font-weight: 700; }
        .badge-label.sale { background: var(--pink); color: white; }
        .badge-label.star { background: var(--secondary); color: var(--secondary-text); }
        .product-info { padding: 10px 12px 12px; }
        .product-brand { font-size: 11px; color: var(--text-muted); margin-bottom: 4px; }
        .product-name { font-size: 13px; color: var(--text); line-height: 1.45; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; min-height: 38px; margin-bottom: 6px; }
        .price-original { font-size: 11px; color: #AAAAAA; text-decoration: line-through; }
        .price-main { font-size: 16px; font-weight: 700; color: var(--pink); line-height: 1.2; }
        .discount-tag { display: inline-block; background: #F9D0CE; color: #b5606b; font-size: 11px; font-weight: 700; padding: 2px 5px; border-radius: 3px; margin-left: 4px; }
        .product-meta { display: flex; align-items: center; justify-content: space-between; margin-top: 6px; }
        .rating { font-size: 11px; color: #FAAF00; display: flex; align-items: center; gap: 2px; }
        .rating span { color: var(--text-muted); font-size: 11px; }
        .location-tag { font-size: 11px; color: var(--text-muted); }
        .product-tags { margin-top: 6px; display: flex; gap: 4px; flex-wrap: wrap; }
        .tag-cat { background: #F9D0CE; color: #b5606b; font-size: 10px; font-weight: 700; padding: 2px 6px; border-radius: 3px; }
        .tag-off { background: #DCDFBA; color: #5A5E3A; font-size: 10px; font-weight: 700; padding: 2px 6px; border-radius: 3px; }

        /* ADMIN ACTIONS */
        .admin-actions { display: flex; gap: 6px; padding: 8px 12px 10px; border-top: 1px solid var(--border); opacity: 0; transition: opacity 0.2s; }
        .product-card:hover .admin-actions { opacity: 1; }
        .btn-edit, .btn-delete { flex: 1; text-align: center; padding: 7px 4px; border-radius: 5px; font-size: 12px; font-weight: 600; text-decoration: none; }
        .btn-edit { background: #DCDFBA; color: #5A5E3A; border: 1px solid #c8cba0; }
        .btn-edit:hover { background: #c8cba0; }
        .btn-delete { background: var(--pink); color: white; border: none; }
        .btn-delete:hover { background: #e07880; }

        /* EMPTY STATE */
        .empty-state { text-align: center; padding: 80px 20px; background: white; border-radius: var(--card-radius); grid-column: 1 / -1; }
        .empty-state .empty-icon { font-size: 60px; margin-bottom: 16px; }
        .empty-state h3 { font-size: 18px; font-weight: 700; margin-bottom: 8px; }
        .empty-state p { font-size: 13px; color: var(--text-muted); margin-bottom: 20px; }
        .btn-back { display: inline-flex; align-items: center; gap: 6px; background: var(--pink); color: white; padding: 10px 22px; border-radius: 20px; font-size: 13px; font-weight: 600; text-decoration: none; }
        .btn-back:hover { background: #e07880; }

        /* FOOTER */
        footer { background: white; border-top: 1px solid var(--border); margin-top: 32px; }
        .footer-main { max-width: 1280px; margin: auto; padding: 32px 16px; display: grid; grid-template-columns: 1.5fr 1fr 1fr 1fr; gap: 32px; }
        .footer-brand p { font-size: 12px; color: var(--text-muted); line-height: 1.7; margin-top: 10px; }
        .footer-col h4 { font-size: 13px; font-weight: 700; margin-bottom: 14px; }
        .footer-col a { display: block; font-size: 12px; color: var(--text-muted); text-decoration: none; margin-bottom: 8px; }
        .footer-col a:hover { color: var(--pink); }
        .footer-bottom { border-top: 1px solid var(--border); padding: 14px 16px; text-align: center; font-size: 12px; color: var(--text-muted); max-width: 1280px; margin: auto; }
        .payment-icons { display: flex; gap: 8px; flex-wrap: wrap; margin-top: 12px; }
        .pay-tag { background: #F9D0CE; border: 1px solid #f0b8bc; padding: 4px 10px; border-radius: 4px; font-size: 11px; font-weight: 600; color: #b5606b; }

        @media (max-width: 1024px) { .product-grid { grid-template-columns: repeat(4, 1fr); } }
        @media (max-width: 768px) { .product-grid { grid-template-columns: repeat(2, 1fr); } .footer-main { grid-template-columns: 1fr 1fr; } }
    </style>
</head>
<body>

<!-- TOPBAR -->
<div class="topbar">
    <div class="topbar-inner">
        <div class="topbar-links">
            <a href="#">Bantuan</a>
            <span>|</span>
            <a href="#">🔔 Notifikasi</a>
            <span>|</span>
            <a href="#">Masuk / Daftar</a>
        </div>
    </div>
</div>

<!-- HEADER -->
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
            <a href="#" class="header-action-btn">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
                <span>Wishlist</span>
            </a>
            <a href="#" class="header-action-btn">
                <div style="position:relative;">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
                    <span class="cart-badge">3</span>
                </div>
                <span>Keranjang</span>
            </a>
            <a href="#" class="header-action-btn">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                <span>Akun</span>
            </a>
        </div>
    </div>
</header>

<!-- NAVBAR KATEGORI DINAMIS -->
<nav class="category-nav">
    <div class="nav-inner">
        <a href="index.php">Home</a>
        <a href="index.php#produk">Flash Sale</a>
        <a href="index.php">Best Seller</a>
        <?php foreach ($categories as $cat): ?>
        <a href="category.php?id=<?= $cat['id_category'] ?>"
           class="<?= ($cat['id_category'] == $category_id) ? 'active' : '' ?>">
            <?= htmlspecialchars($cat['category_name']) ?>
        </a>
        <?php endforeach; ?>
    </div>
</nav>

<div class="container">

    <!-- BREADCRUMB -->
    <div class="breadcrumb">
        <a href="index.php">🏠 Home</a>
        <span>›</span>
        <a href="category.php">Semua Kategori</a>
        <?php if ($category_id > 0): ?>
        <span>›</span>
        <span><?= htmlspecialchars($currentCategoryName) ?></span>
        <?php endif; ?>
    </div>

    <!-- HERO BANNER KATEGORI -->
    <div class="category-hero <?= $heroClass ?>">
        <div class="hero-content">
            <div class="eyebrow">✨ Beautify Collection</div>
            <h1><?= htmlspecialchars($currentCategoryName) ?></h1>
            <p><?= $heroDesc ?></p>
        </div>
        <div class="hero-big-icon"><?= $heroIcon ?></div>
    </div>

    <!-- PILLS KATEGORI DINAMIS -->
    <div class="cat-pills">
        <a href="category.php" class="cat-pill-link <?= ($category_id === 0) ? 'active' : '' ?>">
            🌸 Semua
        </a>
        <?php foreach ($categories as $cat):
            $icon = isset($catMeta[$cat['id_category']]) ? $catMeta[$cat['id_category']]['icon'] : '💫';
        ?>
        <a href="category.php?id=<?= $cat['id_category'] ?>"
           class="cat-pill-link <?= ($cat['id_category'] == $category_id) ? 'active' : '' ?>">
            <?= $icon ?> <?= htmlspecialchars($cat['category_name']) ?>
        </a>
        <?php endforeach; ?>
    </div>

    <!-- TOOLBAR -->
    <div class="toolbar">
        <div class="toolbar-left">
            Menampilkan <strong><?= $totalProducts ?> produk</strong>
            <?php if ($category_id > 0): ?>
            dalam kategori <strong><?= htmlspecialchars($currentCategoryName) ?></strong>
            <?php endif; ?>
        </div>
        <div class="toolbar-right">
            <select class="sort-select">
                <option>Terbaru</option>
                <option>Harga Terendah</option>
                <option>Harga Tertinggi</option>
                <option>Paling Populer</option>
            </select>
            <a href="tambah_produk.php" class="btn-add">+ Tambah Produk</a>
        </div>
    </div>

    <!-- GRID PRODUK -->
    <div class="product-grid">
        <?php if ($resultProd->num_rows === 0): ?>
            <div class="empty-state">
                <div class="empty-icon">🔍</div>
                <h3>Belum ada produk di kategori ini</h3>
                <p>Produk untuk <strong><?= htmlspecialchars($currentCategoryName) ?></strong> belum tersedia.</p>
                <a href="category.php" class="btn-back">← Lihat Semua Produk</a>
            </div>
        <?php else:
            $i = 0;
            while ($data = $resultProd->fetch_assoc()):
                $hargaCoret  = $data['price'] * 1.15;
                $disc        = 15;
                $isStarSeller = $data['stock'] > 15;
                $sold        = rand(100, 5000);
                $rating      = number_format(rand(40, 50) / 10, 1);
                $badgeClass  = $isStarSeller ? 'star' : 'sale';
                $badgeText   = $isStarSeller ? '⭐ Star Seller' : '-' . $disc . '%';
                $catId       = (int)$data['category_id'];
                $imgSrc      = $catImages[$catId] ?? $defaultImg;
                $delay       = $i * 0.05;
                $i++;
        ?>
        <div class="product-card" style="animation-delay:<?= $delay ?>s">
            <div class="product-img-wrap">
                <img src="<?= $imgSrc ?>" alt="<?= htmlspecialchars($data['product_name']) ?>" loading="lazy">
                <span class="badge-label <?= $badgeClass ?>"><?= $badgeText ?></span>
                <button class="wishlist-btn">♡</button>
            </div>
            <div class="product-info">
                <div class="product-brand"><?= htmlspecialchars($data['brand']) ?></div>
                <div class="product-name"><?= htmlspecialchars($data['product_name']) ?></div>
                <div class="price-row">
                    <?php if (!$isStarSeller): ?>
                    <div class="price-original">Rp <?= number_format($hargaCoret, 0, ',', '.') ?></div>
                    <?php endif; ?>
                    <div>
                        <span class="price-main">Rp <?= number_format($data['price'], 0, ',', '.') ?></span>
                        <?php if (!$isStarSeller): ?>
                        <span class="discount-tag">-<?= $disc ?>%</span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="product-meta">
                    <div class="rating">★ <?= $rating ?> <span>| <?= number_format($sold, 0, ',', '.') ?> terjual</span></div>
                    <div class="location-tag">Surabaya</div>
                </div>
                <div class="product-tags">
                    <span class="tag-cat"><?= htmlspecialchars($data['category_name']) ?></span>
                    <span class="tag-off">Official</span>
                </div>
            </div>
            <div class="admin-actions">
                <a href="edit_produk.php?id=<?= $data['id_product'] ?>" class="btn-edit">✏ Edit</a>
                <a href="hapus_produk.php?id=<?= $data['id_product'] ?>" onclick="return confirm('Hapus produk ini?')" class="btn-delete">🗑 Hapus</a>
            </div>
        </div>
        <?php endwhile; endif; ?>
    </div>

</div>

<!-- FOOTER -->
<footer>
    <div class="footer-main">
        <div class="footer-brand">
            <a href="index.php" class="logo">Beauti<span>fy</span></a>
            <p>Platform marketplace kecantikan terpercaya di Indonesia. Temukan produk beauty premium dengan harga terbaik.</p>
            <div class="payment-icons">
                <span class="pay-tag">GoPay</span><span class="pay-tag">OVO</span>
                <span class="pay-tag">Dana</span><span class="pay-tag">BCA</span>
                <span class="pay-tag">BRI</span><span class="pay-tag">Mandiri</span>
            </div>
        </div>
        <div class="footer-col">
            <h4>Layanan Pelanggan</h4>
            <a href="#">Pusat Bantuan</a><a href="#">Cara Belanja</a>
            <a href="#">Lacak Pesanan</a><a href="#">Kebijakan Retur</a>
        </div>
        <div class="footer-col">
            <h4>Tentang Beautify</h4>
            <a href="#">Tentang Kami</a><a href="#">Blog Kecantikan</a>
            <a href="#">Karir</a><a href="#">Kebijakan Privasi</a>
        </div>
        <div class="footer-col">
            <h4>Untuk Penjual</h4>
            <a href="#">Daftar Jadi Seller</a><a href="#">Panduan Seller</a>
            <a href="#">Flash Sale Program</a>
        </div>
    </div>
    <div class="footer-bottom">© 2026 Beautify Marketplace. Hak Cipta Dilindungi. | 🇮🇩 Indonesia</div>
</footer>

<script>
    document.querySelectorAll('.wishlist-btn').forEach(btn => {
        btn.addEventListener('click', e => {
            e.stopPropagation();
            btn.textContent = btn.textContent === '♡' ? '♥' : '♡';
            btn.style.color = btn.textContent === '♥' ? '#F297A0' : '';
        });
    });
</script>
</body>
</html>