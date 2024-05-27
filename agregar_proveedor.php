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


// Respuesta por defecto
$response = array('status' => 'error', 'message' => 'Credenciales incorrectas');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtener los datos del formulario
    $data = json_decode(file_get_contents("php://input"), true);
    $nombre_empresa = isset($data["nombre_empresa"]) ? $data["nombre_empresa"] : "";
    $telefono = isset($data["telefono"]) ? $data["telefono"] : "";
    $direccion = isset($data["direccion"]) ? $data["direccion"] : "";
    $email = isset($data["email"]) ? $data["email"] : "";
    $usuario_id = isset($data["id_usuarios"]) ? intval($data["id_usuarios"]) : 0;

    // Validar que los datos obligatorios no estén vacíos
    if (empty($nombre_empresa) || empty($usuario_id)) {
        $response["message"] = "Error: El nombre de la empresa y el ID de usuario no pueden estar vacíos." . $nombre_empresa . " " . $usuario_id;
    } else {
        // Insertar el nuevo proveedor en la base de datos
        $sql_insert = "INSERT INTO proveedores (nombre_empresa, telefono, direccion, email, id_usuarios)
                        VALUES (?, ?, ?, ?, ?)";
        $stmt_insert = $conn->prepare($sql_insert);
        $stmt_insert->bind_param("ssssi", $nombre_empresa, $telefono, $direccion, $email, $usuario_id);

        if ($stmt_insert->execute()) {
            // Obtener la información completa del proveedor recién insertado
            $new_provider_id = $stmt_insert->insert_id;
            $sql_select = "SELECT * FROM proveedores WHERE id_proveedores = $new_provider_id";
            $result_select = $conn->query($sql_select);

            if ($result_select !== false && $result_select->num_rows > 0) {
                $row_select = $result_select->fetch_assoc();
                $response['status'] = 'success';
                $response["message"] = "Proveedor agregado con éxito.";
                $response["data"] = $row_select;
            } else {
                $response["message"] = "Error al obtener la información del proveedor recién insertado.";
            }

            // Cerrar el resultado de la selección
            $result_select->close();
        } else {
            $response["message"] = "Error al agregar el proveedor: " . $stmt_insert->error;
        }

        // Cerrar la sentencia de inserción
        $stmt_insert->close();
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
