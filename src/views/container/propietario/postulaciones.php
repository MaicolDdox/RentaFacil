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

// Obtener el ID del propietario
try {
    $stmt = $pdo->prepare("SELECT id FROM propietarios WHERE id_usuario = :id_usuario");
    $stmt->execute(['id_usuario' => $_SESSION['user_id']]);
    $propietario = $stmt->fetch(PDO::FETCH_ASSOC);
    $id_propietario = $propietario['id'];
} catch (PDOException $e) {
    $error = "Error al obtener el propietario: " . $e->getMessage();
}

// Procesar cancelación de postulación
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['cancelar'])) {
    $postulacion_id = $_POST['postulacion_id'];

    try {
        $pdo->beginTransaction();

        // Verificar que la propiedad pertenece al propietario
        $stmt = $pdo->prepare("SELECT p.id_propietario FROM postulaciones po JOIN propiedades p ON po.id_propiedad = p.id WHERE po.id = :postulacion_id");
        $stmt->execute(['postulacion_id' => $postulacion_id]);
        $propiedad = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($propiedad && $propiedad['id_propietario'] == $id_propietario) {
            // Eliminar la postulación
            $stmt = $pdo->prepare("DELETE FROM postulaciones WHERE id = :postulacion_id");
            $stmt->execute(['postulacion_id' => $postulacion_id]);
            $pdo->commit();
            $success = "Postulación cancelada correctamente.";
        } else {
            $pdo->rollBack();
            $error = "No tienes permiso para cancelar esta postulación.";
        }
    } catch (PDOException $e) {
        $pdo->rollBack();
        $error = "Error al cancelar la postulación: " . $e->getMessage();
    }
}

// Obtener postulaciones del propietario
try {
    $stmt = $pdo->prepare("SELECT po.id, p.direccion, po.nombre_postulante, po.correo, po.telefono_postulante, po.fecha_postulacion FROM postulaciones po JOIN propiedades p ON po.id_propiedad = p.id WHERE p.id_propietario = :id_propietario ORDER BY po.fecha_postulacion DESC");
    $stmt->execute(['id_propietario' => $id_propietario]);
    $postulaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Error al cargar las postulaciones: " . $e->getMessage();
}
?>

<div class="row">
    <div class="col-12">
        <div class="card" style="background-color: #2c2c2c; border-radius: 15px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2); margin:30px;">
            <div class="card-body text-white">
                <h2 class="text-center mb-4">
                    <i class="fa-solid fa-file-signature me-2"></i>POSTULACIONES
                </h2>
            </div>
        </div>
    </div>
</div>

<section class="hero-banner d-flex align-items-center text-center">
    <div class="container text-white">

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger" role="alert">
                <i class="fa-solid fa-circle-exclamation me-2"></i><?php echo $error; ?>
            </div>
        <?php elseif (!empty($success)): ?>
            <div class="alert alert-success" role="alert">
                <i class="fa-solid fa-circle-check me-2"></i><?php echo $success; ?>
            </div>
        <?php endif; ?>

        <!-- Lista de postulaciones -->
        <div class="row">
            <div class="col-12">
                <?php if (!empty($postulaciones)): ?>
                    <div class="table-responsive">
                        <table class="table table-dark table-striped">
                            <thead>
                                <tr>
                                    <th><i class="fa-solid fa-hashtag"></i> Número</th>
                                    <th><i class="fa-solid fa-location-dot"></i> Propiedad</th>
                                    <th><i class="fa-solid fa-user"></i> Nombre</th>
                                    <th><i class="fa-solid fa-envelope"></i> Correo</th>
                                    <th><i class="fa-solid fa-phone"></i> Teléfono</th>
                                    <th><i class="fa-solid fa-calendar-days"></i> Fecha de Postulación</th>
                                    <th><i class="fa-solid fa-gears"></i> Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($postulaciones as $postulacion): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($postulacion['id']); ?></td>
                                        <td><?php echo htmlspecialchars($postulacion['direccion']); ?></td>
                                        <td><?php echo htmlspecialchars($postulacion['nombre_postulante']); ?></td>
                                        <td><?php echo htmlspecialchars($postulacion['correo']); ?></td>
                                        <td><?php echo htmlspecialchars($postulacion['telefono_postulante']); ?></td>
                                        <td>
                                            <i class="fa-regular fa-clock me-1"></i>
                                            <?php echo htmlspecialchars($postulacion['fecha_postulacion']); ?>
                                        </td>
                                        <td>
                                            <!-- Botón cancelar -->
                                            <form method="POST" action="dashboardPropietario.php?page=postulaciones" style="display:inline;" id="cancelForm<?php echo $postulacion['id']; ?>">
                                                <input type="hidden" name="cancelar" value="1">
                                                <input type="hidden" name="postulacion_id" value="<?php echo $postulacion['id']; ?>">
                                                <button type="button" class="btn btn-danger btn-sm" onclick="confirmCancel(<?php echo $postulacion['id']; ?>)">
                                                    <i class="fa-solid fa-user-xmark"></i>
                                                </button>
                                            </form>
                                            <!-- Botón convertir en arrendatario -->
                                            <a href="dashboardPropietario.php?page=arrendatarios&postulacion_id=<?php echo $postulacion['id']; ?>" class="btn btn-success btn-sm" title="Crear arrendatario">
                                                <i class="fa-solid fa-user-check"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="card bg-dark text-white" style="border-radius: 15px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);">
                        <div class="card-body">
                            <p class="text-center">
                                <i class="fa-solid fa-circle-info me-2"></i>No tienes postulaciones registradas.
                            </p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<!-- Script para confirmación de cancelación con SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function confirmCancel(id) {
        Swal.fire({
            title: '¿Estás seguro?',
            text: '¿Deseas cancelar esta postulación?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Confirmar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('cancelForm' + id).submit();
            }
        });
    }
</script>