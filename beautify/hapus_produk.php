<?php
include 'koneksi.php';

$id = intval($_GET['id']);

$stmt = $conn->prepare("
DELETE FROM products
WHERE id_product = ?
");

$stmt->bind_param("i",$id);
$stmt->execute();

header("Location: produk.php");
exit;
?>