<?php
// Iniciar la sesión si no está iniciada
session_start();


// Habilitar CORS solo para tu aplicación Blazor WebAssembly
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    // Respuesta preflight para solicitudes CORS
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Authorization");
    header("Access-Control-Max-Age: 3600");
    exit; // No proceses la solicitud en este caso
}

// Permitir solicitudes desde tu aplicación Blazor WebAssembly
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
// Verificar si hay una sesión iniciada
if (!isset($_SESSION['nombre'])) {
    // Si no hay sesión iniciada, enviar un mensaje de error
    $response = array(
        'status' => 'error',
        'message' => 'Sesión no iniciada. No se puede cerrar sesión.'
    );

    // Enviar la respuesta como JSON
    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}

// Destruir todas las variables de sesión
$_SESSION = array();

// Si se desea eliminar la cookie de sesión, es posible hacerlo así
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// Finalmente, destruir la sesión
session_destroy();

// Redireccionar a la página de inicio de sesión o a otra página después de cerrar sesión
$response = array(
    'status' => 'success',
    'message' => 'Sesión cerrada con éxito.'
);

// Enviar la respuesta como JSON
header('Content-Type: application/json');
echo json_encode($response);
exit();
?>


