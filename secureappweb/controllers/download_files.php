<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../auth/check.php'; // Asegura que esté autenticado

// Asegurar que el usuario esté autenticado
$user_id = AUTH_USER;

// Validar que el parámetro file_id sea válido
if (!isset($_GET['file_id']) || !is_numeric($_GET['file_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Parámetro file_id inválido']);
    exit;
}

$file_id = intval($_GET['file_id']);

try {
    $pdo = getConnection();
    $stmt = $pdo->prepare("SELECT * FROM user_files WHERE id = :id AND user_id = :user_id");
    $stmt->execute([
        ':id' => $file_id,
        ':user_id' => $user_id
    ]);
    $file = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$file) {
        http_response_code(404);
        echo json_encode(['error' => 'Archivo no encontrado o no autorizado']);
        exit;
    }

    // Ruta física del archivo
    $filePath = __DIR__ . '/../uploads/' . $file['filename'];

    if (!file_exists($filePath)) {
        http_response_code(410);
        echo json_encode(['error' => 'Archivo eliminado del servidor']);
        exit;
    }

    // Enviar encabezados de descarga
    header('Content-Description: File Transfer');
    header('Content-Type: ' . $file['mime_type']);
    header('Content-Disposition: attachment; filename="' . basename($file['original_name']) . '"');
    header('Content-Length: ' . filesize($filePath));
    header('Pragma: public');
    flush();
    readfile($filePath);
    exit;

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al acceder a la base de datos']);
    exit;
}
