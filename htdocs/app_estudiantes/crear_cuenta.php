<?php
require_once __DIR__ . '/conexion.php';
if (session_status() !== PHP_SESSION_ACTIVE) session_start();

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $password2 = $_POST['password2'] ?? '';

    if ($username === '' || $password === '') {
        $errors[] = 'Usuario y contraseña son obligatorios.';
    } elseif ($password !== $password2) {
        $errors[] = 'Las contraseñas no coinciden.';
    } else {
        $stmt = $mysqli->prepare("SELECT id FROM usuarios WHERE username = ? LIMIT 1");
        if ($stmt) {
            $stmt->bind_param('s', $username);
            $stmt->execute();
            $stmt->store_result();
            if ($stmt->num_rows > 0) {
                $errors[] = 'El usuario ya existe.';
                $stmt->close();
            } else {
                $stmt->close();
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $ins = $mysqli->prepare("INSERT INTO usuarios (username, password) VALUES (?, ?)");
                if ($ins) {
                    $ins->bind_param('ss', $username, $hash);
                    if ($ins->execute()) {
                        $ins->close();
                        header('Location: iniciar_sesion.php');
                        exit;
                    } else {
                        $errors[] = 'Error al crear la cuenta.';
                        $ins->close();
                    }
                } else {
                    $errors[] = 'Error de base de datos.';
                }
            }
        } else {
            $errors[] = 'Error de base de datos.';
        }
    }
}
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Crear cuenta</title>
<style>
body{font-family:Arial,Helvetica,sans-serif;background:#f5f7fb;display:flex;align-items:center;justify-content:center;height:100vh;margin:0}
.box{background:#fff;padding:20px;border-radius:10px;box-shadow:0 6px 18px rgba(0,0,0,.06);width:320px}
h1{color:#2b6cb0;font-size:18px;margin:0 0 12px}
.input{width:100%;padding:10px;border:1px solid #e6eef8;border-radius:8px;margin-bottom:10px}
.btn{width:100%;background:#2b6cb0;color:#fff;padding:10px;border:0;border-radius:8px;cursor:pointer}
.link{display:block;text-align:center;margin-top:10px;color:#2b6cb0;text-decoration:none}
.error{background:#fff5f5;color:#b91c1c;padding:8px;border-radius:6px;margin-bottom:10px;font-size:14px}
.toplink{display:inline-block;margin-bottom:8px;color:#2b6cb0;text-decoration:none;font-weight:600}
</style>
</head>
<body>
<div class="box">
    <a class="toplink" href="index.php">Volver al inicio</a>
    <h1>Crear cuenta</h1>

    <?php if (!empty($errors)): ?>
        <div class="error"><?= htmlspecialchars($errors[0], ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>

    <form method="post" action="crear_cuenta.php" autocomplete="off">
        <input class="input" name="username" type="text" placeholder="Usuario" value="<?= htmlspecialchars($_POST['username'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
        <input class="input" name="password" type="password" placeholder="Contraseña">
        <input class="input" name="password2" type="password" placeholder="Confirmar contraseña">
        <button class="btn" type="submit">Crear cuenta</button>
    </form>

    <a class="link" href="iniciar_sesion.php">Ir a iniciar sesión</a>
</div>
</body>
</html>