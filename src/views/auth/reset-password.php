<?php
namespace Maicolddox\RentaFacil;

require '../../vendor/autoload.php';
require '../../config/config.php';

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['token'])) {
    $token = $_GET['token'];

    // Verificar el token
    try {
        $stmt = $pdo->prepare("SELECT * FROM password_resets WHERE token = ? AND expires_at > NOW()");
        $stmt->execute([$token]);
        $reset = $stmt->fetch();

        if (!$reset) {
            $error = "El enlace de restablecimiento no es válido o ha expirado.";
        }
    } catch (\PDOException $e) {
        $error = "Error al verificar el enlace: " . $e->getMessage();
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['token'])) {
    $token = $_POST['token'];
    $contrasena = password_hash($_POST['contrasena'], PASSWORD_DEFAULT);

    try {
        // Verificar el token nuevamente
        $stmt = $pdo->prepare("SELECT * FROM password_resets WHERE token = ? AND expires_at > NOW()");
        $stmt->execute([$token]);
        $reset = $stmt->fetch();

        if ($reset) {
            // Actualizar la contraseña del usuario
            $stmt = $pdo->prepare("UPDATE usuarios SET contrasena = ?, updated_at = NOW() WHERE correo = ?");
            $stmt->execute([$contrasena, $reset['correo']]);

            // Eliminar el token usado
            $stmt = $pdo->prepare("DELETE FROM password_resets WHERE token = ?");
            $stmt->execute([$token]);

            $success = "Tu contraseña ha sido restablecida con éxito. Ahora puedes iniciar sesión.";
        } else {
            $error = "El enlace de restablecimiento no es válido o ha expirado.";
        }
    } catch (\PDOException $e) {
        $error = "Error al restablecer la contraseña: " . $e->getMessage();
    }
}
?>

<?php include '../layouts/Auth/headerAuth.php'; ?>


<!-- Formulario de restablecimiento de contraseña -->
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <h2 class="text-center title-text mt-5">Restablecer Contraseña</h2>

            <?php if (empty($error) && $_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['token'])): ?>
            <p class="text-center">Ingresa tu nueva contraseña.</p>

            <form method="POST" action="reset-password.php">
                <input type="hidden" name="token" value="<?php echo htmlspecialchars($_GET['token']); ?>">
                <div data-mdb-input-init class="form-outline mb-4">
                    <input type="password" id="form1Example23" name="contrasena" class="form-control form-control-lg" required />
                    <label class="form-label text" for="form1Example23">Nueva Contraseña</label>
                </div>

                <button type="submit" data-mdb-button-init data-mdb-ripple-init class="btn btn-light btn-lg btn-block">
                    Restablecer Contraseña
                </button>
            </form>
            <?php endif; ?>

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
                }).then(() => {
                    window.location.href = './login.php';
                });
            </script>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../layouts/Auth/footerAuth.php'; ?>