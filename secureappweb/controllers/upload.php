<?php
require_once __DIR__ . '/../cors.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../auth/jwt_utils.php';
require_once __DIR__ . '/../auth/check.php';

header('Content-Type: application/json');

// Verificar autenticación
$user_id = AUTH_USER;
if (!$user_id) {
    http_response_code(401);
    echo json_encode(["error" => "Acceso no autorizado."]);
    exit;
}

// Validar si se envió un archivo
if (!isset($_FILES['file'])) {
    http_response_code(400);
    echo json_encode(["error" => "No se ha enviado ningún archivo."]);
    exit;
}

$file = $_FILES['file'];

// Validaciones
if ($file['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode(["error" => "Error al subir el archivo."]);
    exit;
}

$allowedMimeTypes = [
    'image/jpeg','image/pjpeg','image/png','image/gif','image/webp',
    'image/bmp','image/tiff','image/x-icon','image/svg+xml'
];

if (!in_array($file['type'], $allowedMimeTypes)) {
    http_response_code(400);
    echo json_encode(["error" => "Tipo de archivo no permitido."]);
    exit;
}

$maxSize = 10 * 1024 * 1024; // 10MB
if ($file['size'] > $maxSize) {
    http_response_code(400);
    echo json_encode(["error" => "El archivo supera el tamaño máximo permitido (10MB)."]);
    exit;
}

// Preparar nombre y guardar
$original_name = basename($file['name']);
$sanitized_name = preg_replace("/[^a-zA-Z0-9\.\-_]/", "_", $original_name);
$unique_name = uniqid() . "_" . $sanitized_name;

$upload_dir = __DIR__ . '/../uploads/';
$upload_path = $upload_dir . $unique_name;

if (!move_uploaded_file($file['tmp_name'], $upload_path)) {
    http_response_code(500);
    echo json_encode(["error" => "No se pudo guardar el archivo."]);
    exit;
}

// Registrar en base de datos
try {
    $stmt = $pdo->prepare("
        INSERT INTO user_files (user_id, filename, original_name, mime_type, size)
        VALUES (:user_id, :filename, :original_name, :mime_type, :size)
    ");
    $stmt->execute([
        ':user_id' => $user_id,
        ':filename' => $unique_name,
        ':original_name' => $original_name,
        ':mime_type' => $file['type'],
        ':size' => $file['size']
    ]);

    http_response_code(201);
    echo json_encode([
        "success" => true,
        "message" => "Archivo subido exitosamente."
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "error" => "Error al registrar el archivo en la base de datos.",
        "details" => $e->getMessage()
    ]);
}
