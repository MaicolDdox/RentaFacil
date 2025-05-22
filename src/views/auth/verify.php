<?php

namespace Maicolddox\RentaFacil;

require '../../vendor/autoload.php';
require '../../config/config.php';

use PHPMailer\PHPMailer\Exception;

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $correo = $_POST['correo'];
    $code = $_POST['verification_code'];

    try {
        // Verificar si el correo y el código coinciden
        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE correo = ? AND verification_code = ? AND is_verified = FALSE");
        $stmt->execute([$correo, $code]);
        $user = $stmt->fetch();

        if ($user) {
            // Actualizar el estado a verificado
            $stmt = $pdo->prepare("UPDATE usuarios SET is_verified = TRUE, updated_at = NOW() WHERE correo = ? AND verification_code = ?");
            $stmt->execute([$correo, $code]);
            $success = "Tu cuenta ha sido verificada con éxito. Ahora puedes iniciar sesión.";
        } else {
            $error = "Código de verificación incorrecto o cuenta ya verificada.";
        }
    } catch (\PDOException $e) {
        $error = "Error al verificar la cuenta: " . $e->getMessage();
    }
}
?>

<?php include '../layouts/Auth/headerAuth.php'; ?>


<!-- Formulario de verificación -->
<div class="row justify-content-center">
    <h2 class="text-center title-text mt-5">Verificación de Correo</h2>
    <p class="text-center">Ingresa el código de verificación enviado a tu correo.</p>

    <form method="POST" action="verify.php">
        <div data-mdb-input-init class="form-outline mb-4">
            <input type="email" id="form1Example13" name="correo" class="form-control form-control-lg" value="<?php echo isset($_POST['correo']) ? htmlspecialchars($_POST['correo']) : ''; ?>" required />
            <label class="form-label text" for="form1Example13">Correo Electrónico</label>
        </div>

        <div data-mdb-input-init class="form-outline mb-4">
            <input type="text" id="form1Example2" name="verification_code" class="form-control form-control-lg" required />
            <label class="form-label text" for="form1Example2">Código de Verificación</label>
        </div>

        <button type="submit" data-mdb-button-init data-mdb-ripple-init class="btn btn-light btn-lg btn-block">
            Verificar Código
        </button>

        <a href="./login.php" class="btn btn-danger btn-lg btn-block mt-3">Volver a Iniciar Sesión</a>
    </form>

    <!-- Mostrar SweetAlert si hay éxito o error -->
    <?php if (!empty($success)): ?>
        <script>
            Swal.fire({
                icon: 'success',
                title: '¡Éxito!',
                text: '<?php echo $success; ?>',
            }).then(() => {
                window.location.href = './login.php';
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


<?php include '../layouts/Auth/footerAuth.php'; ?>