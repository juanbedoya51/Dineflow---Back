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


// Verificar la conexión
if ($conn->connect_error) {
    die("Error de conexión a la base de datos: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Obtener los datos para eliminar la mesa
    $data = json_decode(file_get_contents("php://input"), true);
    $mesa_id = isset($data["id_mesa"]) ? $data["id_mesa"] : "";
    $usuario_id = isset($data["id_usuarios"]) ? $data["id_usuarios"] : "";

    // Validar que el ID de la mesa no esté vacío
    if (empty($mesa_id)) {
        $response["message"] = "Error: El ID de la mesa no puede estar vacío.";
    } else {
        // Verificar si la mesa pertenece al usuario actual antes de eliminarla
        $sql_check = "SELECT * FROM mesa WHERE id_mesa = ? AND id_usuarios = ?";
        $stmt_check = $conn->prepare($sql_check);
        $stmt_check->bind_param("ii", $mesa_id, $usuario_id);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();

        if ($result_check->num_rows > 0) {
            // Eliminar la mesa si pertenece al usuario actual
            $sql_delete = "DELETE FROM mesa WHERE id_mesa = ?";
            $stmt_delete = $conn->prepare($sql_delete);
            $stmt_delete->bind_param("i", $mesa_id);

            if ($stmt_delete->execute()) {
                $response["status"] = "success";
                $response["message"] = "Mesa eliminada con éxito.";
            } else {
                $response["message"] = "Error al eliminar la mesa: " . $stmt_delete->error;
            }

            // Cerrar la sentencia de eliminación
            $stmt_delete->close();
        } else {
            $response["message"] = "Error: La mesa no pertenece al usuario actual.";
        }

        // Liberar resultados
        $result_check->close();
        $stmt_check->close();
    }
} else {
    $response["message"] = "Error: Solo se permiten solicitudes POST.";
}

// Devolver la respuesta en formato JSON
header('Content-Type: application/json');
echo json_encode($response);

// Cerrar la conexión a la base de datos
$conn->close();
?>
