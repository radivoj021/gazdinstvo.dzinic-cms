<?php
// =====================================================================
//  PROVERA KONEKCIJE SA BAZOM
//  Otvorite u pregledaču: https://vas-sajt.rs/cms/proveri.php
//  OBRIŠITE OVAJ FAJL SA SERVERA ČIM PROVERITE — prikazuje detalje greške.
// =====================================================================
header('Content-Type: text/plain; charset=utf-8');

$c = require __DIR__ . '/config.php';

echo "PHP verzija: " . PHP_VERSION . "\n";
echo "pdo_mysql:   " . (extension_loaded('pdo_mysql') ? 'DA' : 'NE — uključite PDO MySQL u cPanel → Select PHP Version') . "\n\n";

echo "Podaci iz config.php:\n";
echo "  db_host = {$c['db_host']}\n";
echo "  db_name = {$c['db_name']}\n";
echo "  db_user = {$c['db_user']}\n";
echo "  db_pass = " . (strlen((string)$c['db_pass']) ? str_repeat('*', strlen((string)$c['db_pass'])) : '(prazno)') . "\n\n";

try {
    $pdo = new PDO(
        "mysql:host={$c['db_host']};dbname={$c['db_name']};charset=utf8mb4",
        $c['db_user'],
        $c['db_pass'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    echo "KONEKCIJA: USPEŠNA\n\n";

    foreach (['kategorije', 'proizvodi'] as $t) {
        try {
            $n = $pdo->query("SELECT COUNT(*) FROM `$t`")->fetchColumn();
            echo "  tabela $t: $n redova\n";
        } catch (PDOException $e) {
            echo "  tabela $t: NE POSTOJI — uvezite dzinic_cms.sql u ovu bazu\n";
        }
    }
} catch (PDOException $e) {
    echo "KONEKCIJA: NEUSPEŠNA\n";
    echo "Poruka: " . $e->getMessage() . "\n\n";

    $m = $e->getMessage();
    if (strpos($m, '1045') !== false) {
        echo "Uzrok: pogrešan korisnik ili lozinka (db_user / db_pass).\n";
        echo "Na hostingu korisnik obično ima prefiks, npr. mojnalog_korisnik.\n";
    } elseif (strpos($m, '1044') !== false) {
        echo "Uzrok: korisnik nema prava nad bazom.\n";
        echo "cPanel → MySQL Databases → Add User To Database → ALL PRIVILEGES.\n";
    } elseif (strpos($m, '1049') !== false) {
        echo "Uzrok: baza sa tim nazivom ne postoji (db_name).\n";
        echo "Upišite pun naziv sa prefiksom, tačno kao u cPanelu.\n";
    } elseif (strpos($m, '2002') !== false || strpos($m, '2005') !== false) {
        echo "Uzrok: server baze nije dostupan na adresi db_host.\n";
        echo "Na većini hostinga je 'localhost'; ako ne radi, probajte '127.0.0.1'\n";
        echo "ili adresu koju hosting navodi. Na XAMPP-u proverite da je MySQL pokrenut.\n";
    } elseif (stripos($m, 'could not find driver') !== false) {
        echo "Uzrok: PHP nema MySQL drajver. Uključite pdo_mysql.\n";
    }
}
