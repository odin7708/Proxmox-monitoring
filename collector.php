<?php
require_once __DIR__ . '/db.php';

define('PVE_HOST',  '10.1.30.102');
define('PVE_NODE',  'pve');
define('PVE_ID',    'pve1');
define('PVE_TOKEN', 'PVEAPIToken=root@pam!monitoring=b2785519-e2d7-4053-93e8-732dd4d0cf8e');

function pveGet(string $path): ?array {
    $ch = curl_init('https://' . PVE_HOST . ':8006/api2/json' . $path);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_TIMEOUT        => 5,
        CURLOPT_HTTPHEADER     => ['Authorization: ' . PVE_TOKEN],
    ]);
    $resp = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($code !== 200) return null;
    $json = json_decode($resp, true);
    return $json['data'] ?? null;
}

try {
    $pdo = getPDO();

    $node = pveGet('/nodes/' . PVE_NODE . '/status');
    if ($node) {
        $pdo->prepare("INSERT INTO measure_proxmox (id, id_proxmox, timestamp, cpu, ram, storage) VALUES (?,?,NOW(),?,?,?)")
            ->execute([
                'px_' . uniqid(),
                PVE_ID,
                (int) round(($node['cpu'] ?? 0) * 100),
                (int) round(($node['memory']['used'] ?? 0) / 1024 / 1024),
                round((($node['rootfs']['used'] ?? 0) / ($node['rootfs']['total'] ?? 1)) * 100, 2),
            ]);
    }

    $vmList = pveGet('/nodes/' . PVE_NODE . '/qemu');
    if ($vmList) {
        foreach ($vmList as $vm) {
            if (($vm['status'] ?? '') !== 'running') continue;
            $vmid = (string) $vm['vmid'];
            $s = pveGet('/nodes/' . PVE_NODE . '/qemu/' . $vmid . '/status/current');
            if (!$s) continue;
            $pdo->prepare("INSERT IGNORE INTO vm (id_vm, name, os) VALUES (?,?,?)")
                ->execute([$vmid, $vm['name'] ?? "VM $vmid", 'Linux']);
            $pdo->prepare("INSERT INTO measure_vm (id, id_vm, timestamp, cpu, ram, storage) VALUES (?,?,NOW(),?,?,0)")
                ->execute([
                    'vm_' . uniqid(),
                    $vmid,
                    (int) round(($s['cpu'] ?? 0) * 100),
                    (int) round(($s['mem'] ?? 0) / 1024 / 1024),
                ]);
        }
    }
} catch (Exception $e) {
}
