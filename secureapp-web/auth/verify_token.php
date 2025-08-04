<?php
header("Content-Type: application/json");
require_once __DIR__ . '/../cors.php';
require_once(__DIR__ . '/jwt_utils.php');

// Leer token desde el cuerpo de la petición
$data = json_decode(file_get_contents("php://input"));

if (!isset($data->token)) {
    http_response_code(400);
    echo json_encode(['error' => 'Token no proporcionado']);
    exit;
}

$token = trim($data->token);

// Verificar el token
$decoded = decode_jwt($token);

if ($decoded !== false) {
    echo json_encode([
        #1.- Agregamos "Success" a la respuesta.
        'success' => true,
        'message' => 'Token válido',
        'user_id' => $decoded->user_id
    ]);
} else {
    http_response_code(401);
    echo json_encode(['error' => 'Token inválido o expirado']);
}
?>
