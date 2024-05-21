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

// Obtener el id del usuario desde el URL
$usuario_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Validar que el id del usuario no sea 0
if ($usuario_id === 0) {
    $response["message"] = "Error: id de usuario no proporcionado o inválido.";
} else {
    // Verificar la conexión
    if ($conn->connect_error) {
        $response["message"] = "Error de conexión a la base de datos: " . $conn->connect_error;
    } else {

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            // Obtener los datos del formulario
            $data = json_decode(file_get_contents("php://input"), true);
            $id_proveedores = isset($data["id_proveedores"]) ? $data["id_proveedores"] : "";
            $nombre_empresa = isset($data["nombre_empresa"]) ? $data["nombre_empresa"] : "";
            $telefono = isset($data["telefono"]) ? $data["telefono"] : "";
            $direccion = isset($data["direccion"]) ? $data["direccion"] : "";
            $email = isset($data["email"]) ? $data["email"] : "";

            // Validar que los datos obligatorios no estén vacíos
            if (empty($id_proveedores) || empty($nombre_empresa)) {
                $response["message"] = "Error: El id del proveedor y el nombre de la empresa son obligatorios.";
            } else {
                // Obtener el id del usuario desde el resultado
                $usuario_id = obtenerUsuarioId($conn, $usuario_id);

                // Continuar con el proceso de actualización usando $usuario_id
                // Actualizar los datos del proveedor en la base de datos utilizando sentencia preparada
                $sql_update = "UPDATE proveedores SET nombre_empresa = ?, telefono = ?, 
                                direccion = ?, email = ? WHERE id_proveedores = ? AND id_usuarios = ?";

                $stmt_update = $conn->prepare($sql_update);
                $stmt_update->bind_param("ssssii", $nombre_empresa, $telefono, $direccion, $email, $id_proveedores, $usuario_id);

                if ($stmt_update->execute()) {
                    // Obtener la información completa del proveedor recién insertado
                    $sql_select = "SELECT * FROM proveedores WHERE id_proveedores = $id_proveedores";
                    $result_select = $conn->query($sql_select);
                    if ($result_select !== false && $result_select->num_rows > 0) {
                        $row_select = $result_select->fetch_assoc();
                        $response["status"] = "success";
                        $response["message"] = "Proveedor actualizado con éxito.";
                        $response["data"] = $row_select;
                    } else {
                        $response["message"] = "Error al obtener la información del proveedor recién insertado.";
                    }

                    // Cerrar el resultado de la selección
                    $result_select->close();
                } else {
                    $response["message"] = "Error al actualizar proveedor: " . $stmt_update->error;
                }

                // Cerrar la sentencia de actualización
                $stmt_update->close();
            }
        } else {
            $response["message"] = "Error: Método de solicitud no válido.";
        }
    }
}

// Devolver la respuesta en formato JSON
header('Content-Type: application/json');
echo json_encode($response);

// Cerrar la conexión a la base de datos
$conn->close();

// Función para obtener el id del usuario
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