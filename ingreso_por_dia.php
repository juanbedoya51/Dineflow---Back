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

// Respuesta por defecto
$response = array('status' => 'error', 'message' => 'Credenciales incorrectas', 'data' => null);

// Verificar la conexión
if ($conn->connect_error) {
    $response["message"] = "Error de conexión a la base de datos: " . $conn->connect_error;
} else {
    // Obtener el ID del usuario desde la URL
    $usuario_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

    // Validar que el ID del usuario no sea 0
    if ($usuario_id === 0) {
        $response["message"] = "Error: ID de usuario no proporcionado o inválido.";
    } else {
        // Calcular la suma de los montos de ingresos por día
        $sql_sum = "SELECT DATE(fecha_ingreso) as fecha, SUM(monto) AS total FROM ingresos WHERE id_usuarios = ? GROUP BY fecha";
        $stmt_sum = $conn->prepare($sql_sum);
        $stmt_sum->bind_param("i", $usuario_id);
        $stmt_sum->execute();
        $result_sum = $stmt_sum->get_result();

        if ($result_sum !== false && $result_sum->num_rows > 0) {
            $rows_sum = $result_sum->fetch_all(MYSQLI_ASSOC);
            $response["status"] = "success";
            $response["message"] = "Suma de ingresos por día obtenida con éxito";
            $response["data"] = $rows_sum;
        } else {
            $response["message"] = "Error al obtener la suma de ingresos por día.";
        }
    }
}

// Cerrar la conexión a la base de datos
$conn->close();

// Devolver la respuesta en formato JSON
header('Content-Type: application/json');
echo json_encode($response);
?>