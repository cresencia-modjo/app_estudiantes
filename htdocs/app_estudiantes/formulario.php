<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: index.php');
    exit;
}

require_once 'includes/funciones.php';

 $estudiante = null; // Para el modo de agregar
 $tituloFormulario = "Agregar Nuevo Estudiante";
 $accionFormulario = "guardar";
 $botonTexto = "Guardar Estudiante";

// Modo de edición si se recibe un ID
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $estudiante = obtenerEstudiantePorId($_GET['id']);
    if ($estudiante) {
        $tituloFormulario = "Modificar Estudiante";
        $accionFormulario = "modificar";
        $botonTexto = "Guardar Cambios";
    } else {
        $_SESSION['mensaje'] = "Estudiante no encontrado.";
        $_SESSION['tipo_mensaje'] = "error";
        header('Location: lista.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $tituloFormulario ?></title>
    <link rel="stylesheet" href="css/estilos.css">
</head>
<body>
    <div class="form-container">
        <h1><?= $tituloFormulario ?></h1>
        <form action="operaciones.php" method="POST">
            <input type="hidden" name="accion" value="<?= $accionFormulario ?>">
            <?php if ($estudiante): ?>
                <input type="hidden" name="id_estudiante" value="<?= $estudiante['id'] ?>">
            <?php endif; ?>
            
            <div class="form-group">
                <label for="idEstudiante">ID:</label>
                <input type="text" id="idEstudiante" value="<?= $estudiante ? 'ES-' . str_pad($estudiante['id'], 3, '0', STR_PAD_LEFT) : 'Auto-generado' ?>" readonly>
            </div>
            <div class="form-group">
                <label for="nombre">Nombre Completo:</label>
                <input type="text" id="nombre" name="nombre" value="<?= htmlspecialchars($estudiante['nombre'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label for="edad">Edad:</label>
                <input type="number" id="edad" name="edad" value="<?= htmlspecialchars($estudiante['edad'] ?? '') ?>" min="1" required>
            </div>
            <div class="form-group">
                <label for="carrera">Carrera:</label>
                <input type="text" id="carrera" name="carrera" value="<?= htmlspecialchars($estudiante['carrera'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label for="promedio">Promedio:</label>
                <input type="number" id="promedio" name="promedio" value="<?= htmlspecialchars($estudiante['promedio'] ?? '') ?>" min="0" max="10" step="0.1" required>
            </div>
            
            <div class="button-group">
                <button type="submit" class="btn btn-primary"><?= $botonTexto ?></button>
                <?php if ($estudiante): ?>
                    <button type="submit" name="accion" value="eliminar" class="btn btn-danger" onclick="return confirm('¿Estás seguro de eliminar este estudiante?');">Eliminar</button>
                <?php endif; ?>
                <a href="lista.php" class="btn btn-dark" style="text-align:center; text-decoration:none; line-height: 40px;">Cerrar</a>
            </div>
        </form>
    </div>
    <div id="notificacion"></div>
    <script src="js/scripts.js"></script>
</body>
</html>