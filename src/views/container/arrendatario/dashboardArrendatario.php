<?php
// Iniciar la sesión
session_start();

// Verificar si el usuario ha iniciado sesión y es arrendatario
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'arrendatario') {
    header("Location: ../../auth/login.php");
    exit;
}

// Obtener el nombre del usuario
$nombre = isset($_SESSION['nombre']) ? htmlspecialchars($_SESSION['nombre']) : 'Arrendatario';
?>

<?php include '../../layouts/container/arrendatario/headerArrendatario.php'; ?>

<main>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 text-center">
                <h1>Bienvenid@, <?php echo $nombre; ?>!</h1>
                <p>Aquí puedes gestionar tus contratos y postulaciones.</p>
            </div>
        </div>
        <!-- Placeholder para futuras funcionalidades -->
        <div class="card" style="background-color: #2c2c2c; border-radius: 15px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);">
            <div class="card-body text-white text-center">
                <p>Funcionalidades para arrendatarios en desarrollo.</p>
            </div>
        </div>
    </div>
</main>

<?php include '../../layouts/container/arrendatario/footerArrendatario.php'; ?>