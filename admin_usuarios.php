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

session_start();

// Verificar si hay una sesión activa
if (!isset($_SESSION['correo'])) {
    header("Location: sesionadmin.php.php");
    exit();
}


// Respuesta por defecto
$response = array('status' => 'error', 'message' => 'Credenciales incorrectas');

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Verifica si la clave 'correo' existe en la sesión
    if (isset($_SESSION['correo'])) {
        // No necesitas obtener el ID del administrador si deseas todos los usuarios
        $sql = "SELECT * FROM usuarios";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            // Usuarios encontrados
            $usuarios = array();
            while ($row = $result->fetch_assoc()) {
                $usuarios[] = $row;
            }
            $response['status'] = 'success';
            $response['message'] = 'Usuarios obtenidos con éxito';
            $response['data'] = $usuarios;
        } else {
            $response['message'] = 'No se encontraron usuarios';
        }
    } else {
        $response['message'] = 'Error: La clave "correo" no está presente en la sesión.';
    }
}

// Enviar respuesta como JSON
header('Content-Type: application/json');
echo json_encode($response);

// Cerrar la conexión a la base de datos
$conn->close();
?>