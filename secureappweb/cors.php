<?php
// cors.php

// Función para obtener configuración desde getenv() o $_ENV
function get_config(string $key, $default = null) {
    $value = getenv($key);
    if ($value !== false) {
        return $value;
    }
    if (isset($_ENV[$key])) {
        return $_ENV[$key];
    }
    return $default;
}

// Obtener dominio principal desde configuración (variable de entorno o .env)
$main_domain = get_config('MAIN_DOMAIN', 'http://localhost:3000/');

// Lista blanca de orígenes permitidos
$allowed_origins = [
    $main_domain,                    // Dominio de tu app web en Render (dinámico)
    'capacitor://localhost',         // Origen común para apps móviles en desarrollo
    'file://',                       // Origen para apps móviles instaladas
];

// Detectar el origen de la petición
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';

// Validar origen y establecer header si está permitido
if (in_array($origin, $allowed_origins)) {
    header("Access-Control-Allow-Origin: $origin");
    header("Access-Control-Allow-Credentials: true"); // Permite cookies y credenciales
} else {
    // En producción podrías bloquear o poner el dominio principal por defecto
    header("Access-Control-Allow-Origin: $main_domain");
    header("Access-Control-Allow-Credentials: true");
}

// Métodos HTTP permitidos
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");

// Encabezados permitidos en la petición
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Tiempo que el navegador puede cachear esta configuración
header("Access-Control-Max-Age: 86400");

// Responder a preflight OPTIONS requests y terminar la ejecución
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}
