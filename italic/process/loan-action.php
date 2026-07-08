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
    respond(401, ['ok' => false, 'error' => 'Sesi berakhir. Silakan masuk kembali.']);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(405, ['ok' => false, 'error' => 'Metode tidak diizinkan.']);
}

include "../config/database.php";

$idAnggota = (int) $_SESSION['id'];
$kode = trim($_POST['kode'] ?? '');
$action = $_POST['action'] ?? '';

if ($kode === '' || !in_array($action, ['cancel', 'delete'], true)) {
    $conn->close();
    respond(422, ['ok' => false, 'error' => 'Permintaan tidak valid.']);
}

// Pastikan tiket ini benar-benar milik anggota yang sedang login
$stmt = $conn->prepare("
    SELECT id_peminjaman, status
    FROM peminjaman
    WHERE kode_peminjaman = ? AND id_anggota = ?
    LIMIT 1
");
$stmt->bind_param("si", $kode, $idAnggota);
$stmt->execute();
$loan = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$loan) {
    $conn->close();
    respond(404, ['ok' => false, 'error' => 'Tiket tidak ditemukan.']);
}

$idPeminjaman = (int) $loan['id_peminjaman'];

if ($action === 'cancel') {

    if ($loan['status'] !== 'diproses') {
        $conn->close();
        respond(422, ['ok' => false, 'error' => 'Hanya tiket berstatus "Diproses" yang bisa dibatalkan.']);
    }

    $conn->begin_transaction();
    try {
        $stmt = $conn->prepare("UPDATE peminjaman SET status = 'dibatalkan' WHERE id_peminjaman = ?");
        $stmt->bind_param("i", $idPeminjaman);
        $stmt->execute();
        $stmt->close();

        // Lepaskan kembali eksemplar yang sempat dikunci untuk tiket ini
        $stmt = $conn->prepare("
            UPDATE eksemplar e
            JOIN detail_peminjaman d ON d.id_eksemplar = e.id_eksemplar
            SET e.status = 'tersedia'
            WHERE d.id_peminjaman = ? AND e.status = 'dipinjam'
        ");
        $stmt->bind_param("i", $idPeminjaman);
        $stmt->execute();
        $stmt->close();

        $conn->commit();
        respond(200, ['ok' => true, 'status' => 'dibatalkan']);
    } catch (Throwable $e) {
        $conn->rollback();
        $conn->close();
        respond(500, ['ok' => false, 'error' => 'Gagal membatalkan tiket. Silakan coba lagi.']);
    }

} else { // delete

    if (!in_array($loan['status'], ['dibatalkan', 'selesai'], true)) {
        $conn->close();
        respond(422, ['ok' => false, 'error' => 'Hanya tiket berstatus "Dibatalkan" atau "Selesai" yang bisa dihapus. Batalkan dulu tiket yang masih diproses.']);
    }

    $stmt = $conn->prepare("DELETE FROM peminjaman WHERE id_peminjaman = ? AND id_anggota = ?");
    $stmt->bind_param("ii", $idPeminjaman, $idAnggota);
    $ok = $stmt->execute();
    $stmt->close();
    $conn->close();

    if ($ok) {
        respond(200, ['ok' => true, 'deleted' => true]);
    } else {
        respond(500, ['ok' => false, 'error' => 'Gagal menghapus tiket. Silakan coba lagi.']);
    }
}
