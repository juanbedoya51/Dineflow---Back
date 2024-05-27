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
        // Calcular la suma de los montos de gastos por mes
        $sql_sum_mes = "SELECT YEAR(fecha_gasto) AS year, MONTH(fecha_gasto) AS mes, SUM(monto) AS total FROM gastos WHERE id_usuarios = ? GROUP BY year, mes";
        $stmt_sum_mes = $conn->prepare($sql_sum_mes);
        $stmt_sum_mes->bind_param("i", $usuario_id);
        $stmt_sum_mes->execute();
        $result_sum_mes = $stmt_sum_mes->get_result();

        if ($result_sum_mes !== false && $result_sum_mes->num_rows > 0) {
            $rows_sum_mes = $result_sum_mes->fetch_all(MYSQLI_ASSOC);
            $response["status"] = "success";
            $response["message"] = "Suma de gastos por mes obtenida con éxito";
            $response["data"] = $rows_sum_mes;
        } else {
            $response["message"] = "Error al obtener la suma de gastos por mes.";
        }
    }
}

// Cerrar la conexión a la base de datos
$conn->close();

// Devolver la respuesta en formato JSON
header('Content-Type: application/json');
echo json_encode($response);
?>