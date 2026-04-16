<?php
require_once __DIR__ . '/db.php';

header('Content-Type: application/json');

$apiKey = $_SERVER['HTTP_X_API_KEY'] ?? '';
if ($apiKey !== 'CLE_SECRETE_123') {
    http_response_code(401);
    echo '{"error":"cle invalide"}';
    exit;
}

$contentType = $_SERVER['CONTENT_TYPE'] ?? '';

if (stripos($contentType, 'application/json') !== false) {
    $raw  = file_get_contents('php://input');
    $data = json_decode($raw, true);
    $temperature = isset($data['temperature']) ? (float) $data['temperature'] : null;
    $humidite    = isset($data['humidite'])    ? (float) $data['humidite']    : null;
    $id_sensor   = isset($data['id_capteur'])  ? (string) $data['id_capteur'] : 'sensor1';
} else {
    $temperature = isset($_POST['temperature']) ? (float) $_POST['temperature'] : null;
    $humidite    = isset($_POST['humidity'])     ? (float) $_POST['humidity']    : null;
    $id_sensor   = 'sensor1';
}

if ($temperature === null || $humidite === null) {
    http_response_code(400);
    echo '{"error":"parametres manquants"}';
    exit;
}

if ($temperature < -40 || $temperature > 85 || $humidite < 0 || $humidite > 100) {
    http_response_code(422);
    echo '{"error":"valeurs hors plage"}';
    exit;
}

try {
    $pdo  = getPDO();
    $id   = 'baie_' . uniqid();

    $stmt = $pdo->prepare(
        "INSERT INTO measure_baie (id, id_sensor, temperature, humidite, timestamp)
         VALUES (:id, :sensor, :temp, :hum, NOW())"
    );
    $stmt->execute([
        ':id'     => $id,
        ':sensor' => $id_sensor,
        ':temp'   => $temperature,
        ':hum'    => $humidite,
    ]);

    echo '{"ok":true,"id":"' . $id . '"}';

} catch (PDOException $e) {
    http_response_code(500);
    echo '{"error":"bdd"}';
}
