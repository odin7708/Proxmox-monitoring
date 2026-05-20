<?php
session_start();
require_once __DIR__ . '/db.php';

$erreur = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($username && $password) {
        $pdo  = getPDO();
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? LIMIT 1");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user'] = $user['username'];
            header('Location: index.php');
            exit;
        }
    }
    $erreur = 'Identifiants incorrects.';
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion — Monitoring</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body { display: flex; align-items: center; justify-content: center; min-height: 100vh; }
        .login-box { border: 1px solid #ccc; border-radius: 6px; padding: 2rem; width: 300px; }
        .login-box h1 { font-size: .9rem; text-transform: uppercase; letter-spacing: .1em; margin-bottom: 1.5rem; }
        .login-box label { font-size: .7rem; text-transform: uppercase; letter-spacing: .08em; color: #666; display: block; margin-bottom: .3rem; }
        .login-box input { width: 100%; padding: .5rem .6rem; border: 1px solid #ccc; border-radius: 4px; font-family: monospace; font-size: .9rem; margin-bottom: 1rem; }
        .login-box button { width: 100%; padding: .55rem; background: #111; color: #fff; border: none; border-radius: 4px; font-family: monospace; font-size: .85rem; cursor: pointer; letter-spacing: .08em; text-transform: uppercase; }
        .login-box button:hover { background: #333; }
        .erreur { color: red; font-size: .75rem; margin-bottom: .8rem; }
    </style>
</head>
<body>
<div class="login-box">
    <h1>Monitoring — Connexion</h1>
    <?php if ($erreur): ?>
        <p class="erreur"><?= htmlspecialchars($erreur) ?></p>
    <?php endif; ?>
    <form method="POST">
        <label>Identifiant</label>
        <input type="text" name="username" autofocus>
        <label>Mot de passe</label>
        <input type="password" name="password">
        <button type="submit">Se connecter</button>
    </form>
</div>
</body>
</html>
