<?php
$host     = "localhost";   // server database (lokal = localhost)
$user     = "root";        // username MySQL (default XAMPP = root)
$password = "";            // password MySQL (default XAMPP = kosong)
$database = "beautify_db"; // nama database kamu

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}
?>