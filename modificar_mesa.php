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
        $id_mesa = isset($data["id_mesa"]) ? $data["id_mesa"] : "";
        $codigo_qr = isset($data["codigo_qr"]) ? $data["codigo_qr"] : "";

        // Validar que los datos obligatorios no estén vacíos
        if (empty($id_mesa) || empty($codigo_qr)) {
            $response["message"] = "Error: ID de mesa y código QR son obligatorios.";
        } else {
            // Actualizar el código QR de la mesa en la base de datos utilizando sentencia preparada
            $sql_update = "UPDATE mesa SET codigo_qr = ? WHERE id_mesa = ?";

            $stmt_update = $conn->prepare($sql_update);
            $stmt_update->bind_param("si", $codigo_qr, $id_mesa);

            if ($stmt_update->execute()) {
                // Obtener la información actualizada de la mesa
                $sql_select = "SELECT * FROM mesa WHERE id_mesa = $id_mesa";
                $result_select = $conn->query($sql_select);

                if ($result_select !== false && $result_select->num_rows > 0) {
                    $row_select = $result_select->fetch_assoc();
                    $response["status"] = true;
                    $response["message"] = "Código QR de la mesa actualizado con éxito.";
                    $response["data"] = $row_select;
                } else {
                    $response["message"] = "Error al obtener la información de la mesa actualizada.";
                }

                // Cerrar el resultado de la selección
                $result_select->close();
            } else {
                $response["message"] = "Error al actualizar el código QR de la mesa: " . $stmt_update->error;
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

