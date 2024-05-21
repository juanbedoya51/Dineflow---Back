<?php
// Configuración de la base de datos
$servername = "mysql8003.site4now.net";
$username = "aa209b_dineflo";
$password = "Juan1087*";
$database = "db_aa209b_dineflo";


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

session_start(); // Inicia la sesión

// Conexión a la base de datos
$conn = new mysqli($servername, $username, $password, $database);

// Recibir el JSON enviado por el método POST
$json = file_get_contents('php://input');
$data = json_decode($json);


// Verificar la conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

if (isset($data->id_pedido)) {
    // Si se recibe un ID de pedido, eliminar el registro correspondiente en la tabla "pedido"
    $id_pedido = $data->id_pedido;
    
    // Eliminar registros relacionados en la tabla "pedidodetalle"
    $delete_pedidodetalle_sql = "DELETE FROM pedidodetalle WHERE id_pedido = ?";
    $delete_pedidodetalle_stmt = $conn->prepare($delete_pedidodetalle_sql);
    $delete_pedidodetalle_stmt->bind_param("i", $id_pedido);
    $delete_pedidodetalle_stmt->execute();

    // Eliminar el registro en la tabla "pedido"
    $delete_pedido_sql = "DELETE FROM pedido WHERE id_pedido = ?";
    $delete_pedido_stmt = $conn->prepare($delete_pedido_sql);
    $delete_pedido_stmt->bind_param("i", $id_pedido);
    $delete_pedido_stmt->execute();

    $sql_update_mesa = "UPDATE mesa SET id_pedido = 0 WHERE id_pedido = ?";
$stmt_update_mesa = $conn->prepare($sql_update_mesa);
$stmt_update_mesa->bind_param("i", $id_pedido);
$stmt_update_mesa->execute();

    $response["status"] = "success";
    $response["message"] = "Pedido y detalles eliminados con éxito.";
    $response["data"] = null;

    // Devolver la respuesta en formato JSON
    header('Content-Type: application/json');
    echo json_encode($response);
}

// Cerrar la conexión
$conn->close();
?>
