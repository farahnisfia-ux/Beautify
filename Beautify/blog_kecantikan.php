<?php include 'koneksi.php'; ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blog Kecantikan – Beautify</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;1,400&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        :root {
            --pink: #E8828C;
            --pink-soft: #F5C4C8;
            --pink-bg: #FDF0F1;
            --cream: #FAF6F0;
            --tan: #EDE4D8;
            --text: #2C1F20;
            --muted: #8C7575;
            --border: #E8DDD5;
            --white: #FFFFFF;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'DM Sans', sans-serif; background: var(--cream); color: var(--text); font-size: 15px; line-height: 1.7; }

        .topbar { background: var(--pink); color: rgba(255,255,255,0.9); font-size: 12px; text-align: center; padding: 7px 16px; letter-spacing: 0.5px; }

        header { background: var(--white); border-bottom: 1px solid var(--border); position: sticky; top: 0; z-index: 100; }
        .header-inner { max-width: 1100px; margin: auto; padding: 14px 24px; display: flex; align-items: center; justify-content: space-between; }
        .logo { font-family: 'Playfair Display', serif; font-size: 26px; font-weight: 700; color: var(--text); text-decoration: none; }
        .logo em { font-style: italic; color: var(--pink); }
        .header-nav { display: flex; gap: 28px; }
        .header-nav a { font-size: 13px; font-weight: 500; color: var(--muted); text-decoration: none; transition: color 0.2s; }
        .header-nav a:hover, .header-nav a.active { color: var(--pink); }

        .hero { background: var(--white); padding: 72px 24px 80px; text-align: center; border-bottom: 1px solid var(--border); }
        .hero-eyebrow { display: inline-block; font-size: 11px; font-weight: 500; letter-spacing: 3px; text-transform: uppercase; color: var(--pink); margin-bottom: 20px; }
        .hero h1 { font-family: 'Playfair Display', serif; font-size: 54px; font-weight: 700; line-height: 1.1; color: var(--text); margin-bottom: 16px; letter-spacing: -1px; }
        .hero h1 em { font-style: italic; font-weight: 400; color: var(--pink); }
        .hero p { font-size: 16px; color: var(--muted); max-width: 460px; margin: 0 auto; line-height: 1.75; }

        .wrap { max-width: 1100px; margin: auto; padding: 0 24px; }

        .sec-eyebrow { font-size: 11px; font-weight: 500; letter-spacing: 3px; text-transform: uppercase; color: var(--pink); margin-bottom: 8px; }
        .sec-title { font-family: 'Playfair Display', serif; font-size: 34px; font-weight: 700; line-height: 1.2; margin-bottom: 40px; letter-spacing: -0.5px; }
        .sec-title em { font-style: italic; font-weight: 400; color: var(--pink); }

        /* STEPS */
        .steps-section { background: var(--white); padding: 80px 0; }
        .steps-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 2px; background: var(--border); border-radius: 20px; overflow: hidden; }
        .step-card { background: var(--white); overflow: hidden; }
        .step-img { height: 210px; overflow: hidden; position: relative; }
        .step-img img { width: 100%; height: 100%; object-fit: cover; }
        .step-num { position: absolute; top: 14px; left: 14px; width: 34px; height: 34px; background: var(--pink); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 14px; font-weight: 500; font-family: 'Playfair Display', serif; }
        .step-body { padding: 24px; }
        .step-body h3 { font-size: 15px; font-weight: 500; margin-bottom: 10px; }
        .step-body p { font-size: 13px; color: var(--muted); line-height: 1.75; margin-bottom: 14px; }
        .step-tags { display: flex; flex-wrap: wrap; gap: 6px; }
        .step-tag { background: var(--pink-bg); color: var(--pink); font-size: 10px; font-weight: 500; padding: 3px 10px; border-radius: 20px; letter-spacing: 0.5px; }

        /* GUIDES */
        .guides-section { padding: 80px 0; }
        .guide-block { display: grid; grid-template-columns: 1fr 1fr; gap: 2px; background: var(--border); border-radius: 20px; overflow: hidden; margin-bottom: 3px; }
        .guide-block:last-child { margin-bottom: 0; }
        .guide-block.flip { direction: rtl; }
        .guide-block.flip > * { direction: ltr; }
        .guide-img { height: 360px; overflow: hidden; }
        .guide-img img { width: 100%; height: 100%; object-fit: cover; }
        .guide-body { background: var(--white); padding: 48px 44px; display: flex; flex-direction: column; justify-content: center; }
        .guide-type-badge { display: inline-block; background: var(--pink-bg); color: var(--pink); font-size: 11px; font-weight: 500; letter-spacing: 2px; text-transform: uppercase; padding: 5px 14px; border-radius: 20px; margin-bottom: 18px; width: fit-content; }
        .guide-body h2 { font-family: 'Playfair Display', serif; font-size: 26px; font-weight: 700; line-height: 1.3; margin-bottom: 12px; letter-spacing: -0.3px; }
        .guide-body .intro { font-size: 14px; color: var(--muted); line-height: 1.8; margin-bottom: 24px; }
        .guide-tips { list-style: none; display: flex; flex-direction: column; gap: 12px; }
        .guide-tips li { display: flex; gap: 14px; align-items: flex-start; font-size: 13px; line-height: 1.65; }
        .tip-icon { flex-shrink: 0; width: 28px; height: 28px; background: var(--pink-bg); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 13px; margin-top: 1px; }
        .guide-tips li strong { font-weight: 500; color: var(--text); display: block; margin-bottom: 1px; }
        .guide-tips li span { color: var(--muted); }

        .section-divider { border: none; border-top: 1px solid var(--border); margin: 0; }

        footer { background: var(--white); border-top: 1px solid var(--border); padding: 32px 24px; text-align: center; }
        .footer-inner { max-width: 1100px; margin: auto; }
        .footer-logo { font-family: 'Playfair Display', serif; font-size: 22px; font-weight: 700; color: var(--text); margin-bottom: 6px; }
        .footer-logo em { font-style: italic; color: var(--pink); }
        footer p { font-size: 12px; color: var(--muted); }

        @media (max-width: 900px) {
            .hero h1 { font-size: 38px; }
            .steps-grid { grid-template-columns: repeat(2, 1fr); }
            .guide-block, .guide-block.flip { grid-template-columns: 1fr; direction: ltr; }
            .guide-img { height: 260px; }
            .guide-body { padding: 32px 28px; }
        }
        @media (max-width: 560px) {
            .hero h1 { font-size: 30px; }
            .steps-grid { grid-template-columns: 1fr; }
            .sec-title { font-size: 26px; }
        }
    </style>
</head>
<body>

<div class="topbar">Selamat datang di Beautify — Platform Kecantikan #1 Indonesia</div>

<header>
    <div class="header-inner">
        <a href="index.php" class="logo">Beauti<em>fy</em></a>
        <nav class="header-nav">
            <a href="index.php">Toko</a>
            <a href="blog_kecantikan.php" class="active">Blog</a>
            <a href="tentang_kami.php">Tentang</a>
        </nav>
    </div>
</header>

<div class="hero">
    <div class="hero-eyebrow">Beauty Guide</div>
    <h1>Tutorial & Panduan<br><em>Makeup</em> Lengkap</h1>
    <p>Dari persiapan kulit hingga finishing touch — semua yang kamu butuhkan untuk tampil percaya diri setiap hari.</p>
</div>

<!-- TUTORIAL STEP BY STEP -->
<div class="steps-section">
    <div class="wrap">
        <div class="sec-eyebrow" style="text-align:center;">Step by Step</div>
        <div class="sec-title" style="text-align:center;margin-bottom:40px;">Tutorial <em>Makeup</em> Lengkap</div>
        <div class="steps-grid">

            <div class="step-card">
                <div class="step-img">
                    <img src="https://images.unsplash.com/photo-1631730486784-74757d38e27f?auto=format&fit=crop&w=600&q=80" alt="Skincare">
                    <div class="step-num">1</div>
                </div>
                <div class="step-body">
                    <h3>Persiapan & Skincare</h3>
                    <p>Mulai dengan membersihkan wajah, lalu gunakan toner, serum, dan moisturizer. Kulit yang terhidrasi adalah kunci base makeup yang mulus dan tahan lama.</p>
                    <div class="step-tags">
                        <span class="step-tag">Cleanser</span>
                        <span class="step-tag">Toner</span>
                        <span class="step-tag">Moisturizer</span>
                        <span class="step-tag">SPF</span>
                    </div>
                </div>
            </div>

            <div class="step-card">
                <div class="step-img">
                    <img src="https://images.unsplash.com/photo-1522335789203-aabd1fc54bc9?auto=format&fit=crop&w=600&q=80" alt="Primer Base">
                    <div class="step-num">2</div>
                </div>
                <div class="step-body">
                    <h3>Primer & Base</h3>
                    <p>Gunakan primer untuk memperpanjang daya tahan makeup. Pilih foundation sesuai undertone kulitmu, lalu blend merata menggunakan beauty sponge atau brush.</p>
                    <div class="step-tags">
                        <span class="step-tag">Primer</span>
                        <span class="step-tag">Foundation</span>
                        <span class="step-tag">Concealer</span>
                    </div>
                </div>
            </div>

            <div class="step-card">
                <div class="step-img">
                    <img src="https://images.unsplash.com/photo-1512496015851-a90fb38ba796?auto=format&fit=crop&w=600&q=80" alt="Mata Alis">
                    <div class="step-num">3</div>
                </div>
                <div class="step-body">
                    <h3>Mata & Alis</h3>
                    <p>Bentuk alis terlebih dahulu sebagai frame wajah. Lanjutkan dengan eyeshadow, eyeliner, dan maskara untuk tampilan mata yang lebih ekspresif.</p>
                    <div class="step-tags">
                        <span class="step-tag">Pensil Alis</span>
                        <span class="step-tag">Eyeshadow</span>
                        <span class="step-tag">Maskara</span>
                    </div>
                </div>
            </div>

            <div class="step-card">
                <div class="step-img">
                    <img src="https://images.unsplash.com/photo-1596462502278-27bfdc403348?auto=format&fit=crop&w=600&q=80" alt="Blush Contour">
                    <div class="step-num">4</div>
                </div>
                <div class="step-body">
                    <h3>Blush & Contour</h3>
                    <p>Aplikasikan blush on di area pipi untuk kesan segar. Tambahkan contour di tulang pipi dan hidung untuk dimensi wajah yang lebih terdefinisi.</p>
                    <div class="step-tags">
                        <span class="step-tag">Blush On</span>
                        <span class="step-tag">Contour</span>
                        <span class="step-tag">Highlighter</span>
                    </div>
                </div>
            </div>

            <div class="step-card">
                <div class="step-img">
                    <img src="https://images.unsplash.com/photo-1503236823255-94609f598e71?auto=format&fit=crop&w=600&q=80" alt="Lip Makeup">
                    <div class="step-num">5</div>
                </div>
                <div class="step-body">
                    <h3>Lip Makeup</h3>
                    <p>Sempurnakan tampilan dengan lip liner untuk ketegasan, diikuti lipstik atau lip gloss sesuai mood. Pilih warna yang sesuai dengan keseluruhan look.</p>
                    <div class="step-tags">
                        <span class="step-tag">Lip Liner</span>
                        <span class="step-tag">Lipstik</span>
                        <span class="step-tag">Lip Gloss</span>
                    </div>
                </div>
            </div>

            <div class="step-card">
                <div class="step-img">
                    <img src="https://images.unsplash.com/photo-1616683693504-3ea7e9ad6fec?auto=format&fit=crop&w=600&q=80" alt="Setting">
                    <div class="step-num">6</div>
                </div>
                <div class="step-body">
                    <h3>Setting & Finishing</h3>
                    <p>Kunci seluruh makeup dengan setting powder atau setting spray agar tahan lama. Tambahkan highlighter di titik-titik tertentu untuk efek glowing alami.</p>
                    <div class="step-tags">
                        <span class="step-tag">Setting Powder</span>
                        <span class="step-tag">Setting Spray</span>
                        <span class="step-tag">Highlighter</span>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<hr class="section-divider">

<!-- PANDUAN JENIS KULIT -->
<div class="guides-section">
    <div class="wrap">
        <div class="sec-eyebrow" style="text-align:center;">Panduan Kulit</div>
        <div class="sec-title" style="text-align:center;">Makeup Sesuai<br><em>Jenis Kulitmu</em></div>

        <!-- OILY -->
        <div class="guide-block">
            <div class="guide-img">
                <img src="https://images.unsplash.com/photo-1503236823255-94609f598e71?auto=format&fit=crop&w=800&q=80" alt="Oily Skin">
            </div>
            <div class="guide-body">
                <span class="guide-type-badge">Kulit Berminyak</span>
                <h2>Makeup Tahan Lama untuk Kulit Oily</h2>
                <p class="intro">Kulit berminyak cenderung membuat makeup cepat luntur dan kusam. Kunci utamanya adalah memilih produk berbasis air atau matte finish, dan meminimalkan lapisan produk yang terlalu tebal.</p>
                <ul class="guide-tips">
                    <li>
                        <div class="tip-icon">💧</div>
                        <div><strong>Gunakan mattifying primer</strong><span>Primer jenis ini mengunci minyak berlebih sejak awal, sehingga foundation lebih menyatu dan tahan lama.</span></div>
                    </li>
                    <li>
                        <div class="tip-icon">🧴</div>
                        <div><strong>Pilih foundation oil-free atau water-based</strong><span>Hindari foundation dengan kandungan minyak. Formulasi ringan membuat kulit tidak terasa berat meski dipakai seharian.</span></div>
                    </li>
                    <li>
                        <div class="tip-icon">🪄</div>
                        <div><strong>Finishing dengan loose powder di T-zone</strong><span>Tabur setting powder tipis-tipis di dahi, hidung, dan dagu untuk mengontrol kilap tanpa membuat wajah tampak tepung.</span></div>
                    </li>
                    <li>
                        <div class="tip-icon">✨</div>
                        <div><strong>Simpan blotting paper di pouch</strong><span>Untuk touch-up cepat di siang hari tanpa harus menambah lapisan powder yang bisa menumpuk.</span></div>
                    </li>
                </ul>
            </div>
        </div>

        <!-- DRY -->
        <div class="guide-block flip">
            <div class="guide-img">
                <img src="https://images.unsplash.com/photo-1522335789203-aabd1fc54bc9?auto=format&fit=crop&w=800&q=80" alt="Dry Skin">
            </div>
            <div class="guide-body">
                <span class="guide-type-badge">Kulit Kering</span>
                <h2>Makeup Glowing untuk Kulit Dry & Normal</h2>
                <p class="intro">Kulit kering butuh hidrasi ekstra sebelum makeup diaplikasikan. Pilih produk dengan kandungan lembab agar hasil akhir terlihat segar, bukan cakey atau mengelupas.</p>
                <ul class="guide-tips">
                    <li>
                        <div class="tip-icon">🌿</div>
                        <div><strong>Lapis moisturizer lebih tebal sebelum makeup</strong><span>Tunggu 2–3 menit hingga moisturizer meresap sempurna sebelum mengaplikasikan primer atau foundation.</span></div>
                    </li>
                    <li>
                        <div class="tip-icon">💦</div>
                        <div><strong>Pilih foundation dengan finish dewy atau satin</strong><span>Foundation berbasis serum atau yang mengandung hyaluronic acid memberikan tampilan kulit sehat dan bercahaya.</span></div>
                    </li>
                    <li>
                        <div class="tip-icon">🫧</div>
                        <div><strong>Hindari setting powder terlalu banyak</strong><span>Cukup set di T-zone jika perlu. Terlalu banyak powder membuat kulit kering terlihat flat dan kusam.</span></div>
                    </li>
                    <li>
                        <div class="tip-icon">🌸</div>
                        <div><strong>Gunakan cream blush dan liquid highlighter</strong><span>Formula cream lebih menyatu dengan kulit kering dibanding produk powder, hasil lebih natural.</span></div>
                    </li>
                </ul>
            </div>
        </div>

        <!-- SENSITIVE -->
        <div class="guide-block">
            <div class="guide-img">
                <img src="https://images.unsplash.com/photo-1631730486784-74757d38e27f?auto=format&fit=crop&w=800&q=80" alt="Sensitive Skin">
            </div>
            <div class="guide-body">
                <span class="guide-type-badge">Kulit Sensitif</span>
                <h2>Makeup Aman untuk Kulit Sensitif & Berjerawat</h2>
                <p class="intro">Kulit sensitif mudah bereaksi terhadap bahan kimia tertentu. Prioritaskan formula yang lembut, non-comedogenic, dan sudah dermatologically tested.</p>
                <ul class="guide-tips">
                    <li>
                        <div class="tip-icon">🧪</div>
                        <div><strong>Pilih produk non-comedogenic & fragrance-free</strong><span>Parfum dan bahan pengawet tertentu adalah pemicu iritasi paling umum. Cari label "hypoallergenic" untuk keamanan ekstra.</span></div>
                    </li>
                    <li>
                        <div class="tip-icon">🪷</div>
                        <div><strong>Gunakan mineral foundation atau BB cream ringan</strong><span>Mineral makeup mengandung lebih sedikit bahan kimia dan umumnya lebih bersahabat untuk kulit reaktif.</span></div>
                    </li>
                    <li>
                        <div class="tip-icon">🧼</div>
                        <div><strong>Bersihkan kuas dan beauty sponge secara rutin</strong><span>Alat makeup yang kotor adalah sumber bakteri dan bisa memperparah kondisi kulit sensitif atau berjerawat.</span></div>
                    </li>
                    <li>
                        <div class="tip-icon">🌙</div>
                        <div><strong>Selalu double cleanse sebelum tidur</strong><span>Gunakan micellar water dulu, lalu facial wash. Penting untuk menjaga kulit bersih dan mencegah pori tersumbat.</span></div>
                    </li>
                </ul>
            </div>
        </div>

    </div>
</div>

<footer>
    <div class="footer-inner">
        <div class="footer-logo">Beauti<em>fy</em></div>
        <p>© 2026 Beautify Marketplace. Hak Cipta Dilindungi. Indonesia.</p>
    </div>
</footer>

</body>
</html>