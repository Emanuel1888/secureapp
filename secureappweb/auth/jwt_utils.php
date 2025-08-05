<?php

// Codifica datos en Base64 URL-safe (sin caracteres especiales como +, /, =)
function base64url_encode($data) {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

// Decodifica datos de Base64 URL-safe a formato normal
function base64url_decode($data) {
    return base64_decode(strtr($data, '-_', '+/'));
}

// Carga la clave secreta para firmar/verificar tokens desde el archivo .env
function get_jwt_secret() {
    $secret = getenv('JWT_SECRET');
    return $secret !== false ? $secret : 'clave_por_defecto';
}





// Genera un JWT con un payload personalizado y tiempo de expiración (por defecto: 1 hora)
function generate_jwt(array $payload, int $expireSeconds = 3600): string {
    // Cabecera del token: algoritmo y tipo
    $header = ['alg' => 'HS256', 'typ' => 'JWT'];

    // Se añade el campo "exp" con la hora de expiración (timestamp)
    $payload['exp'] = time() + $expireSeconds;

    // Codificación de cabecera y contenido
    $headerEncoded  = base64url_encode(json_encode($header));
    $payloadEncoded = base64url_encode(json_encode($payload));

    $secret = get_jwt_secret(); // Se obtiene la clave secreta

    // Se genera la firma (firma = hash HMAC de header.payload usando la clave secreta)
    $signature = hash_hmac('sha256', "$headerEncoded.$payloadEncoded", $secret, true);
    $signatureEncoded = base64url_encode($signature);

    // Se une todo: header.payload.signature
    return "$headerEncoded.$payloadEncoded.$signatureEncoded";
}





// Verifica que el JWT sea válido (estructura, firma y expiración)
function is_jwt_valid(string $jwt): bool {
    $secret = get_jwt_secret();
    $parts = explode('.', $jwt); // Divide el JWT en partes

    if (count($parts) !== 3) return false; // JWT debe tener 3 partes

    [$headerEncoded, $payloadEncoded, $signatureEncoded] = $parts;

    // Recalcular la firma esperada
    $expectedSignature = base64url_encode(
        hash_hmac('sha256', "$headerEncoded.$payloadEncoded", $secret, true)
    );

    // Verifica que la firma enviada sea igual a la esperada (protegido contra timing attacks)
    if (!hash_equals($expectedSignature, $signatureEncoded)) return false;

    // Decodifica el contenido (payload)
    $payload = json_decode(base64url_decode($payloadEncoded), true);

    // Verifica si el token ha expirado
    return isset($payload['exp']) && $payload['exp'] >= time();
}

// Extrae el contenido (payload) del JWT si es válido (por ejemplo, user_id)
function get_payload_from_jwt(string $jwt): ?array {
    $parts = explode('.', $jwt);
    if (count($parts) !== 3) return null;

    [$headerEncoded, $payloadEncoded, $signatureEncoded] = $parts;

    // Decodifica el contenido del token
    $payload = json_decode(base64url_decode($payloadEncoded), true);

    // Devuelve el payload como array (si es válido)
    return is_array($payload) ? $payload : null;
}


#1.- Esta función  devuelve el contenido de payload en tipo objeto.
function decode_jwt(string $jwt): object|false {
    if (!is_jwt_valid($jwt)) {
        return false;
    }

    $payload = get_payload_from_jwt($jwt);

    // Devuelve como objeto stdClass
    return is_array($payload) ? (object) $payload : false;
}

#1.- Función para decodificar el ID del user y devolverlo
function get_user_id_from_jwt(string $jwt): ?int {
    $decoded = decode_jwt($jwt); // Usa tu función decode_jwt que valida el token

    if (!$decoded || !isset($decoded->user_id)) {
        // Token inválido o no tiene user_id
        return null;
    }

    return (int) $decoded->user_id;
}
