<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../auth/check.php';

$user_id = AUTH_USER;

// 1. Validar parámetro
if (!isset($_GET['file_id']) || !is_numeric($_GET['file_id'])) {
    http_response_code(400);
    echo json_encode(["error" => "Parámetro 'file_id' inválido."]);
    exit;
}

$file_id = (int) $_GET['file_id'];

try {
    // 2. Consultar información del archivo
    $stmt = $pdo->prepare("SELECT filename, original_name, mime_type FROM user_files WHERE id = :file_id AND user_id = :user_id");
    $stmt->execute([
        ':file_id' => $file_id,
        ':user_id' => $user_id
    ]);

    $file = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$file) {
        http_response_code(404);
        echo json_encode(["error" => "Archivo no encontrado o no autorizado."]);
        exit;
    }

    // 3. Ruta física (igual que en la lógica de eliminación)
    $upload_dir = realpath(__DIR__ . '/../uploads'); // Asegura ruta absoluta
    $file_path = $upload_dir . DIRECTORY_SEPARATOR . $file['filename'];

    if (!file_exists($file_path)) {
        http_response_code(410); // Gone
        echo json_encode(["error" => "Archivo eliminado del servidor."]);
        exit;
    }

    // 4. Forzar descarga segura
    header('Content-Description: File Transfer');
    header('Content-Type: ' . $file['mime_type']);
    header('Content-Disposition: attachment; filename="' . basename($file['original_name']) . '"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($file_path));

    readfile($file_path);
    exit;

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "Error al descargar archivo.", "details" => $e->getMessage()]);
    exit;
}
