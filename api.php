<?php
declare(strict_types=1);
require __DIR__ . '/bootstrap.php';

// =====================================================================
//  API za proizvode — stranica CMS-a (index.php) šalje zahteve ovde.
//
//  GET  api.php?akcija=kategorije              → kartice sa brojem proizvoda
//  GET  api.php?akcija=proizvodi&kategorija=1  → proizvodi jedne kategorije
//  POST api.php?akcija=izmeni   (id, dostupan, cena, kolicina)
//  POST api.php?akcija=dodaj    (kategorija_id, naziv, cena, jedinica, kolicina, slika)
//  POST api.php?akcija=obrisi   (id)
// =====================================================================

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

const JEDINICE = ['kg', 'kom', 'tegla', 'flaša'];

function odgovor($podaci, int $kod = 200): void
{
    http_response_code($kod);
    echo json_encode($podaci, JSON_UNESCAPED_UNICODE);
    exit;
}

function greska(string $poruka, int $kod = 400): void
{
    odgovor(['greska' => $poruka], $kod);
}

function samo_post(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        greska('Ova akcija zahteva POST.', 405);
    }
}

function broj($vrednost, string $polje): float
{
    $vrednost = str_replace(',', '.', trim((string)$vrednost));
    if ($vrednost === '' || !is_numeric($vrednost) || (float)$vrednost < 0) {
        greska("Polje „{$polje}“ mora biti broj 0 ili veći.");
    }
    return round((float)$vrednost, 2);
}

function mapiraj(array $p): array
{
    return [
        'id'            => (int)$p['id'],
        'kategorija_id' => (int)$p['kategorija_id'],
        'naziv'         => $p['naziv'],
        'opis'          => $p['opis'],
        'cena'          => (float)$p['cena'],
        'jedinica'      => $p['jedinica'],
        'kolicina'      => (float)$p['kolicina'],
        'dostupan'      => (bool)$p['dostupan'],
        'slika'         => $p['slika'],
    ];
}

function nadji_proizvod(int $id): array
{
    $s = db()->prepare('SELECT * FROM proizvodi WHERE id = ?');
    $s->execute([$id]);
    $p = $s->fetch();
    if (!$p) {
        greska('Proizvod ne postoji.', 404);
    }
    return $p;
}

function sacuvaj_sliku(array $f): string
{
    if ($f['error'] !== UPLOAD_ERR_OK) {
        greska('Slika nije otpremljena. Pokušajte ponovo.');
    }
    $max = (int)(config()['max_slika_mb'] ?? 5);
    if ($f['size'] > $max * 1024 * 1024) {
        greska("Slika je veća od {$max} MB.");
    }
    $tip = (new finfo(FILEINFO_MIME_TYPE))->file($f['tmp_name']);
    $ekstenzije = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    if (!isset($ekstenzije[$tip])) {
        greska('Dozvoljene su samo JPG, PNG i WEBP slike.');
    }
    $folder = __DIR__ . '/uploads';
    if (!is_dir($folder) && !mkdir($folder, 0755, true)) {
        greska('Folder uploads ne postoji na serveru.', 500);
    }
    $ime = bin2hex(random_bytes(8)) . '.' . $ekstenzije[$tip];
    if (!move_uploaded_file($f['tmp_name'], "$folder/$ime")) {
        greska('Slika nije sačuvana na serveru.', 500);
    }
    return 'uploads/' . $ime;
}

// Briše fajl slike samo ako ga više niko ne koristi.
function obrisi_sliku(?string $putanja): void
{
    if (!$putanja || !preg_match('#^uploads/[A-Za-z0-9._-]+$#', $putanja)) {
        return;
    }
    foreach (['proizvodi', 'kategorije'] as $tabela) {
        $s = db()->prepare("SELECT COUNT(*) FROM {$tabela} WHERE slika = ?");
        $s->execute([$putanja]);
        if ((int)$s->fetchColumn() > 0) {
            return;
        }
    }
    $fajl = __DIR__ . '/' . $putanja;
    if (is_file($fajl)) {
        @unlink($fajl);
    }
}

// ---------------------------------------------------------------------
//  Provere pre svake akcije
// ---------------------------------------------------------------------
if (!ulogovan()) {
    greska('Niste prijavljeni.', 401);
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    if (!is_string($token) || !hash_equals(csrf_token(), $token)) {
        greska('Sesija je istekla. Osvežite stranicu.', 403);
    }
}

$akcija = (string)($_GET['akcija'] ?? '');

try {
    switch ($akcija) {

        case 'kategorije':
            $redovi = db()->query(
                'SELECT k.id, k.naziv, k.slika, COUNT(p.id) AS broj
                   FROM kategorije k
                   LEFT JOIN proizvodi p ON p.kategorija_id = k.id
                  GROUP BY k.id, k.naziv, k.slika, k.redosled
                  ORDER BY k.redosled, k.id'
            )->fetchAll();
            odgovor(array_map(fn($k) => [
                'id'    => (int)$k['id'],
                'naziv' => $k['naziv'],
                'slika' => $k['slika'],
                'broj'  => (int)$k['broj'],
            ], $redovi));

        case 'proizvodi':
            $s = db()->prepare('SELECT * FROM proizvodi WHERE kategorija_id = ? ORDER BY redosled, naziv');
            $s->execute([(int)($_GET['kategorija'] ?? 0)]);
            odgovor(array_map('mapiraj', $s->fetchAll()));

        case 'izmeni':
            samo_post();
            $id = (int)($_POST['id'] ?? 0);
            nadji_proizvod($id);
            $dostupan = (($_POST['dostupan'] ?? '0') === '1') ? 1 : 0;
            $cena     = broj($_POST['cena'] ?? '', 'Cena');
            $kolicina = broj($_POST['kolicina'] ?? '', 'Količina');
            $s = db()->prepare('UPDATE proizvodi SET dostupan = ?, cena = ?, kolicina = ? WHERE id = ?');
            $s->execute([$dostupan, $cena, $kolicina, $id]);
            odgovor(mapiraj(nadji_proizvod($id)));

        case 'dodaj':
            samo_post();
            $naziv = trim((string)($_POST['naziv'] ?? ''));
            if ($naziv === '' || mb_strlen($naziv) > 150) {
                greska('Upišite naziv proizvoda (najviše 150 znakova).');
            }
            $kategorija = (int)($_POST['kategorija_id'] ?? 0);
            $s = db()->prepare('SELECT COUNT(*) FROM kategorije WHERE id = ?');
            $s->execute([$kategorija]);
            if ((int)$s->fetchColumn() === 0) {
                greska('Izabrana kategorija ne postoji.');
            }
            $jedinica = (string)($_POST['jedinica'] ?? 'kg');
            if (!in_array($jedinica, JEDINICE, true)) {
                greska('Nepoznata jedinica mere.');
            }
            $cena     = broj($_POST['cena'] ?? '', 'Cena');
            $kolicina = broj($_POST['kolicina'] ?? '', 'Količina');

            $slika = null;
            if (!empty($_FILES['slika']) && $_FILES['slika']['error'] !== UPLOAD_ERR_NO_FILE) {
                $slika = sacuvaj_sliku($_FILES['slika']);
            }

            try {
                $s = db()->prepare('SELECT COALESCE(MAX(redosled), 0) + 1 FROM proizvodi WHERE kategorija_id = ?');
                $s->execute([$kategorija]);
                $redosled = (int)$s->fetchColumn();

                $s = db()->prepare(
                    'INSERT INTO proizvodi (kategorija_id, naziv, cena, jedinica, kolicina, dostupan, slika, redosled)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
                );
                $s->execute([$kategorija, $naziv, $cena, $jedinica, $kolicina, $kolicina > 0 ? 1 : 0, $slika, $redosled]);
            } catch (Throwable $t) {
                if ($slika) {
                    @unlink(__DIR__ . '/' . $slika);
                }
                throw $t;
            }
            odgovor(mapiraj(nadji_proizvod((int)db()->lastInsertId())), 201);

        case 'obrisi':
            samo_post();
            $p = nadji_proizvod((int)($_POST['id'] ?? 0));
            $s = db()->prepare('DELETE FROM proizvodi WHERE id = ?');
            $s->execute([(int)$p['id']]);
            obrisi_sliku($p['slika']);
            odgovor(['ok' => true]);

        default:
            greska('Nepoznata akcija.', 404);
    }
} catch (PDOException $e) {
    error_log('Džinić CMS: ' . $e->getMessage());
    greska('Greška u radu sa bazom podataka.', 500);
}
