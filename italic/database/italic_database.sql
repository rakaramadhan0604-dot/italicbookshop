-- =====================================================================
--  ITALIC — SKEMA BASIS DATA SEWA BUKU
--  Mesin   : MySQL 8 / MariaDB 10.4+ (InnoDB, utf8mb4)
--  Cakupan : anggota, staf, katalog (penulis/kategori/buku/eksemplar),
--            transaksi peminjaman + detail item, pembayaran, denda,
--            log pemindaian QR, ulasan, wishlist, dan notifikasi.
--
--  Catatan desain:
--  - `buku` adalah data JUDUL (metadata katalog).
--  - `eksemplar` adalah data FISIK per kopi buku (yang benar-benar
--    dipinjam), sehingga satu judul bisa punya beberapa kopi yang
--    beredar dan dilacak status masing-masing secara independen.
--  - `peminjaman` = satu transaksi/tiket sewa (satu kode, satu QR).
--  - `detail_peminjaman` = item per eksemplar dalam transaksi tsb.
--    Dipisah dari `peminjaman` agar struktur siap dikembangkan ke
--    "sewa banyak buku dalam satu tiket" tanpa mengubah skema inti.
--  - `qr_code_data` pada `peminjaman` menyimpan payload JSON yang
--    persis yang di-encode ke dalam gambar QR di sisi front-end
--    (lihat assets/js/script.js -> ItalicBooking.qrPayload).
-- =====================================================================

CREATE DATABASE IF NOT EXISTS italic_db
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE italic_db;

SET FOREIGN_KEY_CHECKS = 0;

-- ---------------------------------------------------------------------
-- 1. ANGGOTA (pelanggan / penyewa)
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS anggota;
CREATE TABLE anggota (
  id_anggota          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  kode_anggota        VARCHAR(20)  NOT NULL UNIQUE,          -- ex: ITL-MBR-000123
  nama_lengkap        VARCHAR(120) NOT NULL,
  email               VARCHAR(120) NOT NULL UNIQUE,
  no_hp               VARCHAR(20)  NOT NULL,
  no_ktp              VARCHAR(20)  NOT NULL,
  alamat              TEXT         NOT NULL,
  kata_sandi_hash     VARCHAR(255) NOT NULL,
  tipe_keanggotaan    ENUM('reguler','premium') NOT NULL DEFAULT 'reguler',
  status_keanggotaan  ENUM('aktif','ditangguhkan','nonaktif') NOT NULL DEFAULT 'aktif',
  saldo_denda         DECIMAL(10,2) NOT NULL DEFAULT 0.00,   -- akumulasi denda belum lunas
  tanggal_daftar      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  dibuat_pada         DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  diperbarui_pada     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_anggota_email (email),
  INDEX idx_anggota_status (status_keanggotaan)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 2. STAF (admin gerai / kurator / kurir)
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS staf;
CREATE TABLE staf (
  id_staf         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nama            VARCHAR(120) NOT NULL,
  email           VARCHAR(120) NOT NULL UNIQUE,
  kata_sandi_hash VARCHAR(255) NOT NULL,
  peran           ENUM('admin','kasir','kurator','kurir') NOT NULL DEFAULT 'kasir',
  aktif           TINYINT(1) NOT NULL DEFAULT 1,
  dibuat_pada     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 3. PENULIS
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS penulis;
CREATE TABLE penulis (
  id_penulis    INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nama_penulis  VARCHAR(150) NOT NULL,
  biografi      TEXT NULL,
  UNIQUE KEY uq_penulis_nama (nama_penulis)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 4. KATEGORI (genre)
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS kategori;
CREATE TABLE kategori (
  id_kategori    INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nama_kategori  VARCHAR(60) NOT NULL,
  slug           VARCHAR(60) NOT NULL UNIQUE,
  deskripsi      VARCHAR(255) NULL
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 5. BUKU (judul / metadata katalog)
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS buku;
CREATE TABLE buku (
  id_buku               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  judul                 VARCHAR(200) NOT NULL,
  id_penulis            INT UNSIGNED NOT NULL,
  id_kategori           INT UNSIGNED NOT NULL,
  isbn                  VARCHAR(20)  NULL,
  penerbit              VARCHAR(120) NULL,
  tahun_terbit          SMALLINT UNSIGNED NULL,
  bahasa                VARCHAR(30)  NOT NULL DEFAULT 'Indonesia',
  jumlah_halaman        SMALLINT UNSIGNED NULL,
  sinopsis              TEXT NULL,
  cover_image           VARCHAR(255) NULL,
  harga_sewa_per_hari   DECIMAL(10,2) NOT NULL,
  harga_denda_per_hari  DECIMAL(10,2) NOT NULL,
  rating_rata           DECIMAL(2,1) NOT NULL DEFAULT 0.0,
  dibuat_pada           DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_buku_penulis  FOREIGN KEY (id_penulis)  REFERENCES penulis(id_penulis)   ON DELETE RESTRICT,
  CONSTRAINT fk_buku_kategori FOREIGN KEY (id_kategori) REFERENCES kategori(id_kategori) ON DELETE RESTRICT,
  INDEX idx_buku_judul (judul),
  INDEX idx_buku_kategori (id_kategori)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 6. EKSEMPLAR (kopi fisik per judul — yang benar-benar dipinjam)
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS eksemplar;
CREATE TABLE eksemplar (
  id_eksemplar   INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_buku        INT UNSIGNED NOT NULL,
  kode_eksemplar VARCHAR(30)  NOT NULL UNIQUE,   -- barcode fisik, ex: ITL-EX-001-01
  kondisi        ENUM('Sangat Baik','Baik','Cukup Baik','Perlu Perbaikan') NOT NULL DEFAULT 'Baik',
  lokasi_rak     VARCHAR(20) NULL,
  status         ENUM('tersedia','dipinjam','diproses','perbaikan','hilang') NOT NULL DEFAULT 'tersedia',
  tanggal_masuk  DATE NOT NULL DEFAULT (CURRENT_DATE),
  CONSTRAINT fk_eksemplar_buku FOREIGN KEY (id_buku) REFERENCES buku(id_buku) ON DELETE CASCADE,
  INDEX idx_eksemplar_status (status),
  INDEX idx_eksemplar_buku (id_buku)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 7. PEMINJAMAN (transaksi / tiket sewa — satu baris = satu kode QR)
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS peminjaman;
CREATE TABLE peminjaman (
  id_peminjaman        INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  kode_peminjaman       VARCHAR(30) NOT NULL UNIQUE,     -- ex: ITL-20260703-4821 (di-encode ke QR)
  id_anggota            INT UNSIGNED NOT NULL,
  id_staf_pemroses      INT UNSIGNED NULL,               -- staf yang memvalidasi pengambilan
  id_staf_penerima      INT UNSIGNED NULL,               -- staf yang memvalidasi pengembalian
  tanggal_pinjam        DATE NOT NULL,
  tanggal_jatuh_tempo    DATE NOT NULL,
  tanggal_kembali_aktual DATE NULL,
  metode_pengambilan    ENUM('ambil_di_toko','diantar') NOT NULL DEFAULT 'ambil_di_toko',
  alamat_pengantaran    TEXT NULL,
  biaya_antar           DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  subtotal_sewa         DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  total_denda           DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  total_biaya           DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  status                ENUM('diproses','aktif','selesai','terlambat','dibatalkan') NOT NULL DEFAULT 'diproses',
  catatan               TEXT NULL,
  qr_code_data          TEXT NOT NULL,                   -- payload JSON persis isi QR
  dibuat_pada           DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  diperbarui_pada       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_pinjam_anggota FOREIGN KEY (id_anggota) REFERENCES anggota(id_anggota) ON DELETE RESTRICT,
  CONSTRAINT fk_pinjam_staf_proses  FOREIGN KEY (id_staf_pemroses) REFERENCES staf(id_staf) ON DELETE SET NULL,
  CONSTRAINT fk_pinjam_staf_terima  FOREIGN KEY (id_staf_penerima) REFERENCES staf(id_staf) ON DELETE SET NULL,
  INDEX idx_pinjam_status (status),
  INDEX idx_pinjam_anggota (id_anggota),
  INDEX idx_pinjam_jatuh_tempo (tanggal_jatuh_tempo)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 8. DETAIL_PEMINJAMAN (item eksemplar per transaksi)
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS detail_peminjaman;
CREATE TABLE detail_peminjaman (
  id_detail            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_peminjaman        INT UNSIGNED NOT NULL,
  id_eksemplar         INT UNSIGNED NOT NULL,
  harga_sewa_per_hari  DECIMAL(10,2) NOT NULL,     -- snapshot harga saat transaksi dibuat
  jumlah_hari          SMALLINT UNSIGNED NOT NULL,
  subtotal             DECIMAL(10,2) NOT NULL,
  kondisi_saat_pinjam  ENUM('Sangat Baik','Baik','Cukup Baik','Perlu Perbaikan') NOT NULL,
  kondisi_saat_kembali ENUM('Sangat Baik','Baik','Cukup Baik','Perlu Perbaikan','Hilang') NULL,
  status_item          ENUM('dipinjam','dikembalikan','rusak','hilang') NOT NULL DEFAULT 'dipinjam',
  CONSTRAINT fk_detail_peminjaman FOREIGN KEY (id_peminjaman) REFERENCES peminjaman(id_peminjaman) ON DELETE CASCADE,
  CONSTRAINT fk_detail_eksemplar  FOREIGN KEY (id_eksemplar)  REFERENCES eksemplar(id_eksemplar)   ON DELETE RESTRICT,
  INDEX idx_detail_peminjaman (id_peminjaman)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 9. PEMBAYARAN
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS pembayaran;
CREATE TABLE pembayaran (
  id_pembayaran      INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_peminjaman      INT UNSIGNED NOT NULL,
  jumlah_bayar       DECIMAL(10,2) NOT NULL,
  metode_pembayaran  ENUM('qris','transfer_bank','tunai','kartu_debit','kartu_kredit') NOT NULL,
  status_pembayaran  ENUM('menunggu','lunas','gagal','dikembalikan') NOT NULL DEFAULT 'menunggu',
  referensi_bayar    VARCHAR(80) NULL,          -- ID transaksi payment gateway / no. struk
  tanggal_bayar      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_bayar_peminjaman FOREIGN KEY (id_peminjaman) REFERENCES peminjaman(id_peminjaman) ON DELETE CASCADE,
  INDEX idx_bayar_status (status_pembayaran)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 10. DENDA (rincian denda per insiden — terlambat/rusak/hilang)
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS denda;
CREATE TABLE denda (
  id_denda       INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_peminjaman  INT UNSIGNED NOT NULL,
  id_detail      INT UNSIGNED NULL,
  jenis_denda    ENUM('terlambat','rusak','hilang') NOT NULL,
  jumlah         DECIMAL(10,2) NOT NULL,
  keterangan     VARCHAR(255) NULL,
  status_denda   ENUM('belum_dibayar','lunas','dihapuskan') NOT NULL DEFAULT 'belum_dibayar',
  tanggal_dikenakan DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_denda_peminjaman FOREIGN KEY (id_peminjaman) REFERENCES peminjaman(id_peminjaman) ON DELETE CASCADE,
  CONSTRAINT fk_denda_detail     FOREIGN KEY (id_detail)     REFERENCES detail_peminjaman(id_detail) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 11. QR_SCAN_LOG (audit setiap pemindaian tiket QR)
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS qr_scan_log;
CREATE TABLE qr_scan_log (
  id_log         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_peminjaman  INT UNSIGNED NOT NULL,
  kode_qr        VARCHAR(30) NOT NULL,
  tipe_pemindaian ENUM('pengambilan','pengembalian','verifikasi_lain') NOT NULL,
  hasil_pemindaian ENUM('valid','tidak_valid','kedaluwarsa') NOT NULL DEFAULT 'valid',
  id_staf_pemindai INT UNSIGNED NULL,
  lokasi_pemindaian VARCHAR(120) NULL,
  waktu_pindai   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_log_peminjaman FOREIGN KEY (id_peminjaman) REFERENCES peminjaman(id_peminjaman) ON DELETE CASCADE,
  CONSTRAINT fk_log_staf FOREIGN KEY (id_staf_pemindai) REFERENCES staf(id_staf) ON DELETE SET NULL,
  INDEX idx_log_kode (kode_qr)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 12. ULASAN
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS ulasan;
CREATE TABLE ulasan (
  id_ulasan     INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_anggota    INT UNSIGNED NOT NULL,
  id_buku       INT UNSIGNED NOT NULL,
  id_peminjaman INT UNSIGNED NULL,       -- ulasan tertaut ke transaksi tertentu (opsional)
  rating        TINYINT UNSIGNED NOT NULL CHECK (rating BETWEEN 1 AND 5),
  komentar      TEXT NULL,
  tanggal       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_ulasan_anggota FOREIGN KEY (id_anggota) REFERENCES anggota(id_anggota) ON DELETE CASCADE,
  CONSTRAINT fk_ulasan_buku    FOREIGN KEY (id_buku)    REFERENCES buku(id_buku)       ON DELETE CASCADE,
  CONSTRAINT fk_ulasan_pinjam  FOREIGN KEY (id_peminjaman) REFERENCES peminjaman(id_peminjaman) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 13. WISHLIST
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS wishlist;
CREATE TABLE wishlist (
  id_wishlist       INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_anggota        INT UNSIGNED NOT NULL,
  id_buku           INT UNSIGNED NOT NULL,
  tanggal_ditambahkan DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_wishlist_anggota FOREIGN KEY (id_anggota) REFERENCES anggota(id_anggota) ON DELETE CASCADE,
  CONSTRAINT fk_wishlist_buku    FOREIGN KEY (id_buku)    REFERENCES buku(id_buku)       ON DELETE CASCADE,
  UNIQUE KEY uq_wishlist (id_anggota, id_buku)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 14. NOTIFIKASI
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS notifikasi;
CREATE TABLE notifikasi (
  id_notifikasi  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_anggota     INT UNSIGNED NOT NULL,
  id_peminjaman  INT UNSIGNED NULL,
  tipe           ENUM('pengingat_jatuh_tempo','keterlambatan','promo','info_akun') NOT NULL,
  pesan          VARCHAR(255) NOT NULL,
  status_baca    ENUM('belum_dibaca','dibaca') NOT NULL DEFAULT 'belum_dibaca',
  tanggal_kirim  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_notif_anggota FOREIGN KEY (id_anggota) REFERENCES anggota(id_anggota) ON DELETE CASCADE,
  CONSTRAINT fk_notif_peminjaman FOREIGN KEY (id_peminjaman) REFERENCES peminjaman(id_peminjaman) ON DELETE SET NULL
) ENGINE=InnoDB;

SET FOREIGN_KEY_CHECKS = 1;
-- =====================================================================
-- Seed: staf gerai Italic
-- =====================================================================
USE italic_db;

INSERT INTO staf (id_staf, nama, email, kata_sandi_hash, peran) VALUES
(1, 'Alya Ramadhani', 'alya@italic.co.id', '$2y$10$examplehash000000000000000000000000000001', 'admin'),
(2, 'Dimas Prakoso',  'dimas@italic.co.id', '$2y$10$examplehash000000000000000000000000000002', 'kasir'),
(3, 'Salsa Nuraini',  'salsa@italic.co.id', '$2y$10$examplehash000000000000000000000000000003', 'kasir'),
(4, 'Reza Firmansyah','reza@italic.co.id', '$2y$10$examplehash000000000000000000000000000004', 'kurir');
USE italic_db;

-- Seed: penulis
INSERT INTO penulis (id_penulis, nama_penulis) VALUES
(1, 'Matt Haig'),
(2, 'Tere Liye'),
(3, 'Leila S Chudori'),
(4, 'Toshikazu Kawaguchi'),
(5, 'Keigo Higashino'),
(6, 'Ziggy Zeszyazeoviennazabrizkie'),
(7, 'Nanda Afandi'),
(8, 'R.F. Kuang'),
(9, 'Zoulfa Katouh'),
(10, 'Taylor Jenkins Reid'),
(11, 'Nina Ardianti'),
(12, 'Chan Ho-Kei'),
(13, 'Durian Sukegawa'),
(14, 'Ayu Rianna');

-- Seed: kategori
INSERT INTO kategori (id_kategori, nama_kategori, slug) VALUES
(1, 'Fiction', 'fiction'),
(2, 'Romance', 'romance');

-- Seed: buku (judul)
INSERT INTO buku (id_buku, judul, id_penulis, id_kategori, isbn, penerbit, tahun_terbit, bahasa, jumlah_halaman, sinopsis, cover_image, harga_sewa_per_hari, harga_denda_per_hari, rating_rata) VALUES
(1, 'The Midnight Library', 1, 1, '978-602-0001-17-1', 'Penerbit Mitra Italic', 2019, 'Inggris', 186, 'Di dalam Perpustakaan Tengah Malam, terdapat rak-rak yang menyimpan buku-buku untuk memberikan kesempatan Nora mencoba kehidupan lain yang bisa ia jalani. Ketika di ambang kematian, Nora mencoba kehidupan lainnya yang ingin ia jalani lewat buku-buku di Perpustakaan Tengah Malam, dibantu oleh penjaga perpustakaan, Mrs. Elm. Nora tidak bisa kembali ke Perpustakaan apabila ia tidak merasakan penyesalan saat menjalani kehidupan lainnya yang sedang ia jalani. Perpustakaan Tengah Malam karya Matt Haig adalah sebuah novel yang mempesona karena membawa pembaca berpetualang menjelajahi kemungkinan kehidupan-kehidupan Nora Seed di rak perpustakaan. Penulis buku terlaris internasional Reasons to Stay Alive dan How To Stop Time ini kemudian merilis cerita dengan isu kesehatan mental yang dibungkus oleh genre sci-fi yang menarik. Buku ini berbicara tentang penyesalan, hubungan, mimpi, hewan peliharaa', 'assets/photos/cover-01.jpg', 4000, 800, 4.1),
(2, 'Hujan', 2, 2, '978-602-0002-24-2', 'Penerbit Mitra Italic', 2016, 'Indonesia', 215, 'Mengisahkan kisah cinta serta perjuangan hidup Lail. Saat usianya baru menginjak 13 tahun, Lail menjadi seorang yatim piatu akibat ayah dan ibu Lail yang terkena letusan Gunung Api Purba dan gempa yang membuat kota tempat mereka tinggal hancur. Lail yang pada saat itu juga termasuk korban bencana berhasil diselamatkan oleh anak laki-laki bernama Esok. Lail dan Esok akhirnya menjadi sepasang yang tak terpisahkan sampai akhirnya mereka harus berpisah karena tempat pengungsian yang mereka tinggali tutup. Lail akhirnya menetap di sebuah panti sosial dan Esok diangkat menjadi anak salah satu keluarga. Mereka menjalani kehidupannya masing-masing. Pertemuan mereka untuk melepas rindu dilakukan rutin sebulan sekali meski akhirnya jadwal pertemuan harus diubah akibat Esok yang harus meneruskan pendidikan di Ibu Kota. Pertemuan mereka berubah menjadi setiap Esok berlibur semester. Frekuensi pertem', 'assets/photos/cover-02.jpg', 3000, 600, 4.2),
(3, 'Laut Bercerita', 3, 1, '978-602-0003-31-3', 'Penerbit Mitra Italic', 2021, 'Indonesia', 331, 'Laut Bercerita, bertutur tentang kisah keluarga yang kehilangan, sekumpulan sahabat yang merasakan kekosongan di dada, sekelompok orang yang gemar menyiksa dan lancar berkhianat, sejumlah keluarga yang mencari kejelasan makam anaknya, dan tentang cinta yang tak akan luntur. Menceritakan bagaimana Laut dan kawan-kawannya menyusun rencana, berpindah-pindah dalam pelarian, hingga tertangkap oleh pasukan rahasia. Tentang perasaan keluarga korban penghilangan paksa, bagaimana pencarian mereka terhadap kerabat mereka yang tak pernah kembali.', 'assets/photos/cover-03.jpg', 3000, 600, 4.3),
(4, 'Funiculi Funicula (1)', 4, 2, '978-602-0004-38-4', 'Penerbit Mitra Italic', 2018, 'Indonesia', 203, 'Kafe tua yang berada di gang kecil Tokyo terletak di bawah gedung lain, tidak butuh pendingin untuk mendinginkan Kafe tersebut. Tidak begitu ramai, namun terkenal karena bisa membawa pengunjungnya menjelajahi waktu. Keajaiban kafe itu menarik seorang wanita yang ingin memutar waktu untuk berbaikan dengan kekasihnya, seorang perawat yang ingin membaca surat yang tak sempat diberikan suaminya yang sakit, seorang kakak yang ingin menemui adiknya untuk terakhir kali, dan seorang ibu yang ingin bertemu dengan anak yang mungkin takkan pernah dikenalnya. Namun ada banyak peraturan yang harus diingat. Satu, mereka harus tetap duduk di kursi yang telah ditentukan. Dua, apapun yang mereka lakukan di masa yang didatangi takkan mengubah kenyataan di masa kini. Tiga, mereka harus menghabiskan kopi khusus yang disajikan sebelum kopi itu dingin. Rentetan peraturan lainnya tak menghentikan orang-orang i', 'assets/photos/cover-04.jpg', 2000, 400, 4.4),
(5, 'Funiculi Funicula (2)', 4, 2, '978-602-0005-45-5', 'Penerbit Mitra Italic', 2018, 'Indonesia', 323, 'Funiculi Funicula, sebuah kafe di gang sempit di Tokyo, masih kerap didatangi oleh orang-orang yang ingin menjelajah waktu. Peraturan-peraturan yang merepotkan masih berlaku, tetapi itu semua tidak menyurutkan harapan mereka untuk memutar waktu. Kali ini ada seorang pria yang ingin kembali ke masa lalu untuk menemui sahabat yang putrinya ia besarkan, seorang putra putus asa yang tidak menghadiri pemakaman ibunya, seorang pria sekarat yang ingin melompat kedua tahun kemudian untuk memastikan kekasihnya bahagia, dan seorang detektif yang ingin memberi istrinya hadiah ulang tahun untuk pertama sekaligus terakhir kalinya. Kenyataan memang akan tetap sama. Namun dalam singkatnya durasi sampai kopi mendingin, mungkin masih tersisa waktu bagi mereka untuk menghapus penyesalan, membebaskan diri dari rasa bersalah atau mungkin melihat terwujudnya harapan.', 'assets/photos/cover-05.jpg', 2000, 400, 4.5),
(6, 'Dona Dona (3)', 4, 2, '978-602-0006-52-6', 'Penerbit Mitra Italic', 2022, 'Indonesia', 236, 'Di sebuah lereng indah tak bernama di Hakodate, Hokkaido, berdiri Kafe Dona Dona yang menawarkan layanan istimewa kepada pengunjungnya: perjalananan melintasi waktu. Seperti di Funiculi Funicula yang ada di Tokyo, hal tersebut hanya dapat dilakukan jika berbagai peraturan yang merepotkan dipenuhi dan dengan secangkir kopi yang dituangkan oleh perempuan di keluarga Tokita. Mereka yang ingin memutar waktu adalah seorang wanita muda yang menyimpan dendam kepada orangtua yang menjadikannya yatim piatu kesepian, seorang komedian yang kehilangan tujuan hidup setelah berhasil mewujudkan impian mendiang istrinya, seorang adik yang khawatir kakaknya takkan bisa tersenyum lagi setelah kepergiannya, dan seorang pemuda yang tak mampu mengungkapkan cinta terpendam kepada sahabatnya. Mungkin perjalanan mereka hanya akan menyisakan kenangan. Namun, kehangatannya akan membekas dan barangkali, pada akhir', 'assets/photos/cover-06.jpg', 2500, 500, 4.5),
(7, 'Keajaiban Toko Kelontong Namiya', 5, 1, '978-602-0007-59-7', 'Penerbit Mitra Italic', 2015, 'Indonesia', 387, 'Ketika tiga pemuda berandal bersembunyi di toko kelontong tak berpenghuni setelah melakukan pencurian, sepucuk surat misterius mendadak diselipkan ke dalam toko melalui lubang surat. Surat yang berisi permintaan saran. Sungguh aneh. Namun, surat aneh itu ternyata membawa mereka dalam petualangan melintasi waktu, menggantikan peran kakek pemilik toko kelontong yang menghabiskan tahun-tahun terakhirnya memberikan nasihat tulus kepada orang-orang yang meminta bantuan. Hanya untuk satu malam. Dan saat fajar menjelang, hidup ketiga sahabat itu tidak akan pernah sama lagi.', 'assets/photos/cover-07.jpg', 3000, 600, 4.6),
(8, 'Pulang (2023)', 3, 1, '978-602-0008-66-8', 'Penerbit Mitra Italic', 2019, 'Indonesia', 267, 'Kisah dua generasi—Dimas Suryo dan putrinya, Lintang Utara—yang bersama-sama menetap di Paris, Prancis. Seperti ribuan warga Indonesia lain yang terjebak di berbagai negara dengan status stateless, keluarga Dimas Suryo tak pernah bisa pulang ke Indonesia karena paspor mereka dicabut dan kehidupan mereka terancam. Pada tahun 1998, Lintang Utara akhirnya berhasil menyentuh tanah air. Dia datang untuk mereka pengalaman keluarga korban Tragedi 1965 sebagai tugas akhir kuliahnya. Apa yang terkuak oleh Lintang bukan sekadar masa lalu ayahnya, tetapi juga bagaimana sejarah paling berdarah di Indonesia berkaitan dengan Dimas Suryo dan kawan-kawannya.', 'assets/photos/cover-08.jpg', 3000, 600, 4.7),
(9, 'Di Tanah Lada', 6, 1, '978-602-0009-73-9', 'Penerbit Mitra Italic', 2020, 'Indonesia', 375, 'Berkisah tentang seorang anak bernama Salva. Panggilannya Ava. Namun papanya memanggil dia Saliva atau ludah karena menganggapnya tidak berguna. Ava sekeluarga pindah ke Rusun Nero setelah Kakek Kia meninggal. Kakek Kia, ayahnya Papa, pernah memberi Ava kamus sebagai hadiah ulang tahun yang ketiga. Sejak itu Ava menjadi anak yang pintar berbahasa Indonesia. Sayangnya, kebanyakan orang dewasa lebih menganggap penting anak yang pintar berbahasa Inggris. Setelah pindah ke Rusun Nero, Ava bertemu dengan anak laki-laki bernama P. Dari pertemuan itulah, petualangan Ava dan P bermula hingga sampai pada akhir yang mengejutkan.', 'assets/photos/cover-09.jpg', 2500, 500, 4.8),
(10, 'Semua Ikan di Langit', 6, 1, '978-602-0010-80-0', 'Penerbit Mitra Italic', 2016, 'Indonesia', 277, 'Perjalanan dimulai dengan menyelamatkan seekor kucing malang bernama Bastet. Ia lalu diturunkan di padang pasir luas agar ia bisa bergerak bebas. Kemudian di sebuah penjara luar angkasa Nad ditemukan beserta anak-anaknya. Nad diselamatkan dan menjadi teman perjalanan Saya dengan Beliau.', 'assets/photos/cover-10.jpg', 2500, 500, 4.0),
(11, 'Namaku Alam', 3, 1, '978-602-0011-87-1', 'Penerbit Mitra Italic', 2019, 'Indonesia', 334, 'Inilah yang kubayangkan detik-detik terakhir Bapak: 18 Mei 1970. Hari gelap. Langit berwarna hitam dengan garis ungu. Bulan bersembunyi di balik ranting pohon randu. Sekumpulan burung nasar bertengger di pagar kawat. Mereka mencium aroma manusia yang nyaris jadi mayat bercampur bau mesiu. Terdengar lolongan anjing berkepanjangan. Empat orang berbaris rapi, masing-masing berdiri dengan senapan yang diarahkan kepada Bapak. Hanya satu senapan berisi peluru mematikan. Selebihnya, peluru karet. Tak satu pun di antara keempat lelaki itu tahu siapa yang kelak menghentikan hidup Bapak. Pada usianya yang ke-33 tahun, Segara Alam menjenguk kembali masa kecilnya hingga dewasa. Semua peristiwa tertanam dengan kuat. Karena memiliki photographic memory, Alam ingat pertama kali dia ditodong senapan oleh seorang lelaki dewasa ketika masih berusia tiga tahun; pertama kali sepupunya mencercanya sebagai an', 'assets/photos/cover-11.jpg', 3000, 600, 4.1),
(12, 'Like We Just Met', 7, 2, '978-602-0012-94-2', 'Penerbit Mitra Italic', 2016, 'Indonesia', 317, 'Aluna Nuansa Senja tidak pernah menduga bahwa sebuah buku yang tertinggal di kedai kopi kecil di Inggris mengubah hidupnya. Di tengah kesibukannya mengejar gelar S2 di jurusan Creative Writing, pertemuannya dengan Makaio Adhyaksa, barista yang juga merupakan musisi, perlahan mengguncang prinsipnya untuk tidak jatuh cinta lagi. Ajaibnya, Makaio selalu hadir di setiap sudut Inggris yang Aluna jelajahi. Berbagai kebetulan membawa keduanya pada perjalanan tak terduga. Di tengah hiruk pikuk kota-kota di Inggris, buku-buku klasik, matahari sedingin es, dan langit dengan cahaya ungu dan merah jambu yang menari-nari, Aluna dan Makaio belajar bahwa mungkin cinta adalah tentang menemukan jalan pulang. Akankah Makaio menjadi bagian dari masa depannya, atau hanya akan menjadi kenangan di perjalanan singkat Aluna di Inggris?', 'assets/photos/cover-12.jpg', 3000, 600, 4.2),
(13, 'Rindu', 2, 2, '978-602-0013-11-3', 'Penerbit Mitra Italic', 2019, 'Indonesia', 321, 'Apalah arti memiliki? Ketika diri kami sendiri bukanlah milik kami. Apalah arti kehilangan, Ketika kami sebenarnya menemukan banyak saat kehilangan, dan sebaliknya, kehilangan banyak pula saat menemukan? Apalah arti cinta, Ketika kami menangis terluka atas perasaan yang seharusnya indah? Bagaimana mungkin, kami terduduk patah hati atas sesuatu yang seharusnya suci dan tidak menuntut apa pun? Wahai, bukankah banyak kerinduan saat kami hendak melupakan? Dan tidak terbilang keinginan melupakan saat kami dalam rindu? Hingga rindu dan melupkan jaraknya setipis benang saja.” Ini adalah kisah tentang masa lalu yang memilukan. Tentang kebencian kepada seseorang yang seharusnya disayangi. Tentang kehilangan kekasih hati. Tentang cinta sejati. Tentang kemunafikan. Lima kisah dalam sebuah perjalanan panjang kerinduan.', 'assets/photos/cover-13.jpg', 3000, 600, 4.3),
(14, 'Tentang Kamu', 2, 2, '978-602-0014-18-4', 'Penerbit Mitra Italic', 2018, 'Indonesia', 327, 'Terima kasih untuk kesempatan mengenalmu, itu adalah salah satu anugerah terbesar hidupku. Cinta memang tidak perlu ditemukan, cintalah yang akan menemukan kita. Terima kasih. Nasihat lama itu benar sekali, aku tidak akan menangis karena sesuatu telah berakhir, tapi aku akan tersenyum karena sesuatu itu pernah terjadi. Masa lalu. Rasa sakit. Masa depan. Mimpi-mimpi. Semua akan berlalu, seperti sungai yang mengalir. Maka biarlah hidupku mengalir seperti sungai kehidupan.', 'assets/photos/cover-14.jpg', 3000, 600, 4.4),
(15, 'The Poppy War', 8, 1, '978-602-0015-25-5', 'Penerbit Mitra Italic', 2018, 'Inggris', 191, 'When Rin aced the Keju—the Empire-wide test to find the most talented youth to learn at the Academies—it was a shock to everyone: to the test officials, who couldn''t believe a war orphan from Rooster Province could pass without cheating; to Rin''s guardians, who believed they''d finally be able to marry her off and further their criminal enterprise; and to Rin herself, who realized she was finally free of the servitude and despair that had made up her daily existence. That she got into Sinegard—the most elite military school in Nikan—was even more surprising. But surprises aren''t always good. Because being a dark-skinned peasant girl from the south is not an easy thing at Sinegard. Targeted from the outset by rival classmates for her color, poverty, and gender, Rin discovers she possesses a lethal, unearthly power—an aptitude for the nearly-mythical art of shamanism. Exploring the depths o', 'assets/photos/cover-15.jpg', 5000, 1000, 4.5),
(16, 'The Dragon Republic', 8, 1, '978-602-0016-32-6', 'Penerbit Mitra Italic', 2018, 'Inggris', 398, 'Rin''s story continues in this acclaimed sequel to The Poppy War—an epic fantasy combining the history of twentieth-century China with a gripping world of gods and monsters. The war is over. The war has just begun. Three times throughout its history, Nikan has fought for its survival in the bloody Poppy Wars. Though the third battle has just ended, shaman and warrior Rin cannot forget the atrocity she committed to save her people. Now she is on the run from her guilt, the opium addiction that holds her like a vice, and the murderous commands of the fiery Phoenix—the vengeful god who has blessed Rin with her fearsome power. Though she does not want to live, she refuses to die until she avenges the traitorous Empress who betrayed Rin''s homeland to its enemies. Her only hope is to join forces with the powerful Dragon Warlord, who plots to conquer Nikan, unseat the Empress, and create a new r', 'assets/photos/cover-16.jpg', 5000, 1000, 4.5),
(17, 'The Burning God', 8, 1, '978-602-0017-39-7', 'Penerbit Mitra Italic', 2022, 'Inggris', 251, 'After saving her nation of Nikan from foreign invaders and battling the evil Empress Su Daji in a brutal civil war, Fang Runin was betrayed by allies and left for dead. Despite her losses, Rin hasn''t given up on those for whom she has sacrificed so much—the people of the southern provinces and especially Tikany, the village that is her home. Returning to her roots, Rin meets difficult challenges—and unexpected opportunities. While her new allies in the Southern Coalition leadership are sly and untrustworthy, Rin quickly realizes that the real power in Nikan lies with the millions of common people who thirst for vengeance and revere her as a goddess of salvation. Backed by the masses and her Southern Army, Rin will use every weapon to defeat the Dragon Republic, the colonizing Hesperians, and all who threaten the shamanic arts and their practitioners. As her power and influence grows, tho', 'assets/photos/cover-17.jpg', 5000, 1000, 4.6),
(18, 'As Long as the Lemon Trees Grow', 9, 2, '978-602-0018-46-8', 'Penerbit Mitra Italic', 2020, 'Inggris', 221, 'Akibat perang Suriah, Salama kehilangan orangtua, dan Hamza, kakaknya ditawan, entah masih hidup atau sudah mati. Kini, Salama harus menjaga Layla, kakak iparnya yang sedang hamil. Hamza berpesan agar Salama menjaga Layla dan calon bayinya. Satu-satunya cara menjaga Layla dari perang adalah mengungsi ke Eropa, tapi setiap kali melihat para korban perang, rasa bersalah menikam Salama. Haruskah dia pergi dari Suriah, ketika bangsanya bergelimpangan akibat perang? Keputusasaan dan ketakutan bercampur hingga mewujud dalam sosok pria bermata dingin, Khaft, yang mendorong Salama untuk pergi. Namun, Salama bertemu Kenan, pemuda bermata hijau dengan semangat membara membela negara. Kini, Salama harus memilih antara tetap tinggal untuk negaranya, ataukah pergi demi memenuhi janjinya kepada Hamza.', 'assets/photos/cover-18.jpg', 4500, 900, 4.7),
(19, 'One True Loves', 10, 2, '978-602-0019-53-9', 'Penerbit Mitra Italic', 2019, 'Inggris', 351, 'In her twenties, Emma Blair marries her high school sweetheart, Jesse. They build a life for themselves, far away from the expectations of their parents and the people of their hometown in Massachusetts. They travel the world together, living life to the fullest and seizing every opportunity for adventure.', 'assets/photos/cover-19.jpg', 2500, 500, 4.8),
(20, 'The Dating Game', 11, 2, '978-602-0020-60-0', 'Penerbit Mitra Italic', 2017, 'Indonesia', 335, 'Kemal Arsjad Attraction is important. But chemistry is more important. Ketika menemukan keduanya, I consider myself lucky. Tetapi itu lima tahun lalu, saat gue bertemu Emma pertama kalinya. Saat ini, tatapan Emma seperti membunuh gue dengan seribu pisau tak terlihat, membuat gue merasa jauh dari kata beruntung. Emma Sjarief From the scale one to hot, Kemal Arsjad is scorching. Membara. Dia seperti book boyfriend yang hanya eksis di novel. Seperti para hero di romancelandia. Tetapi itu lima tahun lalu. Sampai dia mengatakan sesuatu yang membuat harga diriku tergores, membuatku bersumpah, no matter how good he looks, nggak ada yang bisa membuatku memaafkannya. Kemudian dia muncul kembali dengan senyumnya yang charming, ekspresinya yang playful, dan hot summer body yang membuat musim panas di Eropa Selatan terasa semakin gerah. Mengingat kali ini interaksi kami nggak bisa dihindari, seperti', 'assets/photos/cover-20.jpg', 2500, 500, 4.0),
(21, 'Devotion of Suspect X', 5, 1, '978-602-0021-67-1', 'Penerbit Mitra Italic', 2022, 'Indonesia', 221, 'Ketika si mantan suami muncul lagi untuk memeras Yasuko Hanaoka dan putrinya, keadaan menjadi tak terkendali, hingga si mantan suami terbujur kaku di lantai apartemen. Yasuko berniat menghubungi polisi, tetapi mengurungkan niatnya ketika Ishigami, tetangganya, menawarkan bantuan untuk menyembunyikan mayat itu. Saat mayat tersebut ditemukan, penyidikan Detektif Kusanagi mengarah kepada Yasuko. Namun sekuat apa pun insting detektifnya, alibi wanita itu sulit sekali dipatahkan. Kusanagi berkonsultasi dengan sahabatnya, Dr. Manabu Yukawa sang Profesor Galileo, yang ternyata teman kuliah Ishigami. Diselingi nostalgia masa-masa kuliah, Yukawa sang pakar fisika beradu kecerdasan dengan Ishigami, sang genius matematika. Ishigami berjuang melindungi Yasuko dengan berusaha mengakali dan memperdaya Yukawa, yang baru kali ini menemukan lawan paling cerdas dan bertekad baja.', 'assets/photos/cover-21.jpg', 2500, 500, 4.1),
(22, 'Second Sisters', 12, 1, '978-602-0022-74-2', 'Penerbit Mitra Italic', 2023, 'Indonesia', 416, 'Siu-Man melompat dari jendela di lantai dua puluh dan tewas seketika. Nga-Yee, kakak perempuan yang selama ini membesarkannya, menolak percaya bahwa adiknya bunuh diri. Nga-Yee meminta bantuan seorang peretas, yang hanya dikenal sebagai N, untuk menyelidiki kasus kematian adiknya. Penyelidikan amatir mereka berlanjut seperti permainan kucing dan tikus ke seluruh penjuru kota Hong Kong serta dunia digital bawah tanah mereka, terutama di platform-platform gosip daring, tempat seseorang menjatuhkan nama baik Siu-Man tanpa ampun. Berawal dari kasus pelecehan seksual di transportasi umum, yang berlanjut menjadi kasus perundungan di dunia daring ternyata membuat Siu-Man menyerah. Tetapi, benarkah itu penyebab Siu-Man memutuskan untuk bunuh diri? Di era sekarang, dengan percakapan daring dan luring yang terus berlangsung, terkadang manusia melupakan bahwa yang mereka libatkan adalah manusia yan', 'assets/photos/cover-22.jpg', 3000, 600, 4.2),
(23, 'Pasta Kacang Merah', 13, 2, '978-602-0023-81-3', 'Penerbit Mitra Italic', 2015, 'Indonesia', 395, 'Sentaro gagal menjalani kehidupan. Ia memiliki catatan kriminal. sulit meninggalkan kebiasaan minum alkohol. dan impiannya menjadi penulis semakin lama semakin pudar. Ia menghabiskan hari-hari monoton di sebuah kedai dorayaki yang berada di bawah pohon sakura yang berubah seiring perubahan musim. Namun suatu ketika segalanya mulai berubah. Seorang wanita tua bernama Tokue. dengan jemari yang aneh bentuknya. datang ke kehidupan Sentaro. Dengan metode pengajaran yang sama anehnya. Tokue mewariskan pengalaman lima puluh tahunnya membuat pasta kacang merah kepada Sentaro. Namun seiring persahabatan di antara keduanya mulai terjalin. Tekanan dari masyarakat terhadap kondisi Tokue mulai mengungkap rahasia gelap yang wanita itu simpan rapat-rapat. Rahasia itu kemudian menuntut harga yang sangat mahal. Pasta Kacang Merah adalah sebuah cerita yang mengharmonisasikan kudapan manis dengan persahaba', 'assets/photos/cover-23.jpg', 2000, 400, 4.3),
(24, 'A Love Like This', 14, 2, '978-602-0024-88-4', 'Penerbit Mitra Italic', 2020, 'Indonesia', 386, 'Huang Lei dan Selena Fortier bertemu kembali di dapur pastry The Capital Beijing. Mereka bertemu setelah lima tahun berlalu tanpa bertukar kabar. Dua sahabat ini dulu sangat dekat dan selalu bersama menyusuri hutong yang membingkai Kota Terlarang. Namun kini mereka menjadi canggung, asing, dan berjarak. Hanya waktu yang akhirnya berhasil mencairkan kecanggungan mereka berdua. Lei sejak lama masih menyimpan hati pada Selena mulai mendamba kembali. Namun sebelum bisa sepenuhnya memiliki Selena. Lei harus menghadapi lawan yang hadir, yaitu “hantu” dari rumah tangga Selena dan dirinya sendiri yang terlalu sering terlambat melangkah. Kisah cinta yang disangka sederhana pun menjadi rumit karena terjebak dalam labirin hati yang berliku.', 'assets/photos/cover-24.jpg', 2000, 400, 4.4);

-- Seed: eksemplar (physical copies per title)
INSERT INTO eksemplar (id_eksemplar, id_buku, kode_eksemplar, kondisi, lokasi_rak, status) VALUES
(1, 1, 'ITL-EX-001-01', 'Sangat Baik', 'R2-B', 'tersedia'),
(2, 1, 'ITL-EX-001-02', 'Sangat Baik', 'R2-B', 'tersedia'),
(3, 1, 'ITL-EX-001-03', 'Sangat Baik', 'R2-B', 'tersedia'),
(4, 1, 'ITL-EX-001-04', 'Sangat Baik', 'R2-B', 'tersedia'),
(5, 2, 'ITL-EX-002-01', 'Baik', 'R3-C', 'tersedia'),
(6, 3, 'ITL-EX-003-01', 'Sangat Baik', 'R4-D', 'tersedia'),
(7, 3, 'ITL-EX-003-02', 'Sangat Baik', 'R4-D', 'tersedia'),
(8, 3, 'ITL-EX-003-03', 'Sangat Baik', 'R4-D', 'tersedia'),
(9, 3, 'ITL-EX-003-04', 'Sangat Baik', 'R4-D', 'tersedia'),
(10, 4, 'ITL-EX-004-01', 'Sangat Baik', 'R5-A', 'tersedia'),
(11, 5, 'ITL-EX-005-01', 'Sangat Baik', 'R6-B', 'tersedia'),
(12, 6, 'ITL-EX-006-01', 'Cukup Baik', 'R7-C', 'tersedia'),
(13, 6, 'ITL-EX-006-02', 'Cukup Baik', 'R7-C', 'tersedia'),
(14, 6, 'ITL-EX-006-03', 'Cukup Baik', 'R7-C', 'tersedia'),
(15, 6, 'ITL-EX-006-04', 'Cukup Baik', 'R7-C', 'tersedia'),
(16, 7, 'ITL-EX-007-01', 'Baik', 'R8-D', 'tersedia'),
(17, 7, 'ITL-EX-007-02', 'Baik', 'R8-D', 'tersedia'),
(18, 7, 'ITL-EX-007-03', 'Baik', 'R8-D', 'tersedia'),
(19, 8, 'ITL-EX-008-01', 'Cukup Baik', 'R1-A', 'tersedia'),
(20, 9, 'ITL-EX-009-01', 'Baik', 'R2-B', 'tersedia'),
(21, 10, 'ITL-EX-010-01', 'Sangat Baik', 'R3-C', 'tersedia'),
(22, 11, 'ITL-EX-011-01', 'Baik', 'R4-D', 'tersedia'),
(23, 11, 'ITL-EX-011-02', 'Baik', 'R4-D', 'tersedia'),
(24, 12, 'ITL-EX-012-01', 'Cukup Baik', 'R5-A', 'tersedia'),
(25, 13, 'ITL-EX-013-01', 'Sangat Baik', 'R6-B', 'tersedia'),
(26, 13, 'ITL-EX-013-02', 'Sangat Baik', 'R6-B', 'tersedia'),
(27, 14, 'ITL-EX-014-01', 'Baik', 'R7-C', 'tersedia'),
(28, 14, 'ITL-EX-014-02', 'Baik', 'R7-C', 'tersedia'),
(29, 14, 'ITL-EX-014-03', 'Baik', 'R7-C', 'tersedia'),
(30, 14, 'ITL-EX-014-04', 'Baik', 'R7-C', 'tersedia'),
(31, 15, 'ITL-EX-015-01', 'Sangat Baik', 'R8-D', 'tersedia'),
(32, 15, 'ITL-EX-015-02', 'Sangat Baik', 'R8-D', 'tersedia'),
(33, 15, 'ITL-EX-015-03', 'Sangat Baik', 'R8-D', 'tersedia'),
(34, 15, 'ITL-EX-015-04', 'Sangat Baik', 'R8-D', 'tersedia'),
(35, 16, 'ITL-EX-016-01', 'Sangat Baik', 'R1-A', 'tersedia'),
(36, 16, 'ITL-EX-016-02', 'Sangat Baik', 'R1-A', 'tersedia'),
(37, 17, 'ITL-EX-017-01', 'Cukup Baik', 'R2-B', 'tersedia'),
(38, 18, 'ITL-EX-018-01', 'Baik', 'R3-C', 'tersedia'),
(39, 18, 'ITL-EX-018-02', 'Baik', 'R3-C', 'tersedia'),
(40, 18, 'ITL-EX-018-03', 'Baik', 'R3-C', 'tersedia'),
(41, 18, 'ITL-EX-018-04', 'Baik', 'R3-C', 'tersedia'),
(42, 19, 'ITL-EX-019-01', 'Baik', 'R4-D', 'tersedia'),
(43, 19, 'ITL-EX-019-02', 'Baik', 'R4-D', 'tersedia'),
(44, 20, 'ITL-EX-020-01', 'Sangat Baik', 'R5-A', 'tersedia'),
(45, 20, 'ITL-EX-020-02', 'Sangat Baik', 'R5-A', 'tersedia'),
(46, 20, 'ITL-EX-020-03', 'Sangat Baik', 'R5-A', 'tersedia'),
(47, 20, 'ITL-EX-020-04', 'Sangat Baik', 'R5-A', 'tersedia'),
(48, 21, 'ITL-EX-021-01', 'Baik', 'R6-B', 'tersedia'),
(49, 21, 'ITL-EX-021-02', 'Baik', 'R6-B', 'tersedia'),
(50, 21, 'ITL-EX-021-03', 'Baik', 'R6-B', 'tersedia'),
(51, 22, 'ITL-EX-022-01', 'Baik', 'R7-C', 'tersedia'),
(52, 22, 'ITL-EX-022-02', 'Baik', 'R7-C', 'tersedia'),
(53, 23, 'ITL-EX-023-01', 'Baik', 'R8-D', 'tersedia'),
(54, 24, 'ITL-EX-024-01', 'Sangat Baik', 'R1-A', 'tersedia');
-- =====================================================================
-- Seed: data transaksi contoh
-- Menunjukkan siklus penuh satu peminjaman: anggota -> tiket ->
-- detail item -> pembayaran -> log pemindaian QR -> ulasan.
-- Berguna sebagai data uji saat menghubungkan front-end ke backend.
-- =====================================================================
USE italic_db;

-- ---------------------------------------------------------------------
-- Anggota contoh
-- ---------------------------------------------------------------------
INSERT INTO anggota (id_anggota, kode_anggota, nama_lengkap, email, no_hp, no_ktp, alamat, kata_sandi_hash, tipe_keanggotaan) VALUES
(1, 'ITL-MBR-000001', 'Raka Aditya Wijaya', 'raka.aditya@email.com', '081234567890', '3171012345670001',
 'Jl. Melati No. 12, RT 03/RW 05, Kemang, Jakarta Selatan', '$2y$10$examplehash00000000000000000000memberraka', 'reguler'),
(2, 'ITL-MBR-000002', 'Nadia Kusuma Putri', 'nadia.kusuma@email.com', '081298765432', '3171012345670002',
 'Jl. Anggrek No. 8, Cipete, Jakarta Selatan', '$2y$10$examplehash00000000000000000000membernadia', 'premium'),
(3, 'ITL-MBR-000003', 'Bimo Satrio Nugraha', 'bimo.satrio@email.com', '081211122233', '3171012345670003',
 'Jl. Kenanga No. 21, Pondok Indah, Jakarta Selatan', '$2y$10$examplehash00000000000000000000memberbimo', 'reguler');

-- ---------------------------------------------------------------------
-- Peminjaman contoh #1 — sudah selesai (siklus penuh)
-- Buku: The Midnight Library (id_buku 1) -> eksemplar pertama (id 1)
-- ---------------------------------------------------------------------
INSERT INTO peminjaman (
  id_peminjaman, kode_peminjaman, id_anggota, id_staf_pemroses, id_staf_penerima,
  tanggal_pinjam, tanggal_jatuh_tempo, tanggal_kembali_aktual,
  metode_pengambilan, alamat_pengantaran, biaya_antar,
  subtotal_sewa, total_denda, total_biaya, status, catatan, qr_code_data
) VALUES (
  1, 'ITL-20260601-1001', 1, 2, 2,
  '2026-06-01', '2026-06-08', '2026-06-08',
  'ambil_di_toko', NULL, 0.00,
  28000.00, 0.00, 28000.00, 'selesai', 'Dikembalikan tepat waktu, kondisi baik.',
  '{"kode":"ITL-20260601-1001","peminjam":"Raka Aditya Wijaya","email":"raka.aditya@email.com","buku":"The Midnight Library — Matt Haig","tanggal_pinjam":"2026-06-01","tanggal_kembali":"2026-06-08","metode":"ambil_di_toko","total":28000,"status":"selesai"}'
);

INSERT INTO detail_peminjaman (
  id_detail, id_peminjaman, id_eksemplar, harga_sewa_per_hari, jumlah_hari, subtotal,
  kondisi_saat_pinjam, kondisi_saat_kembali, status_item
) VALUES (
  1, 1, 1, 4000.00, 7, 28000.00, 'Sangat Baik', 'Sangat Baik', 'dikembalikan'
);

INSERT INTO pembayaran (id_pembayaran, id_peminjaman, jumlah_bayar, metode_pembayaran, status_pembayaran, referensi_bayar) VALUES
(1, 1, 28000.00, 'qris', 'lunas', 'QRIS-TRX-88231001');

INSERT INTO qr_scan_log (id_log, id_peminjaman, kode_qr, tipe_pemindaian, hasil_pemindaian, id_staf_pemindai, lokasi_pemindaian) VALUES
(1, 1, 'ITL-20260601-1001', 'pengambilan', 'valid', 2, 'Gerai Kemang — Meja Kasir'),
(2, 1, 'ITL-20260601-1001', 'pengembalian', 'valid', 2, 'Gerai Kemang — Meja Kasir');

INSERT INTO ulasan (id_ulasan, id_anggota, id_buku, id_peminjaman, rating, komentar) VALUES
(1, 1, 1, 1, 5, 'QR-nya bikin proses ambil buku secepat memesan kopi. Tidak ada antre panjang, tinggal scan.');

-- ---------------------------------------------------------------------
-- Peminjaman contoh #2 — sedang aktif berjalan
-- Buku: Rindu (id_buku 13) -> eksemplar pertama miliknya
-- ---------------------------------------------------------------------
INSERT INTO peminjaman (
  id_peminjaman, kode_peminjaman, id_anggota, id_staf_pemroses, id_staf_penerima,
  tanggal_pinjam, tanggal_jatuh_tempo, tanggal_kembali_aktual,
  metode_pengambilan, alamat_pengantaran, biaya_antar,
  subtotal_sewa, total_denda, total_biaya, status, catatan, qr_code_data
) VALUES (
  2, 'ITL-20260628-2044', 2, 4, NULL,
  '2026-06-28', '2026-07-05', NULL,
  'diantar', 'Jl. Anggrek No. 8, Cipete, Jakarta Selatan', 5000.00,
  21000.00, 0.00, 26000.00, 'aktif', 'Diantar oleh kurir Reza, estimasi tiba H+1.',
  '{"kode":"ITL-20260628-2044","peminjam":"Nadia Kusuma Putri","email":"nadia.kusuma@email.com","buku":"Rindu — Tere Liye","tanggal_pinjam":"2026-06-28","tanggal_kembali":"2026-07-05","metode":"diantar","total":26000,"status":"aktif"}'
);

INSERT INTO detail_peminjaman (
  id_detail, id_peminjaman, id_eksemplar, harga_sewa_per_hari, jumlah_hari, subtotal,
  kondisi_saat_pinjam, kondisi_saat_kembali, status_item
)
SELECT 2, 2, e.id_eksemplar, 3000.00, 7, 21000.00, 'Baik', NULL, 'dipinjam'
FROM eksemplar e WHERE e.id_buku = 13 LIMIT 1;

INSERT INTO pembayaran (id_pembayaran, id_peminjaman, jumlah_bayar, metode_pembayaran, status_pembayaran, referensi_bayar) VALUES
(2, 2, 26000.00, 'transfer_bank', 'lunas', 'BANKTRX-99441207');

INSERT INTO qr_scan_log (id_log, id_peminjaman, kode_qr, tipe_pemindaian, hasil_pemindaian, id_staf_pemindai, lokasi_pemindaian) VALUES
(3, 2, 'ITL-20260628-2044', 'pengambilan', 'valid', 4, 'Rute Antar — Cipete');

-- ---------------------------------------------------------------------
-- Wishlist & notifikasi contoh
-- ---------------------------------------------------------------------
INSERT INTO wishlist (id_wishlist, id_anggota, id_buku) VALUES
(1, 1, 15), (2, 1, 16), (3, 3, 7);

INSERT INTO notifikasi (id_notifikasi, id_anggota, id_peminjaman, tipe, pesan, status_baca) VALUES
(1, 2, 2, 'pengingat_jatuh_tempo', 'Buku "Rindu" jatuh tempo pada 5 Juli 2026. Perpanjang lewat halaman Riwayat jika masih dibaca.', 'belum_dibaca'),
(2, 1, 1, 'info_akun', 'Terima kasih sudah mengembalikan "The Midnight Library" tepat waktu!', 'dibaca');

-- Sinkronkan status eksemplar yang sedang dipinjam pada contoh #2
UPDATE eksemplar e
JOIN detail_peminjaman d ON d.id_eksemplar = e.id_eksemplar
SET e.status = 'dipinjam'
WHERE d.id_peminjaman = 2;
-- =====================================================================
-- Views & Triggers pendukung operasional Italic
-- =====================================================================
USE italic_db;

-- ---------------------------------------------------------------------
-- VIEW: peminjaman_aktif
-- Ringkasan seluruh tiket yang belum selesai, siap dipakai untuk
-- dashboard staf gerai (siapa yang harus mengembalikan, kapan).
-- ---------------------------------------------------------------------
DROP VIEW IF EXISTS v_peminjaman_aktif;
CREATE VIEW v_peminjaman_aktif AS
SELECT
  p.id_peminjaman,
  p.kode_peminjaman,
  a.nama_lengkap   AS nama_peminjam,
  a.no_hp,
  b.judul          AS judul_buku,
  p.tanggal_pinjam,
  p.tanggal_jatuh_tempo,
  DATEDIFF(p.tanggal_jatuh_tempo, CURRENT_DATE()) AS sisa_hari,
  p.status,
  p.total_biaya
FROM peminjaman p
JOIN anggota a           ON a.id_anggota = p.id_anggota
JOIN detail_peminjaman d ON d.id_peminjaman = p.id_peminjaman
JOIN eksemplar e         ON e.id_eksemplar = d.id_eksemplar
JOIN buku b               ON b.id_buku = e.id_buku
WHERE p.status IN ('diproses', 'aktif', 'terlambat');

-- ---------------------------------------------------------------------
-- VIEW: katalog_ketersediaan
-- Jumlah eksemplar tersedia vs total per judul — dipakai katalog.
-- ---------------------------------------------------------------------
DROP VIEW IF EXISTS v_katalog_ketersediaan;
CREATE VIEW v_katalog_ketersediaan AS
SELECT
  bk.id_buku,
  bk.judul,
  pn.nama_penulis,
  kt.nama_kategori,
  bk.harga_sewa_per_hari,
  COUNT(ek.id_eksemplar)                                        AS total_eksemplar,
  SUM(CASE WHEN ek.status = 'tersedia' THEN 1 ELSE 0 END)       AS eksemplar_tersedia
FROM buku bk
JOIN penulis pn  ON pn.id_penulis = bk.id_penulis
JOIN kategori kt ON kt.id_kategori = bk.id_kategori
LEFT JOIN eksemplar ek ON ek.id_buku = bk.id_buku
GROUP BY bk.id_buku, bk.judul, pn.nama_penulis, kt.nama_kategori, bk.harga_sewa_per_hari;

-- ---------------------------------------------------------------------
-- VIEW: laporan_pendapatan_harian
-- Rekap pendapatan (sewa + denda) per hari untuk laporan kasir.
-- ---------------------------------------------------------------------
DROP VIEW IF EXISTS v_laporan_pendapatan_harian;
CREATE VIEW v_laporan_pendapatan_harian AS
SELECT
  DATE(py.tanggal_bayar)              AS tanggal,
  COUNT(DISTINCT py.id_peminjaman)    AS jumlah_transaksi,
  SUM(py.jumlah_bayar)                AS total_pendapatan
FROM pembayaran py
WHERE py.status_pembayaran = 'lunas'
GROUP BY DATE(py.tanggal_bayar)
ORDER BY tanggal DESC;

-- ---------------------------------------------------------------------
-- TRIGGER: set eksemplar jadi 'dipinjam' saat item peminjaman dibuat
-- ---------------------------------------------------------------------
DROP TRIGGER IF EXISTS trg_detail_after_insert;
DELIMITER //
CREATE TRIGGER trg_detail_after_insert
AFTER INSERT ON detail_peminjaman
FOR EACH ROW
BEGIN
  UPDATE eksemplar
  SET status = 'dipinjam'
  WHERE id_eksemplar = NEW.id_eksemplar;
END //
DELIMITER ;

-- ---------------------------------------------------------------------
-- TRIGGER: kembalikan eksemplar ke 'tersedia' saat item ditandai
-- dikembalikan dalam kondisi baik (rusak/hilang tidak dikembalikan
-- ke peredaran secara otomatis — perlu tinjauan staf kurator).
-- ---------------------------------------------------------------------
DROP TRIGGER IF EXISTS trg_detail_after_update_kembali;
DELIMITER //
CREATE TRIGGER trg_detail_after_update_kembali
AFTER UPDATE ON detail_peminjaman
FOR EACH ROW
BEGIN
  IF NEW.status_item = 'dikembalikan' AND OLD.status_item <> 'dikembalikan' THEN
    UPDATE eksemplar
    SET status = 'tersedia'
    WHERE id_eksemplar = NEW.id_eksemplar;
  ELSEIF NEW.status_item IN ('rusak') AND OLD.status_item <> NEW.status_item THEN
    UPDATE eksemplar SET status = 'perbaikan' WHERE id_eksemplar = NEW.id_eksemplar;
  ELSEIF NEW.status_item = 'hilang' AND OLD.status_item <> 'hilang' THEN
    UPDATE eksemplar SET status = 'hilang' WHERE id_eksemplar = NEW.id_eksemplar;
  END IF;
END //
DELIMITER ;

-- ---------------------------------------------------------------------
-- CATATAN: event terjadwal untuk menandai tiket 'terlambat' otomatis
-- SENGAJA TIDAK disertakan di sini. Banyak hosting (dan MySQL/MariaDB
-- default di XAMPP) mematikan event_scheduler, sehingga statement
-- CREATE EVENT akan menggagalkan seluruh proses import ini.
-- Lihat database/06_optional_event_scheduler.sql jika ingin
-- mengaktifkan fitur tersebut secara terpisah di server yang mendukung.
-- ---------------------------------------------------------------------
