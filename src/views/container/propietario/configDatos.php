<?php
// Iniciar la sesión
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar si el usuario está logueado y es propietario
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'propietario') {
    header("Location: ../../auth/login.php");
    exit;
}

// Incluir la configuración de la base de datos
require '../../../config/config.php';

$success = '';
$error = '';

// Obtener datos actuales del propietario
try {
    $stmt = $pdo->prepare("SELECT u.id, u.nombre, u.correo, u.telefono, u.contrasena FROM usuarios u JOIN propietarios p ON u.id = p.id_usuario WHERE p.id_usuario = :id_usuario");
    $stmt->execute(['id_usuario' => $_SESSION['user_id']]);
    $propietario = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$propietario) {
        $error = "No se encontraron datos del propietario.";
    }
} catch (PDOException $e) {
    $error = "Error al cargar los datos: " . $e->getMessage();
}

// Procesar actualización de datos
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['actualizar'])) {
    $nombre = $_POST['nombre'];
    $correo = $_POST['correo'];
    $telefono = $_POST['telefono'];
    $contrasena = $_POST['contrasena'];
    $contrasena2 = $_POST['contrasena2'];

    // Validar confirmación de contraseña solo si se intenta cambiar
    if (!empty($contrasena) || !empty($contrasena2)) {
        if ($contrasena !== $contrasena2) {
            $error = "Las contraseñas no coinciden.";
        } elseif (strlen($contrasena) < 6) {
            $error = "La contraseña debe tener al menos 6 caracteres.";
        }
    }

    if (empty($error)) {
        $contrasena_hash = !empty($contrasena) ? password_hash($contrasena, PASSWORD_DEFAULT) : $propietario['contrasena'];
        try {
            $pdo->beginTransaction();

            // Verificar si el correo ya existe (excluyendo el propio usuario)
            $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE correo = :correo AND id != :id");
            $stmt->execute(['correo' => $correo, 'id' => $propietario['id']]);
            if ($stmt->fetchColumn() && $correo !== $propietario['correo']) {
                $error = "El correo ya está en uso.";
            } else {
                // Actualizar datos
                $stmt = $pdo->prepare("UPDATE usuarios SET nombre = :nombre, correo = :correo, telefono = :telefono, contrasena = :contrasena WHERE id = :id");
                $stmt->execute([
                    'nombre' => $nombre,
                    'correo' => $correo,
                    'telefono' => $telefono,
                    'contrasena' => $contrasena_hash,
                    'id' => $propietario['id']
                ]);

                // Actualizar las variables de sesión
                $_SESSION['nombre'] = $nombre;
                $_SESSION['correo'] = $correo;

                $pdo->commit();
                $success = "Datos actualizados correctamente.";
            }
        } catch (PDOException $e) {
            $pdo->rollBack();
            $error = "Error al actualizar los datos: " . $e->getMessage();
        }
    }
}
?>

    
<div class="container mt-5">
    <div class="card" style="background-color: #2c2c2c; border-radius: 15px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);">
        <div class="card-body text-white">
            <h2 class="text-center mb-4">Configurar Datos Personales</h2>
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger" role="alert"><?php echo $error; ?></div>
            <?php elseif (!empty($success)): ?>
                <div class="alert alert-success" role="alert"><?php echo $success; ?></div>
            <?php endif; ?>

            <form method="POST" action="dashboardPropietario.php?page=configuraciones">
                <input type="hidden" name="actualizar" value="1">
                <div class="mb-3">
                    <label for="nombre" class="form-label">Nombre</label>
                    <input type="text" class="form-control" id="nombre" name="nombre" value="<?php echo htmlspecialchars($propietario['nombre']); ?>" required>
                </div>
                <div class="mb-3">
                    <label for="correo" class="form-label">Correo</label>
                    <input type="email" class="form-control" id="correo" name="correo" value="<?php echo htmlspecialchars($propietario['correo']); ?>" required>
                </div>
                <div class="mb-3">
                    <label for="telefono" class="form-label">Teléfono</label>
                    <input type="tel" class="form-control" id="telefono" name="telefono" value="<?php echo htmlspecialchars($propietario['telefono']); ?>" required>
                </div>
                <div class="mb-3">
                    <label for="contrasena" class="form-label">Contraseña (dejar en blanco para no cambiar)</label>
                    <input type="password" class="form-control" id="contrasena" name="contrasena">
                </div>
                <div class="mb-3">
                    <label for="contrasena2" class="form-label">Confirmar Contraseña (dejar en blanco para no cambiar)</label>
                    <input type="password" class="form-control" id="contrasena2" name="contrasena2">
                </div>
                <button type="submit" class="btn btn-primary w-100">Actualizar</button>
            </form>
        </div>
    </div>
</div>
