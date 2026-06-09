<?php
session_start();
require_once '../../includes/db.php';
require_once '../../includes/auth.php';
requireAdmin();
 
$msg = '';
 
// HAPUS
if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];
    $row = $conn->query("SELECT gambar FROM produk WHERE id=$id")->fetch_assoc();
    if ($row && $row['gambar']) {
        $path = $_SERVER['DOCUMENT_ROOT'] . '/beautify/assets/img/produk/' . $row['gambar'];
        if (file_exists($path)) unlink($path);
    }
    $conn->query("DELETE FROM produk WHERE id=$id");
    header("Location: produk.php?msg=hapus_ok");
    exit;
}
 
// SIMPAN (tambah/edit)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id       = (int)($_POST['id'] ?? 0);
    $nama     = trim($_POST['nama']);
    $harga    = (int)$_POST['harga'];
    $stok     = (int)$_POST['stok'];
    $kat_id   = (int)$_POST['kategori_id'];
    $deskripsi = trim($_POST['deskripsi'] ?? '');
    $gambar   = '';
 
    // Upload gambar
    if (!empty($_FILES['gambar']['name'])) {
        $ext = pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION);
        $allowed = ['jpg','jpeg','png','webp'];
        if (in_array(strtolower($ext), $allowed)) {
            $gambar = 'produk_' . time() . '_' . rand(100,999) . '.' . $ext;
            $dest = $_SERVER['DOCUMENT_ROOT'] . '/beautify/assets/img/produk/' . $gambar;
            move_uploaded_file($_FILES['gambar']['tmp_name'], $dest);
        }
    }
 
    if ($id > 0) {
        // Edit
        if ($gambar) {
            // hapus gambar lama
            $old = $conn->query("SELECT gambar FROM produk WHERE id=$id")->fetch_assoc();
            if ($old && $old['gambar']) {
                $oldPath = $_SERVER['DOCUMENT_ROOT'] . '/beautify/assets/img/produk/' . $old['gambar'];
                if (file_exists($oldPath)) unlink($oldPath);
            }
            $conn->query("UPDATE produk SET nama='".mysqli_real_escape_string($conn,$nama)."', harga=$harga, stok=$stok, kategori_id=$kat_id, deskripsi='".mysqli_real_escape_string($conn,$deskripsi)."', gambar='$gambar' WHERE id=$id");
        } else {
            $conn->query("UPDATE produk SET nama='".mysqli_real_escape_string($conn,$nama)."', harga=$harga, stok=$stok, kategori_id=$kat_id, deskripsi='".mysqli_real_escape_string($conn,$deskripsi)."' WHERE id=$id");
        }
        header("Location: produk.php?msg=edit_ok");
    } else {
        // Tambah
        $conn->query("INSERT INTO produk (nama, harga, stok, kategori_id, deskripsi, gambar) VALUES ('".mysqli_real_escape_string($conn,$nama)."', $harga, $stok, $kat_id, '".mysqli_real_escape_string($conn,$deskripsi)."', '$gambar')");
        header("Location: produk.php?msg=tambah_ok");
    }
    exit;
}
 
// Ambil data edit
$edit_data = null;
if (isset($_GET['edit'])) {
    $edit_data = $conn->query("SELECT * FROM produk WHERE id=".(int)$_GET['edit'])->fetch_assoc();
}
 
// List produk + filter
$search = trim($_GET['q'] ?? '');
$kat_filter = (int)($_GET['kat'] ?? 0);
$where = "WHERE 1=1";
if ($search) $where .= " AND p.nama LIKE '%".mysqli_real_escape_string($conn,$search)."%'";
if ($kat_filter) $where .= " AND p.kategori_id=$kat_filter";
 
$produk = $conn->query("SELECT p.*, k.nama as kat_nama FROM produk p LEFT JOIN kategori k ON p.kategori_id=k.id $where ORDER BY p.id DESC");
$kategori_all = $conn->query("SELECT * FROM kategori ORDER BY nama");
 
$msg_text = ['tambah_ok'=>'✅ Produk berhasil ditambahkan!','edit_ok'=>'✅ Produk berhasil diupdate!','hapus_ok'=>'✅ Produk berhasil dihapus!'];
$msg = $msg_text[$_GET['msg'] ?? ''] ?? '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Kelola Produk – Admin Beautify</title>
<link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&family=Playfair+Display:ital,wght@0,700;1,300&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="/beautify/assets/css/style.css">
<style>
:root { --pk:#F297A0; --pk-light:#F9D0CE; --green:#B6BB79; --cream:#F3EBD8; }
body { background:#faf7f4; }
.admin-wrap { display:flex; min-height:100vh; }
.admin-sidebar { width:240px; min-height:100vh; background:#fff; border-right:1px solid #f0e8e0; display:flex; flex-direction:column; position:fixed; top:0; left:0; z-index:100; box-shadow:2px 0 12px rgba(242,151,160,.08); }
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
.admin-topbar { background:#fff; border-bottom:1px solid #f0e8e0; padding:16px 28px; display:flex; align-items:center; justify-content:space-between; position:sticky; top:0; z-index:50; box-shadow:0 2px 8px rgba(0,0,0,.04); }
.admin-topbar h1 { font-size:18px; font-weight:800; color:#333; margin:0; }
.admin-content { padding:28px; }
 
.msg-box { padding:12px 18px; border-radius:10px; margin-bottom:20px; font-weight:700; font-size:13px; background:#e8f5e9; color:#2e7d32; border:1px solid #c8e6c9; }
 
/* FORM CARD */
.form-card { background:#fff; border-radius:14px; border:1px solid #f0e8e0; padding:24px; margin-bottom:24px; box-shadow:0 2px 8px rgba(0,0,0,.04); }
.form-card h3 { font-size:15px; font-weight:800; color:#333; margin:0 0 20px; display:flex; align-items:center; gap:8px; }
.form-row { display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:16px; }
.form-row.three { grid-template-columns:1fr 1fr 1fr; }
.form-group { display:flex; flex-direction:column; gap:6px; }
.form-group label { font-size:12px; font-weight:800; color:#666; text-transform:uppercase; letter-spacing:.5px; }
.form-group input, .form-group select, .form-group textarea {
  padding:10px 14px; border:1.5px solid #e8e0dc; border-radius:8px;
  font-size:14px; font-family:Nunito; color:#333;
  transition:border .2s; outline:none; background:#faf7f4;
}
.form-group input:focus, .form-group select:focus, .form-group textarea:focus { border-color:var(--pk); background:#fff; }
.form-group textarea { resize:vertical; min-height:80px; }
.btn-save { background:var(--pk); color:#fff; border:none; padding:11px 24px; border-radius:8px; font-size:14px; font-weight:700; cursor:pointer; display:inline-flex; align-items:center; gap:8px; transition:all .2s; }
.btn-save:hover { background:#e8848e; }
.btn-cancel { background:#f5f0ee; color:#666; border:none; padding:11px 20px; border-radius:8px; font-size:14px; font-weight:700; cursor:pointer; text-decoration:none; display:inline-flex; align-items:center; gap:8px; }
.btn-cancel:hover { background:#ece5e0; }
 
/* FILTER BAR */
.filter-bar { display:flex; gap:10px; align-items:center; margin-bottom:16px; flex-wrap:wrap; }
.filter-bar input, .filter-bar select { padding:9px 14px; border:1.5px solid #e8e0dc; border-radius:8px; font-size:13px; font-family:Nunito; outline:none; background:#faf7f4; }
.filter-bar input:focus, .filter-bar select:focus { border-color:var(--pk); background:#fff; }
.btn-filter { background:var(--pk); color:#fff; border:none; padding:9px 18px; border-radius:8px; font-size:13px; font-weight:700; cursor:pointer; }
 
/* TABLE */
.table-card { background:#fff; border-radius:14px; border:1px solid #f0e8e0; overflow:hidden; box-shadow:0 2px 8px rgba(0,0,0,.04); }
.table-hdr { padding:16px 20px; border-bottom:1px solid #f8f0ee; display:flex; align-items:center; justify-content:space-between; }
.table-hdr h3 { font-size:14px; font-weight:800; color:#333; margin:0; }
.admin-table { width:100%; border-collapse:collapse; font-size:13px; }
.admin-table th { padding:10px 16px; text-align:left; font-size:11px; font-weight:800; color:#999; text-transform:uppercase; letter-spacing:.5px; border-bottom:1px solid #f5f0ee; background:#fdfaf8; }
.admin-table td { padding:12px 16px; border-bottom:1px solid #f8f5f3; color:#444; vertical-align:middle; }
.admin-table tr:last-child td { border-bottom:none; }
.admin-table tr:hover td { background:#fffaf9; }
.prod-thumb { width:44px; height:44px; border-radius:8px; object-fit:cover; background:var(--pk-light); }
.prod-thumb-empty { width:44px; height:44px; border-radius:8px; background:var(--pk-light); display:flex; align-items:center; justify-content:center; font-size:18px; }
.badge-stok-ok { background:#e8f5e9; color:#2e7d32; padding:3px 9px; border-radius:20px; font-size:11px; font-weight:800; }
.badge-stok-warn { background:#fff3e0; color:#d97706; padding:3px 9px; border-radius:20px; font-size:11px; font-weight:800; }
.badge-stok-danger { background:#fce8e8; color:#c62828; padding:3px 9px; border-radius:20px; font-size:11px; font-weight:800; }
.btn-edit { background:var(--cream); color:#886a4e; border:none; padding:5px 12px; border-radius:6px; font-size:12px; font-weight:700; cursor:pointer; text-decoration:none; }
.btn-edit:hover { background:#e8d8c0; }
.btn-hapus { background:#fce8e8; color:#c62828; border:none; padding:5px 12px; border-radius:6px; font-size:12px; font-weight:700; cursor:pointer; }
.btn-hapus:hover { background:#fcd0d0; }
 
/* IMAGE PREVIEW */
.img-preview { width:80px; height:80px; border-radius:8px; object-fit:cover; border:2px solid var(--pk-light); display:none; margin-top:8px; }
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
    <a href="produk.php" class="active"><i class="fas fa-box"></i> Kelola Produk</a>
    <a href="pesanan.php"><i class="fas fa-shopping-bag"></i> Kelola Pesanan</a>
    <a href="laporan.php"><i class="fas fa-chart-bar"></i> Laporan & Statistik</a>
    <div class="sidebar-label">Pengaturan</div>
    <a href="profile.php"><i class="fas fa-user-circle"></i> Profil Admin</a>
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
  <div class="admin-topbar">
    <div><h1><?= $edit_data ? '✏️ Edit Produk' : '📦 Kelola Produk' ?></h1></div>
    <?php if (!$edit_data): ?>
    <button onclick="document.getElementById('formTambah').scrollIntoView({behavior:'smooth'})" style="background:var(--pk);color:#fff;border:none;padding:8px 16px;border-radius:8px;font-size:13px;font-weight:700;cursor:pointer;display:flex;align-items:center;gap:6px">
      <i class="fas fa-plus"></i> Tambah Produk
    </button>
    <?php endif; ?>
  </div>
 
  <div class="admin-content">
    <?php if ($msg): ?><div class="msg-box"><?= $msg ?></div><?php endif; ?>
 
    <!-- FORM -->
    <div class="form-card" id="formTambah">
      <h3><?= $edit_data ? '<i class="fas fa-edit" style="color:var(--pk)"></i> Edit Produk' : '<i class="fas fa-plus-circle" style="color:var(--pk)"></i> Tambah Produk Baru' ?></h3>
      <form method="POST" enctype="multipart/form-data">
        <?php if ($edit_data): ?><input type="hidden" name="id" value="<?= $edit_data['id'] ?>"><?php endif; ?>
        <div class="form-row">
          <div class="form-group" style="grid-column:1/-1">
            <label>Nama Produk</label>
            <input type="text" name="nama" required placeholder="Masukkan nama produk..." value="<?= htmlspecialchars($edit_data['nama'] ?? '') ?>">
          </div>
        </div>
        <div class="form-row three">
          <div class="form-group">
            <label>Harga (Rp)</label>
            <input type="number" name="harga" required min="0" placeholder="0" value="<?= $edit_data['harga'] ?? '' ?>">
          </div>
          <div class="form-group">
            <label>Stok</label>
            <input type="number" name="stok" required min="0" placeholder="0" value="<?= $edit_data['stok'] ?? '' ?>">
          </div>
          <div class="form-group">
            <label>Kategori</label>
            <select name="kategori_id" required>
              <option value="">-- Pilih Kategori --</option>
              <?php $kategori_all->data_seek(0); while ($k = $kategori_all->fetch_assoc()): ?>
              <option value="<?= $k['id'] ?>" <?= ($edit_data['kategori_id'] ?? '') == $k['id'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($k['nama']) ?>
              </option>
              <?php endwhile; ?>
            </select>
          </div>
        </div>
        <div class="form-row">
          <div class="form-group" style="grid-column:1/-1">
            <label>Deskripsi</label>
            <textarea name="deskripsi" placeholder="Deskripsi produk..."><?= htmlspecialchars($edit_data['deskripsi'] ?? '') ?></textarea>
          </div>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label>Gambar Produk <?= $edit_data ? '(Kosongkan jika tidak diganti)' : '' ?></label>
            <input type="file" name="gambar" accept="image/*" onchange="previewImg(this)">
            <img id="imgPreview" class="img-preview"
              <?php if ($edit_data && $edit_data['gambar'] && file_exists($_SERVER['DOCUMENT_ROOT'].'/beautify/assets/img/produk/'.$edit_data['gambar'])): ?>
                src="/beautify/assets/img/produk/<?= $edit_data['gambar'] ?>" style="display:block"
              <?php endif; ?>
            >
          </div>
        </div>
        <div style="display:flex;gap:10px;margin-top:4px">
          <button type="submit" class="btn-save"><i class="fas fa-save"></i> <?= $edit_data ? 'Simpan Perubahan' : 'Tambah Produk' ?></button>
          <?php if ($edit_data): ?><a href="produk.php" class="btn-cancel"><i class="fas fa-times"></i> Batal</a><?php endif; ?>
        </div>
      </form>
    </div>
 
    <!-- FILTER -->
    <form method="GET" class="filter-bar">
      <input type="text" name="q" placeholder="🔍 Cari produk..." value="<?= htmlspecialchars($search) ?>">
      <select name="kat">
        <option value="">Semua Kategori</option>
        <?php $kategori_all->data_seek(0); while ($k = $kategori_all->fetch_assoc()): ?>
        <option value="<?= $k['id'] ?>" <?= $kat_filter==$k['id']?'selected':'' ?>><?= htmlspecialchars($k['nama']) ?></option>
        <?php endwhile; ?>
      </select>
      <button type="submit" class="btn-filter"><i class="fas fa-search"></i> Filter</button>
      <?php if ($search || $kat_filter): ?><a href="produk.php" class="btn-cancel">Reset</a><?php endif; ?>
    </form>
 
    <!-- TABLE -->
    <div class="table-card">
      <div class="table-hdr">
        <h3>📋 Daftar Produk (<?= $produk->num_rows ?>)</h3>
      </div>
      <table class="admin-table">
        <thead>
          <tr>
            <th>#</th>
            <th>Gambar</th>
            <th>Nama Produk</th>
            <th>Kategori</th>
            <th>Harga</th>
            <th>Stok</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($produk->num_rows === 0): ?>
          <tr><td colspan="7" style="text-align:center;color:#aaa;padding:30px">Belum ada produk</td></tr>
          <?php else: $no=1; while ($p = $produk->fetch_assoc()): ?>
          <tr>
            <td style="color:#999;font-weight:700"><?= $no++ ?></td>
            <td>
              <?php $imgSrc = ($p['gambar'] && file_exists($_SERVER['DOCUMENT_ROOT'].'/beautify/assets/img/produk/'.$p['gambar'])) ? '/beautify/assets/img/produk/'.$p['gambar'] : ''; ?>
              <?php if ($imgSrc): ?>
                <img src="<?= $imgSrc ?>" class="prod-thumb" alt="">
              <?php else: ?>
                <div class="prod-thumb-empty">💄</div>
              <?php endif; ?>
            </td>
            <td style="font-weight:700;max-width:200px"><?= htmlspecialchars($p['nama']) ?></td>
            <td><span style="background:var(--pk-light);color:#a55;padding:3px 9px;border-radius:20px;font-size:11px;font-weight:700"><?= htmlspecialchars($p['kat_nama'] ?? '-') ?></span></td>
            <td style="font-weight:700">Rp <?= number_format($p['harga'],0,',','.') ?></td>
            <td>
              <?php if ($p['stok'] <= 5): ?>
                <span class="badge-stok-danger"><?= $p['stok'] ?> pcs ⚠️</span>
              <?php elseif ($p['stok'] <= 15): ?>
                <span class="badge-stok-warn"><?= $p['stok'] ?> pcs</span>
              <?php else: ?>
                <span class="badge-stok-ok"><?= $p['stok'] ?> pcs</span>
              <?php endif; ?>
            </td>
            <td style="display:flex;gap:6px">
              <a href="produk.php?edit=<?= $p['id'] ?>" class="btn-edit"><i class="fas fa-edit"></i> Edit</a>
              <button class="btn-hapus" onclick="konfirmHapus(<?= $p['id'] ?>,'<?= addslashes(htmlspecialchars($p['nama'])) ?>')">
                <i class="fas fa-trash"></i> Hapus
              </button>
            </td>
          </tr>
          <?php endwhile; endif; ?>
        </tbody>
      </table>
    </div>
 
  </div>
</main>
</div>
 
<script>
function previewImg(input) {
  const preview = document.getElementById('imgPreview');
  if (input.files && input.files[0]) {
    const reader = new FileReader();
    reader.onload = e => { preview.src = e.target.result; preview.style.display = 'block'; };
    reader.readAsDataURL(input.files[0]);
  }
}
function konfirmHapus(id, nama) {
  if (confirm(`Hapus produk "${nama}"?\nTindakan ini tidak bisa dibatalkan.`)) {
    window.location.href = 'produk.php?hapus=' + id;
  }
}
</script>
</body>
</html>