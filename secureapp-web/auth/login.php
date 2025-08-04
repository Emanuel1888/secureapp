<?php
#1.- Agregamos el Cors
require_once(__DIR__ . '/../cors.php'); 

header("Content-Type: application/json");
#2.- Agregamos ubicación de send_email
require_once(__DIR__ . '/../controllers/send_email.php');
require_once(__DIR__ . '/../config/db.php');
require_once(__DIR__ . '/jwt_utils.php'); // Contiene generate_jwt y enviarCorreoToken

// Leer datos del cuerpo de la petición (JSON)
$data = json_decode(file_get_contents("php://input"));

// Validar campos
if (!isset($data->username) || !isset($data->password)) {
    http_response_code(400);
    echo json_encode(['error' => 'Faltan campos obligatorios']);
    exit;
}

$username = htmlspecialchars(trim($data->username));
$password = trim($data->password);

// Buscar usuario por username
$stmt = $pdo->prepare("SELECT id, username, password, email FROM users WHERE username = ?");
$stmt->execute([$username]);
$user = $stmt->fetch();




if ($user && password_verify($password, $user['password'])) {
    // Generar JWT
    $token = generate_jwt(['user_id' => $user['id']]);

    // Enviar token al correo del usuario
    $correoExito = sendTokenByEmail($user['email'], $token);

    if ($correoExito) {
        #1.- Agregamos "Success" a la respuesta.
        echo json_encode(['success' => true,
        'message' => 'Token enviado al correo electrónico.']);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Error al enviar el token por correo.']);
    }

} else {
    http_response_code(401);
    echo json_encode(['error' => 'Credenciales inválidas']);
}
?>
