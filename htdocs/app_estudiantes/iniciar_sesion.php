<?php
require_once __DIR__ . '/conexion.php';
if (session_status() !== PHP_SESSION_ACTIVE) session_start();

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $u = trim($_POST['username'] ?? '');
    $p = $_POST['password'] ?? '';

    if ($u === '' || $p === '') {
        $errors[] = 'Usuario y contraseña son obligatorios.';
    } else {
        $stmt = $mysqli->prepare("SELECT id,password FROM usuarios WHERE username = ? LIMIT 1");
        $stmt->bind_param('s', $u);
        $stmt->execute();
        $stmt->bind_result($id, $hash);
        if ($stmt->fetch() && password_verify($p, $hash)) {
            $_SESSION['user_id'] = $id;
            $_SESSION['username'] = $u;
            $stmt->close();
            header('Location: index.php');
            exit;
        }
        $stmt->close();
        $errors[] = 'Credenciales incorrectas.';
    }
}
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Iniciar sesión</title>
<style>
:root{--accent:#2b6cb0;--muted:#6b7280}
body{font-family:Arial,Helvetica,sans-serif;background:#f5f7fb;display:flex;align-items:center;justify-content:center;height:100vh;margin:0}
.box{background:#fff;padding:20px;border-radius:10px;box-shadow:0 6px 18px rgba(0,0,0,.06);width:320px}
h1{color:var(--accent);font-size:18px;margin:0 0 12px}
.input{width:100%;padding:10px;border:1px solid #e6eef8;border-radius:8px;margin-bottom:10px}
.btn{width:100%;background:var(--accent);color:#fff;padding:10px;border:0;border-radius:8px;cursor:pointer}
.link{display:block;text-align:center;margin-top:10px;color:var(--accent);text-decoration:none}
.error{background:#fff5f5;color:#b91c1c;padding:8px;border-radius:6px;margin-bottom:10px;font-size:14px}
.toplink{display:inline-block;margin-bottom:8px;color:var(--accent);text-decoration:none;font-weight:600}
</style>
</head>
<body>
<div class="box">
    <a class="toplink" href="index.php">Volver al inicio</a>
    <h1>Iniciar sesión</h1>

    <?php if (!empty($errors)): ?>
        <div class="error"><?= htmlspecialchars($errors[0], ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>

    <form method="post" action="iniciar_sesion.php" autocomplete="off">
        <input class="input" name="username" type="text" placeholder="Usuario" value="<?= htmlspecialchars($_POST['username'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
        <input class="input" name="password" type="password" placeholder="Contraseña">
        <button class="btn" type="submit">Entrar</button>
    </form>

    <a class="link" href="crear_cuenta.php">Crear cuenta</a>
</div>
</body>
</html>