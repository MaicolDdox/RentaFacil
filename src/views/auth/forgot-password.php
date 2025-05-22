<?php

namespace Maicolddox\RentaFacil;

require '../../vendor/autoload.php';
require '../../config/config.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$success = '';
$error = '';



if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $correo = $_POST['correo'];

    // Validar que el correo sea sintácticamente correcto
    if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $error = "El correo electrónico no es válido.";
    } else {
        // Verificar si el correo existe en la base de datos
        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE correo = ?");
        $stmt->execute([$correo]);
        $user = $stmt->fetch();

        if ($user) {
            // Generar un token único
            $token = bin2hex(random_bytes(32));
            $expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour')); // Expira en 1 hora

            // Guardar el token en la tabla password_resets
            try {
                $stmt = $pdo->prepare("INSERT INTO password_resets (correo, token, expires_at) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE token = ?, expires_at = ?");
                $stmt->execute([$correo, $token, $expiresAt, $token, $expiresAt]);

                // Configurar PHPMailer para enviar el correo
                $mail = new PHPMailer(true);
                try {
                    $mail->isSMTP();
                    $mail->Host = 'smtp.gmail.com';
                    $mail->SMTPAuth = true;
                    $mail->Username = 'maicolduvangascarodas@gmail.com';
                    $mail->Password = 'pokt hqwe ohbc lsgo';
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port = 587;

                    $mail->setFrom('maicolduvangascarodas@gmail.com', 'RentaFacil');
                    $mail->addAddress($correo, $user['nombre']);
                    $mail->isHTML(true);
                    $mail->Subject = 'Restablecer Contraseña - RentaFacil';
                    $resetLink = "http://localhost/RentaFacil/src/views/auth/reset-password.php?token=" . urlencode($token);
                    $mail->Body = "Hola {$user['nombre']},<br><br>Recibimos una solicitud para restablecer tu contraseña en RentaFacil. Haz clic en el siguiente enlace para restablecer tu contraseña:<br><br><a href='$resetLink'>Restablecer Contraseña</a><br><br>Este enlace expirará en 1 hora.";
                    $mail->AltBody = "Hola {$user['nombre']},\n\nRecibimos una solicitud para restablecer tu contraseña en RentaFacil. Copia y pega este enlace en tu navegador para restablecer tu contraseña:\n\n$resetLink\n\nEste enlace expirará en 1 hora.";

                    $mail->send();
                    $success = "Se ha enviado un enlace para restablecer tu contraseña a tu correo.";
                } catch (Exception $e) {
                    $error = "No se pudo enviar el correo. Error: {$mail->ErrorInfo}";
                }
            } catch (\PDOException $e) {
                $error = "Error al generar el enlace de recuperación: " . $e->getMessage();
            }
        } else {
            $error = "No se encontró una cuenta con ese correo.";
        }
    }
}
?>

<?php include '../layouts/Auth/headerAuth.php'; ?>


<!-- Formulario de recuperación de contraseña -->

<h2 class="text-center title-text mt-5">Recuperar Contraseña</h2>
<p class="text-center">Ingresa tu correo para recibir un enlace de restablecimiento.</p>

<form method="POST" action="forgot-password.php">
    <div data-mdb-input-init class="form-outline mb-4">
        <input type="email" id="form1Example13" name="correo" class="form-control form-control-lg" required />
        <label class="form-label text" for="form1Example13">Correo Electrónico</label>
    </div>

    
    <button type="submit" data-mdb-button-init data-mdb-ripple-init class="btn btn-light btn-lg btn-block">
        Enviar Enlace
    </button><br>
    <a href="./login.php" class="btn btn-danger btn-lg btn-block mt-3" >Volver a Iniciar Sesión</a>


</form>

<!-- Mostrar SweetAlert si hay éxito o error -->
<?php if (!empty($success)): ?>
    <script>
        Swal.fire({
            icon: 'success',
            title: '¡Éxito!',
            text: '<?php echo $success; ?>',
        });
    </script>
<?php elseif (!empty($error)): ?>
    <script>
        Swal.fire({
            icon: 'error',
            title: 'ERROR!',
            text: '<?php echo $error; ?>',
        });
    </script>
<?php endif; ?>
</div>
</div>
</div>

<?php include '../layouts/Auth/footerAuth.php'; ?>