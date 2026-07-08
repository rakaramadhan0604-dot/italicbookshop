/* =====================================================================
   ITALIC — script.js
   Shared behaviour: nav toggle, catalogue rendering, forms, and the
   QR rendering helper used by rent.php / account.php. Book rentals are
   created and stored server-side (see process/rent.php and
   process/loan-action.php); this file no longer touches localStorage
   for booking data.
   ===================================================================== */

(function () {
  "use strict";

  /* ---------------- Nav toggle ---------------- */
  const hamburger = document.getElementById("hamburger-menu");
  const nav = document.getElementById("navbar-nav");
  if (hamburger && nav) {
    hamburger.addEventListener("click", () => {
      const open = nav.classList.toggle("is-open");
      hamburger.setAttribute("aria-expanded", String(open));
    });
    nav.querySelectorAll("a").forEach((a) =>
      a.addEventListener("click", () => {
        nav.classList.remove("is-open");
        hamburger.setAttribute("aria-expanded", "false");
      })
    );
  }

  /* ---------------- Toast helper ---------------- */
  window.italicToast = function (message) {
    let el = document.querySelector(".toast");
    if (!el) {
      el = document.createElement("div");
      el.className = "toast";
      document.body.appendChild(el);
    }
    el.textContent = message;
    requestAnimationFrame(() => el.classList.add("is-visible"));
    clearTimeout(el._t);
    el._t = setTimeout(() => el.classList.remove("is-visible"), 3200);
  };

  /* ---------------- Generic feedback forms (contact, login, signup) ---------------- */
  document.querySelectorAll("form[data-feedback-form]").forEach((form) => {
    form.addEventListener("submit", (e) => {
      e.preventDefault();
      const required = form.querySelectorAll("[required]");
      let valid = true;
      required.forEach((input) => {
        const field = input.closest(".field");
        if (!input.value.trim()) {
          valid = false;
          if (field) field.classList.add("has-error");
        } else if (field) {
          field.classList.remove("has-error");
        }
      });
      const statusEl = form.querySelector(".form-status");
      if (!valid) {
        if (statusEl) {
          statusEl.textContent = "Mohon lengkapi kolom yang wajib diisi.";
          statusEl.className = "form-status err";
        }
        return;
      }
      if (statusEl) {
        statusEl.textContent = form.dataset.successMessage || "Terima kasih, data telah dikirim.";
        statusEl.className = "form-status ok";
      }
      form.reset();
    });
  });

  /* ---------------- Radio pill visual state ---------------- */
  document.querySelectorAll(".radio-pill input[type='radio']").forEach((input) => {
    const sync = () => {
      document.querySelectorAll(`input[name="${input.name}"]`).forEach((i) => {
        i.closest(".radio-pill").classList.toggle("is-checked", i.checked);
      });
    };
    input.addEventListener("change", sync);
    sync();
  });

  /* ---------------- FAQ: only one open at a time (optional nicety) ---------------- */
  document.querySelectorAll(".faq-list").forEach((list) => {
    list.querySelectorAll("details").forEach((d) => {
      d.addEventListener("toggle", () => {
        if (d.open) {
          list.querySelectorAll("details").forEach((o) => {
            if (o !== d) o.open = false;
          });
        }
      });
    });
  });

  /* =====================================================================
     Catalogue rendering — used by index.php (featured) & catalog.php
     ===================================================================== */
  window.formatRupiah = function (n) {
    return "Rp" + Number(n).toLocaleString("id-ID");
  };

  function tileTemplate(book) {
    const outOfStock = book.copies <= 0;
    return `
      <a class="cat-tile" href="book.php?id=${book.id}" aria-label="${book.title} oleh ${book.author}">
        <div class="cover">
          <img src="${book.cover}" alt="Sampul buku ${book.title}" loading="lazy" width="400" height="533" />
          ${outOfStock ? '<span class="badge out">Dipinjam</span>' : `<span class="badge">${book.copies} Tersedia</span>`}
        </div>
        <div class="meta">
          <span class="index-no idx">No. ${String(book.id).padStart(3, "0")} — ${book.genre}</span>
          <div class="title">${book.title}</div>
          <div class="author upright">${book.author}</div>
          <div class="row">
            <span>${book.language} · ${book.pages} hlm.</span>
            <span class="price">${window.formatRupiah(book.pricePerDay)}/hari</span>
          </div>
        </div>
      </a>`;
  }
  window.renderBookTiles = function (books, mountEl) {
    if (!mountEl) return;
    if (!books.length) {
      mountEl.innerHTML = '<div class="empty-state">Tidak ada judul yang cocok dengan pencarianmu. Coba kata kunci atau kategori lain.</div>';
      return;
    }
    mountEl.innerHTML = books.map(tileTemplate).join("");
  };

  /* =====================================================================
     QR rendering helper — used by rent.php (new ticket) and account.php
     (re-showing a saved ticket's QR). Loan records now live in the real
     database (see process/rent.php & process/loan-action.php); this file
     only renders the QR image from a payload string built by the caller.
     ===================================================================== */

  /* Render a QR code into a container using the QRCode.js CDN library
     (loaded on rent.php / account.php only). Falls back to a plain
     text block if the library failed to load (e.g. offline demo). */
  window.renderQrInto = function (containerEl, text) {
    if (!containerEl) return;
    containerEl.innerHTML = "";
    if (window.QRCode) {
      new window.QRCode(containerEl, {
        text,
        width: 128,
        height: 128,
        colorDark: "#15150F",
        colorLight: "#ffffff",
        correctLevel: window.QRCode.CorrectLevel.M,
      });
    } else {
      containerEl.innerHTML = '<div style="width:128px;height:128px;display:flex;align-items:center;justify-content:center;font-size:11px;text-align:center;border:1px dashed #ccc;padding:6px;">QR tidak dapat dimuat (offline)</div>';
    }
  };
})();
