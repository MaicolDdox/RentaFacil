<?php

require '../../../config/config.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Obtener datos del usuario logueado
$nombre_usuario =  $_SESSION['nombre'];
$rol_usuario = $_SESSION['rol'];

// Determinar contenido según la página seleccionada
$pagina = isset($_GET['page']) ? $_GET['page'] : 'inicio';

switch ($pagina) {
    case 'propiedades':
        $contenido = 'propiedades.php';
        break;
    case 'arrendatarios':
        $contenido = 'arrendatarios.php';
        break;
    case 'contratos':
        $contenido = 'contratos.php';
        break;
    case 'calendario':
        $contenido = 'calendario.php';
        break;
    case 'pagos':
        $contenido = 'pagos.php';
        break;
    case 'postulaciones':
        $contenido = 'postulaciones.php';
        break;
    case 'configuraciones':
        $contenido = 'configDatos.php';
        break;
    default:
        $contenido = 'main.php';
        break;
}
?>
<?php include '../../layouts/container/Arrendatario_Propietario/header.php'; ?>
<nav class="nav flex-column">
    <a href="?page=propiedades" class="nav-link"><i class="fas fa-home"></i> Propiedades</a>
    <a href="?page=arrendatarios" class="nav-link"><i class="fas fa-users"></i> Arrendatarios</a>
    <a href="?page=contratos" class="nav-link"><i class="fas fa-file-contract"></i> Contratos</a>
    <a href="?page=calendario" class="nav-link"><i class="fas fa-calendar"></i> Calendario</a>
    <a href="?page=pagos" class="nav-link"><i class="fas fa-money-bill"></i> Pagos</a>
    <a href="?page=postulaciones" class="nav-link"><i class="fas fa-paper-plane"></i> Postulaciones</a>
    <div class="mt-auto">
        <a href="../../auth/logout.php" class="nav-link" style="color: #dc3545;"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a>
        <a href="?page=configuraciones" class="nav-link text-primary"><i class="fas fa-cog"></i> Configuraciones</a>
    </div>
</nav>
<?php include '../../layouts/container/Arrendatario_Propietario/footer.php'; ?>