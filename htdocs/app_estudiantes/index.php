<?php
require_once __DIR__ . '/conexion.php';
if (session_status() !== PHP_SESSION_ACTIVE) session_start();

// Si el usuario inició sesión desde `iniciar_sesion.php` (usa user_id),
// marcar also `loggedin` para compatibilidad con `operaciones.php`.
if (isset($_SESSION['user_id']) && empty($_SESSION['loggedin'])) {
    $_SESSION['loggedin'] = true;
}

$estudiantes = [];
$search = trim($_GET['q'] ?? '');

// Solo cargar estudiantes si el usuario está autenticado
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    if ($search !== '') {
        $like = "%" . $search . "%";
        $stmt = $mysqli->prepare("SELECT id, nombre, edad, carrera, promedio FROM estudiantes WHERE nombre LIKE ? OR carrera LIKE ? ORDER BY nombre");
        if ($stmt) {
            $stmt->bind_param('ss', $like, $like);
            $stmt->execute();
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $estudiantes[] = $row;
            }
            $stmt->close();
        }
    } else {
        $query = "SELECT id, nombre, edad, carrera, promedio FROM estudiantes ORDER BY nombre";
        if ($result = $mysqli->query($query)) {
            while ($row = $result->fetch_assoc()) {
                $estudiantes[] = $row;
            }
            $result->free();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Estudiantes registrados</title>
    <style>
        :root{--bg:#f5f7fb;--card:#ffffff;--accent:#2b6cb0;--muted:#6b7280;}
        *{box-sizing:border-box;font-family:Inter,Segoe UI,Arial,sans-serif}
        body{margin:0;background:linear-gradient(180deg,#eef2ff 0%,var(--bg) 100%);color:#111827}
        .header{display:flex;align-items:center;justify-content:space-between;padding:20px 28px}
        .brand{display:flex;gap:12px;align-items:center}
        .logo{width:44px;height:44px;border-radius:8px;background:var(--accent);display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700}
        h1{margin:0;font-size:20px}
        .top-actions a{display:inline-block;padding:10px 14px;background:transparent;border:2px solid var(--accent);color:var(--accent);border-radius:8px;text-decoration:none;font-weight:600}
        .container{max-width:980px;margin:18px auto;padding:20px}
        .card{background:var(--card);border-radius:12px;padding:18px;box-shadow:0 6px 18px rgba(15,23,42,0.06)}
        .panel-title{display:flex;align-items:center;justify-content:space-between;margin-bottom:12px}
        .panel-title h2{margin:0;font-size:18px}
        .count{color:var(--muted);font-size:14px}
        table{width:100%;border-collapse:collapse;margin-top:8px}
        th,td{padding:12px 10px;text-align:left;border-bottom:1px solid #eef2f6}
        th{color:var(--muted);font-weight:600;font-size:13px}
        td{font-size:14px}
        .empty{padding:28px;text-align:center;color:var(--muted)}
        .btn-primary{background:var(--accent);color:#fff;padding:8px 12px;border-radius:8px;text-decoration:none;font-weight:600}
        @media (max-width:640px){
            th,td{padding:10px 6px}
            .header{padding:12px}
            .container{padding:12px}
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="brand">
            <div class="logo">E</div>
            <div>
                <h1>Panel de Estudiantes</h1>
                <div style="font-size:13px;color:var(--muted)">Registros guardados en la base de datos</div>
            </div>
        </div>
        <div style="display:flex;align-items:center;gap:10px">
            <form method="get" action="index.php" style="margin:0">
                <input name="q" type="search" placeholder="Buscar estudiantes..." value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>" style="padding:8px;border-radius:8px;border:1px solid #dbeafe">
                <button type="submit" style="margin-left:6px;padding:8px 10px;border-radius:8px;border:0;background:var(--accent);color:#fff;">Buscar</button>
            </form>
            <div class="top-actions">
                <?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true): ?>
                    <a href="logout.php" style="display:inline-block;padding:10px 14px;background:transparent;border:2px solid var(--accent);color:var(--accent);border-radius:8px;text-decoration:none;font-weight:600">Cerrar sesión</a>
                <?php else: ?>
                    <a href="iniciar_sesion.php">Iniciar sesión</a>
                    <a href="crear_cuenta.php" style="margin-left:8px">Crear cuenta</a>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <main class="container">
        <?php if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true): ?>
            <div class="card" style="max-width:660px;margin:80px auto;text-align:center">
                <h2>Panel de Estudiantes</h2>
                <p style="color:var(--muted)">Para ver y registrar estudiantes debe iniciar sesión o crear una cuenta.</p>
                <div style="margin-top:18px;display:flex;gap:10px;justify-content:center">
                   
                </div>
            </div>
        <?php else: ?>
            <div style="display:grid;grid-template-columns:1fr 1.2fr;gap:18px">
                <!-- Formulario de registro (sección similar a formulario.php) -->
                <div class="card">
                    <h2>Registrar estudiante</h2>
                    <form action="operaciones.php" method="post">
                        <input type="hidden" name="accion" value="guardar">
                        <div style="margin-top:12px">
                            <label>Nombre completo</label>
                            <input name="nombre" type="text" required style="width:100%;padding:8px;border-radius:8px;border:1px solid #e6eef8;margin-top:6px">
                        </div>
                        <div style="margin-top:12px;display:flex;gap:8px">
                            <div style="flex:1">
                                <label>Edad</label>
                                <input name="edad" type="number" min="1" required style="width:100%;padding:8px;border-radius:8px;border:1px solid #e6eef8;margin-top:6px">
                            </div>
                            <div style="flex:1">
                                <label>Carrera</label>
                                <input name="carrera" type="text" required style="width:100%;padding:8px;border-radius:8px;border:1px solid #e6eef8;margin-top:6px">
                            </div>
                        </div>
                        <div style="margin-top:12px">
                            <label>Promedio</label>
                            <input name="promedio" type="number" min="0" max="10" step="0.01" required style="width:100%;padding:8px;border-radius:8px;border:1px solid #e6eef8;margin-top:6px">
                        </div>
                        <div style="margin-top:14px;display:flex;gap:8px">
                            <button type="submit" class="btn-primary">Guardar estudiante</button>
                            <a href="lista.php" class="btn-primary" style="background:#475569">Ver lista</a>
                        </div>
                    </form>
                </div>

                <!-- Lista de estudiantes -->
                <div class="card">
                    <div class="panel-title">
                        <h2>Estudiantes registrados</h2>
                        <div class="count"><?= count($estudiantes) ?> inscritos</div>
                    </div>

                    <?php if (count($estudiantes) === 0): ?>
                        <div class="empty">No hay estudiantes registrados aún.</div>
                    <?php else: ?>
                        <div style="overflow:auto">
                        <table>
                            <thead>
                                <tr>
                                    <th>Nombre</th>
                                    <th>Edad</th>
                                    <th>Carrera</th>
                                    <th>Promedio</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($estudiantes as $e): ?>
                                <tr>
                                    <td><?= htmlspecialchars($e['nombre'], ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars($e['edad'], ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars($e['carrera'], ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars($e['promedio'], ENT_QUOTES, 'UTF-8') ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </main>
</body>
</html>