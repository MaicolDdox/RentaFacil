<?php
namespace Maicolddox\RentaFacil;

require '../../vendor/autoload.php';
require '../../config/config.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = $_POST['nombre'];
    $correo = $_POST['correo'];
    $telefono = $_POST['telefono'];
    $contrasena = password_hash($_POST['contrasena'], PASSWORD_DEFAULT);

    // Validar que el correo sea sintácticamente correcto
    if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $error = "El correo electrónico no es válido.";
    } else {
        // Verificar si el correo ya existe en la base de datos
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM usuarios WHERE correo = ?");
        $stmt->execute([$correo]);
        $emailExists = $stmt->fetchColumn();

        if ($emailExists > 0) {
            $error = "Este correo ya está registrado. Por favor, usa otro correo.";
        } else {
            // Generar un código de verificación aleatorio
            $verification_code = str_pad(mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);

            // Configurar PHPMailer para enviar el correo
            $mail = new PHPMailer(true);
            try {
                // Configuración del servidor SMTP (Gmail)
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'maicolduvangascarodas@gmail.com';
                $mail->Password = 'pokt hqwe ohbc lsgo';
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;

                // Configuración del correo
                $mail->setFrom('maicolduvangascarodas@gmail.com', 'RentaFacil');
                $mail->addAddress($correo, $nombre);
                $mail->isHTML(true);
                $mail->Subject = 'Verificacion de Correo - RentaFacil';
                $mail->Body = "Hola $nombre,<br><br>Gracias por registrarte en RentaFacil. Tu código de verificacion es: <b>$verification_code</b><br><br>Por favor, ingresa este codigo para verificar tu correo.";
                $mail->AltBody = "Hola $nombre,\n\nGracias por registrarte en RentaFacil. Tu código de verificación es: $verification_code\n\nPor favor, ingresa este código para verificar tu correo.";

                // Enviar el correo
                $mail->send();

                // Si el correo se envía con éxito, guardar el usuario en la base de datos
                try {
                    $stmt = $pdo->prepare("INSERT INTO usuarios (nombre, correo, telefono, contrasena, verification_code, is_verified, created_at, updated_at) VALUES (?, ?, ?, ?, ?, FALSE, NOW(), NOW())");
                    $stmt->execute([$nombre, $correo, $telefono, $contrasena, $verification_code]);
                    $success = "Se ha enviado un código de verificación a tu correo. Por favor, verifica tu cuenta.";
                } catch (\PDOException $e) {
                    $error = "Error al registrar en la base de datos: " . $e->getMessage();
                }
            } catch (Exception $e) {
                $error = "No se pudo enviar el correo de verificación. Error: {$mail->ErrorInfo}";
            }
        }
    }
}
?>

<?php include '../layouts/Auth/headerAuth.php'; ?>


<!-- Formulario de registro -->
<form method="POST" action="register.php">
    <!-- Titulo -->
    <div>
        <h2 class="text-center title-text">Regístrese con el correo electrónico</h2>
    </div>

    <!-- Nombre input -->
    <div data-mdb-input-init class="form-outline mb-4">
        <input type="text" id="form1Example1" name="nombre" class="form-control form-control-lg" />
        <label class="form-label text" for="form1Example1">Nombre</label>
    </div>

    <!-- Email input -->
    <div data-mdb-input-init class="form-outline mb-4">
        <input type="email" id="form1Example13" name="correo" class="form-control form-control-lg" />
        <label class="form-label text" for="form1Example13">Correo Electrónico</label>
    </div>

    <!-- Teléfono input -->
    <div data-mdb-input-init class="form-outline mb-4">
        <input type="text" id="form1Example2" name="telefono" class="form-control form-control-lg" />
        <label class="form-label text" for="form1Example2">Teléfono</label>
    </div>

    <!-- Password input -->
    <div data-mdb-input-init class="form-outline mb-4">
        <input type="password" id="form1Example23" name="contrasena" class="form-control form-control-lg" />
        <label class="form-label text" for="form1Example23">Contraseña</label>
    </div>

    <!-- Checkbox -->
    <div class="d-flex justify-content-around align-items-center mb-4">
        <div class="form-check">
            <input class="form-check-input" type="checkbox" value="" id="form1Example3" checked />
            <label class="form-check-label text" for="form1Example3">Recordar</label>
        </div>
        <a href="./login.php" style="color: rgb(50, 125, 238);">¿Tienes cuenta? Inicia Sesión</a>
    </div>

    <!-- Submit button -->
    <button type="submit" data-mdb-button-init data-mdb-ripple-init class="btn btn-light btn-lg btn-block">
        Crear Cuenta
    </button>

    <!-- Volver a la página principal -->
    <a href="../../../public/index.php" class="btn btn-danger btn-lg btn-block">Volver</a>
</form>

<!-- Mostrar SweetAlert si hay éxito o error -->
<?php if (!empty($success)): ?>
<script>
    Swal.fire({
        icon: 'success',
        title: '¡Éxito!',
        text: '<?php echo $success; ?>',
    }).then(() => {
        window.location.href = './verify.php?correo=<?php echo urlencode($correo); ?>';
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

<?php include '../layouts/Auth/footerAuth.php'; ?>