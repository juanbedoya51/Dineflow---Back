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
$id_usuarios = $_GET['id'];

// Consulta SQL para obtener los pedidos
$sql = "SELECT * FROM pedido WHERE id_usuarios = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_usuarios);
$stmt->execute();

// Obtener resultados de la consulta
$result = $stmt->get_result();

// Crear un array para almacenar los resultados
$pedidos = array();

// Recorrer los resultados y almacenarlos en el array
while ($row = $result->fetch_assoc()) {
    // Formatear la fecha en formato ISO 8601
    $row["fecha"] = date('c', strtotime($row["fecha"]));
    $pedidos[] = $row;
}

// Cerrar la conexión
$conn->close();

// Devolver la respuesta en formato JSON
header('Content-Type: application/json');
echo json_encode($pedidos);
?>
