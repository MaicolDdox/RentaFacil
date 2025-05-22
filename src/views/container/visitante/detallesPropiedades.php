<?php
// Iniciar la sesión
session_start();

// Incluir la configuración de la base de datos
require '../../../config/config.php';

$error = '';
$propiedad = null;
$zona = isset($_GET['zona']) ? trim($_GET['zona']) : '';

// Obtener el ID de la propiedad
$propiedad_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Preparar la consulta para obtener los detalles de la propiedad
try {
    $stmt = $pdo->prepare("SELECT p.id, p.direccion, p.estado, p.precio, p.descripcion, p.zona, i.url_imagen FROM propiedades p LEFT JOIN imagenes_propiedad i ON p.id = i.id_propiedad AND i.orden = 1 WHERE p.id = :id AND p.estado = 'Disponible'");
    $stmt->execute(['id' => $propiedad_id]);
    $propiedad = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$propiedad) {
        $error = "La propiedad no existe o no está disponible.";
    }
} catch (PDOException $e) {
    $error = "Error al cargar los detalles de la propiedad: " . $e->getMessage();
}
?>

<?php include '../../layouts/container/visitante/headerVisitante.php'; ?>

<section class="hero-banner d-flex align-items-center text-center">
    <div class="container text-white">
        <h2 class="mb-4">Detalles de la Propiedad</h2>
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger" role="alert"><?php echo $error; ?></div>
        <?php elseif ($propiedad): ?>
            <div class="row">
                <div class="col-md-8 offset-md-2">
                    <div class="card bg-dark text-white" style="border-radius: 15px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);">
                        <img src="<?php echo '../../../public/' . htmlspecialchars($propiedad['url_imagen'] ?? ''); ?>" class="card-img-top" alt="Imagen" style="height: 300px; object-fit: cover;" onerror="this.src='../../../public/assets/img/default.jpg';">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($propiedad['direccion']); ?></h5>
                            <p class="card-text"><strong>Estado:</strong> <?php echo htmlspecialchars($propiedad['estado']); ?></p>
                            <p class="card-text"><strong>Precio:</strong> <?php echo number_format($propiedad['precio'], 2); ?></p>
                            <p class="card-text"><strong>Descripción:</strong> <?php echo htmlspecialchars($propiedad['descripcion']); ?></p>
                            <p class="card-text"><strong>Zona:</strong> <?php echo htmlspecialchars($propiedad['zona']); ?></p>
                            <p class="card-text"><strong>Imágenes:</strong></p>
                            <?php
                            $stmt = $pdo->prepare("SELECT url_imagen, descripcion FROM imagenes_propiedad WHERE id_propiedad = :propiedad_id ORDER BY orden");
                            $stmt->execute(['propiedad_id' => $propiedad['id']]);
                            $imagenes = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            foreach ($imagenes as $imagen) {
                                echo "<img src='../../../public/" . htmlspecialchars($imagen['url_imagen']) . "' alt='" . htmlspecialchars($imagen['descripcion']) . "' style='width: 200px; height: auto; margin-right: 10px;'>";
                            }
                            ?>
                            <div class="mt-4">
                                <a href="filtro.php<?php echo !empty($zona) ? '?zona=' . urlencode($zona) : ''; ?>" class="btn btn-secondary">Volver</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php include '../../layouts/container/visitante/footerVisitante.php'; ?>