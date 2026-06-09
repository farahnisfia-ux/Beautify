<?php
session_start();
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';

if (isset($_SESSION['user_id'])) {
    header("Location: " . (($_SESSION['user_role']==='admin') ? 'pages/admin/dashboard.php' : 'index.php'));
    exit;
}
$error = '';
if ($_SERVER['REQUEST_METHOD']==='POST') {
    $email = trim($_POST['email']??'');
    $pass  = $_POST['password']??'';
    if (!$email||!$pass) { $error="Email dan password wajib diisi."; }
    else {
        $e = $conn->real_escape_string($email);
        $u = $conn->query("SELECT * FROM users WHERE email='$e' LIMIT 1")->fetch_assoc();
        if ($u && password_verify($pass,$u['password'])) {
            $_SESSION['user_id']    = $u['id'];
            $_SESSION['user_nama']  = $u['nama'];
            $_SESSION['user_email'] = $u['email'];
            $_SESSION['user_foto']  = $u['foto']??'';
            $_SESSION['user_role']  = $u['role'];
            $redir = $_GET['redirect']??'';
            header("Location: ".($u['role']==='admin' ? 'pages/admin/dashboard.php' : ($redir?:'index.php')));
            exit;
        } else { $error="Email atau password salah."; }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Masuk – Beautify</title>
<link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&family=Playfair+Display:wght@300;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="style.css">
</head>
<body>
<div class="auth-page">
<div class="auth-wrap">
  <div class="auth-left">
    <div class="auth-brand">Beauti<em>fy</em></div>
    <div class="auth-brand-tag">Your Beauty, Our Priority</div>
    <p class="auth-left-desc">Selamat datang kembali! Temukan produk kecantikan terbaik untuk tampilan sempurna.</p>
    <div class="auth-dots"><span></span><span></span><span></span></div>
  </div>
  <div class="auth-right">
    <div class="auth-greeting">🌸 Halo</div>
    <h1 class="auth-title">Masuk ke Akun</h1>
    <p class="auth-sub">Belum punya akun? <a href="register.php">Daftar di sini</a></p>
    <?php if($error): ?>
    <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <form method="POST">
      <div class="form-group">
        <label class="form-label">Email</label>
        <div class="input-wrap">
          <i class="fas fa-envelope fi"></i>
          <input type="email" name="email" class="form-control" placeholder="email@contoh.com" value="<?= htmlspecialchars($_POST['email']??'') ?>" required autofocus>
        </div>
      </div>
      <div class="form-group">
        <label class="form-label">Password</label>
        <div class="input-wrap">
          <i class="fas fa-lock fi"></i>
          <input type="password" name="password" id="pwInput" class="form-control has-right" placeholder="Masukkan password" required>
          <button type="button" class="toggle-pw" onclick="togglePw()"><i class="fas fa-eye" id="pwEye"></i></button>
        </div>
      </div>
      <button type="submit" class="btn-primary full" style="margin-top:8px"><i class="fas fa-sign-in-alt"></i> Masuk Sekarang</button>
    </form>
    <p style="text-align:center;margin-top:16px;font-size:12px;color:var(--TL)">
      <a href="in1.php" style="color:var(--P);font-weight:700"><i class="fas fa-arrow-left"></i> Kembali ke Beranda</a>
    </p>
  </div>
</div>
</div>
<script>
function togglePw(){
    const i=document.getElementById('pwInput'),e=document.getElementById('pwEye');
    i.type=i.type==='password'?'text':'password';
    e.className=i.type==='password'?'fas fa-eye':'fas fa-eye-slash';
}
</script>
</body></html>