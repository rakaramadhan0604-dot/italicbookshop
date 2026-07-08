<?php
session_start();

if (isset($_SESSION['login'])) {
    header("Location: account.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="description" content="Masuk ke akun Italic untuk mulai menyewa buku." />

    <!-- Open Graph / social sharing -->
    <meta property="og:type" content="website" />
    <meta property="og:site_name" content="Italic" />
    <meta property="og:title" content="Masuk — Italic" />
    <meta property="og:description" content="Masuk ke akun Italic untuk mulai menyewa buku." />
    <meta property="og:url" content="https://www.italic.co.id/login.php" />
    <meta property="og:image" content="https://www.italic.co.id/assets/og-image.jpg" />
    <meta property="og:image:width" content="1200" />
    <meta property="og:image:height" content="630" />
    <meta property="og:locale" content="id_ID" />
    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:title" content="Masuk — Italic" />
    <meta name="twitter:description" content="Masuk ke akun Italic untuk mulai menyewa buku." />
    <meta name="twitter:image" content="https://www.italic.co.id/assets/og-image.jpg" />
    <title>Masuk — Italic</title>
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
            <a href="account.php">Riwayat</a>
          </div>

          <div class="navbar-extra">
            <a href="catalog.php" class="icon-btn" aria-label="Cari buku"><svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><circle cx="11" cy="11" r="7"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg></a>
            <a href="login.php" class="icon-btn" aria-label="Masuk akun"><svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M20 21v-1a7 7 0 0 0-14 0v1"/><circle cx="12" cy="7" r="4"/></svg></a>
            <button type="button" id="hamburger-menu" class="icon-btn" aria-label="Buka menu" aria-expanded="false" aria-controls="navbar-nav"><svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg></button>
          </div>
        </div>
      </nav>
    </header>

    <main id="main">
      <div class="formsheet">
        <section class="card">
          <a href="javascript:history.back()" class="close icon-btn" aria-label="Tutup dan kembali" style="border:none;"><svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></a>
          <header class="card-title">Masuk ke Italic</header>
          <p class="card-sub">Kelola riwayat sewa dan terbitkan tiket QR baru dengan lebih cepat.</p>

              <?php
              $error = $_GET['error'] ?? '';
              $success = $_GET['success'] ?? '';

              if ($error === 'email') {

                  echo '<p class="form-status err">Email tidak ditemukan.</p>';

              } elseif ($error === 'password') {

                  echo '<p class="form-status err">Password salah.</p>';

              } elseif ($error === 'system') {

                  echo '<p class="form-status err">Terjadi kesalahan sistem. Silakan coba lagi.</p>';

              } elseif ($success === '1') {

                  echo '<p class="form-status ok">Akun berhasil dibuat. Silakan masuk.</p>';

              }
              ?>

          <form action="process/login.php" method="POST">
            <?php
            $redirectTarget = $_GET['redirect'] ?? '';
            // hanya izinkan path relatif di dalam situs, cegah open-redirect
            if ($redirectTarget !== '' && preg_match('#^[a-zA-Z0-9_\-./]+\.php(\?[a-zA-Z0-9=&_\-.]*)?$#', $redirectTarget)) {
                echo '<input type="hidden" name="redirect" value="' . htmlspecialchars($redirectTarget, ENT_QUOTES, 'UTF-8') . '" />';
            }
            ?>
            <div class="field">
              <label for="login-email">Email</label>
              <input
                  type="email"
                  id="login-email"
                  name="email"
                  placeholder="nama@email.com"
                  autocomplete="email"
                  required
              >
            </div>
            <div class="field">
              <label for="login-password">Kata Sandi</label>
              <input
                  type="password"
                  id="login-password"
                  name="password"
                  placeholder="••••••••"
                  autocomplete="current-password"
                  required
              >
            </div>
            <button type="submit" class="btn btn-primary btn-block">Masuk</button>
            <p class="form-status" aria-live="polite"></p>
          </form>
          <p class="helper-link">Belum punya akun? <a href="signup.php">Daftar di sini</a></p>
        </section>
      </div>
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
