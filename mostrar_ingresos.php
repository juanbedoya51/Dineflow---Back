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
    // Obtener todos los ingresos del usuario actual
    $sql_ingresos = "SELECT * FROM ingresos WHERE id_usuarios = ?";
    $stmt_ingresos = $conn->prepare($sql_ingresos);
    $stmt_ingresos->bind_param("i", $usuario_id);
    $stmt_ingresos->execute();
    $result_ingresos = $stmt_ingresos->get_result();

    if ($result_ingresos !== false && $result_ingresos->num_rows > 0) {
        $response["status"] = "success";
        $response["message"] = "ingresos del usuario obtenidos con éxito.";

        // Formatear las fechas en formato ISO 8601 antes de enviarlas al cliente
    $ingresosData = $result_ingresos->fetch_all(MYSQLI_ASSOC);
    foreach ($ingresosData as &$ingreso) {
        $ingreso["fecha_ingreso"] = date('c', strtotime($ingreso["fecha_ingreso"]));
    }

        $response["data"] = $ingresosData;
    } else {
        $response["message"] = "El usuario no tiene ingresos asociados.";
    }

    // Cerrar la sentencia de ingresos
    $stmt_ingresos->close();
}

// Devolver la respuesta en formato JSON
header('Content-Type: application/json');
echo json_encode($response);

// Cerrar la conexión a la base de datos
$conn->close();
?>