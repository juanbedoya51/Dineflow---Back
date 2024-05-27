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
// Recibir el JSON enviado por el método POST
$json = file_get_contents('php://input');
$data = json_decode($json);



// Verificar la conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

if (isset($data->id_detalle)) {
    // Si se recibe un ID de detalle, eliminar el registro correspondiente en la tabla "pedidodetalle"
    $id_detalle = $data->id_detalle;
    $delete_sql = "DELETE FROM pedidodetalle WHERE id_detalle = ?";
    $delete_stmt = $conn->prepare($delete_sql);
    $delete_stmt->bind_param("i", $id_detalle);
    $delete_stmt->execute();

    $response["status"] = "success";
    $response["message"] = "Registro eliminado con éxito.";
    $response["data"] = null;
    // Devolver la respuesta en formato JSON
    header('Content-Type: application/json');
    echo json_encode($response);
}

// Cerrar la conexión
$conn->close();
?>

