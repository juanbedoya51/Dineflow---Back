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
        // Obtener todas las mesas del usuario actual
        $sql_mesas = "SELECT * FROM mesa WHERE id_usuarios = ?";
        $stmt_mesas = $conn->prepare($sql_mesas);
        $stmt_mesas->bind_param("i", $usuario_id);
        $stmt_mesas->execute();
        $result_mesas = $stmt_mesas->get_result();

        if ($result_mesas !== false && $result_mesas->num_rows > 0) {
            $response["status"] = "succes";
            $response["message"] = "Mesas del usuario obtenidas con éxito.";
            $response["data"] = $result_mesas->fetch_all(MYSQLI_ASSOC);
        } else {
            $response["message"] = "El usuario no tiene mesas asociadas.";
        }

        // Cerrar la sentencia de mesas
        $stmt_mesas->close();    
    } 

// Devolver la respuesta en formato JSON
header('Content-Type: application/json');
echo json_encode($response);

// Cerrar la conexión a la base de datos
$conn->close();
?>

