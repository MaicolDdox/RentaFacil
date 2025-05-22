<?php

namespace Maicolddox\RentaFacil;

// Iniciar la sesión
session_start();

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../auth/login.php");
    exit;
}

// Obtener el nombre del usuario (con valor por defecto si no está definido)
$nombre = isset($_SESSION['nombre']) ? htmlspecialchars($_SESSION['nombre']) : 'Usuario';
?>

<?php require '../../layouts/container/propietario/headerPropietario.php'; ?>
<link rel="stylesheet" href="">
<main>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 text-center">
                <h1>Bienvenid@, <?php echo $nombre; ?>!</h1>
                <p>Aquí puedes gestionar tus propiedades y arrendatarios.</p>
            </div>
        </div>
        <!-- Card con botones usando Bootstrap -->
        <div class="card dashboard-card">
            <div class="card-body">
                <div class="dashboard-buttons">
                    <a href="./propiedades.php" class="dashboard-btn">Propiedades</a>
                    <a href="./arrendatarios.php" class="dashboard-btn">Arrendatarios</a>
                    <a href="./contratos.php" class="dashboard-btn">Contratos</a>
                    <a href="./calendario.php" class="dashboard-btn">Calendario</a>
                    <a href="./postulaciones.php" class="dashboard-btn">Postulaciones</a>
                    <a href="./pagos.php" class="dashboard-btn">Pagos</a>
                </div>
            </div>
        </div>
    </div>
</main>

<?php require '../../layouts/container/propietario/footerPropietario.php'; ?>