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

// Inicializar la respuesta
$response = array("status" => "error", "message" => "", "data" => null);

// Verificar la conexión
if ($conn->connect_error) {
    $response["message"] = "Error de conexión a la base de datos: " . $conn->connect_error;
} else {
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Obtener los datos para eliminar el proveedor
        $data = json_decode(file_get_contents("php://input"), true);
        $proveedor_id = isset($data["id_proveedores"]) ? $data["id_proveedores"] : "";

        // Validar que el id del proveedor no esté vacío
        if (empty($proveedor_id)) {
            $response["message"] = "Error: El id del proveedor no puede estar vacío.";
        } else {
            // Eliminar el proveedor de la base de datos
            $sql_delete = "DELETE FROM proveedores WHERE id_proveedores = ?";
            $stmt_delete = $conn->prepare($sql_delete);
            $stmt_delete->bind_param("i", $proveedor_id);

            if ($stmt_delete->execute()) {
                $response['status'] = 'success';
                $response["message"] = "Proveedor eliminado con éxito.";
            } else {
                $response["message"] = "Error al eliminar el proveedor: " . $stmt_delete->error;
            }

            // Cerrar la sentencia de eliminación
            $stmt_delete->close();
        }
    } else {
        $response["message"] = "Error: Método de solicitud no válido.";
    }
}

// Devolver la respuesta en formato JSON
header('Content-Type: application/json');
echo json_encode($response);

// Cerrar la conexión a la base de datos
$conn->close();
?>
