<?php
header("Content-Type: application/json");

// Incluir conexión a la base de datos
require_once(__DIR__ . '/../config/db.php');

// Leer datos del cuerpo (JSON)
$data = json_decode(file_get_contents("php://input"));

// Verificar campos obligatorios
if (
    !isset($data->username) ||
    !isset($data->email) ||
    !isset($data->password)
) {
    http_response_code(400);
    echo json_encode(['error' => 'Faltan campos obligatorios']);
    exit;
}

// Sanitizar y validar los datos
$username = htmlspecialchars(trim($data->username));
$email = filter_var(trim($data->email), FILTER_SANITIZE_EMAIL);
$password = trim($data->password);

// Validar formato del email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['error' => 'Email inválido']);
    exit;
}

// === Validación segura de contraseña ===
function validarPasswordSegura($password) {
    if (strlen($password) < 8) {
        return 'La contraseña debe tener al menos 8 caracteres';
    }
    if (preg_match('/\s/', $password)) {
        return 'La contraseña no debe contener espacios en blanco';
    }
    if (!preg_match('/[A-Z]/', $password)) {
        return 'La contraseña debe contener al menos una letra mayúscula';
    }
    if (!preg_match('/[a-z]/', $password)) {
        return 'La contraseña debe contener al menos una letra minúscula';
    }
    if (!preg_match('/[0-9]/', $password)) {
        return 'La contraseña debe contener al menos un número';
    }
    if (!preg_match('/[\W_]/', $password)) {
        return 'La contraseña debe contener al menos un carácter especial';
    }

    return true;
}

$resultado = validarPasswordSegura($password);
if ($resultado !== true) {
    http_response_code(400);
    echo json_encode(['error' => $resultado]);
    exit;
}

// Verificar si el username o email ya existen
$stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
$stmt->execute([$username, $email]);
if ($stmt->fetch()) {
    http_response_code(409); // Conflicto
    echo json_encode(['error' => 'El nombre de usuario o email ya están registrados']);
    exit;
}

// Hashear la contraseña
$hashedPassword = password_hash($password, PASSWORD_BCRYPT);

// Insertar el nuevo usuario
$stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
try {
    $stmt->execute([$username, $email, $hashedPassword]);
    http_response_code(201); // Created
    echo json_encode(['message' => 'Usuario registrado correctamente']);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al registrar usuario: ' . $e->getMessage()]);
}
?>
