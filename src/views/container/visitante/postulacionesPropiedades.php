<?php
// Iniciar la sesión
session_start();

// Incluir la configuración de la base de datos
require '../../../config/config.php';

$success = '';
$error = '';
$propiedad = null;
$zona = isset($_GET['zona']) ? trim($_GET['zona']) : '';

// Obtener el ID de la propiedad
$propiedad_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Preparar la consulta para obtener los detalles básicos de la propiedad
try {
    $stmt = $pdo->prepare("SELECT id, direccion, estado FROM propiedades WHERE id = :id AND estado = 'Disponible'");
    $stmt->execute(['id' => $propiedad_id]);
    $propiedad = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$propiedad) {
        $error = "La propiedad no existe o no está disponible.";
    }
} catch (PDOException $e) {
    $error = "Error al cargar la propiedad: " . $e->getMessage();
}

// Procesar postulación (para todos, logueados o no)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['postularse'])) {
    $propiedad_id = $_POST['propiedad_id'];
    $nombre_postulante = trim($_POST['nombre_postulante'] ?? '');
    $correo = trim($_POST['correo'] ?? '');
    $telefono_postulante = trim($_POST['telefono_postulante'] ?? '');

    // Validar campos
    if (empty($nombre_postulante) || empty($correo) || empty($telefono_postulante)) {
        $error = "Por favor, completa todos los campos requeridos.";
    } elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $error = "Por favor, ingresa un correo electrónico válido.";
    } else {
        try {
            // Verificar si la propiedad existe y está disponible
            $stmt = $pdo->prepare("SELECT id FROM propiedades WHERE id = :propiedad_id AND estado = 'Disponible'");
            $stmt->execute(['propiedad_id' => $propiedad_id]);
            $property = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$property) {
                $error = "La propiedad no está disponible o no existe.";
            } else {
                // Verificar si ya existe una postulación con el mismo correo
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM postulaciones WHERE correo = :correo AND id_propiedad = :propiedad_id");
                $stmt->execute(['correo' => $correo, 'propiedad_id' => $propiedad_id]);
                if ($stmt->fetchColumn() > 0) {
                    $error = "Ya has enviado una postulación para esta propiedad con este correo.";
                } else {
                    // Insertar postulación en la tabla postulaciones
                    $stmt = $pdo->prepare("INSERT INTO postulaciones (id_propiedad, nombre_postulante, correo, telefono_postulante, fecha_postulacion) VALUES (:id_propiedad, :nombre_postulante, :correo, :telefono_postulante, NOW())");
                    $stmt->execute([
                        'id_propiedad' => $propiedad_id,
                        'nombre_postulante' => htmlspecialchars($nombre_postulante),
                        'correo' => htmlspecialchars($correo),
                        'telefono_postulante' => htmlspecialchars($telefono_postulante)
                    ]);

                    // Verificar que la inserción fue exitosa
                    if ($stmt->rowCount() > 0) {
                        $success = "Postulación enviada correctamente. El propietario se pondrá en contacto contigo.";
                        $error = ''; // Limpiar el mensaje de error si la operación fue exitosa
                    } else {
                        $error = "Error al guardar la postulación.";
                    }
                }
            }
        } catch (PDOException $e) {
            $error = "Error al enviar la postulación: " . $e->getMessage();
        }
    }
}
?>

<?php include '../../layouts/container/visitante/headerVisitante.php'; ?>

<section class="hero-banner d-flex align-items-center text-center">
    <div class="container text-white">
        <h2 class="mb-4">Postularse a la Propiedad</h2>
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger" role="alert"><?php echo $error; ?></div>
        <?php endif; ?>

        <?php if ($propiedad): ?>
            <div class="row">
                <div class="col-md-6 offset-md-3">
                    <div class="card bg-dark text-white" style="border-radius: 15px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($propiedad['direccion']); ?></h5>
                            <!-- Formulario único para todos -->
                            <form method="POST" action="postulacionesPropiedades.php<?php echo !empty($zona) ? '?zona=' . urlencode($zona) : ''; ?>">
                                <input type="hidden" name="postularse" value="1">
                                <input type="hidden" name="propiedad_id" value="<?php echo $propiedad['id']; ?>">
                                <div class="mb-3">
                                    <label for="nombre_postulante" class="form-label">Nombre Completo</label>
                                    <input type="text" class="form-control" id="nombre_postulante" name="nombre_postulante" placeholder="Ingresa tu nombre completo" required>
                                </div>
                                <div class="mb-3">
                                    <label for="correo" class="form-label">Correo Electrónico</label>
                                    <input type="email" class="form-control" id="correo" name="correo" placeholder="Ingresa tu correo electrónico" required>
                                </div>
                                <div class="mb-3">
                                    <label for="telefono_postulante" class="form-label">Número de Teléfono</label>
                                    <input type="tel" class="form-control" id="telefono_postulante" name="telefono_postulante" placeholder="Ingresa tu número de teléfono" required>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <a href="filtro.php<?php echo !empty($zona) ? '?zona=' . urlencode($zona) : ''; ?>" class="btn btn-secondary">Volver</a>
                                    <button type="submit" class="btn btn-primary">Enviar Postulación</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- SweetAlert para el mensaje de éxito -->
<?php if (!empty($success)): ?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    Swal.fire({
        icon: 'success',
        title: '¡Éxito!',
        text: '<?php echo $success; ?>',
        confirmButtonText: 'Aceptar',
        willClose: () => {
            window.location.href = 'filtro.php<?php echo !empty($zona) ? "?zona=" . urlencode($zona) : ""; ?>';
        }
    });
</script>
<?php endif; ?>

<?php include '../../layouts/container/visitante/footerVisitante.php'; ?>