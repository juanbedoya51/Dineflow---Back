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
