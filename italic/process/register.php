<?php
session_start();

include "../config/database.php";

function back_with_errors(array $errors, array $old, mysqli $conn): void
{
    $conn->close();
    $_SESSION['signup_errors'] = $errors;
    $_SESSION['signup_old'] = $old;
    header("Location: ../signup.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $nama     = trim($_POST['fullname'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $hp       = trim($_POST['phone'] ?? '');
    $ktp      = trim($_POST['no_ktp'] ?? '');
    $alamat   = trim($_POST['alamat'] ?? '');
    $password = $_POST['password'] ?? '';

    $old = [
        'fullname' => $nama,
        'email'    => $email,
        'phone'    => $hp,
        'no_ktp'   => $ktp,
        'alamat'   => $alamat,
    ];

    $errors = [];

    if ($nama === '' || mb_strlen($nama) < 3) {
        $errors[] = "Nama lengkap wajib diisi (minimal 3 karakter).";
    }

    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Format email tidak valid.";
    }

    $hpDigits = preg_replace('/\D/', '', $hp);
    if ($hpDigits === '' || strlen($hpDigits) < 9 || strlen($hpDigits) > 15) {
        $errors[] = "Nomor HP tidak valid (9–15 digit).";
    }

    $ktpDigits = preg_replace('/\D/', '', $ktp);
    if (strlen($ktpDigits) !== 16) {
        $errors[] = "Nomor KTP harus terdiri dari 16 digit.";
    }

    if ($alamat === '' || mb_strlen($alamat) < 10) {
        $errors[] = "Alamat wajib diisi lebih lengkap (minimal 10 karakter).";
    }

    if (strlen($password) < 8) {
        $errors[] = "Kata sandi minimal 8 karakter.";
    }

    if (!empty($errors)) {
        back_with_errors($errors, $old, $conn);
    }

    // cek email sudah terdaftar atau belum
    $cek = $conn->prepare("SELECT id_anggota FROM anggota WHERE email = ? LIMIT 1");
    $cek->bind_param("s", $email);
    $cek->execute();
    $cek->store_result();

    if ($cek->num_rows > 0) {
        $cek->close();
        back_with_errors(["Email sudah digunakan. Silakan masuk atau gunakan email lain."], $old, $conn);
    }
    $cek->close();

    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    $kode = "AGT" . date("YmdHis") . random_int(10, 99);

    $stmt = $conn->prepare("
        INSERT INTO anggota
        (
            kode_anggota,
            nama_lengkap,
            email,
            no_hp,
            no_ktp,
            alamat,
            kata_sandi_hash
        )
        VALUES (?,?,?,?,?,?,?)
    ");

    if (!$stmt) {
        back_with_errors(["Terjadi kesalahan sistem. Silakan coba lagi."], $old, $conn);
    }

    $stmt->bind_param(
        "sssssss",
        $kode,
        $nama,
        $email,
        $hpDigits,
        $ktpDigits,
        $alamat,
        $passwordHash
    );

    if ($stmt->execute()) {
        $stmt->close();
        $conn->close();
        header("Location: ../login.php?success=1");
        exit;
    } else {
        $stmt->close();
        back_with_errors(["Registrasi gagal. Silakan coba lagi beberapa saat lagi."], $old, $conn);
    }

} else {
    header("Location: ../signup.php");
    exit;
}
