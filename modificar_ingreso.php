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
        // Obtener los datos del formulario
        $data = json_decode(file_get_contents("php://input"), true);
        $id_ingresos = isset($data["id_ingresos"]) ? $data["id_ingresos"] : "";
        $fecha_ingreso = isset($data["fecha_ingreso"]) ? date('Y-m-d H:i:s', strtotime($data["fecha_ingreso"])) : "";
        $monto = isset($data["monto"]) ? $data["monto"] : "";
        $descripcion_ingreso = isset($data["descripcion_ingreso"]) ? $data["descripcion_ingreso"] : "";
        $usuario_id = isset($data["id_usuarios"]) ? $data["id_usuarios"] : 0;

        // Validar que los datos obligatorios no estén vacíos
        if (empty($id_ingresos)) {
            $response["message"] = "Error: El ID del ingreso es obligatorio";
        } else {
            // Continuar con el proceso de actualización usando $usuario_id
            // Actualizar los datos del ingreso en la base de datos utilizando sentencia preparada
            $sql_update = "UPDATE ingresos SET fecha_ingreso = ?, monto = ?, 
                                descripcion_ingreso = ? WHERE id_ingresos = ? AND id_usuarios = ?";

            $stmt_update = $conn->prepare($sql_update);
            $stmt_update->bind_param("sssii", $fecha_ingreso, $monto, $descripcion_ingreso, $id_ingresos, $usuario_id);

            if ($stmt_update->execute()) {
                // Obtener la información completa del ingreso recién actualizado
                $sql_select = "SELECT * FROM ingresos WHERE id_ingresos = $id_ingresos";
                $result_select = $conn->query($sql_select);

                if ($result_select !== false && $result_select->num_rows > 0) {
                    $row_select = $result_select->fetch_assoc();
                    $response['status'] = 'success';
                    $response["message"] = "Ingreso actualizado con éxito";
                    $response["data"] = $row_select;
                } else {
                    $response["message"] = "Error al obtener la información del ingreso recién actualizado.";
                }

                // Cerrar el resultado de la selección
                $result_select->close();
            } else {
                $response["message"] = "Error al actualizar ingreso: " . $stmt_update->error;
            }

            // Cerrar la sentencia de actualización
            $stmt_update->close();
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