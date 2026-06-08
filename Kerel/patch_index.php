<?php
// ================================================================
//  PATCH index.php — tambahkan 2 bagian ini
//  Buka index.php kamu, lalu ikuti instruksi di bawah
// ================================================================

// ────────────────────────────────────────────────
// BAGIAN 1: Tempel di baris PALING ATAS index.php
//           (tepat setelah include 'koneksi.php';)
// ────────────────────────────────────────────────

$stmtCats = $conn->prepare("SELECT * FROM categories ORDER BY id_category ASC");
$stmtCats->execute();
$categories = $stmtCats->get_result()->fetch_all(MYSQLI_ASSOC);

// ────────────────────────────────────────────────
// BAGIAN 2: GANTI <nav class="category-nav"> yang lama
//           dengan kode ini:
// ────────────────────────────────────────────────
?>
<nav class="category-nav">
    <div class="nav-inner">
        <a href="index.php" class="active">Home</a>
        <a href="index.php#produk">Flash Sale</a>
        <a href="index.php">Best Seller</a>
        <?php foreach ($categories as $cat): ?>
        <a href="category.php?id=<?= $cat['id_category'] ?>">
            <?= htmlspecialchars($cat['category_name']) ?>
        </a>
        <?php endforeach; ?>
    </div>
</nav>

<?php
// ────────────────────────────────────────────────
// BAGIAN 3: GANTI <div class="category-pills">
//           (bagian ikon Lip Products, Eye Makeup, dll)
//           dengan kode ini:
// ────────────────────────────────────────────────

$catIcons = [1=>'✨', 2=>'💄', 3=>'👁', 4=>'🤎'];
$catColors = [1=>'blue', 2=>'pink', 3=>'purple', 4=>'yellow'];
?>
<div class="category-pills">
    <?php foreach ($categories as $cat):
        $id   = $cat['id_category'];
        $icon = $catIcons[$id]  ?? '💫';
        $col  = $catColors[$id] ?? 'pink';
    ?>
    <a href="category.php?id=<?= $id ?>" style="text-decoration:none;">
        <div class="cat-pill">
            <div class="cat-icon <?= $col ?>"><?= $icon ?></div>
            <span><?= htmlspecialchars($cat['category_name']) ?></span>
        </div>
    </a>
    <?php endforeach; ?>
</div>