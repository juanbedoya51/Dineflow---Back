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


// Resto del código sin eliminar la sesión

// Obtener el ID del usuario desde la URL
$usuario_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Validar que el ID del usuario no sea 0
if ($usuario_id === 0) {
    $response["message"] = "Error: ID de usuario no proporcionado o inválido.";
} else {
    // Obtener todos los gastos del usuario actual
    $sql_gastos = "SELECT * FROM gastos WHERE id_usuarios = ?";
    $stmt_gastos = $conn->prepare($sql_gastos);
    $stmt_gastos->bind_param("i", $usuario_id);
    $stmt_gastos->execute();
    $result_gastos = $stmt_gastos->get_result();

    if ($result_gastos !== false && $result_gastos->num_rows > 0) {
        $response["status"] = "success";
        $response["message"] = "Gastos del usuario obtenidos con éxito.";

        // Formatear las fechas en formato ISO 8601 antes de enviarlas al cliente
    $gastosData = $result_gastos->fetch_all(MYSQLI_ASSOC);
    foreach ($gastosData as &$gasto) {
        $gasto["fecha_gasto"] = date('c', strtotime($gasto["fecha_gasto"]));
    }

        $response["data"] = $gastosData;
    } else {
        $response["message"] = "El usuario no tiene gastos asociados.";
    }

    // Cerrar la sentencia de gastos
    $stmt_gastos->close();
}

// Devolver la respuesta en formato JSON
header('Content-Type: application/json');
echo json_encode($response);

// Cerrar la conexión a la base de datos
$conn->close();
?>