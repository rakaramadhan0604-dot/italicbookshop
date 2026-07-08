<?php
session_start();
$italicLoggedIn = isset($_SESSION["login"]);
$italicMemberName = $_SESSION["nama"] ?? "";

if (!$italicLoggedIn) {
    $returnTo = "rent.php" . (isset($_GET['id']) ? "?id=" . urlencode($_GET['id']) : "");
    header("Location: login.php?redirect=" . urlencode($returnTo));
    exit;
}

include "config/database.php";

$stmt = $conn->prepare("SELECT nama_lengkap, email, no_hp, no_ktp, alamat FROM anggota WHERE id_anggota = ? LIMIT 1");
$stmt->bind_param("i", $_SESSION["id"]);
$stmt->execute();
$memberProfile = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$memberProfile) {
    // sesi tidak valid lagi (misal akun sudah dihapus) — paksa login ulang
    $conn->close();
    session_destroy();
    header("Location: login.php");
    exit;
}

// Ambil stok eksemplar tersedia terkini dari database, dipakai untuk
// menimpa angka "copies" statis di books-data.js supaya selalu akurat.
$italicStock = [];
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
    <meta name="description" content="Isi formulir sewa buku Italic dan terbitkan tiket QR peminjaman dalam tiga langkah." />

    <!-- Open Graph / social sharing -->
    <meta property="og:type" content="website" />
    <meta property="og:site_name" content="Italic" />
    <meta property="og:title" content="Sewa Buku — Italic" />
    <meta property="og:description" content="Isi formulir sewa buku Italic dan terbitkan tiket QR peminjaman dalam tiga langkah." />
    <meta property="og:url" content="https://www.italic.co.id/rent.php" />
    <meta property="og:image" content="https://www.italic.co.id/assets/og-image.jpg" />
    <meta property="og:image:width" content="1200" />
    <meta property="og:image:height" content="630" />
    <meta property="og:locale" content="id_ID" />
    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:title" content="Sewa Buku — Italic" />
    <meta name="twitter:description" content="Isi formulir sewa buku Italic dan terbitkan tiket QR peminjaman dalam tiga langkah." />
    <meta name="twitter:image" content="https://www.italic.co.id/assets/og-image.jpg" />
    <title>Sewa Buku — Italic</title>
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
      <div class="formsheet">
        <section class="card" style="max-width:760px;">
          <a href="javascript:history.back()" class="close icon-btn" aria-label="Tutup dan kembali" style="border:none;"><svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></a>

          <header class="card-title">Formulir Sewa Buku</header>
          <p class="card-sub">Lengkapi tiga langkah berikut untuk menerbitkan tiket peminjaman ber-QR.</p>

          <div class="step-indicator" id="step-indicator">
            <div class="dot is-active" data-step="1">1</div>
            <div class="bar"></div>
            <div class="dot" data-step="2">2</div>
            <div class="bar"></div>
            <div class="dot" data-step="3">3</div>
          </div>

          <!-- STEP 1: choose book + dates -->
          <form id="step-1">
            <div class="field">
              <label for="rent-book">Pilih Buku</label>
              <select id="rent-book" required></select>
            </div>
            <div class="field-row">
              <div class="field">
                <label for="rent-date">Tanggal Pengambilan</label>
                <input type="date" id="rent-date" required />
              </div>
              <div class="field">
                <label for="rent-duration">Durasi Sewa (hari)</label>
                <select id="rent-duration" required>
                  <option value="3">3 hari</option>
                  <option value="7" selected>7 hari</option>
                  <option value="14">14 hari</option>
                  <option value="30">30 hari</option>
                </select>
              </div>
            </div>
            <div class="field">
              <label>Metode Pengambilan</label>
              <div class="radio-group">
                <label class="radio-pill is-checked"><input type="radio" name="pickup" value="ambil" checked /> Ambil di Toko</label>
                <label class="radio-pill"><input type="radio" name="pickup" value="antar" /> Diantar (+Rp5.000)</label>
              </div>
            </div>
            <div class="rent-summary" id="summary-1">
              <div class="row"><span>Sewa per hari</span><span id="s1-price">—</span></div>
              <div class="row"><span>Durasi</span><span id="s1-duration">—</span></div>
              <div class="row total"><span>Estimasi Total</span><span id="s1-total">—</span></div>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Lanjut ke Data Diri</button>
          </form>

          <!-- STEP 2: personal data -->
          <form id="step-2" style="display:none;">
            <div class="field-row">
              <div class="field">
                <label for="rent-name">Nama Lengkap</label>
                <input type="text" id="rent-name" value="<?php echo htmlspecialchars($memberProfile['nama_lengkap'], ENT_QUOTES, 'UTF-8'); ?>" readonly />
              </div>
              <div class="field">
                <label for="rent-idnumber">NIK / No. KTP</label>
                <input type="text" id="rent-idnumber" value="<?php echo htmlspecialchars($memberProfile['no_ktp'], ENT_QUOTES, 'UTF-8'); ?>" readonly />
              </div>
            </div>
            <div class="field-row">
              <div class="field">
                <label for="rent-email">Email</label>
                <input type="email" id="rent-email" value="<?php echo htmlspecialchars($memberProfile['email'], ENT_QUOTES, 'UTF-8'); ?>" readonly />
              </div>
              <div class="field">
                <label for="rent-phone">No. HP / WhatsApp</label>
                <input type="tel" id="rent-phone" value="<?php echo htmlspecialchars($memberProfile['no_hp'], ENT_QUOTES, 'UTF-8'); ?>" readonly />
              </div>
            </div>
            <p class="field-hint" style="margin-top:-0.6rem;margin-bottom:1rem;">Data di atas mengikuti akun yang sedang masuk (<?php echo htmlspecialchars($italicMemberName, ENT_QUOTES, 'UTF-8'); ?>).</p>
            <div class="field">
              <label for="rent-address">Alamat <span id="rent-address-req-label">(untuk pengantaran)</span></label>
              <textarea id="rent-address" placeholder="Wajib diisi jika memilih pengantaran"><?php echo htmlspecialchars($memberProfile['alamat'], ENT_QUOTES, 'UTF-8'); ?></textarea>
            </div>
            <div class="field">
              <label for="rent-notes">Catatan Tambahan (opsional)</label>
              <textarea id="rent-notes" placeholder="Contoh: titip di resepsionis, dsb."></textarea>
            </div>
            <p class="rent-tnc" style="font-size:0.86rem;color:var(--ink-soft);margin-bottom:1rem;">Dengan melanjutkan, kamu menyetujui <a href="about.php#syarat" style="border-bottom:1px solid var(--ink);font-weight:600;">Syarat &amp; Ketentuan Sewa Italic</a>, termasuk kewajiban mengembalikan buku dalam kondisi baik sebelum tanggal jatuh tempo.</p>
            <div style="display:flex;gap:0.8rem;">
              <button type="button" class="btn btn-outline" id="back-to-1">Kembali</button>
              <button type="submit" class="btn btn-primary btn-block" id="submit-step-2">Terbitkan Tiket Sewa</button>
            </div>
            <p class="form-status" aria-live="polite"></p>
          </form>

          <!-- STEP 3: ticket / QR -->
          <div id="step-3" style="display:none;">
            <div class="ticket">
              <div class="ticket-head">
                <img src="assets/logo.svg" alt="Italic" />
                <span class="status" id="t-status">Diproses</span>
              </div>
              <div class="ticket-body">
                <div class="ticket-fields">
                  <div><span class="k">Peminjam</span><div class="v" id="t-name"></div></div>
                  <div><span class="k">Buku</span><div class="v" id="t-book"></div></div>
                  <div><span class="k">Tanggal Pinjam</span><div class="v" id="t-borrow"></div></div>
                  <div><span class="k">Jatuh Tempo</span><div class="v" id="t-due"></div></div>
                  <div><span class="k">Total Biaya</span><div class="v" id="t-total"></div></div>
                </div>
                <div class="ticket-qr" id="t-qr"></div>
              </div>
              <div class="ticket-code" id="t-code"></div>
            </div>
            <p class="field-hint mt-1">Simpan tangkapan layar tiket ini. Tunjukkan kode QR saat pengambilan maupun pengembalian buku di gerai Italic atau kepada kurir.</p>
            <div style="display:flex;gap:0.8rem;margin-top:1.4rem;">
              <button type="button" class="btn btn-outline btn-block" onclick="window.print()">Cetak Tiket</button>
              <a href="account.php" class="btn btn-primary btn-block">Lihat Riwayat Pinjaman</a>
            </div>
          </div>
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

    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js" integrity="sha512-CNgIRecGo7nphbeZ04Sc13ka07paqdeTu0WR1IM4kNcpmBAUSHSQX0FslNhTDadL4O5SAGapGt4FodqL8My0mA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script>
      (function () {
        const params = new URLSearchParams(location.search);
        const preselect = Number(params.get("id")) || ITALIC_BOOKS[0].id;

        const bookSelect = document.getElementById("rent-book");
        ITALIC_BOOKS.filter((b) => b.copies > 0).forEach((b) => {
          const opt = document.createElement("option");
          opt.value = b.id;
          opt.textContent = `${b.title} — ${b.author} (${formatRupiah(b.pricePerDay)}/hari)`;
          if (b.id === preselect) opt.selected = true;
          bookSelect.appendChild(opt);
        });

        const dateInput = document.getElementById("rent-date");
        const today = new Date().toISOString().slice(0, 10);
        dateInput.min = today;
        dateInput.value = today;

        const durationSelect = document.getElementById("rent-duration");
        const pickupRadios = document.querySelectorAll('input[name="pickup"]');

        function currentBook() {
          return ITALIC_BOOKS.find((b) => b.id === Number(bookSelect.value)) || ITALIC_BOOKS[0];
        }
        function pickupValue() {
          return document.querySelector('input[name="pickup"]:checked').value;
        }
        function updateSummary() {
          const book = currentBook();
          const days = Number(durationSelect.value);
          const delivery = pickupValue() === "antar" ? 5000 : 0;
          const total = book.pricePerDay * days + delivery;
          document.getElementById("s1-price").textContent = formatRupiah(book.pricePerDay);
          document.getElementById("s1-duration").textContent = days + " hari" + (delivery ? " + ongkir " + formatRupiah(delivery) : "");
          document.getElementById("s1-total").textContent = formatRupiah(total);
          const reqLabel = document.getElementById("rent-address-req-label");
          if (reqLabel) {
            reqLabel.textContent = pickupValue() === "antar" ? "(wajib diisi untuk pengantaran)" : "(opsional untuk ambil di toko)";
          }
        }
        [bookSelect, durationSelect].forEach((el) => el.addEventListener("change", updateSummary));
        pickupRadios.forEach((r) => r.addEventListener("change", updateSummary));
        updateSummary();

        const stepEls = { 1: document.getElementById("step-1"), 2: document.getElementById("step-2"), 3: document.getElementById("step-3") };
        const dots = document.querySelectorAll(".step-indicator .dot");
        const bars = document.querySelectorAll(".step-indicator .bar");
        function goStep(n) {
          Object.entries(stepEls).forEach(([k, el]) => (el.style.display = Number(k) === n ? "" : "none"));
          dots.forEach((d) => {
            const s = Number(d.dataset.step);
            d.classList.toggle("is-active", s === n);
            d.classList.toggle("is-done", s < n);
          });
          bars.forEach((b, i) => b.classList.toggle("is-done", i < n - 1));
        }

        document.getElementById("step-1").addEventListener("submit", (e) => {
          e.preventDefault();
          goStep(2);
        });
        document.getElementById("back-to-1").addEventListener("click", () => goStep(1));

        document.getElementById("step-2").addEventListener("submit", (e) => {
          e.preventDefault();

          const addressEl = document.getElementById("rent-address");
          const isDelivery = pickupValue() === "antar";
          const addressField = addressEl.closest(".field");
          if (isDelivery && !addressEl.value.trim()) {
            addressField.classList.add("has-error");
            return;
          }
          addressField.classList.remove("has-error");

          const submitBtn = document.getElementById("submit-step-2");
          submitBtn.disabled = true;
          submitBtn.textContent = "Memproses...";

          const statusEl = document.querySelector("#step-2 .form-status");
          statusEl.textContent = "";
          statusEl.className = "form-status";

          const book = currentBook();
          const body = new URLSearchParams({
            id_buku: book.id,
            duration_days: durationSelect.value,
            pickup: pickupValue(),
            borrow_date: dateInput.value,
            address: addressEl.value.trim(),
            notes: document.getElementById("rent-notes").value.trim(),
          });

          fetch("process/rent.php", { method: "POST", body })
            .then((res) => res.json().then((data) => ({ status: res.status, data })))
            .then(({ status, data }) => {
              if (status !== 200 || !data.ok) {
                throw new Error(data.error || "Gagal menerbitkan tiket. Silakan coba lagi.");
              }

              const loan = data.loan;
              document.getElementById("t-name").textContent = <?php echo json_encode($memberProfile['nama_lengkap']); ?>;
              document.getElementById("t-book").textContent = book.title + " — " + book.author;
              document.getElementById("t-borrow").textContent = loan.borrowDate;
              document.getElementById("t-due").textContent = loan.dueDate;
              document.getElementById("t-total").textContent = formatRupiah(loan.total);
              document.getElementById("t-code").textContent = loan.loanCode;
              document.getElementById("t-status").textContent = "Diproses";
              renderQrInto(document.getElementById("t-qr"), loan.loanCode);
             
              goStep(3);
              italicToast("Tiket sewa berhasil diterbitkan.");
            })
            .catch((err) => {
              statusEl.textContent = err.message;
              statusEl.className = "form-status err";
            })
            .finally(() => {
              submitBtn.disabled = false;
              submitBtn.textContent = "Terbitkan Tiket Sewa";
            });
        });

        goStep(1);
      })();
    </script>
  </body>
</html>
