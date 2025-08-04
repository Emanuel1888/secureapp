<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../auth/check.php'; // Esto ya incluye jwt_utils.php internamente

header('Content-Type: application/json');

// ✅ Confirmar autenticación basada en check.php
$user_id = AUTH_USER;

// Validar que se recibió el parámetro file_id por GET y que es un número
if (!isset($_GET['file_id']) || !is_numeric($_GET['file_id'])) {
    http_response_code(400);
    echo json_encode(["error" => "Parámetro 'file_id' inválido."]);
    exit;
}

$file_id = (int) $_GET['file_id'];
try {
    // Buscar el archivo y verificar que pertenezca al usuario
    $stmt = $pdo->prepare("SELECT filename FROM user_files WHERE id = :file_id AND user_id = :user_id");
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

    $filePath = __DIR__ . "/../uploads/" . $file['filename'];
    
    // Eliminar del sistema de archivos si existe
    if (file_exists($filePath)) {
        unlink($filePath);
    }

    // Eliminar de la base de datos
    $deleteStmt = $pdo->prepare("DELETE FROM user_files WHERE id = :file_id AND user_id = :user_id");
    $deleteStmt->execute([
        ':file_id' => $file_id,
        ':user_id' => $user_id
    ]);

    echo json_encode(["success" => true, "message" => "Archivo eliminado."]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "error" => "Error al eliminar el archivo.",
        "details" => $e->getMessage()
    ]);
}