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
    <meta name="robots" content="noindex" />
    <title>404 — Halaman Tidak Ditemukan | Italic</title>
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
            <?php if ($italicLoggedIn): ?>
            <a href="account.php" class="icon-btn" aria-label="Akun: <?php echo htmlspecialchars($italicMemberName, ENT_QUOTES, 'UTF-8'); ?>"><svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M20 21v-1a7 7 0 0 0-14 0v1"/><circle cx="12" cy="7" r="4"/></svg></a>
            <?php else: ?>
            <a href="login.php" class="icon-btn" aria-label="Masuk akun"><svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M20 21v-1a7 7 0 0 0-14 0v1"/><circle cx="12" cy="7" r="4"/></svg></a>
            <?php endif; ?>
            <button type="button" id="hamburger-menu" class="icon-btn" aria-label="Buka menu" aria-expanded="false" aria-controls="navbar-nav"><svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg></button>
          </div>
        </div>
      </nav>
    </header>

    <main id="main">
      <div class="formsheet">
        <section class="card" style="text-align:center;">
          <header class="card-title">404 — Halaman Tidak Ditemukan</header>
          <p class="card-sub">Halaman yang kamu cari sudah pindah rak, atau memang belum pernah ada di sini.</p>
          <a href="index.php" class="btn btn-primary btn-block">Kembali ke Beranda</a>
          <p class="helper-link"><a href="catalog.php">Jelajahi katalog buku</a></p>
        </section>
      </div>
    </main>

    <footer class="site-footer">
      <div class="wrap footer-bottom">
        <p>&copy; 2026 Italic Book Rental. Semua hak cipta dilindungi.</p>
      </div>
    </footer>
    <script src="assets/js/script.js"></script>
  </body>
</html>
