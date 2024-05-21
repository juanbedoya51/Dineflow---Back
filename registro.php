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
$response = array('status' => 'error', 'message' => 'Acción no válida');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Manejo de solicitud POST (Crear un nuevo usuario)
    $data = json_decode(file_get_contents("php://input"), true);
    $nombre = $data['nombre'];
    $email = $data['email'];
    $contraseña = $data['contraseña'];
    $nombre_negocio = $data['nombre_negocio'];
    $telefono = $data['telefono'];
    $direccion = $data['direccion'];

    // Validar que los datos obligatorios no estén vacíos
    if (empty($nombre) || empty($contraseña)) {
        $response["message"] = "Error: El nombre y la contraseña no pueden estar vacíos." . $nombre . " " . $contraseña;
    } else {

        $sql = "INSERT INTO usuarios (nombre, contraseña, email, nombre_negocio, telefono, direccion) 
            VALUES ('$nombre', '$contraseña', '$email', '$nombre_negocio', '$telefono', '$direccion')";
            
        if ($conn->query($sql) === TRUE) {
            $response['status'] = 'success';
            $response['message'] = 'Usuario creado con éxito';

            // Consultar y devolver la información del usuario recién creado
            $sql = "SELECT id_usuarios, nombre, email FROM usuarios WHERE id_usuarios = " . $conn->insert_id;
            $result = $conn->query($sql);
            if ($result->num_rows > 0) {
                $response['data'] = $result->fetch_assoc();
            }
        }
    }

} else {
    $response["message"] = "Error: Solo se permiten solicitudes POST.";
}

// Enviar respuesta como JSON
header('Content-Type: application/json');
echo json_encode($response);

// Cerrar la conexión a la base de datos
$conn->close();
?>