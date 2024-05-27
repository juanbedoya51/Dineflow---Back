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
    // Manejo de solicitud POST (Inicio de sesión)
    $data = json_decode(file_get_contents("php://input"), true);
    $nombre = $data['nombre'];
    $contraseña = $data['contraseña'];

    // Verificar las credenciales en la base de datos
    $sql = "SELECT id_usuarios, nombre, contraseña, email, nombre_negocio, telefono, direccion FROM usuarios WHERE nombre = '$nombre' AND contraseña = '$contraseña'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        // Credenciales válidas, se encontró un usuario
        $usuario = $result->fetch_assoc();
        $_SESSION['nombre'] = $usuario['nombre'];
        $response['status'] = 'success';
        $response['message'] = 'Inicio de sesión exitoso';
        $response['data'] = $usuario;
    }
}

// Enviar respuesta como JSON
header('Content-Type: application/json');
echo json_encode($response);

// Cerrar la conexión a la base de datos
$conn->close();
?>