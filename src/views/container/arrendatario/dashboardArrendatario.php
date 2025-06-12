<?php
// Iniciar la sesión
session_start();
require '../../../config/config.php';

// Verificar si el usuario ha iniciado sesión y es arrendatario
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'arrendatario') {
    header("Location: ../../auth/login.php");
    exit;
}

// Obtener el nombre del usuario
$nombre_usuario = $_SESSION['nombre'];
$rol_usuario = $_SESSION['rol'];

// Determinar contenido según la página seleccionada
$pagina = isset($_GET['page']) ? $_GET['page'] : 'inicio';

switch ($pagina) {
    case 'contratos':
        $contenido = 'contratos.php';
        break;
    case 'calendario':
        $contenido = 'calendario.php';
        break;
    case 'pagos':
        $contenido = 'pagos.php';
        break;
    case 'configuraciones':
        $contenido = 'configDatos.php';
        break;

    default:
        $contenido = 'main.php';
        break;
}


//consultar si el usuario tiene un codigo de verificacion
$stm = $pdo->prepare("SELECT verification_code FROM usuarios WHERE id = :id");
$stm->execute(['id' => $_SESSION['user_id']]);
$usuario = $stm->fetch(PDO::FETCH_ASSOC);
?>
<?php include '../../layouts/container/Arrendatario_Propietario/header.php'; ?>

<nav class="nav flex-column">
    <a href="?page=contratos" class="nav-link"><i class="fas fa-file-contract"></i> Contratos</a>
    <a href="?page=calendario" class="nav-link"><i class="fas fa-calendar"></i> Calendario</a>
    <a href="?page=pagos" class="nav-link"><i class="fas fa-money-bill"></i> Pagos</a>
    <div class="mt-auto">
        <a href="../../auth/logout.php" class="nav-link" style="color: #dc3545;"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a>
        <a href="?page=configuraciones" class="nav-link text-primary"><i class="fas fa-cog"></i> Configuraciones</a>
    </div>
</nav>

<?php include '../../layouts/container/Arrendatario_Propietario/footer.php'; ?>