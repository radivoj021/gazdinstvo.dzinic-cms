<?php
declare(strict_types=1);
require __DIR__ . '/bootstrap.php';

if (ulogovan()) {
    header('Location: index.php');
    exit;
}

$greska = '';
$lozinka_sistema = (string)(config()['admin_lozinka'] ?? '');

if ($lozinka_sistema === '' || $lozinka_sistema === 'promeni-me') {
    $greska = 'Prijava je zaključana: postavite lozinku u fajlu config.php.';
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals(csrf_token(), (string)($_POST['csrf'] ?? ''))) {
        $greska = 'Sesija je istekla. Pokušajte ponovo.';
    } elseif (hash_equals($lozinka_sistema, (string)($_POST['lozinka'] ?? ''))) {
        session_regenerate_id(true);
        $_SESSION['admin'] = true;
        header('Location: index.php');
        exit;
    } else {
        sleep(1);
        $greska = 'Pogrešna lozinka.';
    }
}
?><!doctype html>
<html lang="sr-Latn">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Prijava — Džinić CMS</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Fraunces:opsz,wght@9..144,700&display=swap" rel="stylesheet">
<style>
*{box-sizing:border-box}
body{margin:0;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:16px;background:#1F4A2A;font-family:Inter,system-ui,sans-serif;color:#1C2A20}
.kartica{width:100%;max-width:380px;background:#fff;border-radius:18px;padding:28px;display:flex;flex-direction:column;gap:18px}
.logo{display:flex;align-items:center;gap:12px}
.logo b{font-family:Fraunces,Georgia,serif;font-size:28px;color:#1F4A2A;line-height:1}
.logo small{display:block;font-size:12px;color:#5A6659;margin-bottom:2px}
label{display:flex;flex-direction:column;gap:8px;font-size:14px;font-weight:600;color:#4F5B50}
input{height:48px;padding:0 12px;border:1px solid #CFCAB9;border-radius:10px;font:inherit;font-size:16px}
button{height:48px;border:0;border-radius:10px;background:#1F4A2A;color:#fff;font:inherit;font-size:16px;font-weight:600;cursor:pointer}
.greska{margin:0;padding:12px;border-radius:10px;background:#FBE4E2;color:#A12A22;font-size:14px}
</style>
</head>
<body>
<form class="kartica" method="post" autocomplete="off">
  <div class="logo">
    <svg width="44" height="44" viewBox="0 0 44 44" fill="none" stroke="#1F4A2A" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M8 22 L22 10 L36 22"/><path d="M12 19 V32 H32 V19"/><path d="M19 32 V25 H25 V32"/><path d="M4 37 C14 33 30 41 40 37" stroke="#5E9B3F"/></svg>
    <div><small>Poljoprivredno gazdinstvo</small><b>Džinić</b></div>
  </div>
  <?php if ($greska): ?><p class="greska" role="alert"><?= e($greska) ?></p><?php endif; ?>
  <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
  <label>Lozinka
    <input type="password" name="lozinka" required autofocus>
  </label>
  <button type="submit">Prijavi se</button>
</form>
</body>
</html>
