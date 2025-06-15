<?php
session_start();
require '../../../config/config.php';

$success = '';
$error = '';
$propiedad = null;
$zona = isset($_GET['zona']) ? trim($_GET['zona']) : '';

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

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['postularse'])) {
    $propiedad_id = $_POST['propiedad_id'];
    $nombre_postulante = trim($_POST['nombre_postulante'] ?? '');
    $correo = trim($_POST['correo'] ?? '');
    $telefono_postulante = trim($_POST['telefono_postulante'] ?? '');

    if (empty($nombre_postulante) || empty($correo) || empty($telefono_postulante)) {
        $error = "Por favor, completa todos los campos requeridos.";
    } elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $error = "Por favor, ingresa un correo electrónico válido.";
    } else {
        try {
            $stmt = $pdo->prepare("SELECT id FROM propiedades WHERE id = :propiedad_id AND estado = 'Disponible'");
            $stmt->execute(['propiedad_id' => $propiedad_id]);
            $property = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$property) {
                $error = "La propiedad no está disponible o no existe.";
            } else {
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM postulaciones WHERE correo = :correo AND id_propiedad = :propiedad_id");
                $stmt->execute(['correo' => $correo, 'propiedad_id' => $propiedad_id]);
                if ($stmt->fetchColumn() > 0) {
                    $error = "Ya has enviado una postulación para esta propiedad con este correo.";
                } else {
                    $stmt = $pdo->prepare("INSERT INTO postulaciones (id_propiedad, nombre_postulante, correo, telefono_postulante, fecha_postulacion) VALUES (:id_propiedad, :nombre_postulante, :correo, :telefono_postulante, NOW())");
                    $stmt->execute([
                        'id_propiedad' => $propiedad_id,
                        'nombre_postulante' => htmlspecialchars($nombre_postulante),
                        'correo' => htmlspecialchars($correo),
                        'telefono_postulante' => htmlspecialchars($telefono_postulante)
                    ]);
                    if ($stmt->rowCount() > 0) {
                        $success = "Postulación enviada correctamente. El propietario se pondrá en contacto contigo.";
                        $error = '';
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
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css"
        rel="stylesheet" integrity="sha384-4Q6Gf2aSP4eDXB8Miphtr37CMZZQ5oXLH2yaXMJ2w8e2ZtHTl7GptT4jmndRuHDT"
        crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="icon" href=".../../../../../../public/assets/img/logoRF.png" type="image/x-icon">
    <link rel="stylesheet" href="../../../../public/assets/css/visitante.css">
    <title>Postulaciones - Renta Fácil</title>
</head>


<body>
    <div class="page-wrapper d-flex flex-column min-vh-100">
        <header class="fixed-top">
            <nav class="navbar navbar-expand-lg">
                <div class="container-fluid">
                    <!-- Logo + Nombre del sitio -->
                    <div class="d-flex align-items-center" data-aos="fade-right">
                        <img src=".../../../../../../public/assets/img/logoRF.png" alt="Logo" class="logo">
                        <a class="navbar-brand text-white" href="#">Renta Fácil</a>
                    </div>

                    <!-- Botón para responsive -->
                    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                        aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                        <span class="navbar-toggler-icon"></span>
                    </button>

                    <!-- Contenido colapsable -->
                    <div class="collapse navbar-collapse" id="navbarNav">
                        <div class="container-fluid d-flex justify-content-between align-items-center w-100">

                            <!-- Menú centrado -->
                            <ul class="navbar-nav mx-auto text-center" data-aos="fade-down" data-aos-delay="200">
                                <li class="nav-item">
                                    <a class="nav-link" href=".../../../../../../public/index.php">
                                        <i class="fas fa-chart-line me-2"></i>Resumen
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href=".../../../../../../public/index.php">
                                        <i class="fas fa-user-tie me-2"></i>Propietario
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href=".../../../../../../public/index.php">
                                        <i class="fas fa-users me-2"></i>Arrendatario
                                    </a>
                                </li>
                            </ul>

                            <!-- Botón "Empezar" a la derecha -->
                            <div class="d-flex ms-auto" data-aos="fade-left" data-aos-delay="400">
                                <a href="../../Auth/register.php" class="btn">
                                    <i class="fas fa-rocket me-2"></i>Empezar
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </nav>
        </header>
        <div class="flex-grow-1 d-flex flex-column">
            <div class="container flex-grow-1 d-flex flex-clumn">
                <div class="container">
                    <div class="main-container">
                        <div class="text-center mb-5" data-aos="fade-up">
                            <h2>
                                <i class="fas fa-hand-paper me-3"></i>
                                Resultados de búsqueda<?php echo !empty($zona) ? " en $zona" : ''; ?>
                            </h2>
                            <p class="lead text-light opacity-75">
                                <?php echo count($propiedades); ?> Propiedades disponibles para postulacion
                            </p>
                        </div>
                    </div>
                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger text-center fade-in-up" data-aos="fade-up">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <?php echo $error; ?>
                        </div>
                    <?php elseif (!empty($success)): ?>
                        <div class="alert alert-success text-center fade-in-up" data-aos="fade-up">
                            <i class="fas fa-check-circle me-2"></i>
                            <?php echo $success; ?>
                        </div>
                    <?php endif; ?>

                    <div class="row g-4 justify-content-center flex-grow-1">
                        <?php if (!empty($propiedades)): ?>
                            <?php foreach ($propiedades as $index => $propiedad): ?>
                                <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="<?php echo $index * 100; ?>">
                                    <div class="card property-card">
                                        <img src="<?php echo '../../../public/' . htmlspecialchars($propiedad['url_imagen'] ?? ''); ?>"
                                            class="card-img-top"
                                            alt="Imagen de propiedad"
                                            onerror="this.src='../../../public/assets/img/default.jpg';">
                                        <div class="card-body d-flex flex-column">
                                            <h5 class="card-title">
                                                <i class="fas fa-home me-2 text-primary"></i>
                                                <?php echo htmlspecialchars($propiedad['direccion']); ?>
                                            </h5>
                                            <p class="card-text mb-1">
                                                <i class="fas fa-info-circle me-2"></i>
                                                Estado: <span class="text-success"><?php echo htmlspecialchars($propiedad['estado']); ?></span>
                                            </p>
                                            <p class="card-text mb-3">
                                                <i class="fas fa-map-marker-alt me-2"></i>
                                                Zona: <?php echo htmlspecialchars($propiedad['zona']); ?>
                                            </p>
                                            <div class="price-text">
                                                <i class="fas fa-dollar-sign me-1"></i>
                                                $<?php echo number_format($propiedad['precio'], 0, ',', '.'); ?>
                                            </div>
                                            <div class="mt-auto d-flex justify-content-between gap-2">
                                                <button type="button" class="btn btn-info btn-sm flex-fill" data-bs-toggle="modal" data-bs-target="#detalleModal<?php echo $propiedad['id']; ?>">
                                                    <i class="fas fa-eye me-1"></i> Detalles
                                                </button>
                                                <button type="button" class="btn btn-primary btn-sm flex-fill" data-bs-toggle="modal" data-bs-target="#postularModal<?php echo $propiedad['id']; ?>">
                                                    <i class="fas fa-hand-paper me-1"></i> Postularse
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Modal Detalles -->
                                <div class="modal fade" id="detalleModal<?php echo $propiedad['id']; ?>" tabindex="-1" aria-labelledby="detalleModalLabel<?php echo $propiedad['id']; ?>" aria-hidden="true">
                                    <div class="modal-dialog modal-lg modal-dialog-centered">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="detalleModalLabel<?php echo $propiedad['id']; ?>">
                                                    <i class="fas fa-info-circle me-2"></i>Detalles de la Propiedad
                                                </h5>
                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                                            </div>
                                            <div class="modal-body">
                                                <img src="<?php echo '../../../public/' . htmlspecialchars($propiedad['url_imagen'] ?? ''); ?>"
                                                    class="card-img-top mb-3"
                                                    alt="Imagen de propiedad"
                                                    style="height: 300px; object-fit: cover;"
                                                    onerror="this.src='../../../public/assets/img/default.jpg';">

                                                <h5 class="card-title mb-3">
                                                    <i class="fas fa-map-marker-alt me-2 text-primary"></i>
                                                    <?php echo htmlspecialchars($propiedad['direccion']); ?>
                                                </h5>

                                                <div class="row mb-3">
                                                    <div class="col-md-6">
                                                        <p class="card-text">
                                                            <i class="fas fa-info-circle me-2 text-info"></i>
                                                            <strong>Estado:</strong> <span class="text-success"><?php echo htmlspecialchars($propiedad['estado']); ?></span>
                                                        </p>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <p class="card-text">
                                                            <i class="fas fa-map me-2 text-warning"></i>
                                                            <strong>Zona:</strong> <?php echo htmlspecialchars($propiedad['zona']); ?>
                                                        </p>
                                                    </div>
                                                </div>

                                                <p class="card-text mb-3">
                                                    <i class="fas fa-dollar-sign me-2 text-success"></i>
                                                    <strong>Precio:</strong> <span class="text-primary fs-5">$<?php echo number_format($propiedad['precio'], 0, ',', '.'); ?></span>
                                                </p>

                                                <p class="card-text mb-3">
                                                    <i class="fas fa-file-alt me-2 text-secondary"></i>
                                                    <strong>Descripción:</strong> <?php echo htmlspecialchars($propiedad['descripcion']); ?>
                                                </p>

                                                <div class="mb-3">
                                                    <p class="mb-2">
                                                        <i class="fas fa-images me-2 text-info"></i>
                                                        <strong>Galería de imágenes:</strong>
                                                    </p>
                                                    <div class="image-gallery">
                                                        <?php
                                                        $stmtImg = $pdo->prepare("SELECT url_imagen, descripcion FROM imagenes_propiedad WHERE id_propiedad = :propiedad_id ORDER BY orden");
                                                        $stmtImg->execute(['propiedad_id' => $propiedad['id']]);
                                                        $imagenes = $stmtImg->fetchAll(PDO::FETCH_ASSOC);
                                                        foreach ($imagenes as $imagen) {
                                                            $imgSrc = '../../../public/' . htmlspecialchars($imagen['url_imagen']);
                                                            echo "<img src='$imgSrc' alt='" . htmlspecialchars($imagen['descripcion']) . "' onerror=\"this.src='../../../public/assets/img/default.jpg';\">";
                                                        }
                                                        ?>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                                    <i class="fas fa-arrow-left me-2"></i>Volver
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Modal Postularse -->
                                <div class="modal fade" id="postularModal<?php echo $propiedad['id']; ?>" tabindex="-1" aria-labelledby="postularModalLabel<?php echo $propiedad['id']; ?>" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="postularModalLabel<?php echo $propiedad['id']; ?>">
                                                    <i class="fas fa-hand-paper me-2"></i>Postularse a la Propiedad
                                                </h5>
                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="text-center mb-4">
                                                    <h5 class="card-title">
                                                        <i class="fas fa-home me-2 text-primary"></i>
                                                        <?php echo htmlspecialchars($propiedad['direccion']); ?>
                                                    </h5>
                                                    <p class="text-muted">
                                                        <i class="fas fa-dollar-sign me-1"></i>
                                                        $<?php echo number_format($propiedad['precio'], 0, ',', '.'); ?>
                                                    </p>
                                                </div>

                                                <form method="POST" action="./filtro.php<?php echo !empty($zona) ? '?zona=' . urlencode($zona) : ''; ?>">
                                                    <input type="hidden" name="postularse" value="1">
                                                    <input type="hidden" name="propiedad_id" value="<?php echo $propiedad['id']; ?>">

                                                    <div class="mb-3">
                                                        <label for="nombre_postulante_<?php echo $propiedad['id']; ?>" class="form-label">
                                                            <i class="fas fa-user me-2"></i>Nombre Completo
                                                        </label>
                                                        <input type="text" class="form-control" id="nombre_postulante_<?php echo $propiedad['id']; ?>" name="nombre_postulante" placeholder="Ingresa tu nombre completo" required>
                                                    </div>

                                                    <div class="mb-3">
                                                        <label for="correo_<?php echo $propiedad['id']; ?>" class="form-label">
                                                            <i class="fas fa-envelope me-2"></i>Correo Electrónico
                                                        </label>
                                                        <input type="email" class="form-control" id="correo_<?php echo $propiedad['id']; ?>" name="correo" placeholder="Ingresa tu correo electrónico" required>
                                                    </div>

                                                    <div class="mb-4">
                                                        <label for="telefono_postulante_<?php echo $propiedad['id']; ?>" class="form-label">
                                                            <i class="fas fa-phone me-2"></i>Número de Teléfono
                                                        </label>
                                                        <input type="tel" class="form-control" id="telefono_postulante_<?php echo $propiedad['id']; ?>" name="telefono_postulante" placeholder="Ingresa tu número de teléfono" required>
                                                    </div>

                                                    <div class="d-flex justify-content-between gap-3">
                                                        <button type="button" class="btn btn-secondary flex-fill" data-bs-dismiss="modal">
                                                            <i class="fas fa-times me-2"></i>Cancelar
                                                        </button>
                                                        <button type="submit" class="btn btn-primary flex-fill">
                                                            <i class="fas fa-paper-plane me-2"></i>Enviar Postulación
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="col-12">
                                <div class="no-results" data-aos="fade-up">
                                    <i class="fas fa-search"></i>
                                    <h3>No hay propiedades disponibles</h3>
                                    <p><?php echo !empty($zona) ? "No se encontraron propiedades en $zona" : "No hay propiedades disponibles en este momento"; ?>.</p>
                                    <a href=".../../../../../../public/index.php" class="btn mt-3">
                                        <i class="fas fa-arrow-left me-2"></i>Volver al inicio
                                    </a>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer mejorado -->
        <footer data-aos="fade-up">
            <div class="container">
                <div class="row text-center text-md-start">
                    <!-- Columna 1: Marca -->
                    <div class="col-md-4 mb-3">
                        <div class="brand-name">
                            <i class="fas fa-home me-2"></i>
                            Renta Fácil
                        </div>
                        <div class="text-small">
                            Plataforma de gestión de alquileres para<br>
                            propietarios y arrendatarios
                        </div>
                    </div>

                    <!-- Columna 2: Contacto -->
                    <div class="col-md-4 mb-3 contact-info">
                        <div class="mb-2">
                            <i class="fas fa-envelope me-2"></i>
                            <strong>Contacto:</strong> rentafacil@gmail.com
                        </div>
                        <div>
                            <i class="fas fa-phone me-2"></i>
                            <strong>Teléfono:</strong> +72 0000000001
                        </div>
                    </div>

                    <!-- Columna 3: Derechos -->
                    <div class="col-md-4 d-flex align-items-center justify-content-md-end justify-content-center text-small">
                        <div>
                            <i class="fas fa-copyright me-1"></i>
                            2025 RentaFácil. Todos los <br class="d-md-none"> derechos reservados
                        </div>
                    </div>
                </div>
            </div>
        </footer>
    </div>

    <!-- Botón de scroll to top -->
    <button id="scrollToTop" class="scroll-to-top">
        <i class="fas fa-chevron-up"></i>
    </button>


</body>

</html>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-j1CDi7MgGQ12Z7Qab0qlWQ/Qqz24Gc6BM0thvEMVjHnfYGF0rmFCozFSxQBxwHKO"
    crossorigin="anonymous"></script>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-j1CDi7MgGQ12Z7Qab0qlWQ/Qqz24Gc6BM0thvEMVjHnfYGF0rmFCozFSxQBxwHKO"
    crossorigin="anonymous"></script>
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="../../../../public/assets/JavaScript/visitante.js"></script>

<script>
    <?php if ((!empty($error) || !empty($success)) && isset($_POST['propiedad_id'])): ?>
            <
            script >
            document.addEventListener('DOMContentLoaded', function() {
                var modalId = 'postularModal<?php echo htmlspecialchars($_POST['propiedad_id']); ?>';
                var modalElement = document.getElementById(modalId);
                if (modalElement) {
                    var modal = new bootstrap.Modal(modalElement);
                    modal.show();
                }
            });
</script>
<?php endif; ?>
<?php if (!empty($success)): ?>
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
</script>