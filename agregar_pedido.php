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

// Insertar la cabecera del pedido en la tabla "pedido"
$sql = "INSERT INTO pedido (fecha, id_mesa, id_usuarios, pedido_numero, id_ingresos) VALUES (?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("siisi", $data->cabecera->fecha, $data->cabecera->id_mesa, $data->cabecera->id_usuarios, $data->cabecera->pedido_numero, $data->cabecera->id_ingresos);
$stmt->execute();

// Obtener el ID del pedido recién insertado
$id_pedido = $stmt->insert_id;

$pedido_numero = $data->cabecera->id_usuarios . "00" . $data->cabecera->id_mesa . "0" . $id_pedido;

$sql = "UPDATE pedido SET pedido_numero = ? WHERE id_pedido = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("si", $pedido_numero, $id_pedido);
$stmt->execute();

// Insertar detalles del pedido en la tabla "pedidodetalle"
foreach ($data->detalle as $detalle) {
    $sql = "INSERT INTO pedidodetalle (id_pedido, id_platillo, cantidad, costo) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iiii", $id_pedido, $detalle->id_platillo, $detalle->cantidad, $detalle->costo);
    $stmt->execute();

    // Actualizar el ID del detalle en la respuesta
    $detalle->id_detalle = $stmt->insert_id;
    $detalle->id_pedido = $id_pedido;
}

// Actualizar el campo id_pedido en la tabla "mesa"
$sql = "UPDATE mesa SET id_pedido = ? WHERE id_mesa = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $id_pedido, $data->cabecera->id_mesa);
$stmt->execute();

// Cerrar la conexión
$conn->close();

// Actualizar la respuesta con los ID asignados
$body = array(
    'cabecera' => $data->cabecera,
    'detalle' => $data->detalle
);

// Formatear la fecha en la respuesta
$body['cabecera']->fecha = date("Y-m-d H:i:s", strtotime($data->cabecera->fecha));
$body['cabecera']->id_pedido = $id_pedido;
$body['cabecera']->pedido_numero = $pedido_numero;

$response["status"] = "success";
$response["message"] = "Pedido agregado con éxito.";
$response["data"] = $body;


// Devolver la respuesta en formato JSON
header('Content-Type: application/json');
echo json_encode($response);
?>
