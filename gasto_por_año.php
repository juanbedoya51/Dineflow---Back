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
        // Calcular la suma de los montos de gastos por año
        $sql_sum_anio = "SELECT YEAR(fecha_gasto) AS year, SUM(monto) AS total FROM gastos WHERE id_usuarios = ? GROUP BY year";
        $stmt_sum_anio = $conn->prepare($sql_sum_anio);
        $stmt_sum_anio->bind_param("i", $usuario_id);
        $stmt_sum_anio->execute();
        $result_sum_anio = $stmt_sum_anio->get_result();

        if ($result_sum_anio !== false && $result_sum_anio->num_rows > 0) {
            $rows_sum_anio = $result_sum_anio->fetch_all(MYSQLI_ASSOC);
            $response["status"] = "success";
            $response["message"] = "Suma de gastos por año obtenida con éxito";
            $response["data"] = $rows_sum_anio;
        } else {
            $response["message"] = "Error al obtener la suma de gastos por año.";
        }
    }
}

// Cerrar la conexión a la base de datos
$conn->close();

// Devolver la respuesta en formato JSON
header('Content-Type: application/json');
echo json_encode($response);
?>