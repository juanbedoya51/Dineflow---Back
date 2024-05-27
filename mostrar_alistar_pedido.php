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
    $id_ingresos = isset($data["id_ingresos"]) ? intval($data["id_ingresos"]) : 0;

    // Validar que los datos obligatorios no estén vacíos
    if (!empty($id_ingresos)) {
        // Consulta para obtener los platillos asociados al ID de ingresos
        $sql_platillos = "SELECT p.nombre_platillo, p.costo 
                          FROM platillo p 
                          INNER JOIN ingresos_platillos ip ON p.id_platillo = ip.id_platillo 
                          WHERE ip.id_ingresos = ?";
        $stmt_platillos = $conn->prepare($sql_platillos);
        $stmt_platillos->bind_param("i", $id_ingresos);
        $stmt_platillos->execute();
        $result_platillos = $stmt_platillos->get_result();

        if ($result_platillos !== false && $result_platillos->num_rows > 0) {
            $response["status"] = true;
            $response["message"] = "Platillos asociados al ID de ingresos obtenidos con éxito.";
            $response["data"] = $result_platillos->fetch_all(MYSQLI_ASSOC);
        } else {
            $response["message"] = "No se encontraron platillos asociados al ID de ingresos proporcionado.";
        }

        $stmt_platillos->close();
    } else {
        $response["message"] = "Error: ID de ingresos no proporcionado o inválido.";
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


