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

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    // Respuesta preflight para solicitudes CORS
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Authorization");
    header("Access-Control-Max-Age: 3600");
    exit; // No proceses la solicitud en este caso
}

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Respuesta por defecto
$response = array('status' => 'error', 'message' => 'Acción no válida');

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Manejo de solicitud GET (Recuperar datos)
    if (isset($_GET['id'])) {
        // Obtener un usuario por ID
        $id = $_GET['id'];
        $sql = "SELECT ID, nombre, correo, fecha_nacimiento FROM usuario WHERE ID = $id";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            $response['status'] = 'success';
            $response['message'] = '';
            $response['data'] = $result->fetch_assoc();
        }
    } else {
        // Obtener todos los usuario
        $sql = "SELECT ID, nombre, correo, fecha_nacimiento FROM usuario";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            $usuario = array();
            while ($row = $result->fetch_assoc()) {
                $usuario[] = $row;
            }
            $response['status'] = 'success';
            $response['message'] = '';
            $response['data'] = $usuario;
        }
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Manejo de solicitud POST (Crear un nuevo usuario)
    $data = json_decode(file_get_contents("php://input"), true);
    $nombre = $data['nombre'];
    $correo = $data['correo'];
    $contrasena = $data['contrasena'];
    $fechaNacimiento = $data['fecha_nacimiento'];

    $sql = "INSERT INTO usuario (nombre, correo, contrasena, fecha_nacimiento) 
            VALUES ('$nombre', '$correo', '$contrasena', '$fechaNacimiento')";
    
    if ($conn->query($sql) === TRUE) {
        $response['status'] = 'success';
        $response['message'] = 'Usuario creado con éxito';

        // Consultar y devolver la información del usuario recién creado
        $sql = "SELECT ID, nombre, correo, fecha_nacimiento FROM usuario WHERE ID = " . $conn->insert_id;
        $result = $conn->query($sql);
        if ($result->num_rows > 0) {
            $response['data'] = $result->fetch_assoc();
        }
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    // Manejo de solicitud PUT (Actualizar un usuario por su ID)
    $data = json_decode(file_get_contents("php://input"), true);
    if (isset($data['ID'])) {
        $id = $data['ID'];
    }
    if (isset($data['id'])) {
        $id = $data['id'];
    }
    $nombre = $data['nombre'];
    $correo = $data['correo'];
    $contrasena = $data['contrasena'];
    $fechaNacimiento = $data['fecha_nacimiento'];

    $sql = "UPDATE usuario SET nombre = '$nombre', correo = '$correo', contrasena = '$contrasena', fecha_nacimiento = '$fechaNacimiento'
            WHERE ID = $id";
    
    if ($conn->query($sql) === TRUE) {
        $response['status'] = 'success';
        $response['message'] = 'Usuario actualizado con éxito';

        // Consultar y devolver la información del usuario actualizado
        $sql = "SELECT ID, nombre, correo, fecha_nacimiento FROM usuario WHERE ID = $id";
        $result = $conn->query($sql);
        if ($result->num_rows > 0) {
            $response['data'] = $result->fetch_assoc();
        }
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    // Manejo de solicitud DELETE (Eliminar un usuario por su ID)
    $id = $_GET['id'];
    $sql = "DELETE FROM usuario WHERE ID = $id";
    if ($conn->query($sql) === TRUE) {
        $response['status'] = 'success';
        $response['message'] = 'Usuario eliminado con éxito';
    }
}

// Enviar respuesta como JSON
header('Content-Type: application/json');
echo json_encode($response);

// Cerrar la conexión a la base de datos
$conn->close();
?>
