<?php
session_start();
$italicLoggedIn = isset($_SESSION["login"]);
$italicMemberName = $_SESSION["nama"] ?? "";
?>
<!DOCTYPE html>
<html lang="id">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="description" content="Kenali filosofi, misi, tim, dan syarat & ketentuan layanan sewa buku Italic." />

    <!-- Open Graph / social sharing -->
    <meta property="og:type" content="website" />
    <meta property="og:site_name" content="Italic" />
    <meta property="og:title" content="Tentang Kami — Italic" />
    <meta property="og:description" content="Kenali filosofi, misi, tim, dan syarat & ketentuan layanan sewa buku Italic." />
    <meta property="og:url" content="https://www.italic.co.id/about.php" />
    <meta property="og:image" content="https://www.italic.co.id/assets/og-image.jpg" />
    <meta property="og:image:width" content="1200" />
    <meta property="og:image:height" content="630" />
    <meta property="og:locale" content="id_ID" />
    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:title" content="Tentang Kami — Italic" />
    <meta name="twitter:description" content="Kenali filosofi, misi, tim, dan syarat & ketentuan layanan sewa buku Italic." />
    <meta name="twitter:image" content="https://www.italic.co.id/assets/og-image.jpg" />
    <title>Tentang Kami — Italic</title>
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
            <a href="about.php" aria-current="page">Tentang</a>
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
      <section class="hero" style="padding-block:clamp(3rem,7vw,5rem) clamp(2rem,4vw,3rem);">
        <div class="wrap">
          <p class="eyebrow">Tentang Italic</p>
          <h1 style="max-width:16ch;">Membaca Adalah Cara Sunyi untuk Bertumbuh</h1>
          <p class="lede mt-1">Italic lahir dari keyakinan sederhana: buku yang bagus pantas dibaca oleh lebih dari satu orang. Kami menyewakan, bukan hanya menjual — supaya cerita terus berpindah tangan, bukan menumpuk di rak yang sama.</p>
        </div>
      </section>

      <section class="section-tight">
        <div class="wrap split">
          <div class="hero-figure" style="aspect-ratio:4/3;">
            <img src="assets/photos/cover-07.jpg" alt="Koleksi buku Italic" loading="lazy" />
            <img src="assets/photos/cover-11.jpg" alt="Koleksi buku Italic" loading="lazy" />
            <img src="assets/photos/cover-21.jpg" alt="Koleksi buku Italic" loading="lazy" />
            <img src="assets/photos/cover-19.jpg" alt="Koleksi buku Italic" loading="lazy" />
          </div>
          <div>
            <p class="eyebrow">Filosofi</p>
            <h2>Miring Bukan Berarti Goyah</h2>
            <p class="mt-1">Dalam tipografi, huruf miring dipakai untuk menandai penekanan — judul buku, istilah asing, bisikan dalam dialog. Kami memakai nama itu secara harfiah: setiap buku di rak Italic sedang dalam gerakan, condong dari satu pembaca ke pembaca berikutnya, tidak pernah benar-benar diam di satu tempat.</p>
            <p>Model sewa ini juga soal keberlanjutan. Satu eksemplar Italic bisa dibaca puluhan orang sepanjang usianya, dibanding satu eksemplar yang dibeli lalu berhenti di rak setelah dibaca sekali. Kami merawat setiap eksemplar supaya perjalanannya bisa sepanjang mungkin.</p>
          </div>
        </div>
      </section>

      <section class="section" style="background:var(--panel);border-top:1px solid var(--line);border-bottom:1px solid var(--line);">
        <div class="wrap">
          <div class="section-head" style="border-bottom:none;">
            <div><p class="eyebrow">Misi &amp; Visi</p><h2>Apa yang Kami Perjuangkan</h2></div>
          </div>
          <div class="value-grid">
            <div class="value-card" style="background:var(--white);">
              <h3>Visi</h3>
              <p>Menjadi perpustakaan sewa paling dipercaya di Indonesia yang menghadirkan akses membaca tanpa batas, sekaligus menumbuhkan budaya membaca di tengah masyarakat melalui buku-buku yang mudah dijangkau.</p>
            </div>
            <div class="value-card" style="background:var(--white);">
              <h3>Misi</h3>
              <p>Mengkurasi buku lintas genre yang relevan dan berkualitas, menjaga kondisi setiap eksemplar, serta menghadirkan proses peminjaman—mulai dari pemesanan hingga pengembalian—yang cepat, transparan, dan berbasis sistem tiket digital untuk membuat membaca menjadi kebiasaan yang mudah dan menyenangkan.</p>
            </div>
            <div class="value-card" style="background:var(--white);">
              <h3>Nilai</h3>
              <p>Kami percaya bahwa membaca adalah investasi seumur hidup. Karena itu, kami mengutamakan sirkulasi di atas kepemilikan, transparansi dalam setiap biaya, serta penghormatan terhadap setiap buku yang masih memiliki banyak cerita untuk menginspirasi pembaca berikutnya.</p>
            </div>
          </div>
        </div>
      </section>

      <section class="section">
        <div class="wrap">
          <div class="section-head"><div><p class="eyebrow">Perjalanan</p><h2>Jejak Italic</h2></div></div>
          <div class="timeline">
            <div class="timeline-item">
              <span class="yr">2023</span>
              <h4 class="mt-1">Rak Pertama</h4>
              <p>Italic dimulai dari 60 judul koleksi pribadi pendiri yang disewakan lewat pesan singkat ke teman kuliah.</p>
            </div>
            <div class="timeline-item">
              <span class="yr">2024</span>
              <h4 class="mt-1">Sistem Tiket Digital</h4>
              <p>Kami memperkenalkan kode peminjaman dan QR tiket untuk menggantikan catatan manual di buku besar.</p>
            </div>
            <div class="timeline-item">
              <span class="yr">2025</span>
              <h4 class="mt-1">1.000 Judul Beredar</h4>
              <p>Katalog menembus seribu judul aktif dengan lebih dari enam ribu transaksi peminjaman selesai.</p>
            </div>
            <div class="timeline-item">
              <span class="yr">2026</span>
              <h4 class="mt-1">Gerai &amp; Layanan Antar</h4>
              <p>Italic membuka gerai fisik di Jakarta Selatan dan memperluas layanan antar-jemput buku ke seluruh Jabodetabek.</p>
            </div>
          </div>
        </div>
      </section>

      <section class="section" id="syarat" style="background:var(--panel);border-top:1px solid var(--line);">
        <div class="wrap">
          <div class="section-head" style="border-bottom:none;"><div><p class="eyebrow">Ketentuan Layanan</p><h2>Syarat &amp; Ketentuan Sewa</h2></div></div>
          <div class="value-grid">
            <div class="value-card" style="background:var(--white);">
              <h3>Durasi &amp; Perpanjangan</h3>
              <p>Sewa tersedia untuk 3, 7, 14, atau 30 hari. Perpanjangan dapat diajukan lewat halaman Riwayat sebelum tanggal jatuh tempo, selama tidak ada anggota lain yang mengantre judul yang sama.</p>
            </div>
            <div class="value-card" style="background:var(--white);">
              <h3>Denda Keterlambatan</h3>
              <p>Keterlambatan pengembalian dikenakan denda harian sebesar 20% dari harga sewa per hari, dihitung sejak satu hari setelah tanggal jatuh tempo pada tiket.</p>
            </div>
            <div class="value-card" style="background:var(--white);">
              <h3>Kerusakan &amp; Kehilangan</h3>
              <p>Buku yang kembali dalam kondisi rusak berat atau hilang dikenakan biaya penggantian sebesar harga pasar buku tersebut, ditentukan saat verifikasi pengembalian.</p>
            </div>
          </div>
        </div>
      </section>

      <section class="section-tight">
        <div class="wrap">
          <div class="section-head"><div><p class="eyebrow">Pertanyaan Umum</p><h2>FAQ Seputar Italic</h2></div></div>
          <div class="faq-list" style="max-width:820px;">
            <details class="faq-item" open>
              <summary>Apakah saya harus jadi anggota untuk menyewa?</summary>
              <p>Ya. Pendaftaran gratis dan hanya perlu nama, email, dan nomor HP aktif. Ini membantu kami melacak riwayat peminjaman dan mengingatkanmu sebelum jatuh tempo.</p>
            </details>
            <details class="faq-item">
              <summary>Bagaimana cara kerja kode QR pada tiket sewa?</summary>
              <p>Setiap tiket memuat QR yang menyimpan kode peminjaman, data peminjam, judul buku, serta tanggal pinjam dan kembali. Kurir atau staf gerai cukup memindainya untuk memverifikasi transaksi tanpa perlu mencatat ulang.</p>
            </details>
            <details class="faq-item">
              <summary>Bisakah menyewa lebih dari satu buku sekaligus?</summary>
              <p>Saat ini setiap formulir sewa memproses satu judul per tiket agar tanggal jatuh tempo tetap jelas. Untuk beberapa judul, cukup ulangi proses sewa — setiap tiket akan punya kode dan QR masing-masing.</p>
            </details>
            <details class="faq-item">
              <summary>Apa yang terjadi jika saya kehilangan tiket QR?</summary>
              <p>Buka halaman Riwayat dan masukkan kode peminjamanmu untuk menampilkan ulang QR kapan saja, selama tiket dibuat dari perangkat yang sama atau kamu masih menyimpan kodenya.</p>
            </details>
          </div>
        </div>
      </section>

      <section class="cta-band">
        <div class="wrap cta-band-inner">
          <div><h2>Siap Berpindah Buku?</h2><p class="lede">Telusuri katalog kami dan temukan judul yang siap menemani hari-harimu.</p></div>
          <a href="catalog.php" class="btn btn-accent">Jelajahi Katalog</a>
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
  </body>
</html>
