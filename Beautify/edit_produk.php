<?php
include 'koneksi.php';

// Validasi id
$id = intval($_GET['id'] ?? 0);
if ($id <= 0) {
    header("Location: produk.php");
    exit;
}

// Ambil data produk
$stmt = $conn->prepare("SELECT * FROM products WHERE id_product = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();

// Kalau produk tidak ditemukan, balik ke produk.php
if (!$data) {
    header("Location: produk.php");
    exit;
}

$errors = [];
$success = false;

// Proses update
if (isset($_POST['update'])) {
    $product_name = trim($_POST['product_name'] ?? '');
    $brand        = trim($_POST['brand'] ?? '');
    $price        = intval($_POST['price'] ?? 0);
    $stock        = intval($_POST['stock'] ?? 0);

    // Validasi sederhana
    if ($product_name === '') $errors[] = 'Nama produk tidak boleh kosong.';
    if ($brand === '')        $errors[] = 'Brand tidak boleh kosong.';
    if ($price <= 0)          $errors[] = 'Harga harus lebih dari 0.';
    if ($stock < 0)           $errors[] = 'Stok tidak boleh negatif.';

    if (empty($errors)) {
        // s = string, s = string, i = integer, i = integer, i = integer
        $update = $conn->prepare("
            UPDATE products
            SET product_name = ?,
                brand        = ?,
                price        = ?,
                stock        = ?
            WHERE id_product = ?
        ");
        $update->bind_param("ssiii", $product_name, $brand, $price, $stock, $id);
        $update->execute();

        header("Location: produk.php");
        exit;
    }

    // Kalau ada error, isi ulang field dengan nilai yang baru diketik
    $data['product_name'] = $product_name;
    $data['brand']        = $brand;
    $data['price']        = $price;
    $data['stock']        = $stock;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Produk – Beautify</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --pink: #F297A0;
            --pink-light: #F9D0CE;
            --bg: #F3EBD8;
            --text: #3B2A2B;
            --text-muted: #8A7070;
            --border: #EDD9CC;
            --secondary: #DCDFBA;
            --secondary-text: #5A5E3A;
            --card-radius: 12px;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: var(--bg);
            color: var(--text);
            font-size: 14px;
            min-height: 100vh;
            display: flex;
            align-items: flex-start;
            justify-content: center;
            padding: 40px 16px;
        }

        .card {
            background: white;
            border-radius: var(--card-radius);
            box-shadow: 0 4px 24px rgba(0,0,0,0.08);
            width: 100%;
            max-width: 520px;
            overflow: hidden;
        }

        .card-header {
            background: linear-gradient(135deg, #F297A0, #F9D0CE);
            padding: 24px 28px;
        }
        .card-header h2 {
            font-size: 20px;
            font-weight: 700;
            color: white;
            margin-bottom: 2px;
        }
        .card-header p {
            font-size: 12px;
            color: rgba(255,255,255,0.8);
        }

        .card-body { padding: 28px; }

        /* Error list */
        .error-box {
            background: #FFF0F1;
            border: 1px solid #F9D0CE;
            border-radius: 8px;
            padding: 12px 16px;
            margin-bottom: 20px;
        }
        .error-box p {
            font-size: 13px;
            font-weight: 600;
            color: #b5606b;
            margin-bottom: 6px;
        }
        .error-box ul { padding-left: 18px; }
        .error-box ul li { font-size: 12px; color: #b5606b; margin-bottom: 3px; }

        /* Form fields */
        .form-group { margin-bottom: 18px; }
        .form-group label {
            display: block;
            font-size: 12px;
            font-weight: 700;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 6px;
        }
        .form-group input {
            width: 100%;
            padding: 10px 14px;
            border: 1.5px solid var(--border);
            border-radius: 8px;
            font-size: 14px;
            font-family: inherit;
            color: var(--text);
            background: white;
            transition: border-color 0.2s, box-shadow 0.2s;
            outline: none;
        }
        .form-group input:focus {
            border-color: var(--pink);
            box-shadow: 0 0 0 3px rgba(242,151,160,0.15);
        }
        .form-group input.error { border-color: #F297A0; }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
        }

        /* Buttons */
        .btn-row {
            display: flex;
            gap: 10px;
            margin-top: 24px;
        }
        .btn-save {
            flex: 1;
            background: var(--pink);
            color: white;
            border: none;
            padding: 12px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 700;
            font-family: inherit;
            cursor: pointer;
            transition: background 0.2s, transform 0.15s;
        }
        .btn-save:hover { background: #e07880; transform: translateY(-1px); }
        .btn-save:active { transform: translateY(0); }

        .btn-cancel {
            flex: 1;
            background: var(--secondary);
            color: var(--secondary-text);
            border: none;
            padding: 12px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 700;
            font-family: inherit;
            cursor: pointer;
            text-decoration: none;
            text-align: center;
            transition: background 0.2s;
        }
        .btn-cancel:hover { background: #c8cba0; }

        /* ID badge */
        .id-badge {
            display: inline-block;
            background: #F3EBD8;
            color: var(--text-muted);
            font-size: 11px;
            font-weight: 600;
            padding: 3px 10px;
            border-radius: 20px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>

<div class="card">
    <div class="card-header">
        <h2>✏️ Edit Produk</h2>
        <p>Perbarui informasi produk di bawah ini</p>
    </div>

    <div class="card-body">

        <span class="id-badge">ID Produk: #<?= $id ?></span>

        <?php if (!empty($errors)): ?>
        <div class="error-box">
            <p>⚠️ Mohon perbaiki kesalahan berikut:</p>
            <ul>
                <?php foreach ($errors as $err): ?>
                <li><?= htmlspecialchars($err) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>

        <form method="POST" action="edit_produk.php?id=<?= $id ?>">

            <div class="form-group">
                <label>Nama Produk</label>
                <input
                    type="text"
                    name="product_name"
                    value="<?= htmlspecialchars($data['product_name']) ?>"
                    placeholder="Masukkan nama produk"
                    required
                >
            </div>

            <div class="form-group">
                <label>Brand</label>
                <input
                    type="text"
                    name="brand"
                    value="<?= htmlspecialchars($data['brand']) ?>"
                    placeholder="Masukkan nama brand"
                    required
                >
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Harga (Rp)</label>
                    <input
                        type="number"
                        name="price"
                        value="<?= intval($data['price']) ?>"
                        placeholder="0"
                        min="1"
                        required
                    >
                </div>
                <div class="form-group">
                    <label>Stok</label>
                    <input
                        type="number"
                        name="stock"
                        value="<?= intval($data['stock']) ?>"
                        placeholder="0"
                        min="0"
                        required
                    >
                </div>
            </div>

            <div class="btn-row">
                <a href="produk.php" class="btn-cancel">← Batal</a>
                <button type="submit" name="update" class="btn-save">💾 Simpan Perubahan</button>
            </div>

        </form>
    </div>
</div>

</body>
</html>