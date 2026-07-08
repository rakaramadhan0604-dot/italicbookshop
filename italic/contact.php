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
    <meta name="description" content="Hubungi tim Italic untuk pertanyaan peminjaman, kemitraan, atau kendala tiket QR." />

    <!-- Open Graph / social sharing -->
    <meta property="og:type" content="website" />
    <meta property="og:site_name" content="Italic" />
    <meta property="og:title" content="Kontak — Italic" />
    <meta property="og:description" content="Hubungi tim Italic untuk pertanyaan peminjaman, kemitraan, atau kendala tiket QR." />
    <meta property="og:url" content="https://www.italic.co.id/contact.php" />
    <meta property="og:image" content="https://www.italic.co.id/assets/og-image.jpg" />
    <meta property="og:image:width" content="1200" />
    <meta property="og:image:height" content="630" />
    <meta property="og:locale" content="id_ID" />
    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:title" content="Kontak — Italic" />
    <meta name="twitter:description" content="Hubungi tim Italic untuk pertanyaan peminjaman, kemitraan, atau kendala tiket QR." />
    <meta name="twitter:image" content="https://www.italic.co.id/assets/og-image.jpg" />
    <title>Kontak — Italic</title>
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
            <a href="contact.php" aria-current="page">Kontak</a>
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
      <section class="section-tight" style="padding-top:2.5rem;">
        <div class="wrap">
          <p class="eyebrow">Kontak</p>
          <h1 style="font-size:clamp(2rem,4vw,3.2rem);">Ada Pertanyaan? Bicara dengan Kami.</h1>
          <p class="lede mt-1">Untuk pertanyaan peminjaman, kemitraan, atau sekadar rekomendasi bacaan — tim Italic biasanya membalas dalam 1x24 jam pada hari kerja.</p>
        </div>
      </section>

      <section class="section-tight">
        <div class="wrap contact-grid">
          <div class="contact-info">
            <dl>
              <dt>Gerai Italic</dt>
              <dd>Jl. Kemang Selatan Raya No. 24, Kemang, Jakarta Selatan, DKI Jakarta 12730</dd>

              <dt>Jam Operasional</dt>
              <dd>Selasa–Minggu, 10.00–19.00 WIB. Tutup setiap hari Senin dan hari libur nasional.</dd>

              <dt>Telepon / WhatsApp</dt>
              <dd><a href="tel:+622112345678" style="border-bottom:1px solid var(--ink);">(021) 1234-5678</a> &nbsp;·&nbsp; <a href="https://wa.me/6281234567890" style="border-bottom:1px solid var(--ink);">+62 812-3456-7890</a></dd>

              <dt>Email</dt>
              <dd><a href="mailto:halo@italic.co.id" style="border-bottom:1px solid var(--ink);">halo@italic.co.id</a> (umum) &nbsp;·&nbsp; <a href="mailto:kemitraan@italic.co.id" style="border-bottom:1px solid var(--ink);">kemitraan@italic.co.id</a> (kemitraan)</dd>

              <dt>Media Sosial</dt>
              <dd>@italic.books di Instagram &amp; X — kirim DM untuk respons tercepat di luar jam operasional gerai.</dd>
            </dl>

            <div class="map-plate">Peta lokasi gerai — Kemang, Jakarta Selatan</div>
          </div>

          <div>
            <div class="card" style="max-width:none;padding:clamp(1.6rem,3vw,2.2rem);">
              <header class="card-title" style="font-size:1.4rem;">Kirim Pesan</header>
              <p class="card-sub">Pilih topik yang paling sesuai supaya pesanmu sampai ke tim yang tepat.</p>
              <form data-feedback-form data-success-message="Pesan terkirim. Tim Italic akan membalas ke emailmu.">
                <div class="field">
                  <label for="c-topic">Topik</label>
                  <select id="c-topic" required>
                    <option value="">Pilih topik</option>
                    <option>Pertanyaan Peminjaman</option>
                    <option>Kendala Tiket / QR</option>
                    <option>Kemitraan &amp; Donasi Buku</option>
                    <option>Masukan &amp; Lainnya</option>
                  </select>
                </div>
                <div class="field-row">
                  <div class="field">
                    <label for="c-name">Nama</label>
                    <input type="text" id="c-name" required />
                  </div>
                  <div class="field">
                    <label for="c-email">Email</label>
                    <input type="email" id="c-email" required />
                  </div>
                </div>
                <div class="field">
                  <label for="c-message">Pesan</label>
                  <textarea id="c-message" placeholder="Ceritakan kebutuhanmu…" required></textarea>
                </div>
                <button type="submit" class="btn btn-primary btn-block">Kirim Pesan</button>
                <p class="form-status" aria-live="polite"></p>
              </form>
            </div>
          </div>
        </div>
      </section>

      <section class="section-tight" style="border-top:1px solid var(--line);">
        <div class="wrap">
          <div class="section-head"><div><p class="eyebrow">Sebelum Menghubungi Kami</p><h2>Pertanyaan Seputar Kontak</h2></div></div>
          <div class="faq-list" style="max-width:820px;">
            <details class="faq-item" open>
              <summary>Apakah bisa mengembalikan buku di luar jam operasional?</summary>
              <p>Bisa, lewat kotak pengembalian 24 jam di depan gerai. Tunjukkan atau tempelkan cetakan QR tiketmu pada buku sebelum dimasukkan agar tercatat otomatis oleh staf keesokan harinya.</p>
            </details>
            <details class="faq-item">
              <summary>Berapa lama respons untuk pertanyaan kemitraan?</summary>
              <p>Tim kemitraan biasanya membalas dalam 2–3 hari kerja karena melibatkan proses kurasi katalog gabungan.</p>
            </details>
            <details class="faq-item">
              <summary>Bisakah menghubungi lewat WhatsApp untuk keadaan mendesak?</summary>
              <p>Bisa. Nomor WhatsApp di atas aktif pada jam operasional gerai untuk kendala mendesak seperti QR tiket yang tidak terbaca.</p>
            </details>
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
    <script src="assets/js/books-data.js"></script>
    <script src="assets/js/script.js"></script>
  </body>
</html>
