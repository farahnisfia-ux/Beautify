<?php
$host    = "localhost";
$user    = "root";       // username MySQL XAMPP default
$pass    = "";           // password MySQL XAMPP default (kosong)
$db      = "beautifyy";   // nama database

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("<h3 style='color:red;font-family:sans-serif;padding:20px;'>
        ❌ Koneksi database gagal: " . $conn->connect_error . "
        <br><small>Pastikan XAMPP MySQL sudah nyala dan database 'beautify' sudah dibuat.</small>
    </h3>");
}

$conn->set_charset("utf8mb4");
?>