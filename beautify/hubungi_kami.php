<?php include 'koneksi.php'; ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hubungi Kami – Beautify</title>
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
        .search-bar button { background: var(--pink); border: none; color: white; padding: 0 20px; cursor: pointer; display: flex; align-items: center; transition: background 0.2s; }
        .search-bar button:hover { background: #e07880; }
        .header-actions { display: flex; align-items: center; gap: 20px; color: white; }
        .header-action-btn { display: flex; flex-direction: column; align-items: center; gap: 3px; cursor: pointer; color: white; text-decoration: none; font-size: 11px; position: relative; }
        .header-action-btn svg { width: 22px; height: 22px; }
        .cart-badge { position: absolute; top: -6px; right: -8px; background: var(--secondary); color: var(--secondary-text); font-size: 10px; font-weight: 700; width: 18px; height: 18px; border-radius: 50%; display: flex; align-items: center; justify-content: center; }

        /* ─── PROFILE DROPDOWN ─── */
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

        /* ─── NAV ─── */
        nav.category-nav { background: white; border-bottom: 1px solid var(--border); }
        .nav-inner { max-width: 1280px; margin: auto; padding: 0 16px; display: flex; }
        .nav-inner a { display: block; padding: 12px 16px; text-decoration: none; color: var(--text); font-size: 13px; font-weight: 500; border-bottom: 2px solid transparent; white-space: nowrap; transition: all 0.2s; }
        .nav-inner a:hover, .nav-inner a.active { color: var(--pink); border-bottom-color: var(--pink); }

        /* ─── HERO BANNER ─── */
        .contact-hero {
            background: linear-gradient(135deg, #F297A0 0%, #F9D0CE 55%, #F3EBD8 100%);
            padding: 60px 16px 80px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        .contact-hero::before {
            content: '';
            position: absolute;
            inset: 0;
            background: url('https://images.unsplash.com/photo-1596462502278-27bfdc403348?auto=format&fit=crop&w=1400&q=60') center/cover no-repeat;
            opacity: 0.08;
        }
        .contact-hero h1 {
            font-family: 'Fraunces', serif;
            font-size: 48px;
            font-weight: 600;
            color: #3B2A2B;
            margin-bottom: 12px;
            position: relative;
        }
        .contact-hero h1 em { font-style: italic; font-weight: 300; }
        .contact-hero p {
            font-size: 15px;
            color: #7A5A5C;
            max-width: 480px;
            margin: 0 auto;
            line-height: 1.7;
            position: relative;
        }

        /* ─── MAIN CONTAINER ─── */
        .container { max-width: 1100px; margin: auto; padding: 0 16px; }

        /* ─── CONTACT INFO CARDS ─── */
        .info-cards-wrap {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 14px;
            margin-top: -36px;
            margin-bottom: 40px;
            position: relative;
            z-index: 10;
        }
        .info-card {
            background: white;
            border-radius: var(--card-radius);
            padding: 24px 20px;
            text-align: center;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            transition: transform 0.25s, box-shadow 0.25s;
            text-decoration: none;
            color: var(--text);
        }
        .info-card:hover { transform: translateY(-4px); box-shadow: 0 8px 32px rgba(0,0,0,0.13); }
        .info-card-icon {
            width: 52px; height: 52px;
            border-radius: 50%;
            background: var(--pink-light);
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 12px;
            font-size: 22px;
        }
        .info-card h4 { font-size: 14px; font-weight: 700; margin-bottom: 6px; }
        .info-card p { font-size: 13px; color: var(--text-muted); line-height: 1.5; }

        /* ─── MAIN SECTION: MAP + FORM ─── */
        .contact-main {
            display: grid;
            grid-template-columns: 1fr 1.1fr;
            gap: 28px;
            margin-bottom: 60px;
            align-items: start;
        }

        /* MAP SIDE */
        .map-side { display: flex; flex-direction: column; gap: 16px; }
        .map-frame {
            border-radius: var(--card-radius);
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            height: 280px;
        }
        .map-frame iframe { width: 100%; height: 100%; border: none; display: block; }

        .store-info-card {
            background: white;
            border-radius: var(--card-radius);
            padding: 22px 24px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.07);
        }
        .store-info-card h3 {
            font-size: 16px;
            font-weight: 700;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .store-info-row {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            padding: 10px 0;
            border-bottom: 1px solid var(--border);
            font-size: 13px;
        }
        .store-info-row:last-child { border-bottom: none; }
        .store-info-row .si-icon {
            width: 32px; height: 32px;
            border-radius: 8px;
            background: var(--pink-light);
            display: flex; align-items: center; justify-content: center;
            font-size: 15px;
            flex-shrink: 0;
        }
        .store-info-row .si-label { font-size: 11px; color: var(--text-muted); margin-bottom: 2px; }
        .store-info-row .si-val { font-weight: 600; color: var(--text); }

        /* FORM SIDE */
        .form-card {
            background: white;
            border-radius: var(--card-radius);
            padding: 32px 30px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        }
        .form-card h2 {
            font-family: 'Fraunces', serif;
            font-size: 28px;
            font-weight: 600;
            margin-bottom: 6px;
        }
        .form-card h2 em { font-style: italic; font-weight: 300; color: var(--pink); }
        .form-card .form-subtitle { font-size: 13px; color: var(--text-muted); margin-bottom: 24px; line-height: 1.6; }

        .form-group { margin-bottom: 16px; }
        .form-group label { display: block; font-size: 12px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 6px; }
        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 11px 14px;
            border: 1.5px solid var(--border);
            border-radius: 8px;
            font-size: 14px;
            font-family: inherit;
            color: var(--text);
            background: white;
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s;
            resize: none;
        }
        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            border-color: var(--pink);
            box-shadow: 0 0 0 3px rgba(242,151,160,0.15);
        }
        .form-group textarea { height: 120px; }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }

        .btn-send {
            width: 100%;
            background: var(--pink);
            color: white;
            border: none;
            padding: 13px;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 700;
            font-family: inherit;
            cursor: pointer;
            transition: background 0.2s, transform 0.15s;
            margin-top: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        .btn-send:hover { background: #e07880; transform: translateY(-1px); }
        .btn-send:active { transform: translateY(0); }

        /* ─── SUCCESS MESSAGE ─── */
        .success-msg {
            display: none;
            background: #F0FFF4;
            border: 1.5px solid #9AE6B4;
            border-radius: 8px;
            padding: 14px 18px;
            font-size: 13px;
            font-weight: 600;
            color: #276749;
            margin-bottom: 16px;
            align-items: center;
            gap: 10px;
        }
        .success-msg.show { display: flex; }

        /* ─── FOOTER ─── */
        footer { background: white; border-top: 1px solid var(--border); margin-top: 0; }
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
            .contact-main { grid-template-columns: 1fr; }
            .info-cards-wrap { grid-template-columns: repeat(2, 1fr); }
            .footer-main { grid-template-columns: 1fr 1fr; }
        }
        @media (max-width: 560px) {
            .info-cards-wrap { grid-template-columns: 1fr 1fr; }
            .form-row { grid-template-columns: 1fr; }
            .contact-hero h1 { font-size: 32px; }
        }
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
            <a href="#" class="header-action-btn">
                <div style="position:relative;">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/>
                        <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
                    </svg>
                    <span class="cart-badge">0</span>
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
        <a href="kategori.php?cat=flash-sale">Flash Sale</a>
        <a href="kategori.php?cat=best-seller">Best Seller</a>
        <a href="kategori.php?cat=complexion">Complexion</a>
        <a href="kategori.php?cat=lip-products">Lip Products</a>
        <a href="kategori.php?cat=eye-makeup">Eye Makeup</a>
        <a href="kategori.php?cat=eyebrow">Eyebrow</a>
    </div>
</nav>

<!-- ─── HERO ─── -->
<div class="contact-hero">
    <h1>Hubungi <em>Kami</em></h1>
    <p>Ada pertanyaan atau butuh bantuan? Tim kami siap membantu kamu kapan saja.</p>
</div>

<div class="container">

    <!-- ─── INFO CARDS ─── -->
    <div class="info-cards-wrap">
        <a href="tel:+6231123456" class="info-card">
            <div class="info-card-icon">📞</div>
            <h4>Telepon</h4>
            <p>+62 31 123-456<br>Senin–Sabtu, 08.00–17.00</p>
        </a>
        <a href="https://wa.me/6282123456789" target="_blank" class="info-card">
            <div class="info-card-icon">💬</div>
            <h4>WhatsApp</h4>
            <p>082-123-456-789<br>Respon cepat via chat</p>
        </a>
        <a href="mailto:support@beautify.id" class="info-card">
            <div class="info-card-icon">✉️</div>
            <h4>Email</h4>
            <p>support@beautify.id<br>Balasan dalam 1×24 jam</p>
        </a>
        <div class="info-card">
            <div class="info-card-icon">🏬</div>
            <h4>Toko Kami</h4>
            <p>Jl. Raya Darmo No. 12<br>Surabaya, Jawa Timur</p>
        </div>
    </div>

    <!-- ─── MAP + FORM ─── -->
    <div class="contact-main">

        <!-- MAP SIDE -->
        <div class="map-side">
            <div class="map-frame">
                <iframe
                    src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3957.5!2d112.7376!3d-7.2575!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zN8KwMTUnMjcuMCJTIDExMsKwNDQnMTUuNCJF!5e0!3m2!1sid!2sid!4v1234567890"
                    allowfullscreen=""
                    loading="lazy"
                    referrerpolicy="no-referrer-when-downgrade">
                </iframe>
            </div>

            <div class="store-info-card">
                <h3>🏬 Informasi Toko</h3>
                <div class="store-info-row">
                    <div class="si-icon">📍</div>
                    <div>
                        <div class="si-label">Alamat</div>
                        <div class="si-val">Jl. Raya Darmo No. 12, Surabaya</div>
                    </div>
                </div>
                <div class="store-info-row">
                    <div class="si-icon">🕐</div>
                    <div>
                        <div class="si-label">Jam Operasional</div>
                        <div class="si-val">Senin – Sabtu: 08.00 – 17.00 WIB</div>
                    </div>
                </div>
                <div class="store-info-row">
                    <div class="si-icon">📞</div>
                    <div>
                        <div class="si-label">Telepon</div>
                        <div class="si-val">+62 31 123-456</div>
                    </div>
                </div>
                <div class="store-info-row">
                    <div class="si-icon">✉️</div>
                    <div>
                        <div class="si-label">Email</div>
                        <div class="si-val">support@beautify.id</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- FORM SIDE -->
        <div class="form-card">
            <h2>Kirim <em>Pesan</em></h2>
            <p class="form-subtitle">Isi formulir di bawah ini dan tim kami akan segera menghubungi kamu.</p>

            <div class="success-msg" id="successMsg">
                ✅ Pesan kamu berhasil terkirim! Kami akan segera menghubungi kamu.
            </div>

            <form id="contactForm" method="POST" action="hubungi_kami.php">

                <div class="form-row">
                    <div class="form-group">
                        <label>Nama Lengkap</label>
                        <input type="text" name="nama" placeholder="Nama kamu" required>
                    </div>
                    <div class="form-group">
                        <label>Nomor HP / WhatsApp</label>
                        <input type="text" name="telepon" placeholder="08xx-xxxx-xxxx">
                    </div>
                </div>

                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" placeholder="email@contoh.com" required>
                </div>

                <div class="form-group">
                    <label>Topik</label>
                    <select name="topik">
                        <option value="">-- Pilih Topik --</option>
                        <option value="pesanan">Pertanyaan Pesanan</option>
                        <option value="produk">Informasi Produk</option>
                        <option value="retur">Retur & Pengembalian</option>
                        <option value="pembayaran">Pembayaran</option>
                        <option value="lainnya">Lainnya</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Pesan</label>
                    <textarea name="pesan" placeholder="Tulis pesan kamu di sini..." required></textarea>
                </div>

                <button type="submit" class="btn-send">
                    <span>✉️</span> Kirim Pesan Sekarang
                </button>

            </form>
        </div>
    </div>

</div><!-- /container -->

<!-- ─── FOOTER ─── -->
<footer>
    <div class="footer-main">
        <div class="footer-brand">
            <a href="index.php" class="logo" style="color:#F297A0;font-size:24px;font-family:'Fraunces',serif;font-weight:600;text-decoration:none;">
                Beauti<span style="font-style:italic;font-weight:300;">fy</span>
            </a>
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
            <a href="hubungi_kami.php" class="active" style="color:var(--pink);">Hubungi Kami</a>
        </div>
        <div class="footer-col">
            <h4>Tentang Beautify</h4>
            <a href="#">Tentang Kami</a>
            <a href="#">Blog Kecantikan</a>
        </div>
    </div>
    <div class="footer-bottom">
        © 2026 Beautify Marketplace. Hak Cipta Dilindungi. | 🇮🇩 Indonesia
    </div>
</footer>

<script>
// Profile dropdown
function toggleProfile() {
    document.getElementById('profileDropdown').classList.toggle('open');
}
document.addEventListener('click', function(e) {
    const w = document.getElementById('profileWrapper');
    if (w && !w.contains(e.target)) document.getElementById('profileDropdown').classList.remove('open');
});

// Form submit — tampilkan pesan sukses lalu reset
document.getElementById('contactForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const msg = document.getElementById('successMsg');
    msg.classList.add('show');
    this.reset();
    msg.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    setTimeout(() => msg.classList.remove('show'), 5000);
});
</script>

</body>
</html>