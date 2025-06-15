<?php
// Iniciar la sesión
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar si el usuario está logueado y es arrendatario
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'arrendatario') {
    header("Location: ../../auth/login.php");
    exit;
}

// Incluir la configuración de la base de datos
require '../../../config/config.php';

// Obtener el ID del arrendatario
try {
    $stmt = $pdo->prepare("SELECT id FROM arrendatarios WHERE id_usuario = :id_usuario");
    $stmt->execute(['id_usuario' => $_SESSION['user_id']]);
    $arrendatario = $stmt->fetch(PDO::FETCH_ASSOC);
    $id_arrendatario = $arrendatario['id'];

    // Obtener datos del arrendatario
    $stmt = $pdo->prepare("SELECT u.nombre, u.correo, u.telefono FROM usuarios u JOIN arrendatarios a ON u.id = a.id_usuario WHERE a.id = :id_arrendatario");
    $stmt->execute(['id_arrendatario' => $id_arrendatario]);
    $arrendatarioData = $stmt->fetch(PDO::FETCH_ASSOC);

    // Cantidad de pagos pendientes (desde conceptos_pago)
    $stmt = $pdo->prepare("SELECT COUNT(*) as total 
    FROM conceptos_pago cp
    JOIN contratos c ON cp.id_contrato = c.id
    WHERE c.id_arrendatario = :id_arrendatario AND cp.estado = 'Pendiente'");
    $stmt->execute(['id_arrendatario' => $id_arrendatario]);
    $pagosPendientes = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Cantidad de pagos atrasados (desde conceptos_pago)
    $stmt = $pdo->prepare("SELECT COUNT(*) as total 
    FROM conceptos_pago cp
    JOIN contratos c ON cp.id_contrato = c.id
    WHERE c.id_arrendatario = :id_arrendatario AND cp.estado = 'Retrasado'");
    $stmt->execute(['id_arrendatario' => $id_arrendatario]);
    $pagosAtrasados = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Cantidad de contratos activos
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM contratos WHERE id_arrendatario = :id_arrendatario AND estado = 'Activo'");
    $stmt->execute(['id_arrendatario' => $id_arrendatario]);
    $contratosActivos = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Datos de la propiedad contratada
    $stmt = $pdo->prepare("SELECT p.direccion, p.precio, i.url_imagen 
        FROM contratos c
        JOIN propiedades p ON c.id_propiedad = p.id
        LEFT JOIN imagenes_propiedad i ON p.id = i.id_propiedad AND i.orden = 1
        WHERE c.id_arrendatario = :id_arrendatario AND c.estado = 'Activo'
        LIMIT 1
    ");
    $stmt->execute(['id_arrendatario' => $id_arrendatario]);
    $propiedad = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Error al cargar los datos: " . $e->getMessage();
}
?>

<?php include '../../layouts/container/ArrendatarioMain/headerMainArrendatario.php';?>

    <div class="container mt-5">
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger" role="alert"><?php echo $error; ?></div>
        <?php else: ?>

            <!-- Sección de Datos del Arrendatario -->
            <div class="container">
                <div class="card bg-secondary text-white">
                    <div class="card-body">
                        <h4 class="card-title">Mi Perfil</h4>
                        <p><strong>Nombre:</strong> <?php echo htmlspecialchars($arrendatarioData['nombre']); ?></p>
                        <p><strong>Correo:</strong> <?php echo htmlspecialchars($arrendatarioData['correo']); ?></p>
                        <p><strong>Teléfono:</strong> <?php echo htmlspecialchars($arrendatarioData['telefono']); ?></p>
                        <a href="dashboardArrendatario.php?page=configuraciones" class="btn btn-light mt-3">Editar Datos</a>
                    </div>
                </div>
            </div><br>

            <!-- Sección de Estadísticas -->
            <div class="row g-4 mb-5">
                <div class="col-md-3 col-sm-6">
                    <div class="card stat-card bg-warning text-white">
                        <div class="card-body text-center">
                            <i class="bi bi-clock-fill" style="font-size: 2rem;"></i>
                            <h5 class="card-title">Pagos Pendientes</h5>
                            <p class="card-text display-4" id="pendientes-count"><?php echo $pagosPendientes; ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="card stat-card bg-danger text-white">
                        <div class="card-body text-center">
                            <i class="bi bi-exclamation-triangle-fill" style="font-size: 2rem;"></i>
                            <h5 class="card-title">Pagos Atrasados</h5>
                            <p class="card-text display-4" id="atrasados-count"><?php echo $pagosAtrasados; ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="card stat-card bg-success text-white">
                        <div class="card-body text-center">
                            <i class="bi bi-file-earmark-check-fill" style="font-size: 2rem;"></i>
                            <h5 class="card-title">Contratos Activos</h5>
                            <p class="card-text display-4" id="contratos-count"><?php echo $contratosActivos; ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="card stat-card bg-primary text-white">
                        <div class="card-body text-center">
                            <i class="bi bi-house-fill" style="font-size: 2rem;"></i>
                            <h5 class="card-title">Propiedad Contratada</h5>
                            <?php if ($propiedad): ?>
                                <img src="<?php echo '../../../public/' . htmlspecialchars($propiedad['url_imagen'] ?? ''); ?>" class="card-img-top" alt="Propiedad" style="height: 100px; object-fit: cover;" onerror="this.src='../../../public/assets/img/default.jpg';">
                                <p class="card-text mt-2">Dirección: <?php echo htmlspecialchars($propiedad['direccion']); ?></p>
                                <p class="card-text">Precio: $<?php echo number_format($propiedad['precio'], 2); ?></p>
                            <?php else: ?>
                                <p class="card-text">No hay propiedad contratada</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

        <?php endif; ?>
    </div>
<?php include '../../layouts/container/ArrendatarioMain/footerMainArrendatario.php';?>