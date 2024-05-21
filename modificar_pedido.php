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
$id_pedido = $data->cabecera->id_pedido;

// Modificar la cabecera del pedido en la tabla "pedido"
$sql = "UPDATE pedido SET fecha = ?, id_mesa = ?, id_usuarios = ?, id_ingresos = ? WHERE id_pedido = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("siiii", $data->cabecera->fecha, $data->cabecera->id_mesa, $data->cabecera->id_usuarios, $data->cabecera->id_ingresos, $id_pedido);
$stmt->execute();


// Modificar o insertar detalles del pedido en la tabla "pedidodetalle"
foreach ($data->detalle as $detalle) {
    $id_detalle = $detalle->id_detalle;

    if ($id_detalle == 0) {
        // Si el id_detalle es 0, insertar nuevo detalle
        $sql = "INSERT INTO pedidodetalle (id_pedido, id_platillo, cantidad, costo) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iiii", $id_pedido, $detalle->id_platillo, $detalle->cantidad, $detalle->costo);
        $stmt->execute();

        // Obtener el ID del detalle recién insertado
        $id_detalle = $stmt->insert_id;
    } else {
        // Si el id_detalle es diferente de 0, actualizar el detalle existente
        $sql = "UPDATE pedidodetalle SET id_platillo = ?, cantidad = ?, costo = ? WHERE id_detalle = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iiii", $detalle->id_platillo, $detalle->cantidad, $detalle->costo, $id_detalle);
        $stmt->execute();
    }

    // Actualizar el ID del detalle en la respuesta
    $detalle->id_detalle = $id_detalle;
    $detalle->id_pedido = $id_pedido;
}

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

$response["status"] = "success";
$response["message"] = "Pedido modificado con éxito.";
$response["data"] = $body;


// Devolver la respuesta en formato JSON
header('Content-Type: application/json');
echo json_encode($response);
?>
