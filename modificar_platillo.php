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
        $id_platillo = isset($data["id_platillo"]) ? $data["id_platillo"] : "";
        $nombre_platillo = isset($data["nombre_platillo"]) ? $data["nombre_platillo"] : "";
        $costo = isset($data["costo"]) ? $data["costo"] : "";
        $descripcion_platillo = isset($data["descripcion_platillo"]) ? $data["descripcion_platillo"] : "";
        $imagen = isset($data["imagen"]) ? $data["imagen"] : "";
        $usuario_id = isset($data["id_usuarios"]) ? $data["id_usuarios"] : 0;

        // Validar que los datos obligatorios no estén vacíos
        if (empty($id_platillo)) {
            $response["message"] = "Error: El ID del platillo es obligatorio.";
        } else {
            // Obtener el ID del usuario desde el resultado
            $usuario_id = obtenerUsuarioId($conn, $usuario_id);

            // Continuar con el proceso de actualización usando $usuario_id
            // Actualizar los datos del platillo en la base de datos utilizando sentencia preparada
            $sql_update = "UPDATE platillo SET nombre_platillo = ?, costo = ?, 
                                descripcion_platillo = ?, imagen = ? WHERE id_platillo = ? AND id_usuarios = ?";

            $stmt_update = $conn->prepare($sql_update);
            $stmt_update->bind_param("ssssii", $nombre_platillo, $costo, $descripcion_platillo, $imagen, $id_platillo, $usuario_id);

            if ($stmt_update->execute()) {
                // Obtener la información completa del platillo recién insertado
                $sql_select = "SELECT * FROM platillo WHERE id_platillo = $id_platillo";
                $result_select = $conn->query($sql_select);

                if ($result_select !== false && $result_select->num_rows > 0) {
                    $row_select = $result_select->fetch_assoc();
                    $response['status'] = 'success';
                    $response["message"] = "Platillo actualizado con éxito.";
                    $response["data"] = $row_select;
                } else {
                    $response["message"] = "Error al obtener la información del platillo recién actualizado.";
                }

                // Cerrar el resultado de la selección
                $result_select->close();
            } else {
                $response["message"] = "Error al actualizar platillo: " . $stmt_update->error;
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

// Función para obtener el ID del usuario
function obtenerUsuarioId($conn, $usuario_id)
{
    $sql = "SELECT id_usuarios FROM usuarios WHERE id_usuarios = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result !== false && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['id_usuarios'];
    } else {
        return 0;
    }
}
?>