<?php include 'koneksi.php'; ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tentang Kami – Beautify</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Fraunces:ital,wght@0,300;0,600;1,300&display=swap" rel="stylesheet">
    <style>
        :root {
            --pink: #F297A0;
            --pink-light: #F9D0CE;
            --bg: #F3EBD8;
            --white: #FFFFFF;
            --text: #3B2A2B;
            --text-muted: #8A7070;
            --border: #EDD9CC;
            --card-radius: 12px;
            --secondary: #DCDFBA;
            --secondary-text: #5A5E3A;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: var(--bg); color: var(--text); font-size: 14px; }

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
        .search-bar button { background: var(--pink); border: none; color: white; padding: 0 20px; cursor: pointer; display: flex; align-items: center; transition: background 0.2s; }
        .search-bar button:hover { background: #e07880; }
        .header-actions { display: flex; align-items: center; gap: 20px; color: white; }
        .header-action-btn { display: flex; flex-direction: column; align-items: center; gap: 3px; cursor: pointer; color: white; text-decoration: none; font-size: 11px; position: relative; }
        .header-action-btn svg { width: 22px; height: 22px; }
        .cart-badge { position: absolute; top: -6px; right: -8px; background: var(--secondary); color: var(--secondary-text); font-size: 10px; font-weight: 700; width: 18px; height: 18px; border-radius: 50%; display: flex; align-items: center; justify-content: center; }
        .profile-wrapper { position: relative; }
        .profile-trigger { display: flex; flex-direction: column; align-items: center; gap: 3px; cursor: pointer; color: white; font-size: 11px; user-select: none; }
        .profile-trigger svg { width: 22px; height: 22px; }
        .profile-dropdown { display: none; position: absolute; top: calc(100% + 14px); right: -10px; background: white; border-radius: 12px; box-shadow: 0 8px 32px rgba(0,0,0,0.18); min-width: 190px; padding: 8px 0; z-index: 300; animation: dropFade 0.18s ease; }
        @keyframes dropFade { from { opacity:0; transform: translateY(-6px); } to { opacity:1; transform: translateY(0); } }
        .profile-dropdown.open { display: block; }
        .profile-dropdown::before { content: ''; position: absolute; top: -6px; right: 22px; width: 12px; height: 12px; background: white; transform: rotate(45deg); box-shadow: -2px -2px 5px rgba(0,0,0,0.06); z-index: -1; }
        .profile-dropdown a { display: flex; align-items: center; gap: 10px; padding: 10px 18px; font-size: 13px; color: var(--text); text-decoration: none; font-weight: 500; transition: background 0.15s; }
        .profile-dropdown a:hover { background: #FFF0F1; color: var(--pink); }
        .profile-dropdown .dropdown-divider { margin: 6px 0; border: none; border-top: 1px solid #F3EBD8; }
        .profile-dropdown .logout-link { color: var(--pink); }

        nav.category-nav { background: white; border-bottom: 1px solid var(--border); }
        .nav-inner { max-width: 1280px; margin: auto; padding: 0 16px; display: flex; }
        .nav-inner a { display: block; padding: 12px 16px; text-decoration: none; color: var(--text); font-size: 13px; font-weight: 500; border-bottom: 2px solid transparent; white-space: nowrap; transition: all 0.2s; }
        .nav-inner a:hover, .nav-inner a.active { color: var(--pink); border-bottom-color: var(--pink); }

        /* ─── HERO ─── */
        .hero {
            position: relative;
            height: 460px;
            overflow: hidden;
            display: flex;
            align-items: center;
        }
        .hero-bg {
            position: absolute;
            inset: 0;
            background: url('https://images.unsplash.com/photo-1522335789203-aabd1fc54bc9?auto=format&fit=crop&w=1400&q=80') center/cover no-repeat;
        }
        .hero-bg::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(100deg, rgba(242,151,160,0.92) 0%, rgba(249,208,206,0.75) 55%, rgba(243,235,216,0.3) 100%);
        }
        .hero-content {
            position: relative;
            z-index: 2;
            max-width: 1100px;
            margin: auto;
            padding: 0 16px;
            width: 100%;
        }
        .hero-content .eyebrow {
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 3px;
            text-transform: uppercase;
            color: white;
            opacity: 0.85;
            margin-bottom: 14px;
        }
        .hero-content h1 {
            font-family: 'Fraunces', serif;
            font-size: 56px;
            font-weight: 600;
            color: white;
            line-height: 1.1;
            margin-bottom: 16px;
            max-width: 560px;
        }
        .hero-content h1 em { font-style: italic; font-weight: 300; }
        .hero-content p {
            font-size: 15px;
            color: rgba(255,255,255,0.9);
            max-width: 440px;
            line-height: 1.75;
            margin-bottom: 28px;
        }
        .btn-hero {
            display: inline-block;
            background: white;
            color: var(--pink);
            padding: 12px 28px;
            border-radius: 24px;
            font-weight: 700;
            font-size: 14px;
            text-decoration: none;
            transition: transform 0.2s, box-shadow 0.2s;
            box-shadow: 0 4px 16px rgba(0,0,0,0.15);
        }
        .btn-hero:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(0,0,0,0.2); }

        /* ─── CONTAINER ─── */
        .container { max-width: 1100px; margin: auto; padding: 0 16px; }
        section { padding: 64px 0; }

        /* ─── TENTANG KAMI ─── */
        .about-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 60px;
            align-items: center;
        }
        .about-text .section-label {
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 3px;
            text-transform: uppercase;
            color: var(--pink);
            margin-bottom: 12px;
        }
        .about-text h2 {
            font-family: 'Fraunces', serif;
            font-size: 38px;
            font-weight: 600;
            line-height: 1.2;
            margin-bottom: 16px;
        }
        .about-text h2 em { font-style: italic; font-weight: 300; color: var(--pink); }
        .about-text p { font-size: 14px; color: var(--text-muted); line-height: 1.8; margin-bottom: 12px; }
        .about-img-wrap {
            position: relative;
        }
        .about-img-wrap img {
            width: 100%;
            border-radius: 16px;
            object-fit: cover;
            height: 380px;
            display: block;
        }
        .about-img-badge {
            position: absolute;
            bottom: -20px;
            left: -20px;
            background: var(--pink);
            color: white;
            padding: 18px 24px;
            border-radius: 12px;
            font-size: 13px;
            font-weight: 700;
            box-shadow: 0 8px 24px rgba(242,151,160,0.4);
            text-align: center;
            line-height: 1.4;
        }
        .about-img-badge span { font-size: 28px; font-weight: 800; display: block; }

        /* ─── STATS ─── */
        .stats-section {
            background: white;
            border-radius: 20px;
            padding: 48px 40px;
            box-shadow: 0 4px 24px rgba(0,0,0,0.07);
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 32px;
            text-align: center;
        }
        .stat-item .stat-num {
            font-family: 'Fraunces', serif;
            font-size: 48px;
            font-weight: 600;
            color: var(--pink);
            line-height: 1;
            margin-bottom: 8px;
        }
        .stat-item .stat-label { font-size: 13px; color: var(--text-muted); font-weight: 500; line-height: 1.4; }

        /* ─── BRAND LOGOS ─── */
        .brands-section { padding: 40px 0; }
        .brands-label { text-align: center; font-size: 12px; font-weight: 700; letter-spacing: 2px; text-transform: uppercase; color: var(--text-muted); margin-bottom: 28px; }
        .brands-row {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 40px;
            flex-wrap: wrap;
        }
        .brand-chip {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 10px 22px;
            border: 1.5px solid var(--border);
            border-radius: 30px;
            font-size: 13px;
            font-weight: 700;
            color: var(--text-muted);
            background: white;
            transition: all 0.2s;
        }
        .brand-chip:hover { border-color: var(--pink); color: var(--pink); }
        .brand-chip .bc-dot { width: 8px; height: 8px; border-radius: 50%; background: var(--pink); flex-shrink: 0; }

        /* ─── KATEGORI PRODUK ─── */
        .cat-section-header {
            display: grid;
            grid-template-columns: auto 1fr;
            gap: 40px;
            align-items: start;
            margin-bottom: 36px;
        }
        .cat-section-header h2 {
            font-family: 'Fraunces', serif;
            font-size: 38px;
            font-weight: 600;
            line-height: 1.2;
        }
        .cat-section-header h2 em { font-style: italic; font-weight: 300; color: var(--pink); }
        .cat-section-header p { font-size: 14px; color: var(--text-muted); line-height: 1.7; padding-top: 8px; }
        .cat-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
        }
        .cat-card {
            border-radius: var(--card-radius);
            overflow: hidden;
            position: relative;
            aspect-ratio: 3/4;
            cursor: pointer;
            text-decoration: none;
        }
        .cat-card img {
            width: 100%; height: 100%;
            object-fit: cover;
            transition: transform 0.4s;
            display: block;
        }
        .cat-card:hover img { transform: scale(1.07); }
        .cat-card-overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(to top, rgba(59,42,43,0.75) 0%, transparent 55%);
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
            padding: 20px 18px;
        }
        .cat-card-overlay h4 { font-size: 15px; font-weight: 700; color: white; margin-bottom: 3px; }
        .cat-card-overlay span { font-size: 11px; color: rgba(255,255,255,0.75); }

        /* ─── CARA KERJA ─── */
        .how-section {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 60px;
            align-items: center;
        }
        .how-img {
            position: relative;
        }
        .how-img img {
            width: 100%;
            border-radius: 16px;
            object-fit: cover;
            height: 440px;
        }
        .how-badge {
            position: absolute;
            top: 50%;
            right: -24px;
            transform: translateY(-50%);
            background: var(--pink);
            color: white;
            padding: 20px 22px;
            border-radius: 14px;
            font-size: 13px;
            font-weight: 700;
            text-align: center;
            box-shadow: 0 8px 28px rgba(242,151,160,0.45);
            width: 130px;
            line-height: 1.4;
        }
        .how-badge span { font-size: 26px; font-weight: 800; display: block; margin-bottom: 4px; }
        .how-text .section-label { font-size: 12px; font-weight: 700; letter-spacing: 3px; text-transform: uppercase; color: var(--pink); margin-bottom: 12px; }
        .how-text h2 { font-family: 'Fraunces', serif; font-size: 38px; font-weight: 600; line-height: 1.2; margin-bottom: 10px; }
        .how-text h2 em { font-style: italic; font-weight: 300; color: var(--pink); }
        .how-text > p { font-size: 14px; color: var(--text-muted); line-height: 1.7; margin-bottom: 32px; }
        .how-steps { display: flex; flex-direction: column; gap: 20px; }
        .how-step {
            display: flex;
            gap: 16px;
            align-items: flex-start;
        }
        .step-icon {
            width: 44px; height: 44px;
            border-radius: 12px;
            background: var(--pink-light);
            display: flex; align-items: center; justify-content: center;
            font-size: 20px;
            flex-shrink: 0;
        }
        .step-text h4 { font-size: 14px; font-weight: 700; margin-bottom: 4px; }
        .step-text p { font-size: 13px; color: var(--text-muted); line-height: 1.6; }

        /* ─── TESTIMONI ─── */
        .testi-section { background: white; border-radius: 24px; padding: 56px 48px; }
        .testi-header { text-align: center; margin-bottom: 44px; }
        .testi-header h2 { font-family: 'Fraunces', serif; font-size: 36px; font-weight: 600; margin-bottom: 8px; }
        .testi-header h2 em { font-style: italic; font-weight: 300; color: var(--pink); }
        .testi-header p { font-size: 14px; color: var(--text-muted); }
        .testi-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; }
        .testi-card {
            background: var(--bg);
            border-radius: 14px;
            padding: 24px 22px;
            position: relative;
        }
        .testi-quote {
            font-size: 40px;
            font-family: 'Fraunces', serif;
            color: var(--pink-light);
            line-height: 1;
            margin-bottom: 10px;
        }
        .testi-card p { font-size: 13px; color: var(--text); line-height: 1.75; margin-bottom: 20px; font-style: italic; }
        .testi-author { display: flex; align-items: center; gap: 12px; }
        .testi-avatar {
            width: 42px; height: 42px;
            border-radius: 50%;
            object-fit: cover;
            flex-shrink: 0;
        }
        .testi-name { font-size: 13px; font-weight: 700; }
        .testi-role { font-size: 11px; color: var(--text-muted); margin-top: 1px; }
        .testi-stars { color: #FAAF00; font-size: 12px; margin-bottom: 2px; }

        /* ─── CTA BANNER ─── */
        .cta-banner {
            background: linear-gradient(135deg, #F297A0 0%, #F9D0CE 60%, #DCDFBA 100%);
            border-radius: 20px;
            padding: 56px 48px;
            display: grid;
            grid-template-columns: 1fr auto;
            align-items: center;
            gap: 32px;
            margin-bottom: 64px;
        }
        .cta-banner h2 { font-family: 'Fraunces', serif; font-size: 36px; font-weight: 600; color: #3B2A2B; margin-bottom: 8px; line-height: 1.2; }
        .cta-banner h2 em { font-style: italic; font-weight: 300; }
        .cta-banner p { font-size: 14px; color: #7A5A5C; line-height: 1.7; max-width: 480px; }
        .btn-cta {
            display: inline-block;
            background: #3B2A2B;
            color: white;
            padding: 14px 32px;
            border-radius: 24px;
            font-weight: 700;
            font-size: 14px;
            text-decoration: none;
            white-space: nowrap;
            transition: transform 0.2s, box-shadow 0.2s;
            box-shadow: 0 4px 16px rgba(0,0,0,0.2);
        }
        .btn-cta:hover { transform: translateY(-2px); box-shadow: 0 8px 28px rgba(0,0,0,0.25); }

        /* ─── FOOTER ─── */
        footer { background: white; border-top: 1px solid var(--border); }
        .footer-main { max-width: 1280px; margin: auto; padding: 32px 16px; display: grid; grid-template-columns: 1.5fr 1fr 1fr; gap: 32px; }
        .footer-brand p { font-size: 12px; color: var(--text-muted); line-height: 1.7; margin-top: 8px; }
        .footer-col h4 { font-size: 13px; font-weight: 700; margin-bottom: 14px; }
        .footer-col a { display: block; font-size: 12px; color: var(--text-muted); text-decoration: none; margin-bottom: 8px; }
        .footer-col a:hover { color: var(--pink); }
        .footer-bottom { border-top: 1px solid var(--border); padding: 14px 16px; text-align: center; font-size: 12px; color: var(--text-muted); max-width: 1280px; margin: auto; }
        .payment-icons { display: flex; gap: 8px; flex-wrap: wrap; margin-top: 12px; }
        .pay-tag { background: #F9D0CE; border: 1px solid #f0b8bc; padding: 4px 10px; border-radius: 4px; font-size: 11px; font-weight: 600; color: #b5606b; }

        /* ─── RESPONSIVE ─── */
        @media (max-width: 900px) {
            .about-grid, .how-section { grid-template-columns: 1fr; }
            .stats-grid { grid-template-columns: repeat(2, 1fr); }
            .cat-grid { grid-template-columns: repeat(2, 1fr); }
            .testi-grid { grid-template-columns: 1fr; }
            .cta-banner { grid-template-columns: 1fr; text-align: center; }
            .hero-content h1 { font-size: 38px; }
            .footer-main { grid-template-columns: 1fr 1fr; }
            .how-badge { display: none; }
        }
        @media (max-width: 560px) {
            .cat-section-header { grid-template-columns: 1fr; gap: 12px; }
            .brands-row { gap: 12px; }
        }
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
            <a href="login.php">Masuk / Daftar</a>
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
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="width:18px;height:18px;"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            </button>
        </div>
        <div class="header-actions">
            <a href="#" class="header-action-btn">
                <div style="position:relative;">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
                    <span class="cart-badge">0</span>
                </div>
                <span>Keranjang</span>
            </a>
            <div class="profile-wrapper" id="profileWrapper">
                <div class="profile-trigger" onclick="toggleProfile()">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
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

<!-- NAV -->
<nav class="category-nav">
    <div class="nav-inner">
        <a href="index.php">Home</a>
        <a href="kategori.php?cat=flash-sale">Flash Sale</a>
        <a href="kategori.php?cat=best-seller">Best Seller</a>
        <a href="kategori.php?cat=complexion">Complexion</a>
        <a href="kategori.php?cat=lip-products">Lip Products</a>
        <a href="kategori.php?cat=eye-makeup">Eye Makeup</a>
        <a href="kategori.php?cat=eyebrow">Eyebrow</a>
    </div>
</nav>

<!-- ─── HERO ─── -->
<div class="hero">
    <div class="hero-bg"></div>
    <div class="hero-content">
        <div class="eyebrow">✨ Tentang Kami</div>
        <h1>Kecantikan <em>Alami</em><br>untuk Semua</h1>
        <p>Kami adalah marketplace kecantikan terpercaya yang menghadirkan produk beauty premium pilihan terbaik untuk menonjolkan kecantikan alami kamu.</p>
        <a href="index.php" class="btn-hero">Mulai Belanja →</a>
    </div>
</div>

<div class="container">

    <!-- ─── TENTANG KAMI ─── -->
    <section>
        <div class="about-grid">
            <div class="about-text">
                <div class="section-label">✦ Tentang Kami</div>
                <h2>Kami Hadir untuk<br><em>Kecantikanmu</em></h2>
                <p>Beautify adalah platform marketplace kecantikan yang berdedikasi membantu setiap perempuan Indonesia menemukan produk beauty terbaik yang sesuai dengan kebutuhan dan kepribadian mereka.</p>
                <p>Kami bekerja sama langsung dengan brand-brand terpercaya, baik lokal maupun internasional, untuk memastikan setiap produk yang tersedia adalah asli, berkualitas, dan aman digunakan.</p>
                <p>Dari lip products, eye makeup, complexion, hingga eyebrow — semua tersedia dalam satu platform yang mudah digunakan.</p>
            </div>
            <div class="about-img-wrap">
                <img src="https://images.unsplash.com/photo-1522335789203-aabd1fc54bc9?auto=format&fit=crop&w=800&q=80" alt="Tentang Beautify">
                <div class="about-img-badge">
                    <span>5+</span>
                    Tahun<br>Melayani
                </div>
            </div>
        </div>
    </section>

    <!-- ─── STATS ─── -->
    <div class="stats-section">
        <div class="stats-grid">
            <div class="stat-item">
                <div class="stat-num">5+</div>
                <div class="stat-label">Tahun di industri<br>kecantikan</div>
            </div>
            <div class="stat-item">
                <div class="stat-num">500+</div>
                <div class="stat-label">Produk tersedia<br>di platform</div>
            </div>
            <div class="stat-item">
                <div class="stat-num">50rb+</div>
                <div class="stat-label">Pelanggan<br>aktif</div>
            </div>
            <div class="stat-item">
                <div class="stat-num">98%</div>
                <div class="stat-label">Pelanggan<br>puas</div>
            </div>
        </div>
    </div>

    <!-- ─── BRAND MITRA ─── -->
    <section class="brands-section">
        <div class="brands-label">Brand Partner Kami</div>
        <div class="brands-row">
            <div class="brand-chip"><span class="bc-dot"></span> Wardah</div>
            <div class="brand-chip"><span class="bc-dot"></span> Maybelline</div>
            <div class="brand-chip"><span class="bc-dot"></span> Implora</div>
            <div class="brand-chip"><span class="bc-dot"></span> Emina</div>
            <div class="brand-chip"><span class="bc-dot"></span> L'Oréal</div>
            <div class="brand-chip"><span class="bc-dot"></span> Mineral Botanica</div>
        </div>
    </section>

    <!-- ─── KATEGORI PRODUK ─── -->
    <section>
        <div class="cat-section-header">
            <div>
                <h2>Katalog<br><em>Produk</em></h2>
            </div>
            <div>
                <p>Temukan ribuan produk kecantikan terbaik dari berbagai kategori — mulai dari makeup sehari-hari hingga produk untuk tampilan spesial.</p>
            </div>
        </div>
        <div class="cat-grid">
            <a href="kategori.php?cat=lip-products" class="cat-card">
                <img src="https://images.unsplash.com/photo-1596462502278-27bfdc403348?auto=format&fit=crop&w=400&q=80" alt="Lip Products">
                <div class="cat-card-overlay">
                    <h4>Lip Products</h4>
                    <span>Lipstik, lip gloss, lip tint & more</span>
                </div>
            </a>
            <a href="kategori.php?cat=eye-makeup" class="cat-card">
                <img src="https://images.unsplash.com/photo-1512496015851-a90fb38ba796?auto=format&fit=crop&w=400&q=80" alt="Eye Makeup">
                <div class="cat-card-overlay">
                    <h4>Eye Makeup</h4>
                    <span>Eyeshadow, eyeliner, mascara</span>
                </div>
            </a>
            <a href="kategori.php?cat=complexion" class="cat-card">
                <img src="https://images.unsplash.com/photo-1631730486784-74757d38e27f?auto=format&fit=crop&w=400&q=80" alt="Complexion">
                <div class="cat-card-overlay">
                    <h4>Complexion</h4>
                    <span>Foundation, concealer, blush</span>
                </div>
            </a>
            <a href="kategori.php?cat=eyebrow" class="cat-card">
                <img src="https://images.unsplash.com/photo-1616683693504-3ea7e9ad6fec?auto=format&fit=crop&w=400&q=80" alt="Eyebrow">
                <div class="cat-card-overlay">
                    <h4>Eyebrow</h4>
                    <span>Pensil alis, pomade, gel alis</span>
                </div>
            </a>
        </div>
    </section>

    <!-- ─── CARA KERJA ─── -->
    <section>
        <div class="how-section">
            <div class="how-img">
                <img src="https://images.unsplash.com/photo-1556228578-8c89e6adf883?auto=format&fit=crop&w=800&q=80" alt="Cara Kerja Beautify">
                <div class="how-badge">
                    <span>100%</span>
                    Produk<br>Terjamin Asli
                </div>
            </div>
            <div class="how-text">
                <div class="section-label">✦ Cara Kami Bekerja</div>
                <h2>Proses yang<br><em>Mudah & Terpercaya</em></h2>
                <p>Kami membuat pengalaman belanja kecantikan kamu semudah dan semenyenangkan mungkin.</p>
                <div class="how-steps">
                    <div class="how-step">
                        <div class="step-icon">🔍</div>
                        <div class="step-text">
                            <h4>Cari & Temukan Produk</h4>
                            <p>Jelajahi ribuan produk dari berbagai kategori dan brand terpercaya sesuai kebutuhanmu.</p>
                        </div>
                    </div>
                    <div class="how-step">
                        <div class="step-icon">🛒</div>
                        <div class="step-text">
                            <h4>Tambah ke Keranjang</h4>
                            <p>Pilih produk favoritmu dan tambahkan ke keranjang belanja dengan mudah.</p>
                        </div>
                    </div>
                    <div class="how-step">
                        <div class="step-icon">💳</div>
                        <div class="step-text">
                            <h4>Bayar dengan Aman</h4>
                            <p>Berbagai metode pembayaran tersedia — GoPay, OVO, Dana, transfer bank, dan lainnya.</p>
                        </div>
                    </div>
                    <div class="how-step">
                        <div class="step-icon">🚚</div>
                        <div class="step-text">
                            <h4>Terima di Pintu Rumah</h4>
                            <p>Produk dikemas rapi dan dikirim cepat ke seluruh Indonesia. Gratis ongkir min. Rp 50.000.</p>
                        </div>
                    </div>
                    <div class="how-step">
                        <div class="step-icon">⭐</div>
                        <div class="step-text">
                            <h4>Berikan Ulasan</h4>
                            <p>Bagikan pengalamanmu untuk membantu pelanggan lain memilih produk terbaik.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ─── TESTIMONI ─── -->
    <section>
        <div class="testi-section">
            <div class="testi-header">
                <h2>Apa Kata <em>Pelanggan</em> Kami</h2>
                <p>Ribuan pelanggan sudah merasakan manfaat belanja di Beautify</p>
            </div>
            <div class="testi-grid">
                <div class="testi-card">
                    <div class="testi-quote">"</div>
                    <div class="testi-stars">★★★★★</div>
                    <p>Produknya lengkap banget dan harganya bersaing. Pengiriman juga cepat, barang datang dalam kondisi sempurna. Udah langganan di sini dari 2 tahun lalu!</p>
                    <div class="testi-author">
                        <img src="https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&w=100&q=80" class="testi-avatar" alt="">
                        <div>
                            <div class="testi-name">Sari Dewi</div>
                            <div class="testi-role">Pelanggan Setia · Surabaya</div>
                        </div>
                    </div>
                </div>
                <div class="testi-card">
                    <div class="testi-quote">"</div>
                    <div class="testi-stars">★★★★★</div>
                    <p>Suka banget sama koleksi lip productsnya! Banyak pilihan warna dan brand. Customer service-nya juga responsif banget waktu aku tanya-tanya soal produk.</p>
                    <div class="testi-author">
                        <img src="https://images.unsplash.com/photo-1438761681033-6461ffad8d80?auto=format&fit=crop&w=100&q=80" class="testi-avatar" alt="">
                        <div>
                            <div class="testi-name">Rina Kusuma</div>
                            <div class="testi-role">Beauty Enthusiast · Jakarta</div>
                        </div>
                    </div>
                </div>
                <div class="testi-card">
                    <div class="testi-quote">"</div>
                    <div class="testi-stars">★★★★★</div>
                    <p>Produknya terjamin original, harga lebih murah dari toko offline. Packaging juga bagus banget, aman sampai di tangan. Recommended banget buat semua!</p>
                    <div class="testi-author">
                        <img src="https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=100&q=80" class="testi-avatar" alt="">
                        <div>
                            <div class="testi-name">Ayu Pratiwi</div>
                            <div class="testi-role">MUA Professional · Bandung</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ─── CTA ─── -->
    <div class="cta-banner">
        <div>
            <h2>Yuk, Mulai <em>Glowing</em><br>Bersama Beautify!</h2>
            <p>Temukan ribuan produk kecantikan terbaik dengan harga terjangkau. Gratis ongkir untuk pembelian pertama kamu!</p>
        </div>
        <a href="index.php" class="btn-cta">Belanja Sekarang →</a>
    </div>

</div><!-- /container -->

<!-- FOOTER -->
<footer>
    <div class="footer-main">
        <div class="footer-brand">
            <a href="index.php" class="logo" style="color:#F297A0;font-size:24px;font-family:'Fraunces',serif;font-weight:600;text-decoration:none;">Beauti<span style="font-style:italic;font-weight:300;">fy</span></a>
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
            <a href="#">Lacak Pesanan</a>
            <a href="hubungi_kami.php">Hubungi Kami</a>
        </div>
        <div class="footer-col">
            <h4>Tentang Beautify</h4>
            <a href="tentang_kami.php" style="color:var(--pink);">Tentang Kami</a>
            <a href="#">Blog Kecantikan</a>
        </div>
    </div>
    <div class="footer-bottom">
        © 2026 Beautify Marketplace. Hak Cipta Dilindungi. | 🇮🇩 Indonesia
    </div>
</footer>

<script>
function toggleProfile() {
    document.getElementById('profileDropdown').classList.toggle('open');
}
document.addEventListener('click', function(e) {
    const w = document.getElementById('profileWrapper');
    if (w && !w.contains(e.target)) document.getElementById('profileDropdown').classList.remove('open');
});
</script>

</body>
</html>