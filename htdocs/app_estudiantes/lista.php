<?php
require_once __DIR__ . '/conexion.php';
if (session_status() !== PHP_SESSION_ACTIVE) session_start();

// Redirigir si no está autenticado
if (empty($_SESSION['user_id'])) {
    header("Location: iniciar_sesion.php");
    exit;
}

$estudiantes = [];
$filtro_nombre = trim($_GET['nombre'] ?? '');
$filtro_carrera = trim($_GET['carrera'] ?? '');

// Construir consulta con filtros
$query = "SELECT id, nombre, edad, carrera, promedio FROM estudiantes WHERE usuario_id = ? ";
$params = [$_SESSION['user_id']];
$types = 'i';

if ($filtro_nombre !== '') {
    $query .= "AND nombre LIKE ? ";
    $params[] = "%$filtro_nombre%";
    $types .= 's';
}

if ($filtro_carrera !== '') {
    $query .= "AND carrera LIKE ? ";
    $params[] = "%$filtro_carrera%";
    $types .= 's';
}

$query .= "ORDER BY nombre ASC";

$stmt = $mysqli->prepare($query);
if ($stmt) {
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $estudiantes[] = $row;
    }
    $stmt->close();
}

// Obtener lista de carreras para dropdown
$carrieres = [];
$query_carreras = "SELECT DISTINCT carrera FROM estudiantes WHERE usuario_id = ? ORDER BY carrera ASC";
$stmt = $mysqli->prepare($query_carreras);
if ($stmt) {
    $stmt->bind_param('i', $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $carrieres[] = $row['carrera'];
    }
    $stmt->close();
}

// Mostrar mensaje si existe
$msg = $_SESSION['msg'] ?? '';
$error = $_SESSION['error'] ?? '';
unset($_SESSION['msg'], $_SESSION['error']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Listado de Estudiantes</title>
    <style>
        :root{--bg:#f5f7fb;--card:#ffffff;--accent:#2b6cb0;--muted:#6b7280;--danger:#dc2626;}
        *{box-sizing:border-box;font-family:Inter,Segoe UI,Arial,sans-serif}
        body{margin:0;background:linear-gradient(180deg,#eef2ff 0%,var(--bg) 100%);color:#111827}
        .header{display:flex;align-items:center;justify-content:space-between;padding:20px 28px}
        .brand{display:flex;gap:12px;align-items:center}
        .logo{width:44px;height:44px;border-radius:8px;background:var(--accent);display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:20px}
        h1{margin:0;font-size:20px}
        .top-actions a{display:inline-block;padding:10px 14px;background:transparent;border:2px solid var(--accent);color:var(--accent);border-radius:8px;text-decoration:none;font-weight:600}
        .container{max-width:1200px;margin:18px auto;padding:20px}
        .card{background:var(--card);border-radius:12px;padding:18px;box-shadow:0 6px 18px rgba(15,23,42,0.06)}
        .filters{display:grid;grid-template-columns:1fr 1fr auto;gap:12px;margin-bottom:16px;align-items:end}
        .form-group{display:flex;flex-direction:column}
        .form-group label{font-weight:600;font-size:13px;margin-bottom:6px;color:var(--muted)}
        .form-group input,.form-group select{padding:8px;border-radius:8px;border:1px solid #dbeafe;font-size:14px}
        .btn{padding:8px 12px;border-radius:8px;border:none;font-weight:600;cursor:pointer}
        .btn-primary{background:var(--accent);color:#fff}
        .btn-primary:hover{background:#1e4d7b}
        .btn-danger{background:var(--danger);color:#fff;padding:6px 10px;font-size:12px}
        .btn-danger:hover{background:#b91c1c}
        .btn-secondary{background:#475569;color:#fff}
        .btn-secondary:hover{background:#334155}
        .panel-title{display:flex;align-items:center;justify-content:space-between;margin-bottom:12px}
        .panel-title h2{margin:0;font-size:18px}
        .count{color:var(--muted);font-size:14px}
        .alert{padding:12px 16px;border-radius:8px;margin-bottom:16px}
        .alert-success{background:#dcfce7;color:#166534;border:1px solid #86efac}
        .alert-error{background:#fee2e2;color:#991b1b;border:1px solid #fca5a5}
        table{width:100%;border-collapse:collapse;margin-top:8px}
        thead{background:#f9fafb}
        th,td{padding:12px 10px;text-align:left;border-bottom:1px solid #eef2f6}
        th{color:var(--muted);font-weight:600;font-size:13px}
        td{font-size:14px}
        .empty{padding:28px;text-align:center;color:var(--muted)}
        .actions{display:flex;gap:6px}
        @media (max-width:768px){
            .filters{grid-template-columns:1fr;gap:8px}
            .header{flex-direction:column;align-items:flex-start;gap:12px}
            .top-actions{width:100%;display:flex;gap:8px}
            th,td{padding:8px 6px;font-size:12px}
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="brand">
            <div class="logo">E</div>
            <div>
                <h1>Listado de Estudiantes</h1>
                <div style="font-size:13px;color:var(--muted)">Administra y consulta registrados</div>
            </div>
        </div>
        <div class="top-actions">
            <a href="index.php">← Volver al panel</a>
            <a href="logout.php" style="margin-left:8px">Cerrar sesión</a>
        </div>
    </header>

    <main class="container">
        <?php if ($msg): ?>
            <div class="alert alert-success"><?= htmlspecialchars($msg, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>

        <div class="card">
            <div class="panel-title">
                <h2>Estudiantes registrados</h2>
                <div class="count"><?= count($estudiantes) ?> encontrados</div>
            </div>

            <form method="get" action="lista.php" style="margin-bottom:12px">
                <div class="filters">
                    <div class="form-group">
                        <label for="nombre">Filtrar por nombre</label>
                        <input type="text" id="nombre" name="nombre" placeholder="Ej: Juan..." value="<?= htmlspecialchars($filtro_nombre, ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <div class="form-group">
                        <label for="carrera">Filtrar por carrera</label>
                        <select id="carrera" name="carrera">
                            <option value="">-- Todas las carreras --</option>
                            <?php foreach ($carrieres as $carrera): ?>
                                <option value="<?= htmlspecialchars($carrera, ENT_QUOTES, 'UTF-8') ?>" <?= ($filtro_carrera === $carrera) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($carrera, ENT_QUOTES, 'UTF-8') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary" style="align-self:flex-end">Filtrar</button>
                </div>
            </form>

            <?php if (count($estudiantes) === 0): ?>
                <div class="empty">No hay estudiantes registrados que coincidan con los filtros.</div>
            <?php else: ?>
                <div style="overflow-x:auto">
                    <table>
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Edad</th>
                                <th>Carrera</th>
                                <th>Promedio</th>
                                <th style="text-align:center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($estudiantes as $e): ?>
                            <tr>
                                <td><?= htmlspecialchars($e['nombre'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($e['edad'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($e['carrera'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($e['promedio'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td style="text-align:center">
                                    <form action="operaciones.php" method="post" style="display:inline" onsubmit="return confirm('¿Eliminar a <?= htmlspecialchars($e['nombre'], ENT_QUOTES, 'UTF-8') ?>?');">
                                        <input type="hidden" name="accion" value="eliminar_estudiante">
                                        <input type="hidden" name="id" value="<?= intval($e['id']) ?>">
                                        <button type="submit" class="btn btn-danger">Eliminar</button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>
