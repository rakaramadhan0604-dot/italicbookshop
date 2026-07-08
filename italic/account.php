<?php
session_start();
$italicLoggedIn = isset($_SESSION["login"]);
$italicMemberName = $_SESSION["nama"] ?? "";

if (!$italicLoggedIn) {
    header("Location: login.php?redirect=" . urlencode("account.php"));
    exit;
}

include "config/database.php";

$stmt = $conn->prepare("
    SELECT
        p.kode_peminjaman,
        p.status,
        p.tanggal_pinjam,
        p.tanggal_jatuh_tempo,
        p.total_biaya,
        p.metode_pengambilan,
        b.judul AS judul_buku
    FROM peminjaman p
    JOIN detail_peminjaman d ON d.id_peminjaman = p.id_peminjaman
    JOIN eksemplar e ON e.id_eksemplar = d.id_eksemplar
    JOIN buku b ON b.id_buku = e.id_buku
    WHERE p.id_anggota = ?
    ORDER BY p.dibuat_pada DESC
");
$stmt->bind_param("i", $_SESSION["id"]);
$stmt->execute();
$result = $stmt->get_result();
$loans = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="id">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="description" content="Lihat riwayat peminjaman dan kode QR tiket sewa buku Italic." />

    <!-- Open Graph / social sharing -->
    <meta property="og:type" content="website" />
    <meta property="og:site_name" content="Italic" />
    <meta property="og:title" content="Riwayat Pinjaman — Italic" />
    <meta property="og:description" content="Lihat riwayat peminjaman dan kode QR tiket sewa buku Italic." />
    <meta property="og:url" content="https://www.italic.co.id/account.php" />
    <meta property="og:image" content="https://www.italic.co.id/assets/og-image.jpg" />
    <meta property="og:image:width" content="1200" />
    <meta property="og:image:height" content="630" />
    <meta property="og:locale" content="id_ID" />
    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:title" content="Riwayat Pinjaman — Italic" />
    <meta name="twitter:description" content="Lihat riwayat peminjaman dan kode QR tiket sewa buku Italic." />
    <meta name="twitter:image" content="https://www.italic.co.id/assets/og-image.jpg" />
    <title>Riwayat Pinjaman — Italic</title>
    <link rel="icon" href="assets/logo.svg" type="image/svg+xml" />
    <link rel="stylesheet" href="assets/css/style.css" />
    
  </head>
  <body>
    <a class="skip-link" href="#main">Lewati ke konten utama</a>
    <header class="site-header">
      <nav class="navbar" aria-label="Navigasi utama">
        <div class="wrap navbar-inner">
          <a href="index.php" class="navbar-logo" aria-label="Italic — kembali ke beranda">
            <img src="assets/logo.svg" alt="Italic" width="152" height="39" />
          </a>

          <div class="navbar-nav" id="navbar-nav">
            <a href="index.php">Home</a>
            <a href="catalog.php">Katalog</a>
            <a href="about.php">Tentang</a>
            <a href="contact.php">Kontak</a>
            <a href="account.php" aria-current="page">Riwayat</a>
          </div>

          <div class="navbar-extra">
            <a href="catalog.php" class="icon-btn" aria-label="Cari buku"><svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><circle cx="11" cy="11" r="7"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg></a>
            <?php if ($italicLoggedIn): ?>
            <a href="account.php" class="icon-btn" aria-label="Akun: <?php echo htmlspecialchars($italicMemberName, ENT_QUOTES, 'UTF-8'); ?>" title="<?php echo htmlspecialchars($italicMemberName, ENT_QUOTES, 'UTF-8'); ?>"><svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M20 21v-1a7 7 0 0 0-14 0v1"/><circle cx="12" cy="7" r="4"/></svg></a>
            <a href="process/logout.php" class="icon-btn" aria-label="Keluar akun" title="Keluar"><svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg></a>
            <?php else: ?>
            <a href="login.php" class="icon-btn" aria-label="Masuk akun"><svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M20 21v-1a7 7 0 0 0-14 0v1"/><circle cx="12" cy="7" r="4"/></svg></a>
            <?php endif; ?>
            <button type="button" id="hamburger-menu" class="icon-btn" aria-label="Buka menu" aria-expanded="false" aria-controls="navbar-nav"><svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg></button>
          </div>
        </div>
      </nav>
    </header>

    <main id="main">
      <section class="section-tight" style="padding-top:2.5rem;">
        <div class="wrap">
          <p class="eyebrow">Akun Saya</p>
          <h1 style="font-size:clamp(2rem,4vw,3.2rem);">Riwayat Peminjaman</h1>
          <p class="lede mt-1" id="account-greeting">Halo, <?php echo htmlspecialchars($italicMemberName, ENT_QUOTES, 'UTF-8'); ?>. Berikut riwayat peminjaman dari akunmu.</p>
        </div>
      </section>

      <section class="section-tight">
        <div class="wrap">
          <div id="loan-empty" class="empty-state" style="<?php echo count($loans) ? 'display:none;' : ''; ?>">
            Belum ada riwayat peminjaman. <a href="catalog.php" style="border-bottom:1px solid var(--ink);font-weight:600;">Mulai jelajahi katalog →</a>
          </div>
          <div class="loan-scroll" id="loan-wrap" style="<?php echo count($loans) ? '' : 'display:none;'; ?>">
            <table class="loan-table">
              <thead>
                <tr>
                  <th>Kode</th>
                  <th>Buku</th>
                  <th>Pinjam</th>
                  <th>Jatuh Tempo</th>
                  <th>Total</th>
                  <th>Status</th>
                  <th>QR</th>
                  <th>Aksi</th>
                </tr>
              </thead>
              <tbody id="loan-body">
                <?php
                $tagClass = ['diproses' => 'active', 'aktif' => 'active', 'selesai' => 'done', 'terlambat' => 'late', 'dibatalkan' => 'late'];
                $tagLabel = ['diproses' => 'Diproses', 'aktif' => 'Aktif', 'selesai' => 'Selesai', 'terlambat' => 'Terlambat', 'dibatalkan' => 'Dibatalkan'];

                foreach ($loans as $l):
                    $status = $l['status'];
                    $code = htmlspecialchars($l['kode_peminjaman'], ENT_QUOTES, 'UTF-8');
                    $cls = $tagClass[$status] ?? 'active';
                    $label = $tagLabel[$status] ?? $status;
                ?>
                <tr data-code="<?php echo $code; ?>">
                  <td><?php echo $code; ?></td>
                  <td><?php echo htmlspecialchars($l['judul_buku'], ENT_QUOTES, 'UTF-8'); ?></td>
                  <td><?php echo htmlspecialchars($l['tanggal_pinjam'], ENT_QUOTES, 'UTF-8'); ?></td>
                  <td><?php echo htmlspecialchars($l['tanggal_jatuh_tempo'], ENT_QUOTES, 'UTF-8'); ?></td>
                  <td><?php echo 'Rp' . number_format((float) $l['total_biaya'], 0, ',', '.'); ?></td>
                  <td><span class="tag <?php echo $cls; ?>"><?php echo $label; ?></span></td>
                  <td><button class="btn btn-sm btn-outline" onclick="showQr('<?php echo $code; ?>')">Lihat QR</button></td>
                  <td class="loan-actions">
                    <?php if ($status === 'diproses'): ?>
                    <button class="btn btn-sm btn-outline" onclick="loanAction('<?php echo $code; ?>','cancel',this)">Batalkan</button>
                    <?php elseif (in_array($status, ['dibatalkan', 'selesai'], true)): ?>
                    <button class="btn btn-sm btn-outline" onclick="loanAction('<?php echo $code; ?>','delete',this)">Hapus</button>
                    <?php endif; ?>
                  </td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </section>

      <section class="section-tight" style="border-top:1px solid var(--line);">
        <div class="wrap center-col">
          <h3>Lihat Detail Tiket Lewat Kode</h3>
          <p class="lede mt-1" style="margin-inline:auto;">Masukkan kode tiket dari riwayatmu di atas untuk menampilkan ulang QR-nya.</p>
          <div class="field-row mt-2" style="max-width:420px;margin-inline:auto;">
            <input type="text" id="lookup-code" placeholder="Contoh: ITL-20260703-1234" style="border:1px solid var(--line);padding:0.85em 1em;flex:1;" />
            <button class="btn btn-primary" id="lookup-btn">Tampilkan</button>
          </div>
          <div id="lookup-result" style="max-width:520px;margin-inline:auto;margin-top:1.6rem;text-align:left;"></div>
        </div>
      </section>
    </main>
    <footer class="site-footer">
      <div class="wrap footer-inner">
        <div class="footer-brand">
          <img src="assets/logo.svg" alt="Italic" width="121" height="31" />
          <p>Italic menyewakan buku bekas pilihan untuk pembaca yang lebih suka meminjam cerita daripada menumpuknya di rak. Pinjam, baca, kembalikan — ulangi.</p>
          <div class="footer-socials">
            <a href="#" class="icon-btn" aria-label="Instagram Italic"><svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><rect x="3" y="3" width="18" height="18" rx="4"/><circle cx="12" cy="12" r="4"/><line x1="17.5" y1="6.5" x2="17.5" y2="6.5"/></svg></a>
            <a href="#" class="icon-btn" aria-label="Twitter / X Italic"><svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M22 5.9c-.7.3-1.5.6-2.3.7.8-.5 1.5-1.3 1.8-2.3-.8.5-1.7.8-2.6 1a4.1 4.1 0 0 0-7 3.7A11.6 11.6 0 0 1 3.4 4.6a4 4 0 0 0 1.3 5.5c-.7 0-1.3-.2-1.9-.5v.1c0 2 1.4 3.6 3.3 4a4.2 4.2 0 0 1-1.9.1 4.1 4.1 0 0 0 3.8 2.9A8.3 8.3 0 0 1 2 18.4a11.6 11.6 0 0 0 6.3 1.9c7.5 0 11.7-6.3 11.7-11.7v-.5c.8-.6 1.5-1.3 2-2.2z"/></svg></a>
            <a href="#" class="icon-btn" aria-label="Lokasi Italic di peta"><svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><polygon points="1 6 1 22 8 18 16 22 23 18 23 2 16 6 8 2 1 6"/><line x1="8" y1="2" x2="8" y2="18"/><line x1="16" y1="6" x2="16" y2="22"/></svg></a>
          </div>
        </div>

        <div class="footer-col">
          <h5>Jelajahi</h5>
          <ul>
            <li><a href="catalog.php">Katalog</a></li>
            <li><a href="catalog.php?genre=Fiction">Fiction</a></li>
            <li><a href="catalog.php?genre=Romance">Romance</a></li>
            <li><a href="about.php">Tentang Kami</a></li>
          </ul>
        </div>

        <div class="footer-col">
          <h5>Akun</h5>
          <ul>
            <li><a href="login.php">Masuk</a></li>
            <li><a href="signup.php">Daftar</a></li>
            <li><a href="account.php">Riwayat Pinjaman</a></li>
          </ul>
        </div>

        <div class="footer-col">
          <h5>Kontak</h5>
          <ul>
            <li><a href="contact.php">Hubungi Kami</a></li>
            <li><a href="mailto:halo@italic.co.id">halo@italic.co.id</a></li>
            <li><a href="tel:+622112345678">(021) 1234-5678</a></li>
          </ul>
        </div>
      </div>
      <div class="wrap footer-bottom">
        <p>&copy; 2026 Italic Book Rental. Semua hak cipta dilindungi.</p>
      </div>
    </footer>
    <script src="assets/js/books-data.js"></script>
    <script src="assets/js/script.js"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js" integrity="sha512-CNgIRecGo7nphbeZ04Sc13ka07paqdeTu0WR1IM4kNcpmBAUSHSQX0FslNhTDadL4O5SAGapGt4FodqL8My0mA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script>
      (function () {
        const MEMBER_NAME = <?php echo json_encode($italicMemberName); ?>;

        // Data riwayat langsung dari database (bukan localStorage), dipakai
        // untuk fitur "lihat QR" dan pencarian kode tanpa round-trip lagi.
        const LOANS_BY_CODE = <?php
            $byCode = [];
            foreach ($loans as $l) {
                $byCode[$l['kode_peminjaman']] = [
                    'status' => $l['status'],
                    'book' => $l['judul_buku'],
                    'borrowDate' => $l['tanggal_pinjam'],
                    'dueDate' => $l['tanggal_jatuh_tempo'],
                    'total' => (float) $l['total_biaya'],
                ];
            }
            echo json_encode($byCode);
        ?>;

        window.showQr = function (code) {
          const loan = LOANS_BY_CODE[code];
          const result = document.getElementById("lookup-result");
          if (!loan) {
            result.innerHTML = '<p class="form-status err">Kode peminjaman tidak ditemukan di riwayatmu.</p>';
            result.scrollIntoView({ behavior: "smooth", block: "center" });
            return;
          }
          result.innerHTML = `
            <div class="ticket">
              <div class="ticket-head"><img src="assets/logo.svg" alt="Italic" /><span class="status">${loan.status}</span></div>
              <div class="ticket-body">
                <div class="ticket-fields">
                  <div><span class="k">Peminjam</span><div class="v">${MEMBER_NAME}</div></div>
                  <div><span class="k">Buku</span><div class="v">${loan.book}</div></div>
                  <div><span class="k">Jatuh Tempo</span><div class="v">${loan.dueDate}</div></div>
                </div>
                <div class="ticket-qr" id="lookup-qr"></div>
              </div>
              <div class="ticket-code">${code}</div>
            </div>`;
          renderQrInto(
            document.getElementById("lookup-qr"),
            JSON.stringify({
              kode: code,
              peminjam: MEMBER_NAME,
              buku: loan.book,
              tanggal_pinjam: loan.borrowDate,
              tanggal_kembali: loan.dueDate,
              total: loan.total,
              status: loan.status,
            })
          );
          result.scrollIntoView({ behavior: "smooth", block: "center" });
        };

        document.getElementById("lookup-btn").addEventListener("click", () => {
          const code = document.getElementById("lookup-code").value.trim();
          if (code) showQr(code);
        });

        window.loanAction = function (code, action, btnEl) {
          const confirmMsg =
            action === "cancel"
              ? `Batalkan tiket ${code}? Buku akan dilepas kembali ke stok tersedia.`
              : `Hapus tiket ${code} dari riwayat secara permanen? Tindakan ini tidak bisa dibatalkan.`;
          if (!window.confirm(confirmMsg)) return;

          btnEl.disabled = true;
          const originalText = btnEl.textContent;
          btnEl.textContent = "Memproses...";

          fetch("process/loan-action.php", {
            method: "POST",
            body: new URLSearchParams({ kode: code, action }),
          })
            .then((res) => res.json().then((data) => ({ status: res.status, data })))
            .then(({ status, data }) => {
              if (status !== 200 || !data.ok) {
                throw new Error(data.error || "Gagal memproses permintaan.");
              }
              italicToast(action === "cancel" ? "Tiket berhasil dibatalkan." : "Tiket berhasil dihapus dari riwayat.");
              window.location.reload();
            })
            .catch((err) => {
              alert(err.message);
              btnEl.disabled = false;
              btnEl.textContent = originalText;
            });
        };
      })();
    </script>
  </body>
</html>
