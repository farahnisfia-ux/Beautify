<?php
$conn = mysqli_connect("localhost", "root", "", "beautify");

if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}
?>