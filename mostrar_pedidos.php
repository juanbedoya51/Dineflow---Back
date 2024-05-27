<?php
 require_once "./SERVER.PHP";
// Configuración de la base de datos
$servername = SERVER;
$username = USER;
$password = PASS;
$database = DB;

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
