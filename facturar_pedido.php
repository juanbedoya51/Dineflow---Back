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

// Obtener el id_pedido del JSON
$id_pedido = $data->id_pedido;

// Consulta SQL para obtener la suma de (costo * cantidad) de pedidodetalle
$sql_suma = "SELECT SUM(costo * cantidad) as total_costo FROM pedidodetalle WHERE id_pedido = ?";
$stmt_suma = $conn->prepare($sql_suma);
$stmt_suma->bind_param("i", $id_pedido);
$stmt_suma->execute();
$result_suma = $stmt_suma->get_result();
$row_suma = $result_suma->fetch_assoc();
$total_costo = $row_suma['total_costo'];

// Obtener información de la tabla "pedido"
$sql_pedido_info = "SELECT pedido_numero, id_usuarios, id_mesa FROM pedido WHERE id_pedido = ?";
$stmt_pedido_info = $conn->prepare($sql_pedido_info);
$stmt_pedido_info->bind_param("i", $id_pedido);
$stmt_pedido_info->execute();
$result_pedido_info = $stmt_pedido_info->get_result();
$row_pedido_info = $result_pedido_info->fetch_assoc();

$pedido_numero = $row_pedido_info['pedido_numero'];
$id_usuarios = $row_pedido_info['id_usuarios'];
$id_mesa = $row_pedido_info['id_mesa'];

$cadena = "ingreso del pedido #" . $pedido_numero;
// Insertar un nuevo registro en la tabla "ingresos"
$sql_ingresos = "INSERT INTO ingresos (fecha_ingreso, monto, descripcion_ingreso, id_usuarios) VALUES (NOW(), ?, ?, ?)";
$stmt_ingresos = $conn->prepare($sql_ingresos);
$stmt_ingresos->bind_param("dss", $total_costo, $cadena, $id_usuarios);
$stmt_ingresos->execute();

// Obtener el ID del ingreso recién insertado
$id_ingresos = $stmt_ingresos->insert_id;

// Actualizar el campo "id_ingresos" en la tabla "pedido"
$sql_update_pedido = "UPDATE pedido SET id_ingresos = ? WHERE id_pedido = ?";
$stmt_update_pedido = $conn->prepare($sql_update_pedido);
$stmt_update_pedido->bind_param("ii", $id_ingresos, $id_pedido);
$stmt_update_pedido->execute();

// Actualizar el campo "id_pedido" en la tabla "mesa"
$sql_update_mesa = "UPDATE mesa SET id_pedido = 0 WHERE id_mesa = ?";
$stmt_update_mesa = $conn->prepare($sql_update_mesa);
$stmt_update_mesa->bind_param("i", $id_mesa);
$stmt_update_mesa->execute();

// Cerrar la conexión
$conn->close();

// Respuesta exitosa
$response["status"] = "success";
$response["message"] = "Pedido facturado con éxito.";

// Devolver la respuesta en formato JSON
header('Content-Type: application/json');
echo json_encode($response);
?>
