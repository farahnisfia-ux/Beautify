<?php
include 'koneksi.php';

if(isset($_POST['simpan'])){
    $stmt = $conn->prepare("
        INSERT INTO products
        (product_name, brand, category_id, price, stock)
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->bind_param(
        "ssiii",
        $_POST['product_name'],
        $_POST['brand'],
        $_POST['category_id'],
        $_POST['price'],
        $_POST['stock']
    );
    $stmt->execute();
    header("Location: produk.php");
    exit;
}

$kategori = $conn->query("SELECT * FROM categories ORDER BY category_name ASC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Tambah Produk – Beautify</title>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Fraunces:ital,wght@0,300;0,600;1,300&display=swap" rel="stylesheet">
<style>
* { box-sizing: border-box; margin: 0; padding: 0; }

:root {
    --pink:        #F297A0;
    --pink-light:  #F9D0CE;
    --secondary:   #DCDFBA;
    --secondary-dk:#b8bc93;
    --bg:          #F3EBD8;
    --white:       #FFFFFF;
    --text:        #3B2A2B;
    --text-muted:  #8A7070;
    --border:      #EDD9CC;
    --radius:      10px;
}

body {
    font-family: 'Plus Jakarta Sans', sans-serif;
    background: var(--bg);
    color: var(--text);
    font-size: 14px;
    min-height: 100vh;
}

/* ─── TOPBAR ─── */
.topbar {
    background: var(--pink);
    color: rgba(255,255,255,0.85);
    font-size: 12px;
    padding: 6px 0;
}
.topbar-inner {
    max-width: 1280px;
    margin: auto;
    padding: 0 20px;
    display: flex;
    justify-content: space-between;
}
.topbar a { color: rgba(255,255,255,0.85); text-decoration: none; }
.topbar a:hover { color: white; text-decoration: underline; }
.topbar-links { display: flex; gap: 16px; align-items: center; }
.topbar-links span { opacity: 0.4; }

/* ─── HEADER ─── */
header {
    background: var(--pink);
    position: sticky;
    top: 0;
    z-index: 100;
    box-shadow: 0 2px 8px rgba(242,151,160,0.25);
}
.header-inner {
    max-width: 1280px;
    margin: auto;
    padding: 12px 20px;
    display: flex;
    align-items: center;
    gap: 16px;
}
.logo {
    font-family: 'Fraunces', serif;
    font-size: 26px;
    font-weight: 600;
    color: white;
    text-decoration: none;
    letter-spacing: -0.5px;
    white-space: nowrap;
}
.logo em { font-style: italic; font-weight: 300; }
.breadcrumb {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 13px;
    color: rgba(255,255,255,0.75);
    margin-left: 4px;
}
.breadcrumb a { color: rgba(255,255,255,0.75); text-decoration: none; }
.breadcrumb a:hover { color: white; }
.breadcrumb .sep { opacity: 0.5; }
.breadcrumb .current { color: white; font-weight: 600; }

/* ─── PAGE WRAPPER ─── */
.page-wrap {
    max-width: 860px;
    margin: 36px auto;
    padding: 0 20px 60px;
}

/* ─── PAGE TITLE AREA ─── */
.page-heading {
    margin-bottom: 24px;
}
.page-heading h2 {
    font-family: 'Fraunces', serif;
    font-size: 28px;
    font-weight: 600;
    color: var(--text);
    line-height: 1.2;
}
.page-heading p {
    margin-top: 6px;
    color: var(--text-muted);
    font-size: 13px;
}

/* ─── CARD ─── */
.form-card {
    background: var(--white);
    border-radius: 14px;
    box-shadow: 0 2px 16px rgba(180,120,120,0.08);
    overflow: hidden;
}

/* ─── CARD HEADER STRIP ─── */
.card-header-strip {
    background: linear-gradient(135deg, var(--pink) 0%, var(--pink-light) 100%);
    padding: 22px 28px;
    display: flex;
    align-items: center;
    gap: 14px;
}
.card-header-strip .icon-box {
    width: 44px; height: 44px;
    background: rgba(255,255,255,0.25);
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 22px;
    flex-shrink: 0;
}
.card-header-strip h3 {
    font-weight: 700;
    font-size: 16px;
    color: white;
}
.card-header-strip p {
    font-size: 12px;
    color: rgba(255,255,255,0.8);
    margin-top: 2px;
}

/* ─── FORM BODY ─── */
.form-body {
    padding: 28px;
}

/* ─── SECTION DIVIDER ─── */
.form-section {
    margin-bottom: 28px;
}
.form-section-label {
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1.5px;
    color: var(--text-muted);
    margin-bottom: 16px;
    padding-bottom: 8px;
    border-bottom: 1px solid var(--border);
    display: flex;
    align-items: center;
    gap: 8px;
}
.form-section-label span { font-size: 14px; }

/* ─── GRID ─── */
.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
    margin-bottom: 16px;
}
.form-row.single { grid-template-columns: 1fr; }

/* ─── FIELD ─── */
.field {
    display: flex;
    flex-direction: column;
    gap: 6px;
}
.field label {
    font-size: 12px;
    font-weight: 700;
    color: var(--text);
    display: flex;
    align-items: center;
    gap: 4px;
}
.field label .req {
    color: var(--pink);
    font-size: 14px;
    line-height: 1;
}
.field-hint {
    font-size: 11px;
    color: var(--text-muted);
    margin-top: -2px;
}

/* ─── INPUTS ─── */
input[type="text"],
input[type="number"],
select,
textarea {
    font-family: 'Plus Jakarta Sans', sans-serif;
    font-size: 14px;
    color: var(--text);
    background: #FDFAF7;
    border: 1.5px solid var(--border);
    border-radius: 8px;
    padding: 10px 14px;
    outline: none;
    transition: border-color 0.2s, box-shadow 0.2s, background 0.2s;
    width: 100%;
    appearance: none;
    -webkit-appearance: none;
}
input::placeholder, textarea::placeholder { color: #C4A8AA; }
input:focus, select:focus, textarea:focus {
    border-color: var(--pink);
    background: var(--white);
    box-shadow: 0 0 0 3px rgba(242,151,160,0.15);
}

/* Input dengan prefix rupiah */
.input-group {
    position: relative;
}
.input-prefix {
    position: absolute;
    left: 14px; top: 50%;
    transform: translateY(-50%);
    font-size: 13px;
    font-weight: 600;
    color: var(--text-muted);
    pointer-events: none;
    z-index: 1;
}
.input-group input { padding-left: 44px; }

/* Stock dengan suffix */
.input-suffix {
    position: absolute;
    right: 14px; top: 50%;
    transform: translateY(-50%);
    font-size: 12px;
    font-weight: 600;
    color: var(--text-muted);
    pointer-events: none;
}
.input-group input.with-suffix { padding-right: 56px; }

/* Custom select arrow */
.select-wrap {
    position: relative;
}
.select-wrap select { padding-right: 36px; }
.select-wrap::after {
    content: '▾';
    position: absolute;
    right: 14px; top: 50%;
    transform: translateY(-50%);
    font-size: 13px;
    color: var(--text-muted);
    pointer-events: none;
}

/* ─── STOCK INDICATOR ─── */
.stock-indicator {
    display: flex;
    gap: 8px;
    margin-top: 10px;
}
.stock-btn {
    background: #FDFAF7;
    border: 1.5px solid var(--border);
    border-radius: 7px;
    padding: 6px 14px;
    font-size: 12px;
    font-weight: 600;
    color: var(--text-muted);
    cursor: pointer;
    transition: all 0.15s;
}
.stock-btn:hover {
    background: var(--pink-light);
    border-color: var(--pink);
    color: var(--pink);
}

/* ─── PRICE FORMAT PREVIEW ─── */
.price-preview {
    margin-top: 6px;
    font-size: 12px;
    color: var(--text-muted);
    min-height: 18px;
}
.price-preview strong { color: var(--pink); font-weight: 700; }

/* ─── FORM FOOTER ─── */
.form-footer {
    padding: 20px 28px;
    border-top: 1px solid var(--border);
    background: #FDFAF7;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
}
.footer-note {
    font-size: 12px;
    color: var(--text-muted);
    display: flex;
    align-items: center;
    gap: 6px;
}
.form-actions {
    display: flex;
    gap: 10px;
}
.btn-back {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 10px 22px;
    background: white;
    border: 1.5px solid var(--border);
    border-radius: 8px;
    font-family: 'Plus Jakarta Sans', sans-serif;
    font-size: 13px;
    font-weight: 600;
    color: var(--text-muted);
    text-decoration: none;
    cursor: pointer;
    transition: all 0.2s;
}
.btn-back:hover {
    background: var(--bg);
    border-color: #d4bfb0;
    color: var(--text);
}
.btn-save {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 28px;
    background: var(--pink);
    border: none;
    border-radius: 8px;
    font-family: 'Plus Jakarta Sans', sans-serif;
    font-size: 13px;
    font-weight: 700;
    color: white;
    cursor: pointer;
    transition: all 0.2s;
    box-shadow: 0 3px 10px rgba(242,151,160,0.35);
}
.btn-save:hover {
    background: #e07880;
    box-shadow: 0 4px 14px rgba(242,151,160,0.45);
    transform: translateY(-1px);
}
.btn-save:active { transform: translateY(0); }

/* ─── SIDEBAR TIPS ─── */
.page-layout {
    display: grid;
    grid-template-columns: 1fr 260px;
    gap: 20px;
    align-items: start;
}
.tips-card {
    background: white;
    border-radius: 14px;
    box-shadow: 0 2px 16px rgba(180,120,120,0.08);
    overflow: hidden;
}
.tips-header {
    background: var(--secondary);
    padding: 16px 20px;
    display: flex;
    align-items: center;
    gap: 10px;
}
.tips-header h4 {
    font-weight: 700;
    font-size: 13px;
    color: #5A5E3A;
}
.tips-body {
    padding: 16px 20px;
}
.tip-item {
    display: flex;
    gap: 10px;
    margin-bottom: 16px;
}
.tip-item:last-child { margin-bottom: 0; }
.tip-dot {
    width: 28px; height: 28px;
    background: var(--bg);
    border-radius: 7px;
    display: flex; align-items: center; justify-content: center;
    font-size: 14px;
    flex-shrink: 0;
    margin-top: 1px;
}
.tip-text strong {
    display: block;
    font-size: 12px;
    font-weight: 700;
    color: var(--text);
    margin-bottom: 2px;
}
.tip-text p {
    font-size: 11px;
    color: var(--text-muted);
    line-height: 1.55;
}

/* ─── RESPONSIVE ─── */
@media (max-width: 768px) {
    .page-layout { grid-template-columns: 1fr; }
    .form-row { grid-template-columns: 1fr; }
    .form-footer { flex-direction: column; align-items: stretch; }
    .form-actions { flex-direction: column; }
    .btn-save, .btn-back { justify-content: center; }
    .tips-card { order: -1; }
}
</style>
</head>
<body>

<!-- ─── TOPBAR ─── -->
<div class="topbar">
    <div class="topbar-inner">
        <div class="topbar-links">
            <a href="#">Jual di Beautify</a>
            <span>|</span>
            <a href="#">Unduh Aplikasi</a>
        </div>
        <div class="topbar-links">
            <a href="#">Bantuan</a>
            <span>|</span>
            <a href="#">Masuk / Daftar</a>
        </div>
    </div>
</div>

<!-- ─── HEADER ─── -->
<header>
    <div class="header-inner">
        <a href="produk.php" class="logo">Beauti<em>fy</em></a>
        <div class="breadcrumb">
            <span class="sep">/</span>
            <a href="produk.php">Dashboard</a>
            <span class="sep">/</span>
            <span class="current">Tambah Produk</span>
        </div>
    </div>
</header>

<!-- ─── PAGE ─── -->
<div class="page-wrap">

    <div class="page-heading">
        <h2>Tambah Produk Baru</h2>
        <p>Isi informasi produk dengan lengkap dan akurat untuk memaksimalkan penjualan</p>
    </div>

    <div class="page-layout">

        <!-- ─── FORM CARD ─── -->
        <div class="form-card">

            <div class="card-header-strip">
                <div class="icon-box">📦</div>
                <div>
                    <h3>Informasi Produk</h3>
                    <p>Semua field bertanda * wajib diisi</p>
                </div>
            </div>

            <form method="POST">
            <div class="form-body">

                <!-- SECTION: Identitas Produk -->
                <div class="form-section">
                    <div class="form-section-label">
                        <span>✦</span> Identitas Produk
                    </div>

                    <div class="form-row single">
                        <div class="field">
                            <label>Nama Produk <span class="req">*</span></label>
                            <input
                                type="text"
                                name="product_name"
                                required
                                placeholder="Contoh: Maybelline Fit Me Foundation"
                                maxlength="200"
                            >
                            <span class="field-hint">Maks. 200 karakter. Gunakan nama yang deskriptif dan mudah dicari.</span>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="field">
                            <label>Brand / Merek <span class="req">*</span></label>
                            <input
                                type="text"
                                name="brand"
                                required
                                placeholder="Contoh: Maybelline"
                            >
                        </div>
                        <div class="field">
                            <label>Kategori <span class="req">*</span></label>
                            <div class="select-wrap">
                                <select name="category_id" required>
                                    <option value="">Pilih Kategori</option>
                                    <?php while($k = $kategori->fetch_assoc()): ?>
                                        <option value="<?= $k['id_category']; ?>">
                                            <?= htmlspecialchars($k['category_name']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SECTION: Harga & Stok -->
                <div class="form-section">
                    <div class="form-section-label">
                        <span>💰</span> Harga & Stok
                    </div>

                    <div class="form-row">
                        <div class="field">
                            <label>Harga Jual <span class="req">*</span></label>
                            <div class="input-group">
                                <span class="input-prefix">Rp</span>
                                <input
                                    type="number"
                                    name="price"
                                    id="price-input"
                                    required
                                    placeholder="150000"
                                    min="0"
                                    oninput="updatePricePreview(this.value)"
                                >
                            </div>
                            <div class="price-preview" id="price-preview"></div>
                        </div>
                        <div class="field">
                            <label>Stok Tersedia <span class="req">*</span></label>
                            <div class="input-group">
                                <input
                                    type="number"
                                    name="stock"
                                    id="stock-input"
                                    required
                                    placeholder="20"
                                    min="0"
                                    class="with-suffix"
                                >
                                <span class="input-suffix">unit</span>
                            </div>
                            <div class="stock-indicator">
                                <button type="button" class="stock-btn" onclick="setStock(10)">+10</button>
                                <button type="button" class="stock-btn" onclick="setStock(50)">+50</button>
                                <button type="button" class="stock-btn" onclick="setStock(100)">+100</button>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- ─── FORM FOOTER ─── -->
            <div class="form-footer">
                <div class="footer-note">
                    🔒 Data tersimpan dengan aman
                </div>
                <div class="form-actions">
                    <a href="produk.php" class="btn-back">
                        ← Kembali
                    </a>
                    <button type="submit" name="simpan" class="btn-save">
                        Simpan Produk →
                    </button>
                </div>
            </div>
            </form>

        </div><!-- /form-card -->

        <!-- ─── TIPS SIDEBAR ─── -->
        <div>
            <div class="tips-card">
                <div class="tips-header">
                    <span style="font-size:16px;">💡</span>
                    <h4>Tips Penjualan</h4>
                </div>
                <div class="tips-body">
                    <div class="tip-item">
                        <div class="tip-dot">📝</div>
                        <div class="tip-text">
                            <strong>Nama Produk Jelas</strong>
                            <p>Sertakan nama brand, tipe, dan shade/warna agar mudah ditemukan pembeli.</p>
                        </div>
                    </div>
                    <div class="tip-item">
                        <div class="tip-dot">💲</div>
                        <div class="tip-text">
                            <strong>Harga Kompetitif</strong>
                            <p>Riset harga pasar sebelum menentukan harga jual untuk meningkatkan konversi.</p>
                        </div>
                    </div>
                    <div class="tip-item">
                        <div class="tip-dot">📦</div>
                        <div class="tip-text">
                            <strong>Stok Akurat</strong>
                            <p>Stok di atas 15 unit otomatis mendapat badge <strong>⭐ Star Seller</strong> di halaman produk.</p>
                        </div>
                    </div>
                    <div class="tip-item">
                        <div class="tip-dot">🏷</div>
                        <div class="tip-text">
                            <strong>Kategori Tepat</strong>
                            <p>Pilih kategori yang paling sesuai agar produk muncul di filter yang relevan.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Status badge preview -->
            <div style="background:white;border-radius:14px;box-shadow:0 2px 16px rgba(180,120,120,0.08);padding:18px 20px;margin-top:16px;">
                <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:1.5px;color:var(--text-muted);margin-bottom:14px;">Preview Badge</div>
                <div id="badge-preview" style="display:flex;gap:8px;flex-wrap:wrap;">
                    <span style="background:#F9D0CE;color:#b5606b;font-size:12px;font-weight:700;padding:4px 12px;border-radius:4px;">-15% OFF</span>
                </div>
                <p style="font-size:11px;color:var(--text-muted);margin-top:10px;" id="badge-desc">Stok ≤ 15: badge diskon ditampilkan</p>
            </div>
        </div>

    </div><!-- /page-layout -->
</div><!-- /page-wrap -->

<script>
    // ─── FORMAT HARGA ───
    function updatePricePreview(val) {
        const el = document.getElementById('price-preview');
        if (!val || val <= 0) { el.innerHTML = ''; return; }
        const formatted = Number(val).toLocaleString('id-ID');
        el.innerHTML = 'Rp <strong>' + formatted + '</strong>';
    }

    // ─── QUICK STOCK BUTTONS ───
    function setStock(n) {
        const el = document.getElementById('stock-input');
        const cur = parseInt(el.value) || 0;
        el.value = cur + n;
        updateBadgePreview(el.value);
    }

    // ─── BADGE PREVIEW ───
    document.getElementById('stock-input').addEventListener('input', function() {
        updateBadgePreview(this.value);
    });

    function updateBadgePreview(val) {
        const n = parseInt(val) || 0;
        const badge = document.getElementById('badge-preview');
        const desc  = document.getElementById('badge-desc');
        if (n > 15) {
            badge.innerHTML = '<span style="background:#DCDFBA;color:#5A5E3A;font-size:12px;font-weight:700;padding:4px 12px;border-radius:4px;">⭐ Star Seller</span>';
            desc.textContent = 'Stok > 15: badge Star Seller aktif!';
        } else {
            badge.innerHTML = '<span style="background:#F9D0CE;color:#b5606b;font-size:12px;font-weight:700;padding:4px 12px;border-radius:4px;">-15% OFF</span>';
            desc.textContent = 'Stok ≤ 15: badge diskon ditampilkan';
        }
    }
</script>

</body>
</html>