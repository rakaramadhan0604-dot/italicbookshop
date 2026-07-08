<?php
$host = "localhost";
$user = "root";
$password = "";
$database = "italic_db";

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    error_log("Italic DB connection failed: " . $conn->connect_error);
    http_response_code(500);
    die("Maaf, layanan sedang mengalami gangguan. Silakan coba beberapa saat lagi.");
}

$conn->set_charset("utf8mb4");
?>