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

// Inicializar la respuesta
$response = array("status" => "error", "message" => "", "data" => null);

// Verificar la conexión
if ($conn->connect_error) {
    $response["message"] = "Error de conexión a la base de datos: " . $conn->connect_error;
} else {
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
            // Obtener los datos del formulario
            $data = json_decode(file_get_contents("php://input"), true);
            $nombre_platillo = isset($data["nombre_platillo"]) ? $data["nombre_platillo"] : "";
            $costo = isset($data["costo"]) ? $data["costo"] : "";
            $descripcion_platillo = isset($data["descripcion_platillo"]) ? $data["descripcion_platillo"] : "";
            $foto = isset($data["imagen"]) ? $data["imagen"] : "";
            $usuario_id = isset($data["id_usuarios"]) ? $data["id_usuarios"] : 0;

            // Validar que los datos obligatorios no estén vacíos
            if (empty($nombre_platillo) || empty($costo)) {
                $response["message"] = "Error: El nombre del platillo y el costo no pueden estar vacíos.";
            } else {
                // Insertar el nuevo platillo en la base de datos
                $sql_insert = "INSERT INTO platillo (nombre_platillo, costo, descripcion_platillo, imagen, id_usuarios)
                                VALUES (?, ?, ?, ?, ?)";
                $stmt_insert = $conn->prepare($sql_insert);
                $stmt_insert->bind_param("ssssi", $nombre_platillo, $costo, $descripcion_platillo, $foto, $usuario_id);

                if ($stmt_insert->execute()) {
                    // Obtener la información completa del platillo recién insertado
                    $new_dish_id = $stmt_insert->insert_id;
                    $sql_select = "SELECT * FROM platillo WHERE id_platillo = $new_dish_id";
                    $result_select = $conn->query($sql_select);

                    if ($result_select !== false && $result_select->num_rows > 0) {
                        $row_select = $result_select->fetch_assoc();
                        $response['status'] = 'success';
                        $response["message"] = "Platillo agregado con éxito.";
                        $response["data"] = $row_select;
                    } else {
                        $response["message"] = "Error al obtener la información del platillo recién insertado.";
                    }

                    // Cerrar el resultado de la selección
                    $result_select->close();
                } else {
                    $response["message"] = "Error al agregar el platillo: " . $stmt_insert->error;
                }

                // Cerrar la sentencia de inserción
                $stmt_insert->close();
            }
        } else {
            $response["message"] = "Error: Método de solicitud no válido.";
        }
    }


// Devolver la respuesta en formato JSON
header('Content-Type: application/json');
echo json_encode($response);

// Cerrar la conexión a la base de datos
$conn->close();
?>