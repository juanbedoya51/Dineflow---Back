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
    if (!empty($id_ingresos)) {
        // Consulta para sumar el costo de los platillos asociados a este ingreso
        $sql_sum_costo = "SELECT SUM(p.costo) AS total_costo FROM platillo p 
                          INNER JOIN ingresos_platillos ip ON p.id_platillo = ip.id_platillo 
                          WHERE ip.id_ingresos = ?";
        $stmt_sum_costo = $conn->prepare($sql_sum_costo);
        $stmt_sum_costo->bind_param("i", $id_ingresos);
        $stmt_sum_costo->execute();
        $result_sum_costo = $stmt_sum_costo->get_result();

        if ($result_sum_costo !== false && $result_sum_costo->num_rows > 0) {
            $row = $result_sum_costo->fetch_assoc();
            $total_costo = $row['total_costo'];

            // Actualizar el monto en la tabla ingresos con la suma obtenida
            $sql_update_monto = "UPDATE ingresos SET monto = ? WHERE id_ingresos = ?";
            $stmt_update_monto = $conn->prepare($sql_update_monto);
            $stmt_update_monto->bind_param("di", $total_costo, $id_ingresos);
            $stmt_update_monto->execute();
            $stmt_update_monto->close();
        }


        // Consulta para obtener los datos de nombre_platillo y costo asociados al ID de ingreso
        $sql_platillo_info = "SELECT p.nombre_platillo, p.costo FROM platillo p 
                              INNER JOIN ingresos_platillos ip ON p.id_platillo = ip.id_platillo 
                              WHERE ip.id_ingresos = ?";
        $stmt_platillo_info = $conn->prepare($sql_platillo_info);
        $stmt_platillo_info->bind_param("i", $id_ingresos);
        $stmt_platillo_info->execute();
        $result_platillo_info = $stmt_platillo_info->get_result();

        if ($result_platillo_info !== false && $result_platillo_info->num_rows > 0) {
            $descripcion_ingreso = '';
            while ($row = $result_platillo_info->fetch_assoc()) {
                $descripcion_ingreso .= $row['nombre_platillo'] . ' - Costo: $' . $row['costo'] . ', ';
            }
            // Eliminar la última coma y espacio del string
            $descripcion_ingreso = rtrim($descripcion_ingreso, ', ');

            // Actualizar la columna descripcion_ingreso en la tabla ingresos con la información obtenida
            $sql_update_descripcion = "UPDATE ingresos SET descripcion_ingreso = ? WHERE id_ingresos = ?";
            $stmt_update_descripcion = $conn->prepare($sql_update_descripcion);
            $stmt_update_descripcion->bind_param("si", $descripcion_ingreso, $id_ingresos);
            $stmt_update_descripcion->execute();
            $stmt_update_descripcion->close();

            $response["status"] = 'success';
            $response["message"] = "Descripción de ingreso actualizada con éxito para el ID de ingreso: $id_ingresos";
        } else {
            $response["message"] = "No se encontraron platillos asociados al ID de ingreso proporcionado.";
        }

        $stmt_platillo_info->close();
    } else {
        $response["message"] = "Error: El ID de ingresos no puede estar vacío.";
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


