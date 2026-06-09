<?php
session_start();
require_once dirname(__DIR__, 2) . '/includes/db.php';
require_once dirname(__DIR__, 2) . '/includes/auth.php';
requireLogin();

$msg = '';
$err = '';

// Ambil data user
$user = $conn->query("SELECT * FROM users WHERE id=" . userId())->fetch_assoc();

// Statistik user
function safeCount2($conn, $sql) {
    $r = $conn->query($sql);
    if (!$r) return 0;
    $row = $r->fetch_assoc();
    return $row ? (int)array_values($row)[0] : 0;
}
$stat_pesanan = safeCount2($conn, "SELECT COUNT(*) c FROM pesanan WHERE user_id=" . userId());
$stat_selesai = safeCount2($conn, "SELECT COUNT(*) c FROM pesanan WHERE user_id=" . userId() . " AND status='selesai'");
$r = $conn->query("SELECT COALESCE(SUM(total_harga),0) c FROM pesanan WHERE user_id=" . userId() . " AND status!='dibatalkan'");
$stat_spend = $r ? (float)$r->fetch_assoc()['c'] : 0;

// Riwayat pesanan
$rp = $conn->query("SELECT * FROM pesanan WHERE user_id=" . userId() . " ORDER BY created_at DESC LIMIT 10");
$riwayat = ($rp && $rp !== false) ? $rp : null;

// Handle POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {

    if ($_POST['action'] === 'update_profil') {
        $nama          = trim($_POST['nama'] ?? '');
        $email         = trim($_POST['email'] ?? '');
        $telepon       = trim($_POST['telepon'] ?? '');
        $alamat        = trim($_POST['alamat'] ?? '');
        $tgl_lahir     = trim($_POST['tgl_lahir'] ?? '');
        $jenis_kelamin = trim($_POST['jenis_kelamin'] ?? '');
        $foto          = $user['foto'] ?? '';

        // Upload foto
        if (!empty($_FILES['foto']['name']) && $_FILES['foto']['error'] === 0) {
            $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg','jpeg','png','webp'])) {

                // Path absolut agar tidak bergantung pada DOCUMENT_ROOT
                $upload_dir = 'C:/xampp/htdocs/beautify/assets/img/profil/';

                // Buat folder jika belum ada
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }

                // Hapus foto lama jika ada
                if (!empty($foto) && file_exists($upload_dir . $foto)) {
                    unlink($upload_dir . $foto);
                }

                $nama_baru = 'profil_' . userId() . '_' . time() . '.' . $ext;
                $dest      = $upload_dir . $nama_baru;

                if (move_uploaded_file($_FILES['foto']['tmp_name'], $dest)) {
                    $foto = $nama_baru; // ✅ simpan nama file baru ke DB
                } else {
                    $err = 'Gagal menyimpan foto. Cek permission folder.';
                }
            } else {
                $err = 'Format foto tidak didukung. Gunakan jpg, jpeg, png, atau webp.';
            }
        }

        // Cek email duplikat
        $cek = $conn->query("SELECT id FROM users WHERE email='" . mysqli_real_escape_string($conn, $email) . "' AND id!=" . userId());
        if ($cek && $cek->fetch_assoc()) {
            $err = 'Email sudah digunakan akun lain.';
        } else {
            $tgl_sql = $tgl_lahir ? "'" . $tgl_lahir . "'" : 'NULL';
            $conn->query("UPDATE users SET
                nama='"          . mysqli_real_escape_string($conn, $nama)          . "',
                email='"         . mysqli_real_escape_string($conn, $email)         . "',
                telepon='"       . mysqli_real_escape_string($conn, $telepon)       . "',
                alamat='"        . mysqli_real_escape_string($conn, $alamat)        . "',
                tgl_lahir=$tgl_sql,
                jenis_kelamin='" . mysqli_real_escape_string($conn, $jenis_kelamin) . "',
                foto='"          . mysqli_real_escape_string($conn, $foto)          . "'
                WHERE id=" . userId());
            $_SESSION['user_nama']  = $nama;
            $_SESSION['user_email'] = $email;
            $_SESSION['user_foto']  = $foto;
            $user = $conn->query("SELECT * FROM users WHERE id=" . userId())->fetch_assoc();
            $msg = 'Profil berhasil diupdate!';
        }

    } elseif ($_POST['action'] === 'ganti_password') {
        $lama    = $_POST['password_lama']    ?? '';
        $baru    = $_POST['password_baru']    ?? '';
        $konfirm = $_POST['password_konfirm'] ?? '';
        if (!password_verify($lama, $user['password'])) {
            $err = 'Password lama tidak sesuai.';
        } elseif (strlen($baru) < 6) {
            $err = 'Password baru minimal 6 karakter.';
        } elseif ($baru !== $konfirm) {
            $err = 'Konfirmasi password tidak cocok.';
        } else {
            $hash = password_hash($baru, PASSWORD_DEFAULT);
            $conn->query("UPDATE users SET password='$hash' WHERE id=" . userId());
            $msg = 'Password berhasil diubah!';
        }
    }
}

$tab = $_GET['tab'] ?? 'profil';
$fotoSrc = fotoSrc($user['foto'] ?? '');
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Profil Saya – Beautify</title>
<link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&family=Playfair+Display:ital,wght@0,700;1,300&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="/beautify/assets/css/style.css">
<style>
:root{--pk:#F297A0;--pk-light:#F9D0CE;--green:#B6BB79;--cream:#F3EBD8}
body{background:#faf7f4}
.page-wrap{max-width:980px;margin:0 auto;padding:28px 16px}

/* NOTIF */
.msg-box{padding:13px 18px;border-radius:10px;margin-bottom:20px;font-weight:700;font-size:13px;background:#e8f5e9;color:#2e7d32;border:1px solid #c8e6c9;display:flex;align-items:center;gap:8px}
.err-box{padding:13px 18px;border-radius:10px;margin-bottom:20px;font-weight:700;font-size:13px;background:#fce8e8;color:#c62828;border:1px solid #fccfcf;display:flex;align-items:center;gap:8px}

/* HERO */
.profile-hero{background:linear-gradient(135deg,var(--pk-light) 0%,var(--cream) 100%);border-radius:20px;padding:28px 32px;margin-bottom:24px;display:flex;align-items:center;gap:24px;position:relative;overflow:hidden}
.profile-hero::after{content:'💄';position:absolute;right:32px;top:50%;transform:translateY(-50%);font-size:80px;opacity:.12;pointer-events:none}
.avatar-wrap{position:relative;flex-shrink:0}
.avatar-img{width:100px;height:100px;border-radius:50%;object-fit:cover;border:4px solid #fff;box-shadow:0 4px 16px rgba(242,151,160,.3)}
.avatar-empty{width:100px;height:100px;border-radius:50%;background:linear-gradient(135deg,var(--pk),#e8848e);display:flex;align-items:center;justify-content:center;font-size:40px;color:#fff;border:4px solid #fff;box-shadow:0 4px 16px rgba(242,151,160,.3)}
.avatar-edit{position:absolute;bottom:4px;right:4px;width:30px;height:30px;background:var(--pk);border-radius:50%;display:flex;align-items:center;justify-content:center;cursor:pointer;color:#fff;font-size:12px;border:2px solid #fff;box-shadow:0 2px 8px rgba(0,0,0,.15)}
.hero-name{font-size:24px;font-weight:800;color:#333;font-family:'Playfair Display',serif}
.hero-email{font-size:13px;color:#888;margin-top:3px}
.hero-stats{display:flex;gap:16px;margin-top:14px;flex-wrap:wrap}
.hstat{background:rgba(255,255,255,.65);border-radius:12px;padding:10px 18px;text-align:center;backdrop-filter:blur(4px)}
.hstat-val{font-size:20px;font-weight:800;color:#333}
.hstat-label{font-size:11px;color:#888;font-weight:600;margin-top:2px}

/* LAYOUT */
.profile-layout{display:grid;grid-template-columns:200px 1fr;gap:20px;align-items:start}
.side-card{background:#fff;border-radius:14px;border:1px solid #f0e8e0;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,.04)}
.side-card a{display:flex;align-items:center;gap:10px;padding:13px 16px;font-size:14px;font-weight:600;color:#666;text-decoration:none;border-left:3px solid transparent;transition:all .2s}
.side-card a:hover,.side-card a.active{color:var(--pk);background:#fff5f5;border-left-color:var(--pk)}
.side-card a i{width:18px;text-align:center}
.side-card a.danger{color:#c62828}
.side-card a.danger:hover{background:#fce8e8;border-left-color:#c62828}

/* FORM */
.form-card{background:#fff;border-radius:14px;border:1px solid #f0e8e0;padding:24px;margin-bottom:20px;box-shadow:0 2px 8px rgba(0,0,0,.04)}
.form-card-hdr{display:flex;align-items:center;gap:8px;font-size:15px;font-weight:800;color:#333;margin-bottom:20px;padding-bottom:14px;border-bottom:1px solid #f0e8e0}
.form-card-hdr i{color:var(--pk);font-size:16px}
.form-row{display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px}
.form-row.one{grid-template-columns:1fr}
.fg{display:flex;flex-direction:column;gap:6px}
.fg.full{grid-column:1/-1}
.fg label{font-size:11px;font-weight:800;color:#777;text-transform:uppercase;letter-spacing:.6px}
.fg input,.fg select,.fg textarea{padding:10px 14px;border:1.5px solid #e8e0dc;border-radius:8px;font-size:14px;font-family:Nunito;color:#333;outline:none;background:#faf7f4;transition:border .2s}
.fg input:focus,.fg select:focus,.fg textarea:focus{border-color:var(--pk);background:#fff}
.fg textarea{resize:vertical;min-height:80px}
.fg input[disabled]{background:#f5f0ee;color:#bbb;cursor:not-allowed}
.pw-wrap{position:relative}
.pw-eye{position:absolute;right:12px;top:50%;transform:translateY(-50%);cursor:pointer;color:#bbb;font-size:14px}
.btn-save{background:var(--pk);color:#fff;border:none;padding:11px 26px;border-radius:8px;font-size:14px;font-weight:700;cursor:pointer;display:inline-flex;align-items:center;gap:8px;transition:background .2s}
.btn-save:hover{background:#e8848e}

/* AVATAR PREVIEW */
#avatarPreview{display:block}

/* PESANAN TABLE */
.tbl{width:100%;border-collapse:collapse;font-size:13px}
.tbl th{padding:10px 16px;text-align:left;font-size:11px;font-weight:800;color:#999;text-transform:uppercase;letter-spacing:.5px;border-bottom:1px solid #f0e8e0;background:#fdfaf8}
.tbl td{padding:12px 16px;border-bottom:1px solid #f8f5f3;color:#444;vertical-align:middle}
.tbl tr:last-child td{border-bottom:none}
.tbl tr:hover td{background:#fffaf9}
.badge{display:inline-flex;padding:3px 9px;border-radius:20px;font-size:11px;font-weight:700}
.bp{background:#fff8e1;color:#d97706}
.bd{background:#e8f4fd;color:#3a8cc7}
.bk{background:#f3f5e8;color:#5a6e1a}
.bs{background:#e8f5e9;color:#2e7d32}
.bb{background:#fce8e8;color:#c62828}

/* RESPONSIVE */
@media(max-width:680px){
  .profile-layout{grid-template-columns:1fr}
  .form-row{grid-template-columns:1fr}
  .hero-stats{gap:10px}
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
      <?php if(isAdmin()): ?><a href="/beautify/pages/admin/dashboard.php"><i class="fas fa-cog"></i> Panel Admin</a><span>|</span><?php endif; ?>
      <a href="/beautify/pages/user/pesanan.php"><i class="fas fa-box"></i> Pesanan Saya</a>
      <span>|</span>
      <a href="/beautify/logout.php">Keluar</a>
    </div>
    <div style="font-size:11px;opacity:.8">📍 Pengiriman ke seluruh Indonesia</div>
  </div>
</div>

<!-- HEADER -->
<header class="site-header">
  <div class="header-inner">
    <a href="/beautify/index.php" class="logo">Beauti<em>fy</em></a>
    <div class="search-bar">
      <input type="text" placeholder="Cari produk, merek, kategori...">
      <button><i class="fas fa-search"></i></button>
    </div>
    <div class="header-actions">
      <a href="/beautify/pages/user/wishlist.php" class="hbtn"><i class="fas fa-heart"></i><span>Wishlist</span></a>
      <div class="hbtn" onclick="openCart()" style="cursor:pointer">
        <div style="position:relative"><i class="fas fa-shopping-cart" style="font-size:20px"></i>
        <span class="cart-badge" id="cartBadge">0</span></div>
        <span>Keranjang</span>
      </div>
      <div class="prof-wrap" id="profWrap">
        <div class="prof-trigger" onclick="toggleProfileDD()">
          <?php if($fotoSrc): ?>
            <img src="<?=$fotoSrc?>" class="prof-avatar-sm" alt="foto">
          <?php else: ?>
            <i class="fas fa-user-circle" style="font-size:22px"></i>
          <?php endif; ?>
          <span><?=htmlspecialchars(userName())?></span>
          <i class="fas fa-chevron-down" style="font-size:10px;opacity:.6"></i>
        </div>
        <div class="prof-dd" id="profDD">
          <div class="prof-dd-head">
            <div class="prof-dd-name"><?=htmlspecialchars(userName())?></div>
            <div class="prof-dd-email"><?=htmlspecialchars(userEmail())?></div>
          </div>
          <a href="/beautify/pages/user/profile.php" class="active"><i class="fas fa-user-circle"></i> Profil Saya</a>
          <a href="/beautify/pages/user/pesanan.php"><i class="fas fa-box"></i> Pesanan Saya</a>
          <a href="/beautify/pages/user/wishlist.php"><i class="fas fa-heart"></i> Wishlist</a>
          <?php if(isAdmin()): ?>
          <a href="/beautify/pages/admin/dashboard.php"><i class="fas fa-tachometer-alt"></i> Panel Admin</a>
          <?php endif; ?>
          <hr>
          <a href="/beautify/logout.php" class="logout-a"><i class="fas fa-sign-out-alt"></i> Keluar</a>
        </div>
      </div>
    </div>
  </div>
</header>

<nav class="cat-nav">
  <div class="cat-nav-inner">
    <a href="/beautify/index.php">Home</a>
    <a href="/beautify/pages/user/pesanan.php">Pesanan Saya</a>
    <a href="/beautify/pages/user/profile.php" class="active">Profil Saya</a>
  </div>
</nav>

<div class="page-wrap">

  <?php if($msg): ?>
  <div class="msg-box"><i class="fas fa-check-circle"></i> <?=htmlspecialchars($msg)?></div>
  <?php endif; ?>
  <?php if($err): ?>
  <div class="err-box"><i class="fas fa-exclamation-triangle"></i> <?=htmlspecialchars($err)?></div>
  <?php endif; ?>

  <!-- HERO -->
  <div class="profile-hero">
    <div class="avatar-wrap">
      <?php if($fotoSrc): ?>
        <img src="<?=$fotoSrc?>" class="avatar-img" id="avatarPreview" alt="foto profil">
      <?php else: ?>
        <div class="avatar-empty" id="avatarPreview">👤</div>
      <?php endif; ?>
      <label for="fotoInput" class="avatar-edit" title="Ganti foto"><i class="fas fa-camera"></i></label>
    </div>
    <div>
      <div class="hero-name"><?=htmlspecialchars($user['nama'])?></div>
      <div class="hero-email"><i class="fas fa-envelope" style="font-size:11px;margin-right:4px"></i><?=htmlspecialchars($user['email'])?></div>
      <?php if(!empty($user['telepon'])): ?>
      <div class="hero-email" style="margin-top:2px"><i class="fas fa-phone" style="font-size:11px;margin-right:4px"></i><?=htmlspecialchars($user['telepon'])?></div>
      <?php endif; ?>
      <div class="hero-stats">
        <div class="hstat">
          <div class="hstat-val"><?=$stat_pesanan?></div>
          <div class="hstat-label">Total Pesanan</div>
        </div>
        <div class="hstat">
          <div class="hstat-val"><?=$stat_selesai?></div>
          <div class="hstat-label">Selesai</div>
        </div>
        <div class="hstat">
          <div class="hstat-val" style="font-size:15px">Rp <?=number_format($stat_spend,0,',','.')?></div>
          <div class="hstat-label">Total Belanja</div>
        </div>
      </div>
    </div>
  </div>

  <!-- LAYOUT -->
  <div class="profile-layout">

    <!-- SIDEBAR -->
    <div>
      <div class="side-card">
        <a href="?tab=profil" class="<?=$tab==='profil'?'active':''?>"><i class="fas fa-user-edit"></i> Edit Profil</a>
        <a href="?tab=password" class="<?=$tab==='password'?'active':''?>"><i class="fas fa-lock"></i> Ganti Password</a>
        <a href="?tab=pesanan" class="<?=$tab==='pesanan'?'active':''?>"><i class="fas fa-box"></i> Riwayat Pesanan</a>
      </div>
      <div class="side-card" style="margin-top:10px">
        <a href="/beautify/index.php"><i class="fas fa-store"></i> Kembali Belanja</a>
        <a href="/beautify/logout.php" class="danger"><i class="fas fa-sign-out-alt"></i> Keluar</a>
      </div>
    </div>

    <!-- KONTEN -->
    <div>

      <?php if($tab === 'profil'): ?>
      <div class="form-card">
        <div class="form-card-hdr"><i class="fas fa-user-edit"></i> Edit Biodata Diri</div>
        <form method="POST" enctype="multipart/form-data">
          <input type="hidden" name="action" value="update_profil">
          <input type="file" name="foto" id="fotoInput" accept="image/*" style="display:none" onchange="previewAvatar(this)">
          <div class="form-row">
            <div class="fg">
              <label>Nama Lengkap</label>
              <input type="text" name="nama" required value="<?=htmlspecialchars($user['nama'])?>">
            </div>
            <div class="fg">
              <label>Email</label>
              <input type="email" name="email" required value="<?=htmlspecialchars($user['email'])?>">
            </div>
          </div>
          <div class="form-row">
            <div class="fg">
              <label>No. Telepon</label>
              <input type="text" name="telepon" value="<?=htmlspecialchars($user['telepon']??'')?>" placeholder="08xxxxxxxxxx">
            </div>
            <div class="fg">
              <label>Jenis Kelamin</label>
              <select name="jenis_kelamin">
                <option value="">-- Pilih --</option>
                <option value="Perempuan" <?=($user['jenis_kelamin']??'')==='Perempuan'?'selected':''?>>Perempuan</option>
                <option value="Laki-laki" <?=($user['jenis_kelamin']??'')==='Laki-laki'?'selected':''?>>Laki-laki</option>
              </select>
            </div>
          </div>
          <div class="form-row">
            <div class="fg">
              <label>Tanggal Lahir</label>
              <input type="date" name="tgl_lahir" value="<?=$user['tgl_lahir']??''?>">
            </div>
            <div class="fg">
              <label>Role</label>
              <input type="text" value="<?=isAdmin()?'Administrator':'Member'?>" disabled>
            </div>
          </div>
          <div class="form-row one">
            <div class="fg full">
              <label>Alamat Lengkap</label>
              <textarea name="alamat" placeholder="Jalan, Kelurahan, Kecamatan, Kota..."><?=htmlspecialchars($user['alamat']??'')?></textarea>
            </div>
          </div>
          <button type="submit" class="btn-save"><i class="fas fa-save"></i> Simpan Perubahan</button>
        </form>
      </div>

      <?php elseif($tab === 'password'): ?>
      <div class="form-card">
        <div class="form-card-hdr"><i class="fas fa-lock"></i> Ganti Password</div>
        <form method="POST">
          <input type="hidden" name="action" value="ganti_password">
          <div class="form-row one">
            <div class="fg">
              <label>Password Lama</label>
              <div class="pw-wrap">
                <input type="password" name="password_lama" id="p1" required placeholder="Password saat ini">
                <span class="pw-eye" onclick="togglePw('p1',this)"><i class="fas fa-eye"></i></span>
              </div>
            </div>
          </div>
          <div class="form-row">
            <div class="fg">
              <label>Password Baru</label>
              <div class="pw-wrap">
                <input type="password" name="password_baru" id="p2" required placeholder="Min. 6 karakter">
                <span class="pw-eye" onclick="togglePw('p2',this)"><i class="fas fa-eye"></i></span>
              </div>
            </div>
            <div class="fg">
              <label>Konfirmasi Password</label>
              <div class="pw-wrap">
                <input type="password" name="password_konfirm" id="p3" required placeholder="Ulangi password baru">
                <span class="pw-eye" onclick="togglePw('p3',this)"><i class="fas fa-eye"></i></span>
              </div>
            </div>
          </div>
          <button type="submit" class="btn-save"><i class="fas fa-key"></i> Ganti Password</button>
        </form>
      </div>

      <?php elseif($tab === 'pesanan'): ?>
      <div class="form-card" style="padding:0;overflow:hidden">
        <div style="padding:18px 24px;border-bottom:1px solid #f0e8e0">
          <div class="form-card-hdr" style="margin:0;padding:0;border:none"><i class="fas fa-box"></i> Riwayat Pesanan</div>
        </div>
        <div style="overflow-x:auto">
        <table class="tbl">
          <thead>
            <tr><th>#ID</th><th>Total</th><th>Metode Bayar</th><th>Status</th><th>Tanggal</th></tr>
          </thead>
          <tbody>
            <?php
            $status_class=['pending'=>'bp','diproses'=>'bd','dikirim'=>'bk','selesai'=>'bs','dibatalkan'=>'bb'];
            if(!$riwayat||$riwayat->num_rows===0):
            ?>
            <tr><td colspan="5" style="text-align:center;color:#aaa;padding:32px">
              <i class="fas fa-box-open" style="font-size:32px;display:block;margin-bottom:8px;opacity:.3"></i>
              Belum ada pesanan
            </td></tr>
            <?php else: while($p=$riwayat->fetch_assoc()):
              $bc=$status_class[$p['status']]??'bp';
            ?>
            <tr>
              <td style="font-weight:800;color:var(--pk)">#<?=$p['id']?></td>
              <td style="font-weight:700">Rp <?=number_format($p['total_harga'],0,',','.')?></td>
              <td style="color:#888;font-size:12px"><?=htmlspecialchars($p['metode_bayar']??'-')?></td>
              <td><span class="badge <?=$bc?>"><?=ucfirst($p['status'])?></span></td>
              <td style="color:#888;font-size:12px"><?=date('d M Y',strtotime($p['created_at']))?></td>
            </tr>
            <?php endwhile; endif; ?>
          </tbody>
        </table>
        </div>
      </div>
      <?php endif; ?>

    </div>
  </div>

</div>

<!-- FOOTER -->
<footer class="site-footer">
  <div class="footer-inner">
    <div>
      <a href="/beautify/index.php" class="logo footer-logo">Beauti<em>fy</em></a>
      <p class="footer-desc">Marketplace kecantikan terpercaya di Indonesia.</p>
    </div>
    <div class="footer-col">
      <h4>Akun Saya</h4>
      <a href="/beautify/pages/user/profile.php">Profil Saya</a>
      <a href="/beautify/pages/user/pesanan.php">Pesanan Saya</a>
      <a href="/beautify/logout.php">Keluar</a>
    </div>
  </div>
  <div class="footer-bot">© 2026 Beautify Marketplace. 🇮🇩</div>
</footer>

<script src="/beautify/assets/js/main.js"></script>
<script>
function previewAvatar(input) {
  if (!input.files || !input.files[0]) return;
  const reader = new FileReader();
  reader.onload = function(e) {
    const el = document.getElementById('avatarPreview');
    if (el.tagName === 'IMG') {
      el.src = e.target.result;
    } else {
      const img = document.createElement('img');
      img.src = e.target.result;
      img.className = 'avatar-img';
      img.id = 'avatarPreview';
      img.alt = 'foto profil';
      el.replaceWith(img);
    }
  };
  reader.readAsDataURL(input.files[0]);
}
function togglePw(id, btn) {
  const inp = document.getElementById(id);
  if (inp.type === 'password') {
    inp.type = 'text';
    btn.innerHTML = '<i class="fas fa-eye-slash"></i>';
  } else {
    inp.type = 'password';
    btn.innerHTML = '<i class="fas fa-eye"></i>';
  }
}
updateBadge();
renderCart();
</script>
</body>
</html>
