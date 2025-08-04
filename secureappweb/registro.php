<?php
// Iniciar sesión para manejar mensajes si quieres (opcional)
// session_start();

header("Content-Type: text/html; charset=UTF-8");

// Incluir conexión a la base de datos
require_once(__DIR__ . '/config/db.php');

$message = '';
$message_color = 'red';

// Procesar solo si hay datos POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Leer y sanitizar datos
    $username = isset($_POST['username']) ? htmlspecialchars(trim($_POST['username'])) : '';
    $email = isset($_POST['email']) ? filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    // Validaciones básicas
    if (!$username || !$email || !$password) {
        $message = 'Faltan campos obligatorios.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = 'Email inválido.';
    } else {
        // Validar contraseña segura
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
            $message = $resultado;
        } else {
            // Verificar si username o email existen
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$username, $email]);

            if ($stmt->fetch()) {
                $message = 'El nombre de usuario o email ya están registrados.';
            } else {
                // Hashear contraseña e insertar usuario
                $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
                $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");

                try {
                    $stmt->execute([$username, $email, $hashedPassword]);
                    $message = 'Usuario registrado correctamente.';
                    $message_color = 'green';

                    // Limpiar variables para que no se muestren en el formulario después de éxito
                    $username = $email = '';
                } catch (PDOException $e) {
                    $message = 'Error al registrar usuario: ' . htmlspecialchars($e->getMessage());
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <title>Registro de Usuario</title>
  <style>
    /* Reset y base */
    * {
      box-sizing: border-box;
    }
    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background-color: #f0f2f5;
      margin: 0;
      height: 100vh;
      display: flex;
      justify-content: center;
      align-items: center;
      padding: 1rem;
    }
    .container {
      background: #fff;
      padding: 2rem 2.5rem;
      border-radius: 12px;
      box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
      width: 100%;
      max-width: 420px;
    }
    h2 {
      text-align: center;
      margin-bottom: 1.8rem;
      color: #2c3e50;
      font-weight: 700;
    }
    form {
      display: flex;
      flex-direction: column;
    }
    label {
      font-weight: 600;
      margin-bottom: 0.4rem;
      color: #34495e;
    }
    input[type="text"],
    input[type="email"],
    input[type="password"] {
      padding: 12px 15px;
      margin-bottom: 1.2rem;
      border: 1.5px solid #ccc;
      border-radius: 8px;
      font-size: 1rem;
      transition: border-color 0.25s ease;
    }
    input[type="text"]:focus,
    input[type="email"]:focus,
    input[type="password"]:focus {
      border-color: #007bff;
      outline: none;
      box-shadow: 0 0 6px rgba(0, 123, 255, 0.5);
    }
    button {
      background-color: #007bff;
      color: white;
      border: none;
      padding: 14px 0;
      font-size: 1.1rem;
      border-radius: 8px;
      cursor: pointer;
      font-weight: 600;
      transition: background-color 0.3s ease;
      margin-top: 0.5rem;
    }
    button:hover {
      background-color: #0056b3;
    }
    #mensaje {
      margin-top: 1.5rem;
      font-weight: 600;
      text-align: center;
      min-height: 1.4em;
      user-select: none;
      color: <?= $message_color ?>;
    }
  </style>
</head>
<body>
  <div class="container">
    <h2>Formulario de Registro</h2>
    <form id="form-registro" method="POST" action="">
      <label for="username">Nombre de Usuario:</label>
      <input type="text" id="username" name="username" required value="<?= htmlspecialchars($username ?? '') ?>" />

      <label for="email">Correo Electrónico:</label>
      <input type="email" id="email" name="email" required value="<?= htmlspecialchars($email ?? '') ?>" />

      <label for="password">Contraseña:</label>
      <input type="password" id="password" name="password" required />

      <button type="submit">Registrarse</button>
    </form>

    <p id="mensaje"><?= htmlspecialchars($message) ?></p>
  </div>
</body>
</html>
