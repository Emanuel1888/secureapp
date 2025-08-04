<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../auth/check.php';

// ✅ Confirmar autenticación basada en check.php
$user_id = AUTH_USER;

// 2. Validar parámetro requerido: id de archivo
if (!isset($_GET['file_id']) || !is_numeric($_GET['file_id'])) {
    http_response_code(400);
    echo json_encode(["error" => "Parámetro 'file_id' inválido."]);
    exit;
}

$file_id = (int) $_GET['file_id'];

try {
    // 3. Consultar archivo y validar propiedad del usuario
    $stmt = $pdo->prepare("SELECT filename, original_name, mime_type FROM user_files WHERE id = :file_id AND user_id = :user_id");
    $stmt->bindParam(':file_id', $file_id, PDO::PARAM_INT);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();

    $file = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$file) {
        http_response_code(404);
        echo json_encode(["error" => "Archivo no encontrado o no autorizado."]);
        exit;
    }

    // 4. Verificar existencia física del archivo
    $filePath = __DIR__ . '/../uploads/' . $file['filename'];
    if (!file_exists($filePath)) {
        http_response_code(410); // Gone
        echo json_encode(["error" => "Archivo eliminado del servidor."]);
        exit;
    }

    // 5. Forzar descarga segura
    header('Content-Description: File Transfer');
    header('Content-Type: ' . $file['mime_type']);
    header('Content-Disposition: attachment; filename="' . basename($file['original_name']) . '"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($filePath));

    readfile($filePath);
    exit;

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "Error al descargar archivo.", "details" => $e->getMessage()]);
    exit;
}
