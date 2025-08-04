<?php
require_once __DIR__ . '/../auth/check.php';
require_once __DIR__ . '/../config/db.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        getProfile($pdo);
        break;

    case 'PUT':
        updateProfile($pdo);
        break;

    case 'DELETE':
        deleteAccount($pdo);
        break;

    default:
        http_response_code(405);
        echo json_encode(['error' => 'Método no permitido']);
        break;
}

// ─────────────────────────────────────────────
// Función: Obtener perfil del usuario autenticado
function getProfile($pdo) {
    $user_id = AUTH_USER;

    $stmt = $pdo->prepare("SELECT id, username, email, created_at FROM users WHERE id = ?");
    $stmt->execute([$user_id]);

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        echo json_encode(['user' => $user]);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Usuario no encontrado']);
    }
}

// ─────────────────────────────────────────────
// Función: Actualizar nombre de usuario y/o email
function updateProfile($pdo) {
    $user_id = AUTH_USER;

    // Obtener datos JSON del cuerpo de la petición
    $data = json_decode(file_get_contents("php://input"), true);
    if (!$data) {
        http_response_code(400);
        echo json_encode(['error' => 'Datos inválidos']);
        return;
    }

    $username = trim($data['username'] ?? '');
    $email = trim($data['email'] ?? '');

    if (!$username || !$email) {
        http_response_code(400);
        echo json_encode(['error' => 'Username y email son requeridos']);
        return;
    }

    try {
        $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ? WHERE id = ?");
        $stmt->execute([$username, $email, $user_id]);
        echo json_encode(['message' => 'Perfil actualizado']);
    } catch (PDOException $e) {
        if ($e->errorInfo[1] == 1062) {
            http_response_code(409);
            echo json_encode(['error' => 'Username o email ya existen']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Error al actualizar perfil']);
        }
    }
}

// ─────────────────────────────────────────────
// Función: Eliminar la cuenta del usuario
function deleteAccount($pdo) {
    $user_id = AUTH_USER;

    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$user_id]);

    echo json_encode(['message' => 'Cuenta eliminada']);
}
