<?php
session_start();
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
if (isset($_SESSION['user_id'])) { header("Location: index.php"); exit; }
$error=''; $success='';
if ($_SERVER['REQUEST_METHOD']==='POST') {
    $nama  = trim($_POST['nama']??'');
    $email = trim($_POST['email']??'');
    $pass  = $_POST['password']??'';
    $conf  = $_POST['confirm']??'';
    if (!$nama||!$email||!$pass) { $error="Semua field wajib diisi."; }
    elseif (!filter_var($email,FILTER_VALIDATE_EMAIL)) { $error="Format email tidak valid."; }
    elseif (strlen($pass)<6) { $error="Password minimal 6 karakter."; }
    elseif ($pass!==$conf) { $error="Konfirmasi password tidak cocok."; }
    else {
        $e=$conn->real_escape_string($email);
        if ($conn->query("SELECT id FROM users WHERE email='$e'")->num_rows>0) {
            $error="Email sudah terdaftar.";
        } else {
            $n=$conn->real_escape_string($nama);
            $h=password_hash($pass,PASSWORD_DEFAULT);
            $conn->query("INSERT INTO users (nama,email,password,role) VALUES ('$n','$e','$h','user')");
            $success="Akun berhasil dibuat! Silakan masuk.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Daftar – Beautify</title>
<link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&family=Playfair+Display:wght@300;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="/beautify/assets/css/style.css">
</head>
<body>
<div class="auth-page">
<div class="auth-wrap">
  <div class="auth-left">
    <div class="auth-brand">Beauti<em>fy</em></div>
    <div class="auth-brand-tag">Your Beauty, Our Priority</div>
    <p class="auth-left-desc">Bergabunglah dengan ribuan pengguna Beautify dan temukan produk kecantikan terbaik!</p>
    <div class="auth-dots"><span></span><span></span><span></span></div>
  </div>
  <div class="auth-right">
    <div class="auth-greeting">🌸 Selamat Datang</div>
    <h1 class="auth-title">Buat Akun Baru</h1>
    <p class="auth-sub">Sudah punya akun? <a href="login.php">Masuk di sini</a></p>
    <?php if($error): ?>
    <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if($success): ?>
    <div class="alert alert-success"><i class="fas fa-check-circle"></i><?= $success ?> <a href="login.php" style="color:var(--GD);font-weight:800">Masuk sekarang →</a></div>
    <?php endif; ?>
    <form method="POST">
      <div class="form-group">
        <label class="form-label">Nama Lengkap</label>
        <div class="input-wrap">
          <i class="fas fa-user fi"></i>
          <input type="text" name="nama" class="form-control" placeholder="Nama lengkap kamu" value="<?= htmlspecialchars($_POST['nama']??'') ?>" required>
        </div>
      </div>
      <div class="form-group">
        <label class="form-label">Email</label>
        <div class="input-wrap">
          <i class="fas fa-envelope fi"></i>
          <input type="email" name="email" class="form-control" placeholder="email@contoh.com" value="<?= htmlspecialchars($_POST['email']??'') ?>" required>
        </div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">Password</label>
          <div class="input-wrap">
            <i class="fas fa-lock fi"></i>
            <input type="password" name="password" id="pw1" class="form-control has-right" placeholder="Min. 6 karakter" required>
            <button type="button" class="toggle-pw" onclick="togglePw('pw1','eye1')"><i class="fas fa-eye" id="eye1"></i></button>
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Konfirmasi Password</label>
          <div class="input-wrap">
            <i class="fas fa-lock fi"></i>
            <input type="password" name="confirm" id="pw2" class="form-control has-right" placeholder="Ulangi password" required>
            <button type="button" class="toggle-pw" onclick="togglePw('pw2','eye2')"><i class="fas fa-eye" id="eye2"></i></button>
          </div>
        </div>
      </div>
      <button type="submit" class="btn-primary full"><i class="fas fa-user-plus"></i> Buat Akun</button>
    </form>
    <p style="text-align:center;margin-top:14px;font-size:12px;color:var(--TL)">
      <a href="index.php" style="color:var(--P);font-weight:700"><i class="fas fa-arrow-left"></i> Kembali ke Beranda</a>
    </p>
  </div>
</div>
</div>
<script>
function togglePw(id,eyeId){
    const i=document.getElementById(id),e=document.getElementById(eyeId);
    i.type=i.type==='password'?'text':'password';
    e.className=i.type==='password'?'fas fa-eye':'fas fa-eye-slash';
}
</script>
</body></html>