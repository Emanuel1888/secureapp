<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../auth/jwt_utils.php';
require_once __DIR__ . '/../auth/check.php';
require_once __DIR__ . '/../cors.php';

header('Content-Type: application/json');

// Verificar autenticación
$user_id = AUTH_USER;
if (!$user_id) {
    http_response_code(401);
    echo json_encode(["error" => "Acceso no autorizado."]);
    exit;
}

// Consultar archivos subidos por el usuario
try {
    $stmt = $pdo->prepare("
        SELECT id, filename, original_name, mime_type, size, uploaded_at
        FROM user_files
        WHERE user_id = :user_id
    ");
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();

    $files = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "success" => true,
        "files" => $files
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "error" => "Error al obtener archivos.",
        "details" => $e->getMessage()
    ]);
}
