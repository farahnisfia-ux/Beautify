-- ============================================================
--  BEAUTIFY – SQL Patch untuk fitur Profile & Admin
--  Jalankan file ini di phpMyAdmin atau MySQL CLI
--  Database: beautify
-- ============================================================

USE beautify;

-- ------------------------------------------------------------
-- 1. Tabel USERS – tambah kolom yang mungkin belum ada
-- ------------------------------------------------------------
ALTER TABLE users
  ADD COLUMN IF NOT EXISTS telepon      VARCHAR(20)  DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS alamat       TEXT         DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS tgl_lahir    DATE         DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS jenis_kelamin VARCHAR(15) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS foto         VARCHAR(255) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS role         ENUM('user','admin') NOT NULL DEFAULT 'user',
  ADD COLUMN IF NOT EXISTS created_at   DATETIME     DEFAULT CURRENT_TIMESTAMP,
  ADD COLUMN IF NOT EXISTS updated_at   DATETIME     DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

-- ------------------------------------------------------------
-- 2. Tabel KATEGORI
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS kategori (
  id    INT AUTO_INCREMENT PRIMARY KEY,
  nama  VARCHAR(100) NOT NULL,
  icon  VARCHAR(10)  DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed kategori default (skip jika sudah ada)
INSERT IGNORE INTO kategori (id, nama, icon) VALUES
  (1, 'Lips',     '💄'),
  (2, 'Face',     '✨'),
  (3, 'Eyes',     '👁'),
  (4, 'Skincare', '🌿'),
  (5, 'Blush',    '🌸');

-- ------------------------------------------------------------
-- 3. Tabel PRODUK
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS produk (
  id           INT AUTO_INCREMENT PRIMARY KEY,
  nama         VARCHAR(255) NOT NULL,
  deskripsi    TEXT         DEFAULT NULL,
  harga        INT          NOT NULL DEFAULT 0,
  stok         INT          NOT NULL DEFAULT 0,
  gambar       VARCHAR(255) DEFAULT NULL,
  kategori_id  INT          DEFAULT NULL,
  created_at   DATETIME     DEFAULT CURRENT_TIMESTAMP,
  updated_at   DATETIME     DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (kategori_id) REFERENCES kategori(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- 4. Tabel PESANAN
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS pesanan (
  id           INT AUTO_INCREMENT PRIMARY KEY,
  user_id      INT          NOT NULL,
  total_harga  BIGINT       NOT NULL DEFAULT 0,
  metode_bayar VARCHAR(50)  DEFAULT NULL,
  status       ENUM('pending','diproses','dikirim','selesai','dibatalkan') NOT NULL DEFAULT 'pending',
  alamat_kirim TEXT         DEFAULT NULL,
  catatan      TEXT         DEFAULT NULL,
  created_at   DATETIME     DEFAULT CURRENT_TIMESTAMP,
  updated_at   DATETIME     DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- 5. Tabel DETAIL_PESANAN
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS detail_pesanan (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  pesanan_id  INT NOT NULL,
  produk_id   INT NOT NULL,
  qty         INT NOT NULL DEFAULT 1,
  harga       INT NOT NULL DEFAULT 0,
  created_at  DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (pesanan_id) REFERENCES pesanan(id) ON DELETE CASCADE,
  FOREIGN KEY (produk_id)  REFERENCES produk(id)  ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- 6. Tabel WISHLIST
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS wishlist (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  user_id    INT NOT NULL,
  produk_id  INT NOT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uniq_wish (user_id, produk_id),
  FOREIGN KEY (user_id)   REFERENCES users(id)   ON DELETE CASCADE,
  FOREIGN KEY (produk_id) REFERENCES produk(id)  ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- 7. Buat akun ADMIN default (password: admin123)
--    Ganti password setelah login pertama kali!
-- ------------------------------------------------------------
INSERT INTO users (nama, email, password, role) 
SELECT 'Admin Beautify', 'admin@beautify.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'
WHERE NOT EXISTS (
  SELECT 1 FROM users WHERE email = 'admin@beautify.com'
);

-- ============================================================
-- SELESAI! Semua tabel siap digunakan.
-- ============================================================
