<?php
session_start();
require_once __DIR__ . '/conexion.php'; // proporciona $mysqli

function redirect($url) {
    header("Location: $url");
    exit;
}

$accion = $_POST['accion'] ?? $_GET['accion'] ?? '';

if ($accion === 'login') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $_SESSION['error'] = 'Usuario y contraseña requeridos.';
        redirect('iniciar_sesion.php');
    }

    $stmt = mysqli_prepare($mysqli, "SELECT id, password FROM usuarios WHERE username = ? LIMIT 1");
    if (!$stmt) {
        $_SESSION['error'] = 'Error en la consulta de login.';
        redirect('iniciar_sesion.php');
    }
    mysqli_stmt_bind_param($stmt, 's', $username);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);
    mysqli_stmt_bind_result($stmt, $id, $hash);
    if (mysqli_stmt_fetch($stmt)) {
        mysqli_stmt_close($stmt);
        if (password_verify($password, $hash)) {
            $_SESSION['user_id'] = $id;
            $_SESSION['username'] = $username;
            redirect('index.php');
        } else {
            $_SESSION['error'] = 'Credenciales inválidas.';
            redirect('iniciar_sesion.php');
        }
    } else {
        mysqli_stmt_close($stmt);
        $_SESSION['error'] = 'Usuario no encontrado.';
        redirect('iniciar_sesion.php');
    }

} elseif ($accion === 'logout') {
    session_unset();
    session_destroy();
    redirect('iniciar_sesion.php');

} elseif ($accion === 'guardar') {
    if (empty($_SESSION['user_id'])) {
        $_SESSION['error'] = 'Debe iniciar sesión para registrar estudiantes.';
        redirect('iniciar_sesion.php');
    }

    $nombre = trim($_POST['nombre'] ?? '');
    $edad = intval($_POST['edad'] ?? 0);
    $carrera = trim($_POST['carrera'] ?? '');
    $promedio = str_replace(',', '.', trim($_POST['promedio'] ?? '0'));
    $promedio = floatval($promedio);
    $usuario_id = intval($_SESSION['user_id']);

    if ($nombre === '' || $edad <= 0 || $carrera === '') {
        $_SESSION['error'] = 'Complete todos los campos obligatorios.';
        redirect('index.php');
    }

    $stmt = mysqli_prepare($mysqli, "INSERT INTO estudiantes (nombre, edad, carrera, promedio, usuario_id) VALUES (?, ?, ?, ?, ?)");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 'sisdi', $nombre, $edad, $carrera, $promedio, $usuario_id);
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['msg'] = 'Estudiante registrado correctamente.';
        } else {
            $_SESSION['error'] = 'Error al registrar estudiante: ' . mysqli_stmt_error($stmt);
        }
        mysqli_stmt_close($stmt);
    } else {
        $_SESSION['error'] = 'Error en la consulta de inserción.';
    }
    redirect('index.php');

} elseif ($accion === 'actualizar_estudiante') {
    if (empty($_SESSION['user_id'])) {
        $_SESSION['error'] = 'Debe iniciar sesión para actualizar estudiantes.';
        redirect('iniciar_sesion.php');
    }

    $id = intval($_POST['id'] ?? 0);
    $nombre = trim($_POST['nombre'] ?? '');
    $edad = intval($_POST['edad'] ?? 0);
    $carrera = trim($_POST['carrera'] ?? '');
    $promedio = str_replace(',', '.', trim($_POST['promedio'] ?? '0'));
    $promedio = floatval($promedio);

    if ($id <= 0 || $nombre === '' || $edad <= 0 || $carrera === '') {
        $_SESSION['error'] = 'Datos inválidos para actualizar.';
        redirect('index.php');
    }

    $stmt = mysqli_prepare($mysqli, "UPDATE estudiantes SET nombre = ?, edad = ?, carrera = ?, promedio = ? WHERE id = ?");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 'sisdi', $nombre, $edad, $carrera, $promedio, $id);
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['msg'] = 'Estudiante actualizado.';
        } else {
            $_SESSION['error'] = 'Error al actualizar: ' . mysqli_stmt_error($stmt);
        }
        mysqli_stmt_close($stmt);
    } else {
        $_SESSION['error'] = 'Error en la consulta de actualización.';
    }
    redirect('index.php');

} elseif ($accion === 'eliminar_estudiante') {
    if (empty($_SESSION['user_id'])) {
        $_SESSION['error'] = 'Debe iniciar sesión para eliminar estudiantes.';
        redirect('iniciar_sesion.php');
    }

    $id = intval($_POST['id'] ?? 0);
    if ($id <= 0) {
        $_SESSION['error'] = 'ID inválido.';
        redirect('index.php');
    }

    $stmt = mysqli_prepare($mysqli, "DELETE FROM estudiantes WHERE id = ?");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 'i', $id);
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['msg'] = 'Estudiante eliminado.';
        } else {
            $_SESSION['error'] = 'Error al eliminar: ' . mysqli_stmt_error($stmt);
        }
        mysqli_stmt_close($stmt);
    } else {
        $_SESSION['error'] = 'Error en la consulta de eliminación.';
    }
    redirect('index.php');

} elseif ($accion === 'buscar') {
    $q = trim($_POST['q'] ?? '');
    $q_enc = urlencode($q);
    redirect("index.php?q={$q_enc}");

} else {
    redirect('index.php');
}
?>
// ...existing code...