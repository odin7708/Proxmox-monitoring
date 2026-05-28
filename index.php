<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/collector.php';
$pdo = getPDO();

$baie = $pdo->query("SELECT temperature, humidite, timestamp FROM measure_baie ORDER BY timestamp DESC LIMIT 1")->fetch();

$px = $pdo->query("SELECT mp.cpu, mp.ram, mp.storage, mp.timestamp, p.hostname FROM measure_proxmox mp JOIN proxmox p ON p.id_proxmox = mp.id_proxmox ORDER BY mp.timestamp DESC LIMIT 1")->fetch();

$vms = $pdo->query("SELECT v.name, mv.cpu, mv.ram, mv.timestamp FROM vm v LEFT JOIN measure_vm mv ON mv.id = (SELECT id FROM measure_vm WHERE id_vm = v.id_vm ORDER BY timestamp DESC LIMIT 1)")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="refresh" content="15">
    <title>Monitoring</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<h1>Monitoring — <?= date('d/m/Y H:i:s') ?> <a href="logout.php" style="float:right;font-size:.7rem;color:#aaa;text-decoration:none;letter-spacing:.08em">DÉCONNEXION</a></h1>

<h2>Capteur Baie (DHT11)</h2>
<div class="grid">
    <div class="card">
        <div class="label">Température</div>
        <div class="value"><?= $baie ? number_format($baie['temperature'],1) : '--' ?><span> °C</span></div>
        <div class="time"><?= $baie['timestamp'] ?? 'Aucune donnée' ?></div>
    </div>
    <div class="card">
        <div class="label">Humidité</div>
        <div class="value"><?= $baie ? number_format($baie['humidite'],1) : '--' ?><span> %</span></div>
        <div class="time"><?= $baie['timestamp'] ?? '' ?></div>
    </div>
</div>

<h2>Proxmox — <?= htmlspecialchars($px['hostname'] ?? 'pve') ?></h2>
<div class="grid">
    <div class="card">
        <div class="label">CPU</div>
        <div class="value"><?= $px ? number_format($px['cpu'],2) : '--' ?><span> %</span></div>
        <div class="time"><?= $px['timestamp'] ?? '' ?></div>
    </div>
    <div class="card">
        <div class="label">RAM</div>
        <div class="value"><?= $px ? number_format($px['ram']/1024,2) : '--' ?><span> GB</span></div>
        <div class="time"><?= $px['timestamp'] ?? '' ?></div>
    </div>
    <div class="card">
        <div class="label">Stockage</div>
        <div class="value"><?= $px ? number_format($px['storage'],1) : '--' ?><span> %</span></div>
        <div class="time"><?= $px['timestamp'] ?? '' ?></div>
    </div>
</div>

<h2>Machines Virtuelles</h2>
<div class="grid">
<?php foreach ($vms as $vm): ?>
    <div class="card">
        <div class="label"><?= htmlspecialchars($vm['name']) ?></div>
        <div class="value"><?= $vm['cpu'] !== null ? number_format($vm['cpu'],2) : '--' ?><span> % CPU</span></div>
        <div class="value" style="font-size:1.2rem"><?= $vm['ram'] ? number_format($vm['ram']/1024,2) : '--' ?><span> GB RAM</span></div>
        <div class="time"><?= $vm['timestamp'] ?? '' ?></div>
    </div>
<?php endforeach; ?>
</div>

</body>
</html>
