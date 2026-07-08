<?php
session_start();
$italicLoggedIn = isset($_SESSION["login"]);
$italicMemberName = $_SESSION["nama"] ?? "";

// Ambil stok eksemplar tersedia terkini dari database, dipakai untuk
// menimpa angka "copies" statis di books-data.js supaya selalu akurat.
$italicStock = [];
include "config/database.php";
$stockResult = $conn->query("SELECT id_buku, eksemplar_tersedia FROM v_katalog_ketersediaan");
if ($stockResult) {
    while ($row = $stockResult->fetch_assoc()) {
        $italicStock[(int) $row['id_buku']] = (int) $row['eksemplar_tersedia'];
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="id">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="description" content="Italic adalah layanan sewa buku bekas terkurasi dengan tiket QR digital untuk setiap peminjaman." />

    <!-- Open Graph / social sharing -->
    <meta property="og:type" content="website" />
    <meta property="og:site_name" content="Italic" />
    <meta property="og:title" content="Italic — Sewa Buku, Baca Miring" />
    <meta property="og:description" content="Italic adalah layanan sewa buku bekas terkurasi dengan tiket QR digital untuk setiap peminjaman." />
    <meta property="og:url" content="https://www.italic.co.id/index.php" />
    <meta property="og:image" content="https://www.italic.co.id/assets/og-image.jpg" />
    <meta property="og:image:width" content="1200" />
    <meta property="og:image:height" content="630" />
    <meta property="og:locale" content="id_ID" />
    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:title" content="Italic — Sewa Buku, Baca Miring" />
    <meta name="twitter:description" content="Italic adalah layanan sewa buku bekas terkurasi dengan tiket QR digital untuk setiap peminjaman." />
    <meta name="twitter:image" content="https://www.italic.co.id/assets/og-image.jpg" />
    <title>Italic — Sewa Buku, Baca Miring</title>
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
            <a href="index.php" aria-current="page">Home</a>
            <a href="catalog.php">Katalog</a>
            <a href="about.php">Tentang</a>
            <a href="contact.php">Kontak</a>
            <a href="account.php">Riwayat</a>
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
      <section class="hero">
        <div class="wrap hero-inner">
          <div class="hero-copy">
            <p class="eyebrow">Perpustakaan Sewa, Bukan Toko Buku</p>
            <h1>Pinjam Hari Ini.<br>Inspirasi Selamanya.</h1>
            <p class="lede">Italic menyewakan buku bekas pilihan tangan — dari fiksi literer sampai romance yang bikin begadang. Pinjam lewat formulir singkat, ambil tiket QR-mu, baca sampai tuntas, lalu kembalikan. Tidak perlu memiliki setiap buku untuk mencintainya.</p>
            <div class="hero-actions">
              <a href="catalog.php" class="btn btn-primary">Jelajahi Katalog</a>
              <a href="about.php" class="btn btn-outline">Cara Kerja Italic</a>
            </div>
          </div>
          <div class="hero-figure">
            <img src="assets/photos/cover-01.jpg" alt="Sampul buku pilihan Italic" loading="lazy" />
            <img src="assets/photos/cover-15.jpg" alt="Sampul buku pilihan Italic" loading="lazy" />
            <img src="assets/photos/cover-04.jpg" alt="Sampul buku pilihan Italic" loading="lazy" />
            <img src="assets/photos/cover-18.jpg" alt="Sampul buku pilihan Italic" loading="lazy" />
          </div>
        </div>
      </section>

      <section class="stat-strip">
        <div class="wrap">
          <div class="stat-cell"><div class="num">1.240+</div><div class="label">Judul Beredar</div></div>
          <div class="stat-cell"><div class="num">6.800+</div><div class="label">Peminjaman Selesai</div></div>
          <div class="stat-cell"><div class="num">Rp2.000</div><div class="label">Sewa Mulai / Hari</div></div>
          <div class="stat-cell"><div class="num">4,8 / 5</div><div class="label">Rerata Ulasan</div></div>
        </div>
      </section>

      <section class="section">
        <div class="wrap">
          <div class="section-head">
            <div>
              <p class="eyebrow">Rak Pilihan</p>
              <h2>Sedang Ramai Dipinjam</h2>
            </div>
            <a href="catalog.php" class="side-link">Lihat Semua Katalog →</a>
          </div>
          <div class="cat-grid" id="featured-grid"></div>
        </div>
      </section>

      <section class="section section-tight" style="background:var(--panel);border-top:1px solid var(--line);border-bottom:1px solid var(--line);">
        <div class="wrap">
          <div class="section-head" style="border-bottom:none;margin-bottom:2.2rem;">
            <div>
              <p class="eyebrow">Cara Kerja</p>
              <h2>Semudah Menghitung dari Satu sampai Empat</h2>
            </div>
          </div>
          <div class="steps">
            <div class="step">
              <span class="step-no">01</span>
              <h4>Pilih Judul</h4>
              <p>Telusuri katalog, baca sinopsis, cek kondisi dan ketersediaan eksemplar secara langsung.</p>
            </div>
            <div class="step">
              <span class="step-no">02</span>
              <h4>Isi Formulir Sewa</h4>
              <p>Lengkapi data diri, tanggal pinjam, durasi, dan metode pengambilan dalam satu formulir singkat.</p>
            </div>
            <div class="step">
              <span class="step-no">03</span>
              <h4>Terima Tiket QR</h4>
              <p>Sistem menerbitkan kode peminjaman unik beserta QR berisi detail lengkap transaksimu.</p>
            </div>
            <div class="step">
              <span class="step-no">04</span>
              <h4>Baca &amp; Kembalikan</h4>
              <p>Tunjukkan QR saat pengambilan dan pengembalian. Tepat waktu, tanpa denda, tanpa drama.</p>
            </div>
          </div>
        </div>
      </section>

      <section class="section">
        <div class="wrap">
          <div class="section-head">
            <div>
              <p class="eyebrow">Kenapa Italic</p>
              <h2>Dibangun untuk Pembaca yang Berpindah Buku</h2>
            </div>
          </div>
          <div class="value-grid">
            <div class="value-card">
              <h3>Terjangkau</h3>
              <p>Sewa harian mulai dari harga parkir motor. Tidak perlu membeli penuh untuk buku yang hanya kamu baca sekali.</p>
            </div>
            <div class="value-card">
              <h3>Terkurasi</h3>
              <p>Setiap eksemplar diperiksa kondisinya sebelum kembali beredar — halaman lengkap, sampul utuh, layak baca.</p>
            </div>
            <div class="value-card">
              <h3>Terlacak</h3>
              <p>Setiap transaksi punya kode dan QR sendiri, jadi kamu selalu tahu kapan harus mengembalikan buku.</p>
            </div>
          </div>
        </div>
      </section>

      <section class="section section-tight">
        <div class="wrap">
          <div class="section-head">
            <div>
              <p class="eyebrow">Kata Peminjam</p>
              <h2>Ulasan Anggota Italic</h2>
            </div>
          </div>
          <div class="quote-grid">
            <div class="quote-card">
              <p class="stars" aria-hidden="true">★★★★★</p>
              <p class="quote">"QR-nya bikin proses ambil buku secepat memesan kopi. Tidak ada antre panjang, tinggal scan."</p>
              <p class="who">Raka — Anggota sejak 2025</p>
            </div>
            <div class="quote-card">
              <p class="stars" aria-hidden="true">★★★★★</p>
              <p class="quote">"Harga sewa hariannya masuk akal banget untuk mahasiswa yang cuma butuh buku sampai UAS selesai."</p>
              <p class="who">Nadia — Anggota sejak 2024</p>
            </div>
            <div class="quote-card">
              <p class="stars" aria-hidden="true">★★★★☆</p>
              <p class="quote">"Koleksinya niche dan jarang ada di toko lain. Kondisi bukunya juga masih enak dipegang."</p>
              <p class="who">Bimo — Anggota sejak 2025</p>
            </div>
          </div>
        </div>
      </section>

      <section class="cta-band">
        <div class="wrap cta-band-inner">
          <div>
            <h2>Cerita Terus Berlanjut.</h2>
            <p class="lede">Daftar gratis, telusuri katalog, dan terbitkan tiket sewa pertamamu hari ini.</p>
          </div>
          <div style="display:flex;gap:0.9rem;flex-wrap:wrap;">
            <a href="signup.php" class="btn btn-accent">Daftar Sekarang</a>
            <a href="catalog.php" class="btn btn-outline">Lihat Katalog</a>
          </div>
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
    <script>
      const ITALIC_STOCK = <?php echo json_encode($italicStock); ?>;
    </script>
    <script src="assets/js/books-data.js"></script>
    <script>
      // Timpa angka "copies" statis dengan stok eksemplar tersedia sesungguhnya dari database.
      ITALIC_BOOKS.forEach((b) => {
        if (ITALIC_STOCK[b.id] !== undefined) b.copies = ITALIC_STOCK[b.id];
      });
    </script>
    <script src="assets/js/script.js"></script>

    <script>
      renderBookTiles(ITALIC_BOOKS.slice(0, 8), document.getElementById("featured-grid"));
    </script>
  </body>
</html>
