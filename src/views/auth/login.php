<?php
// Iniciar la sesión
session_start();

// Incluir la configuración de la base de datos
require '../../config/config.php';

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $correo = trim($_POST['correo'] ?? '');
    $contrasena = trim($_POST['contrasena'] ?? '');

    // Validar que los campos no estén vacíos
    if (empty($correo) || empty($contrasena)) {
        $error = "Por favor, completa todos los campos.";
    } else {
        try {
            // Consultar el usuario en la base de datos
            $stmt = $pdo->prepare("SELECT id, nombre, contrasena, is_verified FROM usuarios WHERE correo = :correo");
            $stmt->execute(['correo' => $correo]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($contrasena, $user['contrasena']) && $user['is_verified'] == 1) {
                // Autenticación exitosa
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['nombre'] = htmlspecialchars($user['nombre']);

                // Verificar si el usuario es arrendatario
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM arrendatarios WHERE id_usuario = :user_id");
                $stmt->execute(['user_id' => $_SESSION['user_id']]);
                $es_arrendatario = $stmt->fetchColumn();

                if ($es_arrendatario) {
                    // Si es arrendatario, establecer rol y redirigir
                    $_SESSION['rol'] = 'arrendatario';
                    $success = "Inicio de sesión exitoso.";
                    header("Location: ../container/arrendatario/dashboardArrendatario.php");
                    exit;
                } else {
                    // Si no es arrendatario, verificar si es propietario
                    $stmt = $pdo->prepare("SELECT COUNT(*) FROM propietarios WHERE id_usuario = :user_id");
                    $stmt->execute(['user_id' => $_SESSION['user_id']]);
                    $es_propietario = $stmt->fetchColumn();

                    // Si no es propietario, registrarlo automáticamente
                    if ($es_propietario == 0) {
                        $stmt = $pdo->prepare("INSERT INTO propietarios (id_usuario) VALUES (:user_id)");
                        $stmt->execute(['user_id' => $_SESSION['user_id']]);
                    }

                    // Establecer el rol como propietario
                    $_SESSION['rol'] = 'propietario';
                    $success = "Inicio de sesión exitoso.";
                    header("Location: ../container/propietario/dashboardPropietario.php");
                    exit;
                }
            } else {
                $error = "Correo o contraseña incorrectos, o cuenta no verificada.";
            }
        } catch (PDOException $e) {
            $error = "Error al iniciar sesión: " . $e->getMessage();
        }
    }
}
?>

<?php include '../layouts/Auth/headerAuth.php'; ?>


<h2 class="text-center mb-4" style="color: white;">Iniciar Sesión</h2>
<form method="POST" action="login.php">
    <!-- Correo -->
    <div class="mb-3">
        <label for="correo" class="form-label"style="color: white;" >Correo Electrónico</label>
        <input type="email" id="correo" name="correo" class="form-control" required>
    </div>

    <!-- Contraseña -->
    <div class="mb-3">
        <label for="contrasena" class="form-label"style="color: white;" >Contraseña</label>
        <input type="password" id="contrasena" name="contrasena" class="form-control" required>
    </div>

    <!-- Enlace para olvidar contraseña -->
    <div class="d-flex justify-content-center mb-3">
        <a href="./forgot-password.php" style="color: #327dfa;">¿Olvidaste tu contraseña?</a>
    </div>

    <!-- Botón de iniciar sesión -->
    <button type="submit" class="btn btn-light w-100 mb-3">Iniciar Sesión</button>

    <!-- Botón de registrarse -->
    <a href="./register.php" class="btn btn-danger w-100">Registrarse</a>



    <!-- Mostrar SweetAlert si hay éxito o error -->
    <?php if (!empty($success)): ?>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script>
            Swal.fire({
                icon: 'success',
                title: '¡Éxito!',
                text: '<?php echo $success; ?>',
            });
        </script>
    <?php elseif (!empty($error)): ?>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: '<?php echo $error; ?>',
            });
        </script>
    <?php endif; ?>

    <?php include '../layouts/Auth/footerAuth.php'; ?>