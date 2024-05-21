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

// Respuesta por defecto
$response = array('status' => 'error', 'message' => 'Credenciales incorrectas');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtener los datos del formulario
    $data = json_decode(file_get_contents("php://input"), true);
    $usuario_id = isset($data["id_usuarios"]) ? intval($data["id_usuarios"]) : 0;

    // Validar que los datos obligatorios no estén vacíos
    if (!empty($usuario_id)) {
        // Insertar un nuevo registro en la tabla "ingresos" con la fecha actual
        $sql_insert = "INSERT INTO ingresos (fecha_ingreso, id_usuarios) VALUES (NOW(), ?)";
        $stmt_insert = $conn->prepare($sql_insert);
        $stmt_insert->bind_param("i", $usuario_id);

        if ($stmt_insert->execute()) {
            $new_income_id = $stmt_insert->insert_id; // Obtener el ID del nuevo ingreso

            // Verificar si hay platillos en el JSON
            if (isset($data["platillos"]) && !empty($data["platillos"])) {
                $platillos = $data["platillos"];

                // Preparar la consulta para insertar en ingresos_platillos
                $sql_insert_ingresos_platillos = "INSERT INTO ingresos_platillos (id_ingresos, id_platillo) VALUES (?, ?)";
                $stmt_insert_ingresos_platillos = $conn->prepare($sql_insert_ingresos_platillos);

                foreach ($platillos as $platillo) {
                    $id_platillo = $platillo["id_platillo"];

                    // Insertar registros en la tabla ingresos_platillos
                    $stmt_insert_ingresos_platillos->bind_param("ii", $new_income_id, $id_platillo);
                    $stmt_insert_ingresos_platillos->execute();
                }

                $stmt_insert_ingresos_platillos->close();

                $response["status"] = true;
                $response["message"] = "Registros de ingreso y detalles de platillos agregados con éxito.";
            } else {
                $response["message"] = "Error: No se proporcionaron platillos para agregar.";
            }
        } else {
            $response["message"] = "Error al crear el registro de ingreso: " . $stmt_insert->error;
        }

        // Cerrar la sentencia de inserción de ingresos
        $stmt_insert->close();
    } else {
        $response["message"] = "Error: El ID de usuario no puede estar vacío.";
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













