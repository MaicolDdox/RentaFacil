<?php
// Iniciar la sesión
session_start();

// Incluir la configuración de la base de datos
require '../../../config/config.php';

$success = '';
$error = '';

// Obtener la zona del parámetro GET
$zona = isset($_GET['zona']) ? trim($_GET['zona']) : '';

// Preparar la consulta para propiedades
try {
    if (!empty($zona)) {
        $stmt = $pdo->prepare("SELECT p.id, p.direccion, p.estado, p.precio, p.descripcion, p.zona, i.url_imagen FROM propiedades p LEFT JOIN imagenes_propiedad i ON p.id = i.id_propiedad AND i.orden = 1 WHERE p.estado = 'Disponible' AND p.zona = :zona");
        $stmt->execute(['zona' => $zona]);
    } else {
        $stmt = $pdo->prepare("SELECT p.id, p.direccion, p.estado, p.precio, p.descripcion, p.zona, i.url_imagen FROM propiedades p LEFT JOIN imagenes_propiedad i ON p.id = i.id_propiedad AND i.orden = 1 WHERE p.estado = 'Disponible'");
        $stmt->execute();
    }
    $propiedades = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Error al cargar las propiedades: " . $e->getMessage();
}
?>

<?php include '../../layouts/container/visitante/headerVisitante.php'; ?>

<section class="hero-banner d-flex align-items-center text-center">
    <div class="container text-white">
        <h2 class="mb-4">Resultados de búsqueda<?php echo !empty($zona) ? " en $zona" : ''; ?></h2>
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger" role="alert"><?php echo $error; ?></div>
        <?php elseif (!empty($success)): ?>
            <div class="alert alert-success" role="alert"><?php echo $success; ?></div>
        <?php endif; ?>

        <!-- Lista de propiedades filtradas -->
        <div class="row g-4">
            <?php if (!empty($propiedades)): ?>
                <?php foreach ($propiedades as $propiedad): ?>
                    <div class="col-md-4">
                        <div class="card bg-dark text-white" style="border-radius: 15px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);">
                            <img src="<?php echo '../../../public/' . htmlspecialchars($propiedad['url_imagen'] ?? ''); ?>" class="card-img-top" alt="Imagen" style="height: 200px; object-fit: cover;" onerror="this.src='../../../public/assets/img/default.jpg';">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($propiedad['direccion']); ?></h5>
                                <p class="card-text">Estado: <?php echo htmlspecialchars($propiedad['estado']); ?></p>
                                <p class="card-text">Precio: <?php echo number_format($propiedad['precio'], 2); ?></p>
                                <div class="d-flex justify-content-between">
                                    <!-- Botón Detalles -->
                                    <a href="detallesPropiedades.php?id=<?php echo $propiedad['id']; ?><?php echo !empty($zona) ? '&zona=' . urlencode($zona) : ''; ?>" class="btn btn-info btn-sm">Detalles</a>
                                    <!-- Botón Postularse (visible para todos) -->
                                    <a href="postulacionesPropiedades.php?id=<?php echo $propiedad['id']; ?><?php echo !empty($zona) ? '&zona=' . urlencode($zona) : ''; ?>" class="btn btn-primary btn-sm">Postularse</a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="text-center">No hay propiedades disponibles<?php echo !empty($zona) ? " en $zona" : ''; ?>.</p>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php include '../../layouts/container/visitante/footerVisitante.php'; ?>