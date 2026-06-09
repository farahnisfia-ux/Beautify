<?php
session_start();
require_once '../../includes/db.php';
require_once '../../includes/auth.php';
requireAdmin();

// Helper query aman
function safeQuery($conn, $sql, $default = 0) {
    $res = $conn->query($sql);
    if (!$res) return $default;
    $row = $res->fetch_assoc();
    return $row ? array_values($row)[0] : $default;
}

// Statistik
$total_produk  = safeQuery($conn, "SELECT COUNT(*) as c FROM produk");
$total_user    = safeQuery($conn, "SELECT COUNT(*) as c FROM users WHERE role='user'");
$total_pesanan = safeQuery($conn, "SELECT COUNT(*) as c FROM pesanan");
$total_revenue = safeQuery($conn, "SELECT COALESCE(SUM(total_harga),0) as c FROM pesanan WHERE status != 'dibatalkan'");

// Pesanan terbaru
$pesanan_baru = $conn->query("SELECT p.*, u.nama as nama_user FROM pesanan p JOIN users u ON p.user_id=u.id ORDER BY p.created_at DESC LIMIT 5");
if (!$pesanan_baru) $pesanan_baru = null;

// Produk stok rendah
$stok_rendah = $conn->query("SELECT * FROM produk WHERE stok <= 10 ORDER BY stok ASC LIMIT 5");
if (!$stok_rendah) $stok_rendah = null;

// Data chart pesanan per bulan (6 bulan terakhir)
$chart_data = [];
for ($i = 5; $i >= 0; $i--) {
    $res = $conn->query("SELECT COUNT(*) as c, COALESCE(SUM(total_harga),0) as rev FROM pesanan WHERE MONTH(created_at)=MONTH(DATE_SUB(NOW(), INTERVAL $i MONTH)) AND YEAR(created_at)=YEAR(DATE_SUB(NOW(), INTERVAL $i MONTH)) AND status != 'dibatalkan'");
    $row = $res ? $res->fetch_assoc() : ['c'=>0,'rev'=>0];
    $chart_data[] = [
        'bulan'   => date('M', strtotime("-$i months")),
        'pesanan' => (int)($row['c'] ?? 0),
        'revenue' => (float)($row['rev'] ?? 0)
    ];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Admin Dashboard – Beautify</title>
<link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&family=Playfair+Display:ital,wght@0,700;1,300&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="/beautify/assets/css/style.css">
<style>
:root {
  --pk: #F297A0;
  --pk-light: #F9D0CE;
  --green: #B6BB79;
  --cream: #F3EBD8;
}
body { background: #F3EBD83; }
.admin-wrap { display: flex; min-height: 100vh; }

/* SIDEBAR */
.admin-sidebar {
  width: 240px; min-height: 100vh;
  background: #fff;
  border-right: 1px solid #f0e8e0;
  display: flex; flex-direction: column;
  position: fixed; top: 0; left: 0; z-index: 100;
  box-shadow: 2px 0 12px rgba(242,151,160,.08);
}
.sidebar-logo {
  padding: 24px 20px 16px;
  border-bottom: 1px solid #f0e8e0;
  font-family: 'Playfair Display', serif;
  font-size: 22px; color: var(--pk);
  text-decoration: none; display: block;
}
.sidebar-logo em { font-style: italic; color: #c97a84; }
.sidebar-menu { padding: 16px 0; flex: 1; }
.sidebar-label {
  font-size: 10px; font-weight: 800; letter-spacing: 1.2px;
  color: #bbb; padding: 12px 20px 4px; text-transform: uppercase;
}
.sidebar-menu a {
  display: flex; align-items: center; gap: 10px;
  padding: 10px 20px; font-size: 14px; font-weight: 600;
  color: #666; text-decoration: none; border-radius: 0;
  transition: all .2s; border-left: 3px solid transparent;
}
.sidebar-menu a:hover, .sidebar-menu a.active {
  color: var(--pk); background: #fff5f5;
  border-left-color: var(--pk);
}
.sidebar-menu a i { width: 18px; text-align: center; }
.sidebar-bottom {
  padding: 16px 20px;
  border-top: 1px solid #f0e8e0;
}
.sidebar-bottom a {
  display: flex; align-items: center; gap: 8px;
  color: #888; font-size: 13px; text-decoration: none;
  font-weight: 600; padding: 8px 0;
}
.sidebar-bottom a:hover { color: var(--pk); }
.sidebar-user {
  display: flex; align-items: center; gap: 10px;
  padding: 12px 16px; background: var(--cream);
  border-radius: 10px; margin-bottom: 8px;
}
.sidebar-user img { width: 36px; height: 36px; border-radius: 50%; object-fit: cover; }
.sidebar-user-icon { width: 36px; height: 36px; border-radius: 50%; background: var(--pk-light); display: flex; align-items: center; justify-content: center; color: var(--pk); }
.sidebar-user-name { font-size: 13px; font-weight: 700; color: #444; }
.sidebar-user-role { font-size: 11px; color: #999; }

/* MAIN */
.admin-main { margin-left: 240px; flex: 1; padding: 0; }
.admin-topbar {
  background: #fff; border-bottom: 1px solid #f0e8e0;
  padding: 16px 28px; display: flex; align-items: center;
  justify-content: space-between; position: sticky; top: 0; z-index: 50;
  box-shadow: 0 2px 8px rgba(0,0,0,.04);
}
.admin-topbar h1 { font-size: 18px; font-weight: 800; color: #333; margin: 0; }
.admin-topbar .date { font-size: 12px; color: #999; margin-top: 2px; }
.admin-content { padding: 28px; }

/* STAT CARDS */
.stat-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 24px; }
.stat-card {
  background: #fff; border-radius: 14px; padding: 20px 22px;
  border: 1px solid #f0e8e0;
  display: flex; align-items: center; gap: 16px;
  box-shadow: 0 2px 8px rgba(0,0,0,.04);
  transition: transform .2s, box-shadow .2s;
}
.stat-card:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(242,151,160,.15); }
.stat-icon {
  width: 52px; height: 52px; border-radius: 12px;
  display: flex; align-items: center; justify-content: center;
  font-size: 22px; flex-shrink: 0;
}
.stat-icon.pink { background: #fff0f1; color: var(--pk); }
.stat-icon.green { background: #f3f5e8; color: #7a7f3a; }
.stat-icon.blue { background: #e8f4fd; color: #3a8cc7; }
.stat-icon.orange { background: #fff3e8; color: #d97706; }
.stat-value { font-size: 24px; font-weight: 800; color: #333; line-height: 1; }
.stat-label { font-size: 12px; color: #999; margin-top: 4px; font-weight: 600; }
.stat-change { font-size: 11px; margin-top: 6px; font-weight: 700; }
.stat-change.up { color: #7a7f3a; }
.stat-change.down { color: var(--pk); }

/* GRID 2 COL */
.dash-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 24px; }
.dash-grid.full { grid-template-columns: 1fr; }
.dash-box {
  background: #F9D0CE; border-radius: 14px;
  border: 1px solid #f0e8e0; overflow: hidden;
  box-shadow: 0 2px 8px rgba(0,0,0,.04);
}
.dash-box-hdr {
  padding: 16px 20px; border-bottom: 1px solid #f8f0ee;
  display: flex; align-items: center; justify-content: space-between;
}
.dash-box-hdr h3 { font-size: 14px; font-weight: 800; color: #333; margin: 0; }
.dash-box-hdr a { font-size: 12px; color: var(--pk); text-decoration: none; font-weight: 700; }
.dash-box-body { padding: 0; }

/* TABLE */
.admin-table { width: 100%; border-collapse: collapse; font-size: 13px; }
.admin-table th { padding: 10px 16px; text-align: left; font-size: 11px; font-weight: 800; color: #999; text-transform: uppercase; letter-spacing: .5px; border-bottom: 1px solid #f5f0ee; background: #fdfaf8; }
.admin-table td { padding: 12px 16px; border-bottom: 1px solid #f8f5f3; color: #444; vertical-align: middle; }
.admin-table tr:last-child td { border-bottom: none; }
.admin-table tr:hover td { background: #fffaf9; }

/* STATUS BADGE */
.badge { display: inline-flex; align-items: center; gap: 4px; padding: 3px 9px; border-radius: 20px; font-size: 11px; font-weight: 700; }
.badge-pending { background: #fff8e1; color: #d97706; }
.badge-proses { background: #e8f4fd; color: #3a8cc7; }
.badge-kirim { background: #f3f5e8; color: #5a6e1a; }
.badge-selesai { background: #e8f5e9; color: #2e7d32; }
.badge-batal { background: #fce8e8; color: #c62828; }

/* CHART */
.chart-wrap { padding: 20px; }
.chart-bars { display: flex; align-items: flex-end; gap: 12px; height: 140px; }
.chart-bar-wrap { flex: 1; display: flex; flex-direction: column; align-items: center; gap: 4px; }
.chart-bar { width: 100%; border-radius: 6px 6px 0 0; transition: all .3s; cursor: pointer; position: relative; }
.chart-bar:hover { filter: brightness(.9); }
.chart-bar-label { font-size: 10px; color: #999; font-weight: 700; }
.chart-bar-val { font-size: 10px; color: #666; font-weight: 700; }

/* STOK RENDAH */
.stok-item {
  display: flex; align-items: center; justify-content: space-between;
  padding: 12px 16px; border-bottom: 1px solid #f8f5f3;
}
.stok-item:last-child { border-bottom: none; }
.stok-nama { font-size: 13px; font-weight: 700; color: #333; }
.stok-count { font-size: 12px; color: #999; }
.stok-badge { padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 800; background: #fce8e8; color: #c62828; }
</style>
</head>
<body>
<div class="admin-wrap">

<!-- SIDEBAR -->
<aside class="admin-sidebar">
  <a href="/beautify/index.php" class="sidebar-logo">Beauti<em>fy</em> <span style="font-size:11px;color:#bbb;font-family:Nunito">Admin</span></a>
  <nav class="sidebar-menu">
    <div class="sidebar-label">Menu</div>
    <a href="dashboard.php" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
    <a href="produk.php"><i class="fas fa-box"></i> Kelola Produk</a>
    <a href="pesanan.php"><i class="fas fa-shopping-bag"></i> Kelola Pesanan</a>
    <a href="laporan.php"><i class="fas fa-chart-bar"></i> Laporan & Statistik</a>
    <div class="sidebar-label">Pengaturan</div>
    <a href="profile.php"><i class="fas fa-user-circle"></i> Profil Admin</a>
    <a href="/beautify/index.php"><i class="fas fa-store"></i> Lihat Toko</a>
  </nav>
  <div class="sidebar-bottom">
    <div class="sidebar-user">
      <?php $f = fotoSrc(userFoto()); ?>
      <?php if ($f): ?>
        <img src="<?= $f ?>" alt="foto">
      <?php else: ?>
        <div class="sidebar-user-icon"><i class="fas fa-user"></i></div>
      <?php endif; ?>
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
    <div>
      <h1>Dashboard Admin</h1>
      <div class="date"><?= date('l, d F Y') ?></div>
    </div>
    <div style="display:flex;align-items:center;gap:12px">
      <a href="produk.php" style="background:var(--pk);color:#fff;padding:8px 16px;border-radius:8px;font-size:13px;font-weight:700;text-decoration:none;display:flex;align-items:center;gap:6px">
        <i class="fas fa-plus"></i> Tambah Produk
      </a>
    </div>
  </div>

  <div class="admin-content">

    <!-- STAT CARDS -->
    <div class="stat-grid">
      <div class="stat-card">
        <div class="stat-icon pink"><i class="fas fa-box"></i></div>
        <div>
          <div class="stat-value"><?= number_format($total_produk) ?></div>
          <div class="stat-label">Total Produk</div>
          <div class="stat-change up">↑ Aktif di toko</div>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon blue"><i class="fas fa-users"></i></div>
        <div>
          <div class="stat-value"><?= number_format($total_user) ?></div>
          <div class="stat-label">Total Member</div>
          <div class="stat-change up">↑ Pengguna terdaftar</div>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon orange"><i class="fas fa-shopping-bag"></i></div>
        <div>
          <div class="stat-value"><?= number_format($total_pesanan) ?></div>
          <div class="stat-label">Total Pesanan</div>
          <div class="stat-change up">↑ Semua pesanan</div>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon green"><i class="fas fa-wallet"></i></div>
        <div>
          <div class="stat-value" style="font-size:18px">Rp <?= number_format($total_revenue, 0, ',', '.') ?></div>
          <div class="stat-label">Total Revenue</div>
          <div class="stat-change up">↑ Semua waktu</div>
        </div>
      </div>
    </div>

    <!-- CHART + STOK -->
    <div class="dash-grid">
      <div class="dash-box">
        <div class="dash-box-hdr">
          <h3>📊 Pesanan 6 Bulan Terakhir</h3>
          <a href="laporan.php">Lihat Laporan →</a>
        </div>
        <div class="dash-box-body chart-wrap">
          <?php
          $maxPesanan = max(array_column($chart_data, 'pesanan'));
          if ($maxPesanan == 0) $maxPesanan = 1;
          ?>
          <div class="chart-bars">
            <?php foreach ($chart_data as $cd): ?>
            <div class="chart-bar-wrap">
              <div class="chart-bar-val"><?= $cd['pesanan'] ?></div>
              <div class="chart-bar"
                style="height:<?= max(8, round(($cd['pesanan'] / $maxPesanan) * 110)) ?>px;
                       background: linear-gradient(180deg, var(--pk) 0%, var(--pk-light) 100%);">
              </div>
              <div class="chart-bar-label"><?= $cd['bulan'] ?></div>
            </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>

      <div class="dash-box">
        <div class="dash-box-hdr">
          <h3>⚠️ Stok Hampir Habis</h3>
          <a href="produk.php">Kelola →</a>
        </div>
        <div class="dash-box-body">
          <?php if (!$stok_rendah || $stok_rendah->num_rows === 0): ?>
            <div style="padding:30px;text-align:center;color:#aaa;font-size:13px">Semua stok aman ✓</div>
          <?php else: while ($s = $stok_rendah->fetch_assoc()): ?>
          <div class="stok-item">
            <div>
              <div class="stok-nama"><?= htmlspecialchars($s['nama']) ?></div>
              <div class="stok-count">Sisa stok</div>
            </div>
            <div class="stok-badge"><?= $s['stok'] ?> pcs</div>
          </div>
          <?php endwhile; endif; ?>
        </div>
      </div>
    </div>

    <!-- PESANAN TERBARU -->
    <div class="dash-box">
      <div class="dash-box-hdr">
        <h3>🛍️ Pesanan Terbaru</h3>
        <a href="pesanan.php">Lihat Semua →</a>
      </div>
      <div class="dash-box-body">
        <table class="admin-table">
          <thead>
            <tr>
              <th>#ID</th>
              <th>Pelanggan</th>
              <th>Total</th>
              <th>Status</th>
              <th>Tanggal</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php if (!$pesanan_baru || $pesanan_baru->num_rows === 0): ?>
            <tr><td colspan="6" style="text-align:center;color:#aaa;padding:30px">Belum ada pesanan</td></tr>
            <?php else: while ($p = $pesanan_baru->fetch_assoc()):
              $badge = ['pending'=>'badge-pending','diproses'=>'badge-proses','dikirim'=>'badge-kirim','selesai'=>'badge-selesai','dibatalkan'=>'badge-batal'][$p['status']] ?? 'badge-pending';
            ?>
            <tr>
              <td style="font-weight:700">#<?= $p['id'] ?></td>
              <td><?= htmlspecialchars($p['nama_user']) ?></td>
              <td style="font-weight:700">Rp <?= number_format($p['total_harga'],0,',','.') ?></td>
              <td><span class="badge <?= $badge ?>"><?= ucfirst($p['status']) ?></span></td>
              <td><?= date('d M Y', strtotime($p['created_at'])) ?></td>
              <td><a href="pesanan.php?id=<?= $p['id'] ?>" style="color:var(--pk);font-weight:700;font-size:12px;text-decoration:none">Detail →</a></td>
            </tr>
            <?php endwhile; endif; ?>
          </tbody>
        </table>
      </div>
    </div>

  </div><!-- /admin-content -->
</main>
</div>
</body>
</html>
