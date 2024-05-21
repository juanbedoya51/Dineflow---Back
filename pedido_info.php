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

// Verificar la conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Obtener el valor del parámetro "id" de la URL
$id_pedido = $_GET['id'];

// Consulta SQL para obtener la información del pedido y sus detalles
$sql = "SELECT p.id_pedido, p.fecha, p.id_mesa, p.id_usuarios, p.id_ingresos, p.pedido_numero, pd.id_detalle, pd.id_platillo, pd.cantidad, pd.costo, pl.nombre_platillo
        FROM pedido p
        JOIN pedidodetalle pd ON p.id_pedido = pd.id_pedido
        JOIN platillo pl ON pd.id_platillo = pl.id_platillo
        WHERE p.id_pedido = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_pedido);
$stmt->execute();

// Obtener resultados de la consulta
$result = $stmt->get_result();

// Crear arrays para almacenar los resultados
$datos_cabecera = null;
$datos_detalle = array();

// Recorrer los resultados y almacenarlos en los arrays correspondientes
while ($row = $result->fetch_assoc()) {
    if ($datos_cabecera === null) {
        $datos_cabecera = array(
            'id_pedido' => $row['id_pedido'],
            'fecha' => date('c', strtotime($row["fecha"])),
            'id_mesa' => $row['id_mesa'],
            'id_usuarios' => $row['id_usuarios'],
            'id_ingresos' => $row['id_ingresos'],
            'pedido_numero' => $row['pedido_numero']
        );
    }

    $datos_detalle[] = array(
        'id_detalle' => $row['id_detalle'],
        'id_pedido' => $row['id_pedido'],
        'id_platillo' => $row['id_platillo'],
        'nombre_platillo' => $row['nombre_platillo'],
        'cantidad' => $row['cantidad'],
        'costo' => $row['costo']
    );
}

// Cerrar la conexión
$conn->close();

// Crear el cuerpo de la respuesta
$body = array(
    'cabecera' => $datos_cabecera,
    'detalle' => $datos_detalle
);

// Crear la respuesta completa
$response = array(
    'status' => 'success',
    'message' => 'Pedido recuperado con éxito.',
    'data' => $body
);


// Devolver la respuesta en formato JSON
header('Content-Type: application/json');
echo json_encode($response);
?>
