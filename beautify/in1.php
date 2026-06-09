<?php
session_start();
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';

$r = $conn->query("SELECT p.*,c.name as category_name FROM product p JOIN category c ON p.category_id=c.id ORDER BY p.id DESC");
$produk = $r ? $r : null;
$r2 = $conn->query("SELECT * FROM category ORDER BY id");
$kategori_all = $r2 ? $r2 : null;
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Beautify – Premium Beauty Marketplace</title>
<link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&family=Playfair+Display:ital,wght@0,700;1,300&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="style.css">
<style>
/* FIX: dropdown profil bisa diklik */
.prof-wrap {
  position: relative;
  z-index: 9999;
}
.prof-dd {
  position: absolute;
  top: calc(100% + 8px);
  right: 0;
  background: #fff;
  border-radius: 12px;
  box-shadow: 0 8px 32px rgba(0,0,0,.15);
  min-width: 200px;
  z-index: 9999;
  display: none;
  border: 1px solid #f0e8e0;
  overflow: hidden;
}
.prof-dd.open {
  display: block;
}
.prof-dd a {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 11px 16px;
  font-size: 14px;
  font-weight: 600;
  color: #555;
  text-decoration: none;
  transition: background .15s;
  pointer-events: all !important;
  cursor: pointer !important;
}
.prof-dd a:hover {
  background: #fff5f5;
  color: #F297A0;
}
.prof-dd .prof-dd-head {
  padding: 14px 16px 10px;
  border-bottom: 1px solid #f5f0ee;
  background: #fdfaf8;
}
.prof-dd .prof-dd-name {
  font-weight: 800;
  font-size: 14px;
  color: #333;
}
.prof-dd .prof-dd-email {
  font-size: 12px;
  color: #aaa;
  margin-top: 2px;
}
.prof-dd hr {
  margin: 4px 0;
  border: none;
  border-top: 1px solid #f5f0ee;
}
.prof-dd .logout-a {
  color: #c62828 !important;
}
.prof-dd .logout-a:hover {
  background: #fce8e8 !important;
}
.prof-trigger {
  display: flex;
  align-items: center;
  gap: 6px;
  cursor: pointer;
  padding: 6px 10px;
  border-radius: 8px;
  transition: background .15s;
}
.prof-trigger:hover {
  background: rgba(255,255,255,.2);
}
</style>
</head>
<body>

<!-- TOPBAR -->
<div class="topbar">
  <div class="topbar-inner">
    <div class="topbar-links">
      <a href="#"><i class="fas fa-headset"></i> Bantuan</a>
      <span>|</span>
      <?php if(isLoggedIn()&&isAdmin()): ?>
        <a href="/beautify/pages/admin/dashboard.php"><i class="fas fa-cog"></i> Panel Admin</a><span>|</span>
      <?php endif; ?>
      <?php if(isLoggedIn()): ?>
        <a href="/beautify/pages/user/pesanan.php"><i class="fas fa-box"></i> Pesanan Saya</a>
        <span>|</span><a href="/beautify/logout.php">Keluar</a>
      <?php else: ?>
        <a href="/beautify/login.php">Masuk</a><span>|</span><a href="/beautify/register.php">Daftar</a>
      <?php endif; ?>
    </div>
    <div style="font-size:11px;opacity:.8">📍 Pengiriman ke seluruh Indonesia</div>
  </div>
</div>

<!-- HEADER -->
<header class="site-header">
  <div class="header-inner">
    <a href="/beautify/index.php" class="logo">Beauti<em>fy</em></a>
    <div class="search-bar">
      <input type="text" id="searchProd" placeholder="Cari produk, merek, kategori..." oninput="filterProds()">
      <button><i class="fas fa-search"></i></button>
    </div>
    <div class="header-actions">
      <?php if(isLoggedIn()): ?>
      <a href="/beautify/pages/user/wishlist.php" class="hbtn" title="Wishlist">
        <i class="fas fa-heart"></i><span>Wishlist</span>
      </a>
      <?php endif; ?>

      <div class="hbtn" onclick="openCart()" style="cursor:pointer" title="Keranjang">
        <div style="position:relative"><i class="fas fa-shopping-cart" style="font-size:20px"></i>
          <span class="cart-badge" id="cartBadge">0</span></div>
        <span>Keranjang</span>
      </div>

      <!-- PROFILE DROPDOWN -->
      <div class="prof-wrap" id="profWrap">
        <div class="prof-trigger" onclick="toggleProfileDD()">
          <?php $f=fotoSrc(userFoto()); if($f&&isLoggedIn()): ?>
            <img src="<?=$f?>" class="prof-avatar-sm" alt="foto">
          <?php else: ?>
            <i class="fas fa-user-circle" style="font-size:22px"></i>
          <?php endif; ?>
          <span><?=isLoggedIn()?htmlspecialchars(userName()):'Akun'?></span>
          <i class="fas fa-chevron-down" style="font-size:10px;opacity:.6"></i>
        </div>
        <div class="prof-dd" id="profDD">
          <?php if(isLoggedIn()): ?>
            <div class="prof-dd-head">
              <div class="prof-dd-name"><?=htmlspecialchars(userName())?></div>
              <div class="prof-dd-email"><?=htmlspecialchars(userEmail())?></div>
            </div>
            <a href="/beautify/pages/user/profile.php"><i class="fas fa-user-circle"></i> Profil Saya</a>
            <a href="/beautify/pages/user/pesanan.php"><i class="fas fa-box"></i> Pesanan Saya</a>
            <a href="/beautify/pages/user/wishlist.php"><i class="fas fa-heart"></i> Wishlist</a>
            <?php if(isAdmin()): ?>
            <a href="/beautify/pages/admin/dashboard.php"><i class="fas fa-tachometer-alt"></i> Panel Admin</a>
            <?php endif; ?>
            <hr>
            <a href="/beautify/logout.php" class="logout-a"><i class="fas fa-sign-out-alt"></i> Keluar</a>
          <?php else: ?>
            <a href="/beautify/login.php"><i class="fas fa-sign-in-alt"></i> Masuk</a>
            <a href="/beautify/register.php"><i class="fas fa-user-plus"></i> Daftar</a>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</header>

<!-- CATEGORY NAV -->
<nav class="cat-nav">
  <div class="cat-nav-inner">
    <a href="/beautify/in1.php" class="active">Home</a>
    <?php if($kategori_all): $kategori_all->data_seek(0); while($k=$kategori_all->fetch_assoc()): ?>
    <a href="/beautify/in1.php?kat=<?=$k['id']?>"><?=$k['icon'].' '.htmlspecialchars($k['nama'])?></a>
    <?php endwhile; endif; ?>
    <a href="/beautify/pages/user/pesanan.php">Pesanan Saya</a>
  </div>
</nav>

<div class="container">

  <!-- HERO -->
  <div class="hero">
    <div class="hero-content">
      <div class="hero-eyebrow">✨ NEW ARRIVAL 2026</div>
      <h2 class="hero-title">Glow <em>Naturally</em>.<br>Shine Confidently.</h2>
      <p class="hero-sub">Produk premium beauty untuk tampilan terbaik setiap hari</p>
      <a href="produk.php" class="btn-hero">Belanja Sekarang →</a>
    </div>
    <div class="hero-deco"></div>
  </div>

  <div class="promo-banner">
    <div>
      <h2>🌸 Summer Beauty Sale</h2>
      <p>Diskon hingga 50% untuk produk pilihan.</p>
    </div>
    <a href="produk.php" class="btn-primary">Belanja Sekarang</a>
  </div>

  <div class="promo-strip">
    <div class="promo-item"><span class="promo-icon">🚚</span><div><div>Gratis Ongkir</div><div class="promo-sub">Min. pembelian Rp 50.000</div></div></div>
    <div class="promo-item"><span class="promo-icon">🔄</span><div><div>Retur Mudah</div><div class="promo-sub">7 hari retur gratis</div></div></div>
    <div class="promo-item"><span class="promo-icon">🔒</span><div><div>Belanja Aman</div><div class="promo-sub">Uang kembali 100%</div></div></div>
    <div class="promo-item"><span class="promo-icon">🎁</span><div><div>Member Rewards</div><div class="promo-sub">Poin setiap pembelian</div></div></div>
  </div>

  <!-- KATEGORI PILLS -->
  <div class="cat-pills">
    <div class="cat-pills-grid">
      <?php
      $icons=[['cp-pink','💄'],['cp-purple','👁'],['cp-blue','✨'],['cp-green','🌿'],['cp-yellow','🌸']];
      if($kategori_all){ $kategori_all->data_seek(0); $ci=0; while($k=$kategori_all->fetch_assoc()):
        $ic=$icons[$ci%count($icons)]; $ci++; ?>
      <a href="/beautify/index.php?kat=<?=$k['id']?>" class="cat-pill">
        <div class="cat-pill-icon <?=$ic[0]?>"><?=$k['icon']??$ic[1]?></div>
        <span><?=htmlspecialchars($k['nama'])?></span>
      </a>
      <?php endwhile; } ?>
    </div>
  </div>

  <!-- PRODUK -->
  <div class="section-box" id="produk">
    <div class="section-hdr">
      <div class="section-title">⚡ Semua Produk</div>
      <div style="font-size:13px;color:var(--TL);font-weight:700"><?=($produk?$produk->num_rows:0)?> produk tersedia</div>
    </div>
    <div style="margin-bottom:14px;">
      <input type="text" id="searchProd2" class="form-control no-icon" style="max-width:320px" placeholder="🔍 Cari produk..." oninput="filterProds2()">
    </div>
    <?php
    $imgs=['https://images.unsplash.com/photo-1586495777744-4e6232bf2263?w=400&q=80','https://images.unsplash.com/photo-1596462502278-27bfdc403348?w=400&q=80','https://images.unsplash.com/photo-1522335789203-aabd1fc54bc9?w=400&q=80','https://images.unsplash.com/photo-1570194065650-d99fb4bedf0a?w=400&q=80','https://images.unsplash.com/photo-1487412947147-5cebf100ffc2?w=400&q=80'];
    $ii=0;
    ?>
    <div class="product-grid" id="prodGrid">
    <?php if(!$produk||$produk->num_rows===0): ?>
      <div style="grid-column:1/-1;text-align:center;padding:50px;color:var(--TL)">
        <i class="fas fa-box-open" style="font-size:44px;margin-bottom:12px;display:block;opacity:.4"></i>
        <p style="font-weight:700">Belum ada produk</p>
      </div>
    <?php else: while($d=$produk->fetch_assoc()):
      $imgSrc=($d['gambar']&&file_exists(__DIR__.'/assets/img/produk/'.$d['gambar']))?'/beautify/assets/img/produk/'.htmlspecialchars($d['gambar']):$imgs[$ii%count($imgs)];
      $disc=15; $ori=round($d['harga']*1.18); $ii++;
    ?>
    <div class="prod-card" data-nama="<?=strtolower(htmlspecialchars($d['nama']))?>">
      <div class="prod-img-wrap">
        <img src="<?=$imgSrc?>" alt="<?=htmlspecialchars($d['nama'])?>" loading="lazy">
        <span class="prod-badge <?=$d['stok']>15?'pb-star':'pb-sale'?>">
          <?=$d['stok']>15?'⭐ Best Seller':'-'.$disc.'%'?>
        </span>
        <button class="wish-btn" title="Wishlist" onclick="toggleWish(this,<?=$d['id']?>)">🤍</button>
        <button class="cart-qbtn" data-id="<?=$d['id']?>" title="Tambah ke keranjang"
          onclick="addToCart(<?=$d['id']?>,'<?=addslashes(htmlspecialchars($d['nama']))?>',<?=$d['harga']?>,'<?=$imgSrc?>')">
          <i class="fas fa-cart-plus"></i>
        </button>
      </div>
      <div class="prod-info">
        <div class="prod-brand">Beautify Official</div>
        <div class="prod-name"><?=htmlspecialchars($d['nama'])?></div>
        <div>
          <?php if($d['stok']<=15): ?>
          <div class="price-ori">Rp <?=number_format($ori,0,',','.')?></div>
          <?php endif; ?>
          <span class="price-main">Rp <?=number_format($d['harga'],0,',','.')?></span>
          <?php if($d['stok']<=15): ?><span class="disc-tag">-<?=$disc?>%</span><?php endif; ?>
        </div>
        <div class="prod-meta">
          <div class="prod-rating">★ <?=number_format(rand(42,49)/10,1)?> <span>| <?=rand(100,5000)?> terjual</span></div>
          <div class="prod-loc">Surabaya</div>
        </div>
        <span class="prod-cat-tag"><?=htmlspecialchars($d['kat'])?></span>
      </div>
    </div>
    <?php endwhile; endif; ?>
    </div>
  </div>

</div>

<!-- CART -->
<div class="cart-overlay" id="cartOverlay" onclick="closeCart()"></div>
<div class="cart-side" id="cartSide">
  <div class="cart-side-hdr">
    <h3>🛒 Keranjang Belanja</h3>
    <button class="cart-close-btn" onclick="closeCart()">✕</button>
  </div>
  <div class="cart-items" id="cartItems">
    <div class="cart-empty"><i class="fas fa-shopping-bag"></i><p>Keranjang masih kosong</p><small>Yuk tambahkan produk!</small></div>
  </div>
  <div class="cart-foot" id="cartFoot" style="display:none">
    <div class="cart-subtotal"><span id="cartCount">0 produk</span><span>Subtotal</span></div>
    <div class="cart-total"><span>Total</span><span id="cartTotal">Rp 0</span></div>
    <a href="<?=isLoggedIn()?'/beautify/pages/user/checkout.php':'/beautify/login.php?redirect=/beautify/pages/user/checkout.php'?>" class="btn-checkout">Lanjut ke Checkout →</a>
  </div>
</div>

<!-- FOOTER -->
<footer class="site-footer">
  <div class="footer-inner">
    <div>
      <a href="/beautify/index.php" class="logo footer-logo">Beauti<em>fy</em></a>
      <p class="footer-desc">Marketplace kecantikan terpercaya di Indonesia. Produk premium, harga terbaik.</p>
      <div class="pay-tags">
        <span class="pay-tag">GoPay</span><span class="pay-tag">OVO</span><span class="pay-tag">Dana</span>
        <span class="pay-tag">BCA</span><span class="pay-tag">Mandiri</span>
      </div>
    </div>
    <div class="footer-col">
      <h4>Layanan</h4>
      <a href="#">Pusat Bantuan</a><a href="#">Cara Belanja</a><a href="/beautify/pages/user/pesanan.php">Lacak Pesanan</a>
    </div>
    <div class="footer-col">
      <h4>Tentang</h4>
      <a href="#">Tentang Beautify</a><a href="#">Blog Kecantikan</a><a href="#">Karir</a>
    </div>
    <div class="footer-col">
      <h4>Akun</h4>
      <a href="/beautify/login.php">Masuk</a>
      <a href="/beautify/register.php">Daftar</a>
      <a href="/beautify/pages/user/pesanan.php">Pesanan Saya</a>
      <a href="/beautify/pages/user/profile.php">Profil Saya</a>
      <?php if(isAdmin()): ?>
      <a href="/beautify/pages/admin/dashboard.php">Admin Panel</a>
      <?php endif; ?>
    </div>
  </div>
  <div class="footer-bot">© 2026 Beautify Marketplace. Hak Cipta Dilindungi. 🇮🇩</div>
</footer>

<script src="/beautify/assets/js/main.js"></script>
<script>
function filterProds2(){
  const q=document.getElementById('searchProd2').value.toLowerCase();
  document.querySelectorAll('#prodGrid .prod-card').forEach(c=>{
    c.style.display=c.dataset.nama.includes(q)?'':'none';
  });
}
function toggleWish(btn,id){
  <?php if(!isLoggedIn()): ?>
  window.location.href='/beautify/login.php';
  <?php else: ?>
  fetch('/beautify/pages/user/toggle_wish.php?id='+id)
    .then(r=>r.json())
    .then(d=>{
      btn.textContent=d.status==='added'?'❤️':'🤍';
      btn.classList.toggle('wishlisted',d.status==='added');
    });
  <?php endif; ?>
}
updateBadge();
renderCart();

// FIX dropdown profil
function toggleProfileDD() {
  const dd = document.getElementById('profDD');
  if (dd) dd.classList.toggle('open');
}
// Tutup dropdown kalau klik di luar
document.addEventListener('click', function(e) {
  const wrap = document.getElementById('profWrap');
  if (wrap && !wrap.contains(e.target)) {
    const dd = document.getElementById('profDD');
    if (dd) dd.classList.remove('open');
  }
});
</script>
</body>
</html>
