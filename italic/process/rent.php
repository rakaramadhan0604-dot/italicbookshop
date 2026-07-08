<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

function respond(int $code, array $payload): void
{
    http_response_code($code);
    echo json_encode($payload);
    exit;
}

if (!isset($_SESSION['login'], $_SESSION['id'])) {
    respond(401, ['ok' => false, 'error' => 'Sesi berakhir. Silakan masuk kembali untuk menyewa buku.']);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(405, ['ok' => false, 'error' => 'Metode tidak diizinkan.']);
}

include "../config/database.php";

$idAnggota    = (int) $_SESSION['id'];
$idBuku       = (int) ($_POST['id_buku'] ?? 0);
$durationDays = (int) ($_POST['duration_days'] ?? 0);
$pickupRaw    = $_POST['pickup'] ?? 'ambil';
$borrowDate   = $_POST['borrow_date'] ?? '';
$address      = trim($_POST['address'] ?? '');
$notes        = trim($_POST['notes'] ?? '');

$allowedDurations = [3, 7, 14, 30];

$errors = [];

if ($idBuku <= 0) {
    $errors[] = "Buku tidak valid.";
}
if (!in_array($durationDays, $allowedDurations, true)) {
    $errors[] = "Durasi sewa tidak valid.";
}

$pickupMethod = $pickupRaw === 'antar' ? 'diantar' : 'ambil_di_toko';

if ($pickupMethod === 'diantar' && mb_strlen($address) < 10) {
    $errors[] = "Alamat pengantaran wajib diisi lengkap (minimal 10 karakter) untuk metode pengantaran.";
}

$borrowDateObj = DateTime::createFromFormat('Y-m-d', $borrowDate);
$todayObj = new DateTime('today');
if (!$borrowDateObj || $borrowDateObj < $todayObj) {
    $errors[] = "Tanggal pengambilan tidak valid.";
}

if (!empty($errors)) {
    $conn->close();
    respond(422, ['ok' => false, 'error' => implode(' ', $errors)]);
}

$conn->begin_transaction();

try {
    // Kunci baris buku, ambil harga sewa terkini
    $stmt = $conn->prepare("SELECT judul, harga_sewa_per_hari FROM buku WHERE id_buku = ? FOR UPDATE");
    $stmt->bind_param("i", $idBuku);
    $stmt->execute();
    $buku = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$buku) {
        throw new Exception("Buku tidak ditemukan.");
    }

    // Cari satu eksemplar yang masih tersedia, kunci barisnya
    $stmt = $conn->prepare("
        SELECT id_eksemplar, kondisi
        FROM eksemplar
        WHERE id_buku = ? AND status = 'tersedia'
        ORDER BY id_eksemplar
        LIMIT 1
        FOR UPDATE
    ");
    $stmt->bind_param("i", $idBuku);
    $stmt->execute();
    $eksemplar = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$eksemplar) {
        throw new Exception("Maaf, stok buku \"{$buku['judul']}\" sedang habis. Coba judul lain atau kembali lagi nanti.");
    }

    $dueDateObj = clone $borrowDateObj;
    $dueDateObj->modify("+{$durationDays} days");

    $pricePerDay = (float) $buku['harga_sewa_per_hari'];
    $subtotal = $pricePerDay * $durationDays;
    $deliveryFee = $pickupMethod === 'diantar' ? 5000 : 0;
    $total = $subtotal + $deliveryFee;

    // Buat kode peminjaman unik (dipakai sebagai payload QR)
    do {
        $kode = 'ITL-' . date('Ymd') . '-' . random_int(1000, 9999);
        $check = $conn->prepare("SELECT 1 FROM peminjaman WHERE kode_peminjaman = ?");
        $check->bind_param("s", $kode);
        $check->execute();
        $exists = $check->get_result()->fetch_row();
        $check->close();
    } while ($exists);

    $borrowDateStr = $borrowDateObj->format('Y-m-d');
    $dueDateStr = $dueDateObj->format('Y-m-d');

    $qrPayload = json_encode([
        'kode' => $kode,
        'tanggal_pinjam' => $borrowDateStr,
        'tanggal_kembali' => $dueDateStr,
        'total' => $total,
    ]);

    $stmt = $conn->prepare("
        INSERT INTO peminjaman
            (kode_peminjaman, id_anggota, tanggal_pinjam, tanggal_jatuh_tempo,
             metode_pengambilan, alamat_pengantaran, biaya_antar, subtotal_sewa,
             total_biaya, status, catatan, qr_code_data)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'diproses', ?, ?)
    ");
    $stmt->bind_param(
        "sissssdddss",
        $kode, $idAnggota, $borrowDateStr, $dueDateStr,
        $pickupMethod, $address, $deliveryFee, $subtotal,
        $total, $notes, $qrPayload
    );
    $stmt->execute();
    $idPeminjaman = $stmt->insert_id;
    $stmt->close();

    // Trigger trg_detail_after_insert otomatis menandai eksemplar 'dipinjam'
    $stmt = $conn->prepare("
        INSERT INTO detail_peminjaman
            (id_peminjaman, id_eksemplar, harga_sewa_per_hari, jumlah_hari, subtotal, kondisi_saat_pinjam, status_item)
        VALUES (?, ?, ?, ?, ?, ?, 'dipinjam')
    ");
    $stmt->bind_param(
        "iidids",
        $idPeminjaman, $eksemplar['id_eksemplar'], $pricePerDay, $durationDays, $subtotal, $eksemplar['kondisi']
    );
    $stmt->execute();
    $stmt->close();

    $conn->commit();

    respond(200, [
        'ok' => true,
        'loan' => [
            'loanCode' => $kode,
            'status' => 'diproses',
            'borrowDate' => $borrowDateStr,
            'dueDate' => $dueDateStr,
            'pickupMethod' => $pickupRaw,
            'pricePerDay' => $pricePerDay,
            'durationDays' => $durationDays,
            'subtotal' => $subtotal,
            'deliveryFee' => $deliveryFee,
            'total' => $total,
            'notes' => $notes,
        ],
    ]);
} catch (Throwable $e) {
    $conn->rollback();
    $conn->close();
    respond(422, ['ok' => false, 'error' => $e->getMessage()]);
}
