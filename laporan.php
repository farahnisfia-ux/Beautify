<?php
session_start();
require_once '../../includes/db.php';
require_once '../../includes/auth.php';
requireAdmin();

// Filter periode
$periode = $_GET['periode'] ?? 'bulan_ini';
switch ($periode) {
    case 'hari_ini':    $where_date = "DATE(created_at) = CURDATE()"; break;
    case 'minggu_ini':  $where_date = "YEARWEEK(created_at, 1) = YEARWEEK(CURDATE(), 1)"; break;
    case 'bulan_lalu':  $where_date = "MONTH(created_at) = MONTH(DATE_SUB(NOW(), INTERVAL 1 MONTH)) AND YEAR(created_at) = YEAR(DATE_SUB(NOW(), INTERVAL 1 MONTH))"; break;
    case 'tahun_ini':   $where_date = "YEAR(created_at) = YEAR(NOW())"; break;
    default:            $where_date = "MONTH(created_at) = MONTH(NOW()) AND YEAR(created_at) = YEAR(NOW())"; break;
}

$base_where = "WHERE status != 'dibatalkan' AND $where_date";

// Summary stats
$stat = $conn->query("SELECT COUNT(*) as total_pesanan, SUM(total_harga) as revenue, AVG(total_harga) as avg_order FROM pesanan $base_where")->fetch_assoc();
$selesai  = $conn->query("SELECT COUNT(*) as c FROM pesanan WHERE status='selesai' AND $where_date")->fetch_assoc()['c'];
$batal    = $conn->query("SELECT COUNT(*) as c FROM pesanan WHERE status='dibatalkan' AND $where_date")->fetch_assoc()['c'];

// Produk terlaris (dari detail_pesanan atau pesanan, fallback ke produk)
$produk_laris = $conn->query("
  SELECT pr.nama, COUNT(dp.produk_id) as terjual, SUM(dp.harga * dp.qty) as revenue
  FROM detail_pesanan dp
  JOIN produk pr ON dp.produk_id = pr.id
  JOIN pesanan p ON dp.pesanan_id = p.id
  WHERE p.status != 'dibatalkan' AND p.$where_date
  GROUP BY dp.produk_id ORDER BY terjual DESC LIMIT 5
");
// Fallback jika tabel detail_pesanan belum ada
if (!$produk_laris) {
    $produk_laris = $conn->query("SELECT nama, stok as terjual, harga*stok as revenue FROM produk ORDER BY id DESC LIMIT 5");
}

// Pesanan per status (pie chart)
$per_status = [];
$res = $conn->query("SELECT status, COUNT(*) as c FROM pesanan WHERE $where_date GROUP BY status");
while ($r = $res->fetch_assoc()) $per_status[$r['status']] = (int)$r['c'];

// Revenue per hari (30 hari)
$revenue_harian = [];
for ($i = 29; $i >= 0; $i--) {
    $res = $conn->query("SELECT SUM(total_harga) as rev, COUNT(*) as cnt FROM pesanan WHERE DATE(created_at) = DATE_SUB(CURDATE(), INTERVAL $i DAY) AND status != 'dibatalkan'");
    $row = $res->fetch_assoc();
    $revenue_harian[] = ['hari' => date('d/m', strtotime("-$i days")), 'rev' => (float)$row['rev'], 'cnt' => (int)$row['cnt']];
}

// Top pelanggan
$top_user = $conn->query("SELECT u.nama, u.email, COUNT(p.id) as total_pesanan, SUM(p.total_harga) as total_belanja FROM pesanan p JOIN users u ON p.user_id = u.id WHERE p.status != 'dibatalkan' GROUP BY p.user_id ORDER BY total_belanja DESC LIMIT 5");

// Kategori revenue
$kat_revenue_query = $conn->query("
  SELECT k.nama, COUNT(dp.id) as terjual, SUM(dp.harga * dp.qty) as revenue
  FROM detail_pesanan dp
  JOIN produk pr ON dp.produk_id = pr.id
  JOIN kategori k ON pr.kategori_id = k.id
  JOIN pesanan p ON dp.pesanan_id = p.id
  WHERE p.status != 'dibatalkan'
  GROUP BY k.id ORDER BY revenue DESC
");
$kat_data = [];
if ($kat_revenue_query) { while ($r = $kat_revenue_query->fetch_assoc()) $kat_data[] = $r; }
if (empty($kat_data)) {
    $res = $conn->query("SELECT k.nama, 0 as terjual, 0 as revenue FROM kategori k LIMIT 5");
    while ($r = $res->fetch_assoc()) $kat_data[] = $r;
}

$periode_label = ['hari_ini'=>'Hari Ini','minggu_ini'=>'Minggu Ini','bulan_ini'=>'Bulan Ini','bulan_lalu'=>'Bulan Lalu','tahun_ini'=>'Tahun Ini'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Laporan & Statistik – Admin Beautify</title>
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
.admin-topbar { background:#fff; border-bottom:1px solid #f0e8e0; padding:16px 28px; display:flex; align-items:center; justify-content:space-between; position:sticky; top:0; z-index:50; box-shadow:0 2px 8px rgba(0,0,0,.04); }
.admin-topbar h1 { font-size:18px; font-weight:800; color:#333; margin:0; }
.admin-content { padding:28px; }

/* PERIODE TABS */
.periode-tabs { display:flex; gap:8px; margin-bottom:24px; flex-wrap:wrap; }
.periode-tab { padding:8px 16px; border-radius:20px; font-size:13px; font-weight:700; text-decoration:none; border:2px solid #e8e0dc; color:#888; transition:all .2s; }
.periode-tab:hover { border-color:var(--pk); color:var(--pk); }
.periode-tab.active { border-color:var(--pk); background:var(--pk); color:#fff; }

/* STAT CARDS */
.stat-grid { display:grid; grid-template-columns:repeat(4,1fr); gap:16px; margin-bottom:24px; }
.stat-card { background:#fff; border-radius:14px; padding:20px 22px; border:1px solid #f0e8e0; display:flex; align-items:center; gap:16px; box-shadow:0 2px 8px rgba(0,0,0,.04); }
.stat-icon { width:52px; height:52px; border-radius:12px; display:flex; align-items:center; justify-content:center; font-size:22px; flex-shrink:0; }
.stat-icon.pink { background:#fff0f1; color:var(--pk); }
.stat-icon.green { background:#f3f5e8; color:#7a7f3a; }
.stat-icon.blue { background:#e8f4fd; color:#3a8cc7; }
.stat-icon.orange { background:#fff3e8; color:#d97706; }
.stat-value { font-size:22px; font-weight:800; color:#333; line-height:1; }
.stat-label { font-size:12px; color:#999; margin-top:4px; font-weight:600; }

/* GRID */
.dash-grid { display:grid; grid-template-columns:1fr 1fr; gap:20px; margin-bottom:24px; }
.dash-box { background:#fff; border-radius:14px; border:1px solid #f0e8e0; overflow:hidden; box-shadow:0 2px 8px rgba(0,0,0,.04); }
.dash-box-hdr { padding:16px 20px; border-bottom:1px solid #f8f0ee; }
.dash-box-hdr h3 { font-size:14px; font-weight:800; color:#333; margin:0; }
.dash-box-body { padding:20px; }

/* CHART BARS */
.chart-bars-h { display:flex; flex-direction:column; gap:10px; }
.chart-bar-h-wrap { display:flex; align-items:center; gap:10px; }
.chart-bar-h-label { width:110px; font-size:12px; font-weight:700; color:#555; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; flex-shrink:0; }
.chart-bar-h { height:22px; border-radius:4px; display:flex; align-items:center; padding-left:8px; font-size:11px; color:#fff; font-weight:700; min-width:8px; transition:all .3s; }
.chart-bar-h-val { font-size:11px; color:#888; font-weight:700; white-space:nowrap; }

/* SPARKLINE */
.sparkline-wrap { overflow-x:auto; padding-bottom:8px; }
.sparkline-bars { display:flex; align-items:flex-end; gap:4px; height:100px; min-width:600px; }
.sp-bar { flex:1; border-radius:3px 3px 0 0; min-height:4px; cursor:pointer; transition:.2s; position:relative; }
.sp-bar:hover { filter:brightness(.85); }
.sp-labels { display:flex; gap:4px; min-width:600px; margin-top:4px; }
.sp-label { flex:1; font-size:9px; color:#bbb; text-align:center; }

/* TABLE */
.admin-table { width:100%; border-collapse:collapse; font-size:13px; }
.admin-table th { padding:10px 16px; text-align:left; font-size:11px; font-weight:800; color:#999; text-transform:uppercase; letter-spacing:.5px; border-bottom:1px solid #f5f0ee; background:#fdfaf8; }
.admin-table td { padding:11px 16px; border-bottom:1px solid #f8f5f3; color:#444; }
.admin-table tr:last-child td { border-bottom:none; }

/* STATUS PIE (simple) */
.pie-legend { display:flex; flex-direction:column; gap:10px; }
.pie-item { display:flex; align-items:center; gap:10px; }
.pie-dot { width:12px; height:12px; border-radius:3px; flex-shrink:0; }
.pie-label { font-size:13px; font-weight:700; color:#444; flex:1; }
.pie-val { font-size:13px; font-weight:800; color:#333; }
.pie-bar-wrap { flex:2; height:8px; background:#f0e8e0; border-radius:4px; overflow:hidden; }
.pie-bar { height:100%; border-radius:4px; }
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
    <a href="laporan.php" class="active"><i class="fas fa-chart-bar"></i> Laporan & Statistik</a>
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
    <div><h1>📊 Laporan & Statistik</h1></div>
    <a href="laporan.php?export=1" style="background:var(--green);color:#fff;padding:8px 16px;border-radius:8px;font-size:13px;font-weight:700;text-decoration:none;display:flex;align-items:center;gap:6px">
      <i class="fas fa-download"></i> Export CSV
    </a>
  </div>

  <div class="admin-content">

    <!-- PERIODE -->
    <div class="periode-tabs">
      <?php foreach ($periode_label as $k => $v): ?>
      <a href="laporan.php?periode=<?= $k ?>" class="periode-tab <?= $periode===$k?'active':'' ?>"><?= $v ?></a>
      <?php endforeach; ?>
    </div>

    <!-- STATS -->
    <div class="stat-grid">
      <div class="stat-card">
        <div class="stat-icon orange"><i class="fas fa-shopping-bag"></i></div>
        <div>
          <div class="stat-value"><?= number_format($stat['total_pesanan']) ?></div>
          <div class="stat-label">Total Pesanan</div>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon green"><i class="fas fa-wallet"></i></div>
        <div>
          <div class="stat-value" style="font-size:16px">Rp <?= number_format($stat['revenue'] ?? 0, 0, ',', '.') ?></div>
          <div class="stat-label">Revenue</div>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon blue"><i class="fas fa-receipt"></i></div>
        <div>
          <div class="stat-value" style="font-size:16px">Rp <?= number_format($stat['avg_order'] ?? 0, 0, ',', '.') ?></div>
          <div class="stat-label">Avg Order Value</div>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon pink"><i class="fas fa-check-circle"></i></div>
        <div>
          <div class="stat-value"><?= number_format($selesai) ?></div>
          <div class="stat-label">Pesanan Selesai</div>
        </div>
      </div>
    </div>

    <!-- CHART REVENUE 30 HARI + STATUS -->
    <div class="dash-grid">
      <div class="dash-box">
        <div class="dash-box-hdr"><h3>📈 Revenue 30 Hari Terakhir</h3></div>
        <div class="dash-box-body">
          <?php $maxRev = max(array_column($revenue_harian, 'rev')); if (!$maxRev) $maxRev = 1; ?>
          <div class="sparkline-wrap">
            <div class="sparkline-bars">
              <?php foreach ($revenue_harian as $i => $d): ?>
              <div class="sp-bar" title="<?= $d['hari'] ?>: Rp <?= number_format($d['rev'],0,',','.') ?>"
                style="height:<?= max(4, round(($d['rev']/$maxRev)*90)) ?>px;
                       background:<?= $d['rev'] > 0 ? 'linear-gradient(180deg,#F297A0,#F9D0CE)' : '#f0e8e0' ?>">
              </div>
              <?php endforeach; ?>
            </div>
            <div class="sp-labels">
              <?php foreach ($revenue_harian as $i => $d): ?>
              <div class="sp-label"><?= $i % 7 === 0 ? $d['hari'] : '' ?></div>
              <?php endforeach; ?>
            </div>
          </div>
        </div>
      </div>

      <div class="dash-box">
        <div class="dash-box-hdr"><h3>🥧 Status Pesanan</h3></div>
        <div class="dash-box-body">
          <?php
          $total_all = array_sum($per_status) ?: 1;
          $status_colors = ['pending'=>'#F297A0','diproses'=>'#74b9e8','dikirim'=>'#B6BB79','selesai'=>'#6bc97e','dibatalkan'=>'#e88080'];
          $status_names = ['pending'=>'Pending','diproses'=>'Diproses','dikirim'=>'Dikirim','selesai'=>'Selesai','dibatalkan'=>'Dibatalkan'];
          foreach ($status_names as $s => $n):
            $v = $per_status[$s] ?? 0; $pct = round($v/$total_all*100);
          ?>
          <div class="pie-item" style="margin-bottom:12px">
            <div class="pie-dot" style="background:<?= $status_colors[$s] ?>"></div>
            <div class="pie-label"><?= $n ?></div>
            <div class="pie-bar-wrap"><div class="pie-bar" style="width:<?= $pct ?>%;background:<?= $status_colors[$s] ?>"></div></div>
            <div class="pie-val"><?= $v ?> <span style="font-size:11px;color:#aaa">(<?= $pct ?>%)</span></div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>

    <!-- PRODUK LARIS + TOP USER -->
    <div class="dash-grid">
      <div class="dash-box">
        <div class="dash-box-hdr"><h3>🏆 Produk Terlaris</h3></div>
        <div class="dash-box-body">
          <?php
          $rows = [];
          if ($produk_laris) { while ($r = $produk_laris->fetch_assoc()) $rows[] = $r; }
          $maxTerjual = max(array_column($rows, 'terjual') ?: [1]);
          ?>
          <div class="chart-bars-h">
            <?php foreach ($rows as $i => $r):
              $colors = ['#F297A0','#F9D0CE','#B6BB79','#f0c87a','#74b9e8'];
            ?>
            <div class="chart-bar-h-wrap">
              <div class="chart-bar-h-label"><?= htmlspecialchars($r['nama']) ?></div>
              <div class="chart-bar-h"
                style="width:<?= max(8, round(($r['terjual']/($maxTerjual?:1))*180)) ?>px;background:<?= $colors[$i%5] ?>;color:#333">
                <?= $r['terjual'] ?>
              </div>
              <div class="chart-bar-h-val">Rp <?= number_format($r['revenue'],0,',','.') ?></div>
            </div>
            <?php endforeach; ?>
            <?php if (empty($rows)): ?><p style="color:#aaa;text-align:center;font-size:13px">Belum ada data</p><?php endif; ?>
          </div>
        </div>
      </div>

      <div class="dash-box">
        <div class="dash-box-hdr"><h3>👑 Top Pelanggan</h3></div>
        <div class="dash-box-body" style="padding:0">
          <table class="admin-table">
            <thead><tr><th>Pelanggan</th><th>Pesanan</th><th>Total Belanja</th></tr></thead>
            <tbody>
              <?php if ($top_user->num_rows === 0): ?>
              <tr><td colspan="3" style="text-align:center;color:#aaa;padding:20px">Belum ada data</td></tr>
              <?php else: $rank=1; while ($u = $top_user->fetch_assoc()): ?>
              <tr>
                <td>
                  <div style="display:flex;align-items:center;gap:8px">
                    <div style="width:24px;height:24px;border-radius:50%;background:<?= ['#F297A0','#F9D0CE','#B6BB79','#f0c87a','#74b9e8'][$rank-1] ?>;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:800;color:#fff"><?= $rank++ ?></div>
                    <div>
                      <div style="font-weight:700;font-size:13px"><?= htmlspecialchars($u['nama']) ?></div>
                      <div style="font-size:11px;color:#aaa"><?= htmlspecialchars($u['email']) ?></div>
                    </div>
                  </div>
                </td>
                <td style="font-weight:700"><?= $u['total_pesanan'] ?>x</td>
                <td style="font-weight:700;font-size:12px">Rp <?= number_format($u['total_belanja'],0,',','.') ?></td>
              </tr>
              <?php endwhile; endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- KATEGORI -->
    <?php if (!empty($kat_data)): ?>
    <div class="dash-box" style="margin-bottom:24px">
      <div class="dash-box-hdr"><h3>📂 Revenue per Kategori</h3></div>
      <div class="dash-box-body">
        <?php $maxKat = max(array_column($kat_data, 'revenue') ?: [1]); ?>
        <div class="chart-bars-h">
          <?php foreach ($kat_data as $i => $k):
            $colors = ['#F297A0','#B6BB79','#F9D0CE','#f0c87a','#74b9e8'];
          ?>
          <div class="chart-bar-h-wrap">
            <div class="chart-bar-h-label"><?= htmlspecialchars($k['nama']) ?></div>
            <div class="chart-bar-h"
              style="width:<?= max(8, round(($k['revenue']/($maxKat?:1))*240)) ?>px;background:<?= $colors[$i%5] ?>;color:#333">
              <?= $k['terjual'] ?>
            </div>
            <div class="chart-bar-h-val">Rp <?= number_format($k['revenue'],0,',','.') ?></div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
    <?php endif; ?>

  </div>
</main>
</div>
</body>
</html>
<?php
// Export CSV
if (isset($_GET['export'])) {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=laporan_beautify_'.date('Y-m-d').'.csv');
    $out = fopen('php://output', 'w');
    fputs($out, "\xEF\xBB\xBF");
    fputcsv($out, ['ID','Pelanggan','Email','Total','Status','Tanggal']);
    $all = $conn->query("SELECT p.id, u.nama, u.email, p.total_harga, p.status, p.created_at FROM pesanan p JOIN users u ON p.user_id=u.id ORDER BY p.created_at DESC");
    while ($r = $all->fetch_assoc()) {
        fputcsv($out, [$r['id'], $r['nama'], $r['email'], $r['total_harga'], $r['status'], $r['created_at']]);
    }
    fclose($out);
    exit;
}
?>
