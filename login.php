<?php
session_start();
require_once __DIR__ . '/db.php';

$erreur  = '';
$succes  = '';
$mode    = $_GET['mode'] ?? 'login'; // 'login' ou 'register'

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'login';

    if ($action === 'login') {
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
        $mode   = 'login';

    } elseif ($action === 'register') {
        $mode     = 'register';
        $username = trim($_POST['reg_username'] ?? '');
        $password = $_POST['reg_password'] ?? '';
        $confirm  = $_POST['reg_confirm']  ?? '';

        if (!$username || !$password || !$confirm) {
            $erreur = 'Tous les champs sont obligatoires.';
        } elseif (strlen($username) < 3) {
            $erreur = "L'identifiant doit contenir au moins 3 caractères.";
        } elseif (strlen($password) < 6) {
            $erreur = 'Le mot de passe doit contenir au moins 6 caractères.';
        } elseif ($password !== $confirm) {
            $erreur = 'Les mots de passe ne correspondent pas.';
        } else {
            $pdo  = getPDO();
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? LIMIT 1");
            $stmt->execute([$username]);
            if ($stmt->fetch()) {
                $erreur = 'Cet identifiant est déjà utilisé.';
            } else {
                $hash = password_hash($password, PASSWORD_BCRYPT);
                $ins  = $pdo->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
                $ins->execute([$username, $hash]);
                $succes = "Compte créé avec succès.";
                $mode   = 'login';
            }
        }
    }
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
        .login-box input { width: 100%; padding: .5rem .6rem; border: 1px solid #ccc; border-radius: 4px; font-family: monospace; font-size: .9rem; margin-bottom: 1rem; box-sizing: border-box; }
        .login-box button { width: 100%; padding: .55rem; background: #111; color: #fff; border: none; border-radius: 4px; font-family: monospace; font-size: .85rem; cursor: pointer; letter-spacing: .08em; text-transform: uppercase; }
        .login-box button:hover { background: #333; }
        .erreur { color: red; font-size: .75rem; margin-bottom: .8rem; }
        .succes { color: green; font-size: .75rem; margin-bottom: .8rem; }
        .toggle-link { display: block; text-align: center; margin-top: 1rem; font-size: .75rem; color: #555; text-decoration: none; cursor: pointer; }
        .toggle-link:hover { color: #111; text-decoration: underline; }
        .form-section { display: none; }
        .form-section.active { display: block; }
    </style>
</head>
<body>
<div class="login-box">
    <h1 id="form-title">Monitoring — Connexion</h1>

    <?php if ($erreur): ?>
        <p class="erreur"><?= htmlspecialchars($erreur) ?></p>
    <?php endif; ?>
    <?php if ($succes): ?>
        <p class="succes"><?= htmlspecialchars($succes) ?></p>
    <?php endif; ?>

    <div class="form-section <?= $mode === 'login' ? 'active' : '' ?>" id="section-login">
        <form method="POST">
            <input type="hidden" name="action" value="login">
            <label>Identifiant</label>
            <input type="text" name="username" autofocus>
            <label>Mot de passe</label>
            <input type="password" name="password">
            <button type="submit">Se connecter</button>
        </form>
        <a class="toggle-link" onclick="switchMode('register')">Créer un compte</a>
    </div>

    <div class="form-section <?= $mode === 'register' ? 'active' : '' ?>" id="section-register">
        <form method="POST">
            <input type="hidden" name="action" value="register">
            <label>Identifiant</label>
            <input type="text" name="reg_username">
            <label>Mot de passe</label>
            <input type="password" name="reg_password">
            <label>Confirmer le mot de passe</label>
            <input type="password" name="reg_confirm">
            <button type="submit">Créer le compte</button>
        </form>
        <a class="toggle-link" onclick="switchMode('login')">Déjà un compte ? Se connecter</a>
    </div>
</div>

<script>
    function switchMode(mode) {
        const loginSection    = document.getElementById('section-login');
        const registerSection = document.getElementById('section-register');
        const title           = document.getElementById('form-title');

        if (mode === 'register') {
            loginSection.classList.remove('active');
            registerSection.classList.add('active');
            title.textContent = 'Monitoring — Inscription';
        } else {
            registerSection.classList.remove('active');
            loginSection.classList.add('active');
            title.textContent = 'Monitoring — Connexion';
        }
    }

    <?php if ($mode === 'register'): ?>
    document.getElementById('form-title').textContent = 'Monitoring — Inscription';
    <?php endif; ?>
</script>
</body>
</html>
