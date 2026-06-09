<?php
session_start();
require_once '../../includes/db.php';
require_once '../../includes/auth.php';
requireAdmin();

// Update status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $id     = (int)$_POST['pesanan_id'];
    $status = $_POST['status'];
    $allowed = ['pending','diproses','dikirim','selesai','dibatalkan'];
    if (in_array($status, $allowed)) {
        $conn->query("UPDATE pesanan SET status='$status' WHERE id=$id");
    }
    header("Location: pesanan.php?msg=update_ok");
    exit;
}

// Filter
$status_filter = $_GET['status'] ?? '';
$search        = trim($_GET['q'] ?? '');
$where = "WHERE 1=1";
if ($status_filter) $where .= " AND p.status='".mysqli_real_escape_string($conn,$status_filter)."'";
if ($search) $where .= " AND (u.nama LIKE '%".mysqli_real_escape_string($conn,$search)."%' OR p.id='".mysqli_real_escape_string($conn,$search)."')";

$pesanan = $conn->query("SELECT p.*, u.nama as nama_user, u.email FROM pesanan p JOIN users u ON p.user_id=u.id $where ORDER BY p.created_at DESC");

// Count per status
$counts = [];
$res = $conn->query("SELECT status, COUNT(*) as c FROM pesanan GROUP BY status");
while ($r = $res->fetch_assoc()) $counts[$r['status']] = $r['c'];

$msg_text = ['update_ok' => '✅ Status pesanan berhasil diupdate!'];
$msg = $msg_text[$_GET['msg'] ?? ''] ?? '';

$status_labels = ['pending'=>'Pending','diproses'=>'Diproses','dikirim'=>'Dikirim','selesai'=>'Selesai','dibatalkan'=>'Dibatalkan'];
$status_badges = ['pending'=>'badge-pending','diproses'=>'badge-proses','dikirim'=>'badge-kirim','selesai'=>'badge-selesai','dibatalkan'=>'badge-batal'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Kelola Pesanan – Admin Beautify</title>
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

/* STATUS TABS */
.status-tabs { display:flex; gap:8px; margin-bottom:20px; flex-wrap:wrap; }
.status-tab { padding:8px 16px; border-radius:20px; font-size:13px; font-weight:700; text-decoration:none; border:2px solid #e8e0dc; color:#888; transition:all .2s; display:flex; align-items:center; gap:6px; }
.status-tab:hover { border-color:var(--pk); color:var(--pk); }
.status-tab.active { border-color:var(--pk); background:var(--pk); color:#fff; }
.status-count { background:rgba(255,255,255,.3); border-radius:10px; padding:1px 6px; font-size:11px; }
.status-tab:not(.active) .status-count { background:#f0e8e0; color:#888; }

/* FILTER */
.filter-bar { display:flex; gap:10px; align-items:center; margin-bottom:16px; flex-wrap:wrap; }
.filter-bar input { padding:9px 14px; border:1.5px solid #e8e0dc; border-radius:8px; font-size:13px; font-family:Nunito; outline:none; background:#faf7f4; min-width:220px; }
.filter-bar input:focus { border-color:var(--pk); background:#fff; }
.btn-filter { background:var(--pk); color:#fff; border:none; padding:9px 18px; border-radius:8px; font-size:13px; font-weight:700; cursor:pointer; }
.btn-cancel { background:#f5f0ee; color:#666; border:none; padding:9px 18px; border-radius:8px; font-size:13px; font-weight:700; cursor:pointer; text-decoration:none; display:inline-flex; align-items:center; }

/* TABLE */
.table-card { background:#fff; border-radius:14px; border:1px solid #f0e8e0; overflow:hidden; box-shadow:0 2px 8px rgba(0,0,0,.04); }
.table-hdr { padding:16px 20px; border-bottom:1px solid #f8f0ee; display:flex; align-items:center; justify-content:space-between; }
.table-hdr h3 { font-size:14px; font-weight:800; color:#333; margin:0; }
.admin-table { width:100%; border-collapse:collapse; font-size:13px; }
.admin-table th { padding:10px 16px; text-align:left; font-size:11px; font-weight:800; color:#999; text-transform:uppercase; letter-spacing:.5px; border-bottom:1px solid #f5f0ee; background:#fdfaf8; }
.admin-table td { padding:12px 16px; border-bottom:1px solid #f8f5f3; color:#444; vertical-align:middle; }
.admin-table tr:last-child td { border-bottom:none; }
.admin-table tr:hover td { background:#fffaf9; }
.badge { display:inline-flex; align-items:center; gap:4px; padding:3px 9px; border-radius:20px; font-size:11px; font-weight:700; }
.badge-pending { background:#fff8e1; color:#d97706; }
.badge-proses { background:#e8f4fd; color:#3a8cc7; }
.badge-kirim { background:#f3f5e8; color:#5a6e1a; }
.badge-selesai { background:#e8f5e9; color:#2e7d32; }
.badge-batal { background:#fce8e8; color:#c62828; }

/* MODAL */
.modal-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,.4); z-index:200; align-items:center; justify-content:center; }
.modal-overlay.show { display:flex; }
.modal { background:#fff; border-radius:16px; padding:28px; width:440px; max-width:90vw; }
.modal h3 { font-size:16px; font-weight:800; margin:0 0 20px; color:#333; }
.modal-info { background:var(--cream); border-radius:10px; padding:14px 16px; margin-bottom:20px; }
.modal-info div { display:flex; justify-content:space-between; padding:4px 0; font-size:13px; }
.modal-info .label { color:#888; font-weight:600; }
.modal-info .val { font-weight:700; color:#333; }
.modal select { width:100%; padding:10px 14px; border:1.5px solid #e8e0dc; border-radius:8px; font-size:14px; font-family:Nunito; margin-bottom:16px; outline:none; }
.modal select:focus { border-color:var(--pk); }
.modal-actions { display:flex; gap:10px; }
.btn-modal-save { flex:1; background:var(--pk); color:#fff; border:none; padding:11px; border-radius:8px; font-size:14px; font-weight:700; cursor:pointer; }
.btn-modal-cancel { background:#f5f0ee; color:#666; border:none; padding:11px 20px; border-radius:8px; font-size:14px; font-weight:700; cursor:pointer; }
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
    <a href="pesanan.php" class="active"><i class="fas fa-shopping-bag"></i> Kelola Pesanan</a>
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
    <div><h1>🛍️ Kelola Pesanan</h1></div>
  </div>

  <div class="admin-content">
    <?php if ($msg): ?><div class="msg-box"><?= $msg ?></div><?php endif; ?>

    <!-- STATUS TABS -->
    <div class="status-tabs">
      <a href="pesanan.php" class="status-tab <?= !$status_filter ? 'active' : '' ?>">
        Semua <span class="status-count"><?= array_sum($counts) ?></span>
      </a>
      <?php foreach ($status_labels as $s => $label): ?>
      <a href="pesanan.php?status=<?= $s ?>" class="status-tab <?= $status_filter===$s ? 'active' : '' ?>">
        <?= $label ?> <span class="status-count"><?= $counts[$s] ?? 0 ?></span>
      </a>
      <?php endforeach; ?>
    </div>

    <!-- FILTER -->
    <form method="GET" class="filter-bar">
      <?php if ($status_filter): ?><input type="hidden" name="status" value="<?= $status_filter ?>"><?php endif; ?>
      <input type="text" name="q" placeholder="🔍 Cari nama pelanggan / #ID..." value="<?= htmlspecialchars($search) ?>">
      <button type="submit" class="btn-filter"><i class="fas fa-search"></i> Cari</button>
      <?php if ($search): ?><a href="pesanan.php<?= $status_filter ? '?status='.$status_filter : '' ?>" class="btn-cancel">Reset</a><?php endif; ?>
    </form>

    <!-- TABLE -->
    <div class="table-card">
      <div class="table-hdr">
        <h3>📋 Daftar Pesanan (<?= $pesanan->num_rows ?>)</h3>
      </div>
      <table class="admin-table">
        <thead>
          <tr>
            <th>#ID</th>
            <th>Pelanggan</th>
            <th>Total</th>
            <th>Metode Bayar</th>
            <th>Status</th>
            <th>Tanggal</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($pesanan->num_rows === 0): ?>
          <tr><td colspan="7" style="text-align:center;color:#aaa;padding:30px">Belum ada pesanan</td></tr>
          <?php else: while ($p = $pesanan->fetch_assoc()):
            $badge = $status_badges[$p['status']] ?? 'badge-pending';
          ?>
          <tr>
            <td style="font-weight:800;color:var(--pk)">#<?= $p['id'] ?></td>
            <td>
              <div style="font-weight:700"><?= htmlspecialchars($p['nama_user']) ?></div>
              <div style="font-size:11px;color:#999"><?= htmlspecialchars($p['email']) ?></div>
            </td>
            <td style="font-weight:700">Rp <?= number_format($p['total_harga'],0,',','.') ?></td>
            <td style="font-size:12px;color:#888"><?= htmlspecialchars($p['metode_bayar'] ?? '-') ?></td>
            <td><span class="badge <?= $badge ?>"><?= ucfirst($p['status']) ?></span></td>
            <td style="font-size:12px;color:#888"><?= date('d M Y H:i', strtotime($p['created_at'])) ?></td>
            <td>
              <button class="btn-edit" onclick="openModal(<?= $p['id'] ?>,'<?= $p['status'] ?>','<?= addslashes(htmlspecialchars($p['nama_user'])) ?>',<?= $p['total_harga'] ?>,'<?= date('d M Y', strtotime($p['created_at'])) ?>')"
                style="background:var(--pk-light);color:#a55;border:none;padding:5px 12px;border-radius:6px;font-size:12px;font-weight:700;cursor:pointer">
                <i class="fas fa-edit"></i> Update
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

<!-- MODAL UPDATE STATUS -->
<div class="modal-overlay" id="modalOverlay">
  <div class="modal">
    <h3>✏️ Update Status Pesanan</h3>
    <div class="modal-info">
      <div><span class="label">ID Pesanan</span><span class="val" id="mId"></span></div>
      <div><span class="label">Pelanggan</span><span class="val" id="mNama"></span></div>
      <div><span class="label">Total</span><span class="val" id="mTotal"></span></div>
      <div><span class="label">Tanggal</span><span class="val" id="mTgl"></span></div>
    </div>
    <form method="POST" id="formModal">
      <input type="hidden" name="update_status" value="1">
      <input type="hidden" name="pesanan_id" id="mPesananId">
      <label style="font-size:12px;font-weight:800;color:#666;text-transform:uppercase;letter-spacing:.5px;display:block;margin-bottom:8px">Status Baru</label>
      <select name="status" id="mStatus">
        <option value="pending">Pending</option>
        <option value="diproses">Diproses</option>
        <option value="dikirim">Dikirim</option>
        <option value="selesai">Selesai</option>
        <option value="dibatalkan">Dibatalkan</option>
      </select>
      <div class="modal-actions">
        <button type="submit" class="btn-modal-save"><i class="fas fa-save"></i> Simpan</button>
        <button type="button" class="btn-modal-cancel" onclick="closeModal()">Batal</button>
      </div>
    </form>
  </div>
</div>

<script>
function openModal(id, status, nama, total, tgl) {
  document.getElementById('mId').textContent = '#' + id;
  document.getElementById('mNama').textContent = nama;
  document.getElementById('mTotal').textContent = 'Rp ' + total.toLocaleString('id-ID');
  document.getElementById('mTgl').textContent = tgl;
  document.getElementById('mPesananId').value = id;
  document.getElementById('mStatus').value = status;
  document.getElementById('modalOverlay').classList.add('show');
}
function closeModal() { document.getElementById('modalOverlay').classList.remove('show'); }
document.getElementById('modalOverlay').addEventListener('click', function(e) {
  if (e.target === this) closeModal();
});
</script>
</body>
</html>
