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


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Obtener los datos para eliminar el ingreso
    $data = json_decode(file_get_contents("php://input"), true);
    $usuario_id = isset($data["id_usuarios"]) ? $data["id_usuarios"] : 0;
    $ingresos_id = isset($data["id_ingresos"]) ? $data["id_ingresos"] : "";

    // Validar que el ID del usuario no sea 0
    if ($usuario_id === 0) {
        $response["message"] = "Error: ID de usuario no proporcionado o inválido.";
    } elseif (empty($ingresos_id)) {
        $response["message"] = "Error: El ID del ingreso no puede estar vacío.";
    } else {
        // Verificar si el ingreso pertenece al usuario actual antes de eliminarlo
        $sql_check = "SELECT * FROM ingresos WHERE id_ingresos = ? AND id_usuarios = ?";
        $stmt_check = $conn->prepare($sql_check);
        $stmt_check->bind_param("ii", $ingresos_id, $usuario_id);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();

        if ($result_check->num_rows > 0) {
            // Eliminar el ingreso si pertenece al usuario actual
            $sql_delete = "DELETE FROM ingresos WHERE id_ingresos = ?";
            $stmt_delete = $conn->prepare($sql_delete);
            $stmt_delete->bind_param("i", $ingresos_id);

            if ($stmt_delete->execute()) {
                $response["status"] = 'success';
                $response["message"] = "Ingreso eliminado con éxito.";
            } else {
                $response["message"] = "Error al eliminar el ingreso: " . $stmt_delete->error;
            }

            // Cerrar la sentencia de eliminación
            $stmt_delete->close();
        } else {
            $response["message"] = "Error: El ingreso no pertenece al usuario actual.";
        }

        // Liberar resultados
        $stmt_check->close();
    }
}

// Enviar respuesta como JSON
header('Content-Type: application/json');
echo json_encode($response);

// Cerrar la conexión a la base de datos
$conn->close();
?>