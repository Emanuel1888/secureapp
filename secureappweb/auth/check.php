<?php
// check.php

// Encabezados requeridos para APIs (si no se gestionan en otro archivo)
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// Se incluye el archivo con funciones JWT
require_once __DIR__ . '/jwt_utils.php';

// Obtener el token desde el encabezado Authorization: Bearer <token>
$headers = apache_request_headers();
# En caso de que no se use apache en el servidor, considerar el uso de esta fución general
# $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? null;

if (!isset($headers['Authorization'])) {
    http_response_code(401); // Unauthorized
    echo json_encode(["error" => "Falta el encabezado Authorization"]);
    exit;
}

list($type, $jwt) = explode(" ", $headers['Authorization'], 2);

if (strtolower($type) !== 'bearer' || empty($jwt)) {
    http_response_code(401);
    echo json_encode(["error" => "Encabezado Authorization malformado"]);
    exit;
}

// Verificar validez del token
if (!is_jwt_valid($jwt)) {
    http_response_code(401);
    echo json_encode(["error" => "Token inválido o expirado"]);
    exit;
}

// Obtener payload si es válido
$payload = get_payload_from_jwt($jwt);

#1.- Modificación para verificar la existencia del ID del usuario antes de generar la 
if (!isset($payload['user_id'])) {
    http_response_code(401);
    echo json_encode(["error" => "Token válido pero sin identificador de usuario"]);
    exit;
}

#2.- Esta función  obtine el ID_User y lo publica.
$user_id = get_user_id_from_jwt($jwt);

if (!$user_id) {
    http_response_code(401);
    echo json_encode(["error" => "Token inválido o sin identificador de usuario"]);
    exit;
}

// Definir constante con user_id para usar después
define('AUTH_USER', $user_id);