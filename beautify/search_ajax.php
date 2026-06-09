<?php
include 'koneksi.php';
header('Content-Type: application/json');

$keyword = isset($_GET['q']) ? trim($_GET['q']) : '';

if ($keyword === '') {
    $stmt = $conn->prepare("
        SELECT p.*, c.category_name
        FROM products p
        JOIN categories c ON p.category_id = c.id_category
        ORDER BY RAND()
    ");
    $stmt->execute();
} else {
    $stmt = $conn->prepare("
        SELECT p.*, c.category_name
        FROM products p
        JOIN categories c ON p.category_id = c.id_category
        WHERE p.product_name LIKE ?
           OR p.brand LIKE ?
           OR c.category_name LIKE ?
        ORDER BY p.product_name ASC
    ");
    $like = '%' . $keyword . '%';
    $stmt->bind_param('sss', $like, $like, $like);
    $stmt->execute();
}

$result = $stmt->get_result();
$products = [];

while ($data = $result->fetch_assoc()) {
    $hargaCoret  = $data['price'] + ($data['price'] * 0.15);
    $isStarSeller = $data['stock'] > 15;
    $sold        = rand(100, 5000);
    $rating      = number_format(rand(40, 50) / 10, 1);

    $products[] = [
        'id_product'    => $data['id_product'],
        'product_name'  => $data['product_name'],
        'brand'         => $data['brand'],
        'price'         => $data['price'],
        'hargaCoret'    => $hargaCoret,
        'isStarSeller'  => $isStarSeller,
        'sold'          => $sold,
        'rating'        => $rating,
        'category_name' => $data['category_name'],
        'disc'          => 15,
        'img'           => 'https://images.unsplash.com/photo-1596462502278-27bfdc403348?auto=format&fit=crop&w=400&q=80'
    ];
}

echo json_encode(['products' => $products, 'keyword' => $keyword]);