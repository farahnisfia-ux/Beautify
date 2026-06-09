<?php
session_start();
require_once '../../includes/db.php';
require_once '../../includes/auth.php';
requireAdmin();

$msg = '';
$err = '';
$user = $conn->query("SELECT * FROM users WHERE id=".userId())->fetch_assoc();

// Update profil
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {

    if ($_POST['action'] === 'update_profil') {
        $nama    = trim($_POST['nama']);
        $email   = trim($_POST['email']);
        $telepon = trim($_POST['telepon'] ?? '');
        $alamat  = trim($_POST['alamat'] ?? '');
        $foto    = $user['foto'];

        // Upload foto
if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {

    $upload_dir = 'C:/xampp/htdocs/beautify/assets/img/profil/';

    // Buat folder otomatis jika belum ada
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    $ext       = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
    $nama_file = 'profil_' . userId() . '_' . time() . '.' . $ext;
    $tujuan    = $upload_dir . $nama_file;

    if (move_uploaded_file($_FILES['foto']['tmp_name'], $tujuan)) {
        $foto = $nama_file; // ✅ update agar tersimpan ke DB
    } else {
        $err = 'Gagal upload foto.';
    }
}

        $conn->query("UPDATE users SET nama='".mysqli_real_escape_string($conn,$nama)."', email='".mysqli_real_escape_string($conn,$email)."', telepon='".mysqli_real_escape_string($conn,$telepon)."', alamat='".mysqli_real_escape_string($conn,$alamat)."', foto='$foto' WHERE id=".userId());
        $_SESSION['user_nama']  = $nama;
        $_SESSION['user_email'] = $email;
        $_SESSION['user_foto']  = $foto;
        $user = $conn->query("SELECT * FROM users WHERE id=".userId())->fetch_assoc();
        $msg = 'Profil berhasil diupdate!';

    } elseif ($_POST['action'] === 'ganti_password') {
        $lama  = $_POST['password_lama'];
        $baru  = $_POST['password_baru'];
        $konfirm = $_POST['password_konfirm'];
        if (!password_verify($lama, $user['password'])) {
            $err = 'Password lama tidak sesuai.';
        } elseif (strlen($baru) < 6) {
            $err = 'Password baru minimal 6 karakter.';
        } elseif ($baru !== $konfirm) {
            $err = 'Konfirmasi password tidak cocok.';
        } else {
            $hash = password_hash($baru, PASSWORD_DEFAULT);
            $conn->query("UPDATE users SET password='$hash' WHERE id=".userId());
            $msg = 'Password berhasil diubah!';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Profil Admin – Beautify</title>
<link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&family=Playfair+Display:ital,wght@0,700;1,300&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="/beautify/assets/css/style.css">
<style>
:root { --pk:#F297A0; --pk-light:#F9D0CE; --green:#B6BB79; --cream:#F3EBD8; }
body { background:#faf7f4; }
.admin-wrap { display:flex; min-height:100vh; }
.admin-sidebar { width:240px; min-height:100vh; background:#fff; border-right:1px solid #f0e8e0; display:flex; flex-direction:column; position:fixed; top:0; left:0; z-index:100; }
.sidebar-logo { padding:24px 20px 16px; border-bottom:1px solid #f0e8e0; font-family:'Playfair Display',serif; font-size:22px; color:var(--pk); text-decoration:none; display:block; }
.sidebar-logo em { font-style:italic; color:#c97a84; }
.sidebar-menu { padding:16px 0; flex:1; }
.sidebar-label { font-size:10px; font-weight:800; letter-spacing:1.2px; color:#bbb; padding:12px 20px 4px; text-transform:uppercase; }
.sidebar-menu a { display:flex; align-items:center; gap:10px; padding:10px 20px; font-size:14px; font-weight:600; color:#666; text-decoration:none; border-left:3px solid transparent; transition:all .2s; }
.sidebar-menu a:hover, .sidebar-menu a.active { color:var(--pk); background:#fff5f5; border-left-color:var(--pk); }
.sidebar-menu a i { width:18px; text-align:center; }
.sidebar-bottom { padding:16px 20px; border-top:1px solid #f0e8e0; }
.sidebar-user { display:flex; align-items:center; gap:10px; padding:12px 16px; background:var(--cream); border-radius:10px; margin-bottom:8px; }
.sidebar-user-icon { width:36px; height:36px; border-radius:50%; background:var(--pk-light); display:flex; align-items:center; justify-content:center; color:var(--pk); }
.sidebar-user-name { font-size:13px; font-weight:700; color:#444; }
.sidebar-user-role { font-size:11px; color:#999; }
.sidebar-bottom a { display:flex; align-items:center; gap:8px; color:#888; font-size:13px; text-decoration:none; font-weight:600; padding:8px 0; }
.sidebar-bottom a:hover { color:var(--pk); }
.admin-main { margin-left:240px; flex:1; }
.admin-topbar { background:#fff; border-bottom:1px solid #f0e8e0; padding:16px 28px; display:flex; align-items:center; position:sticky; top:0; z-index:50; box-shadow:0 2px 8px rgba(0,0,0,.04); }
.admin-topbar h1 { font-size:18px; font-weight:800; color:#333; margin:0; }
.admin-content { padding:28px; max-width:800px; }
.msg-box { padding:12px 18px; border-radius:10px; margin-bottom:20px; font-weight:700; font-size:13px; background:#e8f5e9; color:#2e7d32; border:1px solid #c8e6c9; }
.err-box { padding:12px 18px; border-radius:10px; margin-bottom:20px; font-weight:700; font-size:13px; background:#fce8e8; color:#c62828; border:1px solid #fccfcf; }

/* PROFILE HEADER */
.profile-header { background:#fff; border-radius:16px; border:1px solid #f0e8e0; padding:28px; margin-bottom:24px; display:flex; align-items:center; gap:24px; box-shadow:0 2px 8px rgba(0,0,0,.04); }
.profile-avatar-wrap { position:relative; flex-shrink:0; }
.profile-avatar { width:100px; height:100px; border-radius:50%; object-fit:cover; border:3px solid var(--pk-light); }
.profile-avatar-empty { width:100px; height:100px; border-radius:50%; background:linear-gradient(135deg,var(--pk-light),var(--pk)); display:flex; align-items:center; justify-content:center; font-size:36px; color:#fff; border:3px solid var(--pk-light); }
.profile-avatar-edit { position:absolute; bottom:4px; right:4px; width:28px; height:28px; background:var(--pk); border-radius:50%; display:flex; align-items:center; justify-content:center; cursor:pointer; color:#fff; font-size:12px; border:2px solid #fff; }
.profile-name { font-size:22px; font-weight:800; color:#333; }
.profile-email { font-size:14px; color:#999; margin-top:4px; }
.profile-role-badge { display:inline-flex; align-items:center; gap:6px; background:var(--cream); color:#a07050; padding:4px 12px; border-radius:20px; font-size:12px; font-weight:700; margin-top:8px; }

/* FORM CARD */
.form-card { background:#fff; border-radius:14px; border:1px solid #f0e8e0; padding:24px; margin-bottom:20px; box-shadow:0 2px 8px rgba(0,0,0,.04); }
.form-card h3 { font-size:15px; font-weight:800; color:#333; margin:0 0 20px; display:flex; align-items:center; gap:8px; border-bottom:1px solid #f0e8e0; padding-bottom:12px; }
.form-row { display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:16px; }
.form-group { display:flex; flex-direction:column; gap:6px; }
.form-group.full { grid-column:1/-1; }
.form-group label { font-size:12px; font-weight:800; color:#666; text-transform:uppercase; letter-spacing:.5px; }
.form-group input, .form-group textarea, .form-group select {
  padding:10px 14px; border:1.5px solid #e8e0dc; border-radius:8px;
  font-size:14px; font-family:Nunito; color:#333; outline:none; background:#faf7f4;
  transition:border .2s;
}
.form-group input:focus, .form-group textarea:focus { border-color:var(--pk); background:#fff; }
.form-group textarea { resize:vertical; min-height:80px; }
.btn-save { background:var(--pk); color:#fff; border:none; padding:11px 24px; border-radius:8px; font-size:14px; font-weight:700; cursor:pointer; display:inline-flex; align-items:center; gap:8px; }
.btn-save:hover { background:#e8848e; }
.pass-wrap { position:relative; }
.pass-toggle { position:absolute; right:12px; top:50%; transform:translateY(-50%); cursor:pointer; color:#aaa; font-size:14px; }
</style>
</head>
<body>
<div class="admin-wrap">
<!-- SIDEBAR -->
<aside class="admin-sidebar">
  <a href="/beautify/index.php" class="sidebar-logo">Beauti<em>fy</em> <span style="font-size:11px;color:#bbb;font-family:Nunito">Admin</span></a>
  <nav class="sidebar-menu">
    <div class="sidebar-label">Menu</div>
    <a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
    <a href="produk.php"><i class="fas fa-box"></i> Kelola Produk</a>
    <a href="pesanan.php"><i class="fas fa-shopping-bag"></i> Kelola Pesanan</a>
    <a href="laporan.php"><i class="fas fa-chart-bar"></i> Laporan & Statistik</a>
    <div class="sidebar-label">Pengaturan</div>
    <a href="profile.php" class="active"><i class="fas fa-user-circle"></i> Profil Admin</a>
    <a href="/beautify/index.php"><i class="fas fa-store"></i> Lihat Toko</a>
  </nav>
  <div class="sidebar-bottom">
    <div class="sidebar-user">
      <?php $f = fotoSrc(userFoto()); ?>
      <?php if ($f): ?><img src="<?= $f ?>" alt="" style="width:36px;height:36px;border-radius:50%;object-fit:cover">
      <?php else: ?><div class="sidebar-user-icon"><i class="fas fa-user"></i></div><?php endif; ?>
      <div>
        <div class="sidebar-user-name"><?= htmlspecialchars(userName()) ?></div>
        <div class="sidebar-user-role">Administrator</div>
      </div>
    </div>
    <a href="/beautify/logout.php"><i class="fas fa-sign-out-alt"></i> Keluar</a>
  </div>
</aside>

<!-- MAIN -->
<main class="admin-main">
  <div class="admin-topbar"><h1>👤 Profil Admin</h1></div>
  <div class="admin-content">
    <?php if ($msg): ?><div class="msg-box">✅ <?= $msg ?></div><?php endif; ?>
    <?php if ($err): ?><div class="err-box">⚠️ <?= $err ?></div><?php endif; ?>

    <!-- PROFILE HEADER -->
    <div class="profile-header">
      <?php $fotoSrc = fotoSrc($user['foto']); ?>
      <div class="profile-avatar-wrap">
        <?php if ($fotoSrc): ?>
          <img src="<?= $fotoSrc ?>" class="profile-avatar" id="previewAvatar" alt="foto">
        <?php else: ?>
          <div class="profile-avatar-empty" id="previewAvatar">👤</div>
        <?php endif; ?>
        <label for="fotoInput" class="profile-avatar-edit" title="Ganti foto"><i class="fas fa-camera"></i></label>
      </div>
      <div>
        <div class="profile-name"><?= htmlspecialchars($user['nama']) ?></div>
        <div class="profile-email"><?= htmlspecialchars($user['email']) ?></div>
        <div class="profile-role-badge"><i class="fas fa-shield-alt"></i> Administrator</div>
      </div>
    </div>

    <!-- FORM PROFIL -->
    <div class="form-card">
      <h3><i class="fas fa-user-edit" style="color:var(--pk)"></i> Edit Biodata</h3>
      <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="action" value="update_profil">
        <input type="file" name="foto" id="fotoInput" accept="image/*" style="display:none" onchange="previewFoto(this)">
        <div class="form-row">
          <div class="form-group">
            <label>Nama Lengkap</label>
            <input type="text" name="nama" required value="<?= htmlspecialchars($user['nama']) ?>" placeholder="Nama lengkap">
          </div>
          <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" required value="<?= htmlspecialchars($user['email']) ?>" placeholder="Email">
          </div>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label>No. Telepon</label>
            <input type="text" name="telepon" value="<?= htmlspecialchars($user['telepon'] ?? '') ?>" placeholder="08xxxxxxxxxx">
          </div>
          <div class="form-group">
            <label>Role</label>
            <input type="text" value="Administrator" disabled style="background:#f5f0ee;color:#aaa;cursor:not-allowed">
          </div>
        </div>
        <div class="form-row">
          <div class="form-group full">
            <label>Alamat</label>
            <textarea name="alamat" placeholder="Alamat lengkap..."><?= htmlspecialchars($user['alamat'] ?? '') ?></textarea>
          </div>
        </div>
        <div style="display:flex;align-items:center;gap:12px">
          <button type="submit" class="btn-save"><i class="fas fa-save"></i> Simpan Perubahan</button>
          <span style="font-size:12px;color:#aaa">Terakhir update: <?= date('d M Y', strtotime($user['updated_at'] ?? $user['created_at'] ?? 'now')) ?></span>
        </div>
      </form>
    </div>

    <!-- FORM PASSWORD -->
    <div class="form-card">
      <h3><i class="fas fa-lock" style="color:var(--pk)"></i> Ganti Password</h3>
      <form method="POST">
        <input type="hidden" name="action" value="ganti_password">
        <div class="form-row">
          <div class="form-group">
            <label>Password Lama</label>
            <div class="pass-wrap">
              <input type="password" name="password_lama" id="passLama" required placeholder="Password saat ini">
              <span class="pass-toggle" onclick="togglePass('passLama',this)"><i class="fas fa-eye"></i></span>
            </div>
          </div>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label>Password Baru</label>
            <div class="pass-wrap">
              <input type="password" name="password_baru" id="passBaru" required placeholder="Min. 6 karakter">
              <span class="pass-toggle" onclick="togglePass('passBaru',this)"><i class="fas fa-eye"></i></span>
            </div>
          </div>
          <div class="form-group">
            <label>Konfirmasi Password</label>
            <div class="pass-wrap">
              <input type="password" name="password_konfirm" id="passKonfirm" required placeholder="Ulangi password baru">
              <span class="pass-toggle" onclick="togglePass('passKonfirm',this)"><i class="fas fa-eye"></i></span>
            </div>
          </div>
        </div>
        <button type="submit" class="btn-save"><i class="fas fa-key"></i> Ganti Password</button>
      </form>
    </div>

  </div>
</main>
</div>
<script>
function previewFoto(input) {
  if (input.files && input.files[0]) {
    const reader = new FileReader();
    reader.onload = e => {
      const el = document.getElementById('previewAvatar');
      if (el.tagName === 'IMG') {
        el.src = e.target.result;
      } else {
        const img = document.createElement('img');
        img.src = e.target.result;
        img.className = 'profile-avatar';
        img.id = 'previewAvatar';
        el.replaceWith(img);
      }
    };
    reader.readAsDataURL(input.files[0]);
  }
}
function togglePass(id, btn) {
  const inp = document.getElementById(id);
  if (inp.type === 'password') { inp.type = 'text'; btn.innerHTML = '<i class="fas fa-eye-slash"></i>'; }
  else { inp.type = 'password'; btn.innerHTML = '<i class="fas fa-eye"></i>'; }
}
</script>
</body>
</html>
