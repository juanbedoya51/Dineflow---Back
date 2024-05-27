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

// Verificar la conexión a la base de datos
if ($conn->connect_error) {
    die("Error de conexión a la base de datos: " . $conn->connect_error);
}

// Verificar si la solicitud es de tipo POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Obtener los datos del formulario en formato JSON
    $data = json_decode(file_get_contents("php://input"), true);
    $codigo_qr = isset($data["codigo_qr"]) ? $data["codigo_qr"] : "";
    $usuario_id = isset($data["id_usuarios"]) ? intval($data["id_usuarios"]) : 0;   

    // Validar que los datos obligatorios no estén vacíos
    if (empty($usuario_id) !== NULL) {

        // Continuar con la inserción si el código QR no está vacío
        if (!empty($codigo_qr)) {

            // Insertar la nueva mesa en la base de datos
            $sql_insert = "INSERT INTO mesa (codigo_qr, id_usuarios)
                            VALUES (?, ?)";
            $stmt_insert = $conn->prepare($sql_insert);
            $stmt_insert->bind_param("si", $codigo_qr, $usuario_id);

            if ($stmt_insert->execute()) {
                $new_table_id = $stmt_insert->insert_id;
                $sql_select = "SELECT * FROM mesa WHERE id_mesa = $new_table_id";
                $result_select = $conn->query($sql_select);

                if ($result_select !== false && $result_select->num_rows > 0) {
                    $row_select = $result_select->fetch_assoc();
                    $response["status"] = "success";
                    $response["message"] = "Mesa agregada con éxito.";
                    $response["data"] = $row_select;
                } else {
                    $response["message"] = "Error al obtener la información de la mesa recién insertada.";
                }

                $result_select->close();
            } else {
                $response["message"] = "Error al agregar la mesa: " . $stmt_insert->error;
            }

            $stmt_insert->close();
        } else {
            $response["message"] = "Error: El código QR no puede estar vacío.";
        }
    } else {
        $response["message"] = "Error: No se pudo obtener el ID del usuario.";
    }
}

// Devolver la respuesta en formato JSON
header('Content-Type: application/json');
echo json_encode($response);

// Cerrar la conexión a la base de datos
$conn->close();
?>


