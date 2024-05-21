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
    $id_ingresos = isset($data["id_ingresos"]) ? intval($data["id_ingresos"]) : 0;

    // Validar que los datos obligatorios no estén vacíos
    if (!empty($id_ingresos) && isset($data["platillos"]) && is_array($data["platillos"])) {
        $platillos = $data["platillos"];
        $stmt_insert = $conn->prepare("INSERT INTO ingresos_platillos (id_ingresos, id_platillo) VALUES (?, ?)");

        foreach ($platillos as $platillo) {
            $id_platillo = isset($platillo["id_platillo"]) ? intval($platillo["id_platillo"]) : 0;

            if (!empty($id_platillo)) {
                $stmt_insert->bind_param("ii", $id_ingresos, $id_platillo);
                $stmt_insert->execute();

                // Obtener información del platillo asociado al ID y agregarlo a la respuesta
                $sql_platillo_info = "SELECT * FROM platillo WHERE id_platillo = ?";
                $stmt_platillo_info = $conn->prepare($sql_platillo_info);
                $stmt_platillo_info->bind_param("i", $id_platillo);
                $stmt_platillo_info->execute();
                $result_platillo_info = $stmt_platillo_info->get_result();

                if ($result_platillo_info !== false && $result_platillo_info->num_rows > 0) {
                    $platillo_info = $result_platillo_info->fetch_assoc();
                    $response["data"][] = $platillo_info;
                }

                $stmt_platillo_info->close();
            }
        }

        $stmt_insert->close();

        $response["status"] = 'success';
        $response["message"] = "Registros en ingresos_platillos agregados con éxito.";
    } else {
        $response["message"] = "Error: Datos incompletos o incorrectos proporcionados.";
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

