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
    <meta name="description" content="Detail buku, sinopsis, dan opsi sewa harian di Italic." />

    <!-- Open Graph / social sharing -->
    <meta property="og:type" content="website" />
    <meta property="og:site_name" content="Italic" />
    <meta property="og:title" content="Detail Buku — Italic" />
    <meta property="og:description" content="Detail buku, sinopsis, dan opsi sewa harian di Italic." />
    <meta property="og:url" content="https://www.italic.co.id/book.php" />
    <meta property="og:image" content="https://www.italic.co.id/assets/og-image.jpg" />
    <meta property="og:image:width" content="1200" />
    <meta property="og:image:height" content="630" />
    <meta property="og:locale" content="id_ID" />
    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:title" content="Detail Buku — Italic" />
    <meta name="twitter:description" content="Detail buku, sinopsis, dan opsi sewa harian di Italic." />
    <meta name="twitter:image" content="https://www.italic.co.id/assets/og-image.jpg" />
    <title>Detail Buku — Italic</title>
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
            <a href="catalog.php" aria-current="page">Katalog</a>
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
      <section class="section-tight" style="padding-top:2.2rem;">
        <div class="wrap">
          <a href="catalog.php" class="side-link" style="border-bottom:1px solid var(--ink);">← Kembali ke Katalog</a>
        </div>
      </section>

      <section class="section-tight" id="book-root">
        <div class="wrap">
          <div class="book-detail">
            <div class="book-cover-wrap">
              <div class="cover"><img id="b-cover" src="" alt="" /></div>
            </div>
            <div class="book-info">
              <span class="index-no idx" id="b-idx"></span>
              <h1 id="b-title"></h1>
              <p class="author-line upright" id="b-author"></p>

              <div class="price-box">
                <div class="price" id="b-price"></div>
                <a id="b-rent-link" class="btn btn-primary" href="#">Sewa Buku Ini</a>
              </div>

              <div class="spec-table" id="b-specs"></div>

              <div class="synopsis-block">
                <h4>Sinopsis</h4>
                <p id="b-synopsis"></p>
              </div>
            </div>
          </div>
        </div>
      </section>

      <section class="section-tight" style="border-top:1px solid var(--line);">
        <div class="wrap">
          <div class="section-head">
            <div>
              <p class="eyebrow">Rekomendasi</p>
              <h2>Judul Serupa</h2>
            </div>
          </div>
          <div class="cat-grid" id="related-grid"></div>
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
      (function () {
        const params = new URLSearchParams(location.search);
        const id = Number(params.get("id")) || 1;
        const book = ITALIC_BOOKS.find((b) => b.id === id) || ITALIC_BOOKS[0];

        document.title = book.title + " — Italic";
        document.getElementById("b-cover").src = book.cover;
        document.getElementById("b-cover").alt = "Sampul buku " + book.title;
        document.getElementById("b-idx").textContent = "No. " + String(book.id).padStart(3, "0") + " — " + book.genre;
        document.getElementById("b-title").textContent = book.title;
        document.getElementById("b-author").textContent = "oleh " + book.author;
        document.getElementById("b-price").innerHTML = formatRupiah(book.pricePerDay) + "<small>/ hari sewa</small>";
        document.getElementById("b-rent-link").href = "rent.php?id=" + book.id;
        document.getElementById("b-synopsis").textContent = book.synopsis;

        const specs = [
          ["Penulis", book.author],
          ["Genre", book.genre],
          ["Bahasa", book.language],
          ["Jumlah Halaman", book.pages + " hlm."],
          ["Tahun Terbit", book.year],
          ["Kondisi Eksemplar", book.condition],
          ["Eksemplar Tersedia", book.copies + " dari total peredaran"],
        ];
        document.getElementById("b-specs").innerHTML = specs
          .map(([k, v]) => `<div><span>${k}</span><span>${v}</span></div>`)
          .join("");

        if (book.copies <= 0) {
          const link = document.getElementById("b-rent-link");
          link.textContent = "Sedang Dipinjam";
          link.classList.add("btn-outline");
          link.removeAttribute("href");
          link.setAttribute("aria-disabled", "true");
        }

        const related = ITALIC_BOOKS.filter((b) => b.genre === book.genre && b.id !== book.id).slice(0, 4);
        renderBookTiles(related.length ? related : ITALIC_BOOKS.filter(b=>b.id!==book.id).slice(0,4), document.getElementById("related-grid"));
      })();
    </script>
  </body>
</html>
