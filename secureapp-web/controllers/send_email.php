<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/load_env.php'; // para usar getenv()

function sendTokenByEmail($to, $token) {
    $mail = new PHPMailer(true);

    try {
        // Configuración del servidor
        $mail->isSMTP();
        $mail->Host       = getenv('SMTP_HOST');
        $mail->SMTPAuth   = true;
        $mail->Username   = getenv('SMTP_USERNAME');
        $mail->Password   = getenv('SMTP_PASSWORD');
        $mail->SMTPSecure = 'tls';
        $mail->Port       = getenv('SMTP_PORT');

        // Remitente y destinatario
        $mail->setFrom(getenv('SMTP_FROM'), getenv('SMTP_FROM_NAME'));
        $mail->addAddress($to);

        // Contenido
        $mail->isHTML(true);
        $mail->Subject = 'Tu código de verificación de SecureApp';
        $mail->Body    = "<h2>Código de verificación</h2><p>Tu token es:</p><code>$token</code>";
        $mail->AltBody = "Tu token de verificación es: $token";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Error al enviar correo: {$mail->ErrorInfo}");
        return false;
    }
}
