<?php
declare(strict_types=1);
require __DIR__ . '/bootstrap.php';

if (!ulogovan()) {
    header('Location: login.php');
    exit;
}
?><!doctype html>
<html lang="sr-Latn">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf" content="<?= e(csrf_token()) ?>">
<title>Proizvodi — Džinić CMS</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Fraunces:opsz,wght@9..144,700&display=swap" rel="stylesheet">
<style>
*{box-sizing:border-box}
body{margin:0;font-family:Inter,system-ui,sans-serif;color:#1C2A20;background:#F6F4EE}
button,input,select{font:inherit}
button{cursor:pointer}
.shell{min-height:100vh;display:flex;flex-wrap:wrap}

/* Bočna traka */
.side{flex:1 1 240px;max-width:100%;background:#1F4A2A;color:#fff;padding:28px 16px;display:flex;flex-direction:column;gap:32px}
.logo{display:flex;align-items:center;gap:12px;padding:0 8px}
.logo small{display:block;font-size:12px;color:#D6E4D6;margin-bottom:2px}
.logo b{font-family:Fraunces,Georgia,serif;font-size:26px;line-height:1}
.nav{display:flex;flex-direction:column;gap:6px;flex:1}
.nav a{display:flex;align-items:center;gap:10px;min-height:48px;padding:0 14px;border-radius:10px;color:#E4EDE4;text-decoration:none;font-size:16px;font-weight:500}
.nav a.aktivno{background:#3C7A3F;color:#fff}
.nav a.odjava{margin-top:auto}

/* Sadržaj */
.main{flex:999 1 560px;min-width:0;padding:40px 32px}
.sadrzaj{display:flex;flex-direction:column;gap:20px}
h1{margin:0;font-size:30px}
.podnaslov{margin:4px 0 0;font-size:15px;color:#5A6659}

/* Kartice kategorija */
.kategorije{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px;max-width:640px;margin-bottom:6px}
.kat{position:relative;display:flex;flex-direction:column;align-items:flex-start;gap:6px;min-height:150px;padding:16px;border-radius:18px;text-align:left;font-size:15px;background:#fff;color:#1C2A20;border:2px dashed #8DB88A}
.kat.aktivna{background:#2A6B30;color:#fff;border:2px solid #2A6B30;box-shadow:0 8px 18px rgba(31,74,42,.25)}
.kat.aktivna::after{content:"";position:absolute;left:50%;bottom:-9px;width:18px;height:18px;background:#2A6B30;border-radius:3px;transform:translateX(-50%) rotate(45deg)}
.kat-vrh{display:flex;justify-content:space-between;align-items:flex-start;width:100%}
.kat img{width:56px;height:56px;border-radius:28px;object-fit:cover;margin-bottom:8px;border:3px solid #E6E2D6;background:#E8F0E3}
.kat.aktivna img{border-color:#fff}
.kat-ikona{display:inline-flex;align-items:center;justify-content:center;color:#2A6B30}
.kat.aktivna .kat-ikona{width:34px;height:34px;border-radius:17px;background:#fff}
.kat-naziv{font-size:18px;font-weight:700;line-height:1.25}
.kat-broj{color:#5A6659}
.kat.aktivna .kat-broj{color:#E4EDE4}

/* Lista proizvoda */
.lista{background:#fff;border:1px solid #E6E2D6;border-radius:14px;overflow:hidden}
.prow{display:grid;grid-template-columns:minmax(0,2.4fr) minmax(0,1fr) minmax(0,1fr) minmax(0,1fr) 28px;gap:16px;align-items:center;width:100%;padding:12px 20px;border:0;background:transparent;color:#1C2A20;font-size:15px;text-align:left}
.zaglavlje{background:#FAF8F2;color:#4F5B50;font-size:13px;font-weight:600;padding:14px 20px}
.stavka{border-top:1px solid #EEEBE2}
.stavka.otvorena{background:#FBFAF6}
.pinfo{display:flex;align-items:center;gap:14px;min-width:0}
.thumb{width:60px;height:60px;flex:none;border-radius:10px;object-fit:cover;background:#E8F0E3;color:#1F4A2A;font-size:22px;font-weight:700;display:inline-flex;align-items:center;justify-content:center}
.ptekst{display:flex;flex-direction:column;gap:4px;min-width:0}
.pnaziv{font-weight:600;font-size:16px}
.pkat{font-size:13px;color:#5A6659}
.bold{font-weight:600}
.siv{color:#4F5B50}
.mob{display:none}
.strelica{display:inline-flex;justify-content:center;color:#4F5B50;transition:transform .2s}
.otvorena .strelica{transform:rotate(180deg)}
.bedz{display:inline-flex;align-items:center;height:26px;padding:0 10px;border-radius:13px;font-size:13px;font-weight:600;white-space:nowrap}
.bedz.da{background:#E3F1E4;color:#1E6B34}
.bedz.ne{background:#FBE4E2;color:#A12A22}
.prazno{padding:28px 20px;color:#5A6659;text-align:center}

/* Polje za izmenu */
.panel{padding:4px 20px 24px 94px;display:flex;flex-wrap:wrap;align-items:flex-end;gap:16px 24px}
.polje{display:flex;flex-direction:column;gap:8px;font-size:13px;font-weight:600;color:#4F5B50;white-space:nowrap}
.polja{display:flex;gap:16px}
.unos{width:160px;height:44px;padding:0 12px;border:1px solid #CFCAB9;border-radius:10px;background:#fff;color:#1C2A20;font-size:16px}
.seg{display:flex;padding:4px;gap:4px;background:#F1EEE5;border-radius:10px}
.seg button{flex:1 1 0;height:36px;padding:0 14px;border:0;border-radius:8px;font-size:14px;font-weight:600;background:transparent;color:#4F5B50}
.seg button.da[aria-pressed="true"]{background:#1F4A2A;color:#fff}
.seg button.ne[aria-pressed="true"]{background:#A12A22;color:#fff}
.akcije{display:flex;gap:10px;margin-left:auto}
.dugme{height:44px;padding:0 18px;border-radius:10px;font-size:15px;font-weight:600;border:1px solid #CFCAB9;background:#fff;color:#1C2A20}
.dugme.glavno{border:0;background:#1F4A2A;color:#fff}
.dugme:disabled{opacity:.6;cursor:wait}
.brisanje{flex:1 1 100%;padding-top:16px;border-top:1px solid #EEEBE2}
.dugme.crveno{display:inline-flex;align-items:center;gap:8px;border-color:#E8B4AE;color:#A12A22}

/* Dodavanje */
.dodaj-dugme{width:100%;display:inline-flex;align-items:center;justify-content:center;gap:10px;height:56px;border:2px dashed #8DB88A;border-radius:14px;background:#fff;color:#1F4A2A;font-size:16px;font-weight:600}
.forma{background:#fff;border:1px solid #E6E2D6;border-radius:14px;padding:20px;display:flex;flex-direction:column;gap:16px}
.forma h2{margin:0;font-size:18px}
.forma .unos{width:100%}
.tri{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:12px}
.slika-izbor{display:flex;align-items:center;gap:16px;padding:12px;border:2px dashed #8DB88A;border-radius:14px;background:#FBFAF6;cursor:pointer}
.slika-izbor input{position:absolute;width:1px;height:1px;opacity:0;overflow:hidden}
.slika-izbor:focus-within{outline:2px solid #1F4A2A;outline-offset:2px}
.slika-prikaz{width:84px;height:84px;flex:none;border-radius:12px;object-fit:cover;background:#E8F0E3;color:#1F4A2A;display:inline-flex;align-items:center;justify-content:center}
.dva{display:flex;gap:10px}
.dva .dugme{flex:1 1 0;height:48px;font-size:16px}
.razmak{height:50vh}

/* Prozor za potvrdu */
.prozor{position:fixed;inset:0;z-index:50;background:rgba(20,30,22,.55);display:flex;align-items:center;justify-content:center;padding:16px}
.prozor[hidden]{display:none}
.prozor-kutija{width:100%;max-width:400px;background:#fff;border-radius:18px;padding:24px;display:flex;flex-direction:column;gap:16px;box-shadow:0 20px 50px rgba(0,0,0,.25)}
.prozor h2{margin:0;font-size:20px}
.prozor p{margin:6px 0 0;font-size:15px;color:#4F5B50;line-height:1.5}
.ikona-crvena{width:48px;height:48px;border-radius:24px;background:#FBE4E2;color:#A12A22;display:inline-flex;align-items:center;justify-content:center}
.dugme.brisi{border:0;background:#B3261E;color:#fff}

/* Obaveštenje */
.poruka{position:fixed;left:50%;bottom:24px;transform:translateX(-50%);z-index:60;max-width:calc(100% - 32px);padding:12px 18px;border-radius:12px;background:#1C2A20;color:#fff;font-size:15px;box-shadow:0 10px 30px rgba(0,0,0,.25)}
.poruka.greska{background:#A12A22}
.poruka[hidden]{display:none}

/* Telefon */
@media (max-width:760px){
  .side{flex-basis:100%;padding:16px;gap:14px}
  .nav{flex-direction:row}
  .nav a{flex:1 1 0;justify-content:center}
  .nav a.odjava{margin-top:0;flex:0 0 auto}
  .main{padding:24px 16px}
  .zaglavlje,.desk{display:none!important}
  .mob{display:flex;flex-wrap:wrap;align-items:center;gap:6px 10px;font-size:14px;color:#4F5B50}
  .prow{grid-template-columns:minmax(0,1fr) 24px;padding:12px 14px}
  .panel{padding:4px 14px 20px}
  .panel .seg-polje,.panel .polja{flex:1 1 100%}
  .polja .polje{flex:1 1 0;min-width:0}
  .panel .unos{width:100%}
  .akcije{margin-left:0;flex:1 1 100%}
  .akcije .dugme{flex:1 1 0}
}
</style>
</head>
<body>
<div class="shell">
  <aside class="side">
    <div class="logo">
      <svg width="44" height="44" viewBox="0 0 44 44" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M8 22 L22 10 L36 22"/><path d="M12 19 V32 H32 V19"/><path d="M19 32 V25 H25 V32"/><path d="M4 37 C14 33 30 41 40 37" stroke="#9CCB7A"/></svg>
      <div><small>Poljoprivredno gazdinstvo</small><b>Džinić</b></div>
    </div>
    <nav class="nav" aria-label="Glavni meni">
      <a href="index.php" class="aktivno" aria-current="page">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3 L20 7.5 V16.5 L12 21 L4 16.5 V7.5 Z"/><path d="M4 7.5 L12 12 L20 7.5"/><path d="M12 12 V21"/></svg>
        Proizvodi
      </a>
      <a href="logout.php" class="odjava">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15 4 H19 V20 H15"/><path d="M10 8 L6 12 L10 16"/><path d="M6 12 H15"/></svg>
        Odjava
      </a>
    </nav>
  </aside>

  <main class="main">
    <div class="sadrzaj">
      <div>
        <h1>Proizvodi</h1>
        <p class="podnaslov">Dodirnite proizvod da izmenite dostupnost, cenu i količinu</p>
      </div>
      <div class="kategorije" id="kategorije" role="group" aria-label="Kategorija"></div>
      <div class="lista" id="lista">
        <div class="prow zaglavlje"><span>Proizvod</span><span>Cena</span><span>Količina</span><span>Status</span><span></span></div>
        <div id="stavke"><p class="prazno">Učitavanje…</p></div>
      </div>
      <div id="dodavanje"></div>
    </div>
  </main>
</div>

<div class="prozor" id="prozor" hidden>
  <div class="prozor-kutija" role="alertdialog" aria-modal="true" aria-labelledby="prozor-naslov" aria-describedby="prozor-tekst">
    <span class="ikona-crvena"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 7 H20 M9 7 V4 H15 V7 M6 7 L7 20 H17 L18 7 M10 11 V16 M14 11 V16"/></svg></span>
    <div>
      <h2 id="prozor-naslov">Da li ste sigurni?</h2>
      <p id="prozor-tekst"></p>
    </div>
    <div class="dva">
      <button type="button" class="dugme" id="prozor-ne">Ne</button>
      <button type="button" class="dugme brisi" id="prozor-da">Da, obriši</button>
    </div>
  </div>
</div>

<div class="poruka" id="poruka" role="status" hidden></div>

<script>
(function () {
  'use strict';

  var CSRF = document.querySelector('meta[name="csrf"]').content;
  var JEDINICE = ['kg', 'kom', 'tegla', 'flaša'];

  var st = {
    kategorije: [],
    aktivna: null,      // id izabrane kategorije
    proizvodi: [],
    otvoren: null,      // id otvorenog proizvoda
    izmena: null,       // {dostupan, cena, kolicina} za otvoreni proizvod
    dodaje: false,
    nacrt: null,        // podaci iz forme za dodavanje
    zaBrisanje: null
  };

  var IKONE = {
    strelica: '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 9 L12 15 L18 9"/></svg>',
    kvacica: '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12.5 L10 17 L19 7"/></svg>',
    desno: '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12 H19 M13 6 L19 12 L13 18"/></svg>',
    plus: '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" aria-hidden="true"><path d="M12 5 V19 M5 12 H19"/></svg>',
    kanta: '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 7 H20 M9 7 V4 H15 V7 M6 7 L7 20 H17 L18 7 M10 11 V16 M14 11 V16"/></svg>',
    foto: '<svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 8 H7.5 L9 5.5 H15 L16.5 8 H20 V19 H4 Z"/><circle cx="12" cy="13" r="3.5"/></svg>'
  };

  // ---------- pomoćne ----------
  function esc(s) {
    return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) {
      return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
    });
  }
  function broj(n) {
    return Number(n).toLocaleString('sr-Latn-RS', { maximumFractionDigits: 2 });
  }
  function $(id) { return document.getElementById(id); }

  var tajmer;
  function poruka(tekst, greska) {
    var el = $('poruka');
    el.textContent = tekst;
    el.className = 'poruka' + (greska ? ' greska' : '');
    el.hidden = false;
    clearTimeout(tajmer);
    tajmer = setTimeout(function () { el.hidden = true; }, 3500);
  }

  function api(akcija, opcije) {
    opcije = opcije || {};
    var url = 'api.php?akcija=' + encodeURIComponent(akcija);
    if (opcije.parametri) url += '&' + new URLSearchParams(opcije.parametri).toString();
    var init = { credentials: 'same-origin', headers: { 'X-CSRF-Token': CSRF } };
    if (opcije.podaci) {
      var fd = opcije.podaci instanceof FormData ? opcije.podaci : new FormData();
      if (!(opcije.podaci instanceof FormData)) {
        Object.keys(opcije.podaci).forEach(function (k) { fd.append(k, opcije.podaci[k]); });
      }
      init.method = 'POST';
      init.body = fd;
    }
    return fetch(url, init).then(function (r) {
      if (r.status === 401) { location.href = 'login.php'; throw new Error('Niste prijavljeni.'); }
      return r.json().catch(function () { throw new Error('Server nije vratio ispravan odgovor.'); })
        .then(function (d) {
          if (!r.ok) throw new Error(d.greska || 'Došlo je do greške.');
          return d;
        });
    });
  }

  function aktivnaKategorija() {
    return st.kategorije.filter(function (k) { return k.id === st.aktivna; })[0];
  }
  function jePovrce() {
    var k = aktivnaKategorija();
    return k && /povr/i.test(k.naziv);
  }

  // ---------- učitavanje ----------
  function ucitajKategorije() {
    return api('kategorije').then(function (k) {
      st.kategorije = k;
      if (!st.aktivna && k.length) st.aktivna = k[0].id;
      crtajKategorije();
    });
  }
  function ucitajProizvode() {
    return api('proizvodi', { parametri: { kategorija: st.aktivna } }).then(function (p) {
      st.proizvodi = p;
      crtajListu();
    });
  }

  // ---------- crtanje ----------
  function crtajKategorije() {
    $('kategorije').innerHTML = st.kategorije.map(function (k) {
      var a = k.id === st.aktivna;
      return '<button type="button" class="kat' + (a ? ' aktivna' : '') + '" data-kat="' + k.id + '" aria-pressed="' + a + '">' +
        '<span class="kat-vrh">' +
          (k.slika ? '<img src="' + esc(k.slika) + '" alt="">' : '<span></span>') +
          '<span class="kat-ikona">' + (a ? IKONE.kvacica : IKONE.desno) + '</span>' +
        '</span>' +
        '<span class="kat-naziv">' + esc(k.naziv) + '</span>' +
        '<span class="kat-broj">' + k.broj + ' proizvoda</span>' +
      '</button>';
    }).join('');
  }

  function thumb(p) {
    return p.slika
      ? '<img class="thumb" src="' + esc(p.slika) + '" alt="">'
      : '<span class="thumb" aria-hidden="true">' + esc(p.naziv.charAt(0).toUpperCase()) + '</span>';
  }

  function crtajListu() {
    if (!st.proizvodi.length) {
      $('stavke').innerHTML = '<p class="prazno">U ovoj kategoriji još nema proizvoda.</p>';
    } else {
      $('stavke').innerHTML = st.proizvodi.map(function (p) {
        var otvoren = p.id === st.otvoren;
        var cena = broj(p.cena) + ' RSD / ' + esc(p.jedinica);
        var kol = broj(p.kolicina) + ' ' + esc(p.jedinica);
        var bedz = '<span class="bedz ' + (p.dostupan ? 'da">Dostupan' : 'ne">Nedostupan') + '</span>';
        return '<div class="stavka' + (otvoren ? ' otvorena' : '') + '" data-id="' + p.id + '">' +
          '<button type="button" class="prow" data-akcija="otvori" aria-expanded="' + otvoren + '">' +
            '<span class="pinfo">' + thumb(p) +
              '<span class="ptekst"><span class="pnaziv">' + esc(p.naziv) + '</span>' +
              '<span class="mob"><span class="bold" style="color:#1C2A20">' + cena + '</span><span>' + kol + '</span>' + bedz + '</span></span>' +
            '</span>' +
            '<span class="desk bold">' + cena + '</span>' +
            '<span class="desk siv">' + kol + '</span>' +
            '<span class="desk">' + bedz + '</span>' +
            '<span class="strelica">' + IKONE.strelica + '</span>' +
          '</button>' +
          (otvoren ? panel(p) : '') +
        '</div>';
      }).join('');
    }
    crtajDodavanje();
  }

  function panel(p) {
    var iz = st.izmena;
    return '<div class="panel">' +
      '<div class="polje seg-polje"><span>Dostupnost</span>' +
        '<div class="seg" role="group" aria-label="Dostupnost">' +
          '<button type="button" class="da" data-akcija="dostupan" aria-pressed="' + iz.dostupan + '">Dostupan</button>' +
          '<button type="button" class="ne" data-akcija="nedostupan" aria-pressed="' + !iz.dostupan + '">Nedostupan</button>' +
        '</div></div>' +
      '<div class="polja">' +
        '<label class="polje"><span>Cena (RSD)</span><input class="unos" type="number" inputmode="decimal" min="0" step="any" data-polje="cena" value="' + esc(iz.cena) + '"></label>' +
        '<label class="polje"><span>Količina (' + esc(p.jedinica) + ')</span><input class="unos" type="number" inputmode="decimal" min="0" step="any" data-polje="kolicina" value="' + esc(iz.kolicina) + '"></label>' +
      '</div>' +
      '<div class="akcije">' +
        '<button type="button" class="dugme" data-akcija="zatvori">Zatvori</button>' +
        '<button type="button" class="dugme glavno" data-akcija="sacuvaj">Sačuvaj</button>' +
      '</div>' +
      '<div class="brisanje"><button type="button" class="dugme crveno" data-akcija="obrisi">' + IKONE.kanta + 'Obriši proizvod</button></div>' +
    '</div>';
  }

  function crtajDodavanje() {
    var natpis = jePovrce() ? 'Dodaj povrće' : 'Dodaj zimnicu';
    if (!st.dodaje) {
      $('dodavanje').innerHTML = '<button type="button" class="dodaj-dugme" id="dodaj-otvori">' + IKONE.plus + esc(natpis) + '</button>';
      return;
    }
    var n = st.nacrt;
    $('dodavanje').innerHTML =
      '<form class="forma" id="forma-dodaj" novalidate>' +
        '<h2>' + esc(natpis) + '</h2>' +
        '<label class="slika-izbor">' +
          '<input type="file" accept="image/*" id="dodaj-slika">' +
          (n.prikaz ? '<img class="slika-prikaz" src="' + n.prikaz + '" alt="Izabrana slika proizvoda">' : '<span class="slika-prikaz">' + IKONE.foto + '</span>') +
          '<span class="ptekst"><span class="pnaziv" style="color:#1F4A2A">' + (n.prikaz ? 'Promeni sliku' : 'Dodaj sliku') + '</span>' +
          '<span class="pkat">Fotografišite ili izaberite iz galerije</span></span>' +
        '</label>' +
        '<label class="polje"><span>Naziv</span><input class="unos" type="text" id="dodaj-naziv" maxlength="150" placeholder="npr. Boranija" autocomplete="off" value="' + esc(n.naziv) + '"></label>' +
        '<div class="tri">' +
          '<label class="polje"><span>Cena (RSD)</span><input class="unos" type="number" inputmode="decimal" min="0" step="any" id="dodaj-cena" value="' + esc(n.cena) + '"></label>' +
          '<label class="polje"><span>Količina</span><input class="unos" type="number" inputmode="decimal" min="0" step="any" id="dodaj-kolicina" value="' + esc(n.kolicina) + '"></label>' +
          '<label class="polje"><span>Jedinica</span><select class="unos" id="dodaj-jedinica">' +
            JEDINICE.map(function (j) { return '<option' + (j === n.jedinica ? ' selected' : '') + '>' + j + '</option>'; }).join('') +
          '</select></label>' +
        '</div>' +
        '<div class="dva">' +
          '<button type="button" class="dugme" id="dodaj-otkazi">Otkaži</button>' +
          '<button type="submit" class="dugme glavno" id="dodaj-posalji">Dodaj</button>' +
        '</div>' +
      '</form>' +
      '<div class="razmak" aria-hidden="true"></div>';
  }

  // ---------- slika: smanjivanje pre slanja ----------
  function smanjiSliku(fajl) {
    return new Promise(function (resolve, reject) {
      var url = URL.createObjectURL(fajl);
      var img = new Image();
      img.onload = function () {
        var s = Math.min(1, 1200 / Math.max(img.width, img.height));
        var c = document.createElement('canvas');
        c.width = Math.round(img.width * s);
        c.height = Math.round(img.height * s);
        c.getContext('2d').drawImage(img, 0, 0, c.width, c.height);
        URL.revokeObjectURL(url);
        c.toBlob(function (b) { b ? resolve(b) : reject(new Error('Slika nije obrađena.')); }, 'image/jpeg', 0.85);
      };
      img.onerror = function () { URL.revokeObjectURL(url); reject(new Error('Ovaj format slike nije podržan.')); };
      img.src = url;
    });
  }

  // ---------- događaji ----------
  $('kategorije').addEventListener('click', function (e) {
    var b = e.target.closest('[data-kat]');
    if (!b) return;
    st.aktivna = Number(b.getAttribute('data-kat'));
    st.otvoren = null;
    st.dodaje = false;
    crtajKategorije();
    $('stavke').innerHTML = '<p class="prazno">Učitavanje…</p>';
    ucitajProizvode().catch(function (er) { poruka(er.message, true); });
  });

  $('stavke').addEventListener('input', function (e) {
    var polje = e.target.getAttribute('data-polje');
    if (polje && st.izmena) st.izmena[polje] = e.target.value;
  });

  $('stavke').addEventListener('click', function (e) {
    var b = e.target.closest('[data-akcija]');
    if (!b) return;
    var red = b.closest('[data-id]');
    var id = Number(red.getAttribute('data-id'));
    var p = st.proizvodi.filter(function (x) { return x.id === id; })[0];
    var akcija = b.getAttribute('data-akcija');

    if (akcija === 'otvori') {
      if (st.otvoren === id) {
        st.otvoren = null;
      } else {
        st.otvoren = id;
        st.izmena = { dostupan: p.dostupan, cena: p.cena, kolicina: p.kolicina };
        st.dodaje = false;
      }
      crtajListu();
    } else if (akcija === 'zatvori') {
      st.otvoren = null;
      crtajListu();
    } else if (akcija === 'dostupan' || akcija === 'nedostupan') {
      st.izmena.dostupan = akcija === 'dostupan';
      red.querySelector('.seg .da').setAttribute('aria-pressed', String(st.izmena.dostupan));
      red.querySelector('.seg .ne').setAttribute('aria-pressed', String(!st.izmena.dostupan));
    } else if (akcija === 'sacuvaj') {
      b.disabled = true;
      api('izmeni', { podaci: { id: id, dostupan: st.izmena.dostupan ? '1' : '0', cena: st.izmena.cena, kolicina: st.izmena.kolicina } })
        .then(function (novi) {
          st.proizvodi = st.proizvodi.map(function (x) { return x.id === id ? novi : x; });
          st.otvoren = null;
          crtajListu();
          poruka('Sačuvano: ' + novi.naziv);
        })
        .catch(function (er) { b.disabled = false; poruka(er.message, true); });
    } else if (akcija === 'obrisi') {
      st.zaBrisanje = p;
      $('prozor-tekst').textContent = 'Proizvod „' + p.naziv + '“ će biti trajno obrisan.';
      $('prozor').hidden = false;
      $('prozor-ne').focus();
    }
  });

  function zatvoriProzor() {
    $('prozor').hidden = true;
    st.zaBrisanje = null;
  }
  $('prozor-ne').addEventListener('click', zatvoriProzor);
  $('prozor').addEventListener('click', function (e) { if (e.target === $('prozor')) zatvoriProzor(); });
  document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && !$('prozor').hidden) zatvoriProzor(); });
  $('prozor-da').addEventListener('click', function () {
    var p = st.zaBrisanje;
    if (!p) return;
    var dugme = $('prozor-da');
    dugme.disabled = true;
    api('obrisi', { podaci: { id: p.id } })
      .then(function () {
        zatvoriProzor();
        st.otvoren = null;
        poruka('Obrisano: ' + p.naziv);
        return Promise.all([ucitajKategorije(), ucitajProizvode()]);
      })
      .catch(function (er) { poruka(er.message, true); })
      .then(function () { dugme.disabled = false; });
  });

  // Dodavanje
  $('dodavanje').addEventListener('click', function (e) {
    if (e.target.closest('#dodaj-otvori')) {
      st.dodaje = true;
      st.otvoren = null;
      st.nacrt = { naziv: '', cena: '', kolicina: '', jedinica: jePovrce() ? 'kg' : 'tegla', slika: null, prikaz: null };
      crtajListu();
      $('dodaj-naziv').focus();
    } else if (e.target.closest('#dodaj-otkazi')) {
      if (st.nacrt && st.nacrt.prikaz) URL.revokeObjectURL(st.nacrt.prikaz);
      st.dodaje = false;
      crtajDodavanje();
    }
  });

  function procitajNacrt() {
    var n = st.nacrt;
    n.naziv = $('dodaj-naziv').value;
    n.cena = $('dodaj-cena').value;
    n.kolicina = $('dodaj-kolicina').value;
    n.jedinica = $('dodaj-jedinica').value;
  }

  $('dodavanje').addEventListener('change', function (e) {
    if (e.target.id !== 'dodaj-slika' || !e.target.files[0]) return;
    procitajNacrt();
    smanjiSliku(e.target.files[0]).then(function (blob) {
      if (st.nacrt.prikaz) URL.revokeObjectURL(st.nacrt.prikaz);
      st.nacrt.slika = blob;
      st.nacrt.prikaz = URL.createObjectURL(blob);
      crtajDodavanje();
    }).catch(function (er) { poruka(er.message, true); });
  });

  $('dodavanje').addEventListener('submit', function (e) {
    e.preventDefault();
    procitajNacrt();
    var n = st.nacrt;
    if (!n.naziv.trim()) { poruka('Upišite naziv proizvoda.', true); $('dodaj-naziv').focus(); return; }
    var fd = new FormData();
    fd.append('kategorija_id', st.aktivna);
    fd.append('naziv', n.naziv.trim());
    fd.append('cena', n.cena || '0');
    fd.append('kolicina', n.kolicina || '0');
    fd.append('jedinica', n.jedinica);
    if (n.slika) fd.append('slika', n.slika, 'proizvod.jpg');
    var dugme = $('dodaj-posalji');
    dugme.disabled = true;
    api('dodaj', { podaci: fd })
      .then(function (novi) {
        if (n.prikaz) URL.revokeObjectURL(n.prikaz);
        st.dodaje = false;
        poruka('Dodato: ' + novi.naziv);
        return Promise.all([ucitajKategorije(), ucitajProizvode()]);
      })
      .catch(function (er) { dugme.disabled = false; poruka(er.message, true); });
  });

  // Na telefonu: kad se otvori tastatura, pomeri polje iznad nje
  document.addEventListener('focusin', function (e) {
    var el = e.target;
    if (!el.matches || !el.matches('input:not([type=file]), select')) return;
    var idi = function () { try { el.scrollIntoView({ block: 'center', behavior: 'smooth' }); } catch (x) { el.scrollIntoView(); } };
    setTimeout(idi, 150);
    setTimeout(idi, 450);
  });

  // Start
  ucitajKategorije()
    .then(ucitajProizvode)
    .catch(function (er) {
      $('stavke').innerHTML = '<p class="prazno">' + esc(er.message) + '</p>';
    });
})();
</script>
</body>
</html>
