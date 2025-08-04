<?php
// Cargar .env
$envPath = __DIR__ . '/../.env';

if (file_exists($envPath)) {
    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($lines as $line) {
        $line = trim($line);

        // Omitir comentarios
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }

        // Verificar que tenga '='
        if (!str_contains($line, '=')) {
            continue;
        }

        list($key, $value) = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);

        // Guardar en $_ENV y en variables de entorno
        $_ENV[$key] = $value;
        putenv("$key=$value"); // Opcional, si usas getenv()
    }
}
