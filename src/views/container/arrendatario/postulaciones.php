<?php
// Iniciar la sesión
session_start();

// Verificar si el usuario está logueado y es arrendatario
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'arrendatario') {
    header("Location: ../../auth/login.php");
    exit;
}

// Incluir la configuración de la base de datos
require '../../../config/config.php';

$success = '';
$error = '';

// Procesar postulación
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['postularse'])) {
    $propiedad_id = $_POST['propiedad_id'];

    try {
        // Verificar si la propiedad existe y está disponible
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM propiedades WHERE id = :propiedad_id AND estado = 'Disponible'");
        $stmt->execute(['propiedad_id' => $propiedad_id]);
        if ($stmt->fetchColumn() == 0) {
            $error = "La propiedad no está disponible o no existe.";
        } else {
            // Verificar si ya existe una postulación
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM postulaciones WHERE id_arrendatario = :id_arrendatario AND id_propiedad = :propiedad_id");
            $stmt->execute(['id_arrendatario' => $_SESSION['user_id'], 'propiedad_id' => $propiedad_id]);
            if ($stmt->fetchColumn() > 0) {
                $error = "Ya te has postulado a esta propiedad.";
            } else {
                // Insertar postulación
                $stmt = $pdo->prepare("INSERT INTO postulaciones (id_arrendatario, id_propiedad, fecha_postulacion, estado) VALUES (:id_arrendatario, :propiedad_id, NOW(), 'Pendiente')");
                $stmt->execute([
                    'id_arrendatario' => $_SESSION['user_id'],
                    'propiedad_id' => $propiedad_id
                ]);
                $success = "Postulación enviada correctamente.";
            }
        }
    } catch (PDOException $e) {
        $error = "Error al enviar la postulación: " . $e->getMessage();
    }
}

// Obtener propiedades disponibles
try {
    $stmt = $pdo->prepare("SELECT p.id, p.direccion, p.tipo, p.precio, i.url_imagen FROM propiedades p LEFT JOIN imagenes_propiedad i ON p.id = i.id_propiedad AND i.orden = 1 WHERE p.estado = 'Disponible'");
    $stmt->execute();
    $propiedades = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Error al cargar las propiedades: " . $e->getMessage();
}

// Obtener postulaciones del arrendatario
try {
    $stmt = $pdo->prepare("SELECT p.id, p.direccion, p.tipo, p.precio, i.url_imagen FROM postulaciones po JOIN propiedades p ON po.id_propiedad = p.id LEFT JOIN imagenes_propiedad i ON p.id = i.id_propiedad AND i.orden = 1 WHERE po.id_arrendatario = :id_arrendatario AND po.estado = 'Pendiente'");
    $stmt->execute(['id_arrendatario' => $_SESSION['user_id']]);
    $misPostulaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Error al cargar las postulaciones: " . $e->getMessage();
}
?>

<?php require '../../layouts/container/propietario/headerPropietario.php'; ?>

<div class="container mt-5">
    <!-- Lista de propiedades disponibles -->
    <div class="row">
        <div class="col-12">
            <div class="card" style="background-color: #2c2c2c; border-radius: 15px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);">
                <div class="card-body text-white">
                    <h2 class="text-center mb-4">Propiedades Disponibles</h2>
                    <?php if (!empty($propiedades)): ?>
                        <div class="row">
                            <?php foreach ($propiedades as $propiedad): ?>
                                <div class="col-md-4 mb-4">
                                    <div class="card h-100" style="background-color: #2c2c2c;">
                                        <img src="<?php echo '../../../public/' . htmlspecialchars($propiedad['url_imagen'] ?? ''); ?>" class="card-img-top" alt="Imagen" style="height: 200px; object-fit: cover;" onerror="this.src='../../../public/assets/img/default.jpg';">
                                        <div class="card-body">
                                            <h5 class="card-title"><?php echo htmlspecialchars($propiedad['direccion']); ?></h5>
                                            <p class="card-text">Tipo: <?php echo htmlspecialchars($propiedad['tipo']); ?></p>
                                            <p class="card-text">Precio: <?php echo number_format($propiedad['precio'], 2); ?></p>
                                            <form method="POST" action="postulaciones.php" style="display:inline;" id="postForm<?php echo $propiedad['id']; ?>">
                                                <input type="hidden" name="postularse" value="1">
                                                <input type="hidden" name="propiedad_id" value="<?php echo $propiedad['id']; ?>">
                                                <button type="button" class="btn btn-primary w-100" onclick="confirmPostulation(<?php echo $propiedad['id']; ?>)">Postularse</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-center">No hay propiedades disponibles.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Lista de mis postulaciones -->
    <div class="row mt-5">
        <div class="col-12">
            <div class="card" style="background-color: #2c2c2c; border-radius: 15px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);">
                <div class="card-body text-white">
                    <h2 class="text-center mb-4">Mis Postulaciones</h2>
                    <?php if (!empty($misPostulaciones)): ?>
                        <div class="row">
                            <?php foreach ($misPostulaciones as $postulacion): ?>
                                <div class="col-md-4 mb-4">
                                    <div class="card h-100" style="background-color: #2c2c2c;">
                                        <img src="<?php echo '../../../public/' . htmlspecialchars($postulacion['url_imagen'] ?? ''); ?>" class="card-img-top" alt="Imagen" style="height: 200px; object-fit: cover;" onerror="this.src='../../../public/assets/img/default.jpg';">
                                        <div class="card-body">
                                            <h5 class="card-title"><?php echo htmlspecialchars($postulacion['direccion']); ?></h5>
                                            <p class="card-text">Tipo: <?php echo htmlspecialchars($postulacion['tipo']); ?></p>
                                            <p class="card-text">Precio: <?php echo number_format($postulacion['precio'], 2); ?></p>
                                            <p class="card-text">Estado: Pendiente</p>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-center">No tienes postulaciones pendientes.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

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

<!-- Script para confirmación de postulación con SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function confirmPostulation(id) {
    Swal.fire({
        title: '¿Estás seguro?',
        text: '¿Deseas postularte a esta propiedad?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Confirmar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('postForm' + id).submit();
        }
    });
}
</script>

<?php require '../../layouts/container/propietario/footerPropietario.php'; ?>