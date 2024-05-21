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
$response = array("status" => false, "message" => "", "data" => null);

// Verificar la conexión
if ($conn->connect_error) {
    $response["message"] = "Error de conexión a la base de datos: " . $conn->connect_error;
} else {
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Obtener los datos del formulario
        $data = json_decode(file_get_contents("php://input"), true);
        $id_usuarios = isset($data["id_usuarios"]) ? $data["id_usuarios"] : "";
        $nombre = isset($data["nombre"]) ? $data["nombre"] : "";
        $email = isset($data["email"]) ? $data["email"] : "";
        $nombre_negocio = isset($data["nombre_negocio"]) ? $data["nombre_negocio"] : "";
        $telefono = isset($data["telefono"]) ? $data["telefono"] : "";
        $direccion = isset($data["direccion"]) ? $data["direccion"] : "";
        $contraseña = isset($data["contraseña"]) ? $data["contraseña"] : "";

        // Validar que los datos obligatorios no estén vacíos
        if (empty($id_usuarios)) {
            $response["message"] = "Error: El ID de usuario es obligatorio.";
        } else {
            // Actualizar los datos del usuario en la base de datos utilizando sentencia preparada
            $sql_update = "UPDATE usuarios SET nombre = ?, email = ?, nombre_negocio = ?, telefono = ?, direccion = ?, contraseña = ? WHERE id_usuarios = ?";

            $stmt_update = $conn->prepare($sql_update);
            $stmt_update->bind_param("ssssssi", $nombre, $email, $nombre_negocio, $telefono, $direccion, $contraseña, $id_usuarios);

            if ($stmt_update->execute()) {
                // Obtener la información actualizada del usuario
                $sql_select = "SELECT * FROM usuarios WHERE id_usuarios = $id_usuarios";
                $result_select = $conn->query($sql_select);

                if ($result_select !== false && $result_select->num_rows > 0) {
                    $row_select = $result_select->fetch_assoc();
                    $response["status"] = true;
                    $response["message"] = "Datos de usuario actualizados con éxito.";
                    $response["data"] = $row_select;
                } else {
                    $response["message"] = "Error al obtener la información del usuario actualizado.";
                }

                // Cerrar el resultado de la selección
                $result_select->close();
            } else {
                $response["message"] = "Error al actualizar los datos del usuario: " . $stmt_update->error;
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


