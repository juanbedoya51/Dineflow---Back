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
    // Obtener el ID del usuario desde el URL
    $usuario_id = isset($_GET['id_usuarios']) ? intval($_GET['id_usuarios']) : 0;

    // Validar que el ID del usuario no sea 0
    if ($usuario_id === 0) {
        $response["message"] = "Error: ID de usuario no proporcionado o inválido.";
    } else {
        //calcula los dastos totales 
        $sql_sum = "SELECT SUM(monto) AS total FROM gastos WHERE id_usuarios = ?";
        $sql_sum = $conn->prepare($sql_sum);
        $sql_sum->bind_param("i", $usuario_id);
        $sql_sum->execute();
        $result_sum = $sql_sum->get_result();

        if ($result_sum !== false && $result_sum->num_rows > 0) {
            $result_sum = $result_sum->fetch_all(MYSQLI_ASSOC);
            $response["status"] = "success";
            $response["message"] = "Suma de gastos totales obtenida con éxito";
            $response["data"] = $result_sum;
        } else {
            $response["message"] = "Error al obtener la suma de gastos.";
        }
    }
}

// Cerrar la conexión a la base de datos
$conn->close();

// Devolver la respuesta en formato JSON
header('Content-Type: application/json');
echo json_encode($response);
?>