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
    die("Error de conexión a la base de datos: " . $conn->connect_error);
}


// Obtener el id del usuario desde la URL
$usuario_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Validar que el id del usuario no sea 0
if ($usuario_id === 0) {
    $response["message"] = "Error: id de usuario no proporcionado o inválido.";
} else {
        // Obtener todos los platillos del usuario actual
        $sql_platillos = "SELECT * FROM platillo WHERE id_usuarios = ?";
        $stmt_platillos = $conn->prepare($sql_platillos);
        $stmt_platillos->bind_param("i", $usuario_id);
        $stmt_platillos->execute();
        $result_platillos = $stmt_platillos->get_result();

        if ($result_platillos !== false && $result_platillos->num_rows > 0) {
            $response["status"] = "success";
            $response["message"] = "Platillos del usuario obtenidos con éxito.";
            $response["data"] = $result_platillos->fetch_all(MYSQLI_ASSOC);
        } else {
            $response["message"] = "El usuario no tiene platillos asociados.";
        }

        // Cerrar la sentencia de platillos
        $stmt_platillos->close();
} 

  


// Devolver la respuesta en formato JSON
header('Content-Type: application/json');
echo json_encode($response);

// Cerrar la conexión a la base de datos
$conn->close();
?>
