<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require_once 'config.php';

require '/home/mascotie/public_html/libs/PHPMailer-master/src/Exception.php';
require '/home/mascotie/public_html/libs/PHPMailer-master/src/PHPMailer.php';
require '/home/mascotie/public_html/libs/PHPMailer-master/src/SMTP.php';


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Recoge y limpia los datos del formulario
    $name = strip_tags(trim($_POST["name"]));
    $email = filter_var(trim($_POST["email"]), FILTER_SANITIZE_EMAIL);
    $subject = strip_tags(trim($_POST["subject"]));
    $message = strip_tags(trim($_POST["message"]));

    // Aquí podrías validar los datos, como asegurarte de que el correo sea válido
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        // No es un correo electrónico válido
        echo json_encode("El correo electrónico no es válido.");
        registrarLog($email);
        exit;
    }

    echo sendEmail($email, $name, $subject, $message);
} else {
    // No es una solicitud POST, redirigir o manejar según sea necesario
    registrarLog($email);
    echo json_encode("Método de solicitud no válido.");
}



function configEmail(){
    $mail = new PHPMailer(true); // Passing `true` enables exceptions
    $mail->Username = EMAIL_USERNAME; // SMTP username
    $mail->Password = EMAIL_PASSWORD; // SMTP password
    // Configuración del servidor
    // $mail->SMTPDebug = 2; // Habilita el debug detallado
    $mail->isSMTP(); // Set mailer to use SMTP
    $mail->Host = EMAIL_SERVER; // Especifica tu servidor SMTP
    $mail->SMTPAuth = true; // Habilita la autenticación SMTP  
    $mail->SMTPSecure = 'ssl';
    $mail->Port = 465; // Puerto TCP para conectarse

    registrarLog($mail);
    return $mail;
}

function sendEmail($email, $name, $subject, $message) {
 try {
    $mail = configEmail();
    $mail->setFrom(EMAIL_USERNAME, 'Contacto preinst');
    $mail->addAddress(EMAIL_USERNAME, 'Contacto preinst');
    $mail->addAddress($email, $name);

    // Contenido
    $mail->isHTML(true); // Set email format to HTML
    $mail->Subject = $subject;
    $mail->Body = createEmailTemplate($email, $name, $subject, $message);
    $mail->AltBody = crearMensajeTexto($email, $name, $subject, $message);

    registrarLog($mail);
    $mail->send();
    return json_encode([
        'status' => 'success',
        'message' => 'Formulario recibido',
        'data' => [
            'name' => $name,
            'email' => $email,
            'subject' => $subject,
            'message' => $message
        ]
    ]);
} catch (Exception $e) {
     $errorMsg = "[" . date('Y-m-d H:i:s') . "] Error: " . $e->getMessage() . " en " . $e->getFile() . " línea " . $e->getLine() . "\n";
     registrarLog($errorMsg);
    echo json_encode('El mensaje no pudo ser enviado. Mailer Error: ', $mail->ErrorInfo);
}
}



function createEmailTemplate($email, $name, $subject, $message) {
    $template = <<<HTML
    <!DOCTYPE html>
    <html>
    <head>
        <title>$subject</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                margin: 0;
                padding: 20px;
                color: #333;
            }
            .header {
                background-color: #ff0000;
                color: #ffffff;
                padding: 10px;
                text-align: center;
            }
            .content {
                margin-top: 20px;
            }
            .footer {
                margin-top: 20px;
                padding-top: 10px;
                border-top: 1px solid #eeeeee;
                font-size: 0.8em;
                text-align: center;
            }
        </style>
    </head>
    <body>
        <div class="header">
            <h2>$subject</h2>
        </div>
        <div class="content">
            <p>Hola $name,</p>
            <p>Hemos recibido tu mensaje referente a:</p>
            <strong>$subject</strong>
            <p>$message</p>
            <hr>
            <h3>
                Pronto nos pondremos en contacto contigo
            </h3>
        </div>
        <div class="footer">
            Sent to: $email
        </div>
    </body>
    </html>
    HTML;

    return $template;
}

function crearMensajeTexto($email, $name, $subject, $message) {
    return "Hola, $name\n\nHemos recibido tu mensaje, pronto te responderemos \n\n$message\n\nSaludos,\npreinst.cl";
}

function registrarLog($object){
    $log = json_encode($object);
    $logFile = fopen( "logs.log", "w");
    fwrite($logFile, $log); 
}
?>