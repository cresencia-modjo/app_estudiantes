<?php
// Cerrar sesión y redirigir al inicio
if (session_status() !== PHP_SESSION_ACTIVE) session_start();

// Limpiar todas las variables de sesión
$_SESSION = [];

// Borrar la cookie de sesión si existe
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params['path'], $params['domain'],
        $params['secure'], $params['httponly']
    );
}

// Destruir la sesión
session_destroy();

// Opcional: establecer mensaje flash antes de redirigir
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
$_SESSION['mensaje'] = 'Has cerrado sesión correctamente.';
$_SESSION['tipo_mensaje'] = 'exito';

header('Location: index.php');
exit;

?>