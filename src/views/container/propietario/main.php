<?php
// Iniciar la sesión
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar si el usuario está logueado y es propietario
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'propietario') {
    header("Location: ../../auth/login.php");
    exit;
}

// Obtener el nombre del usuario
$nombre_usuario = $_SESSION['nombre'];
$rol_usuario = $_SESSION['rol'];

// Incluir la configuración de la base de datos
require '../../../config/config.php';

// Obtener el ID del propietario
try {
    $stmt = $pdo->prepare("SELECT id FROM propietarios WHERE id_usuario = :id_usuario");
    $stmt->execute(['id_usuario' => $_SESSION['user_id']]);
    $propietario = $stmt->fetch(PDO::FETCH_ASSOC);
    $id_propietario = $propietario['id'];

    // Obtener datos del propietario
    $stmt = $pdo->prepare("SELECT u.nombre, u.correo, u.telefono FROM usuarios u JOIN propietarios p ON u.id = p.id_usuario WHERE p.id = :id_propietario");
    $stmt->execute(['id_propietario' => $id_propietario]);
    $propietarioData = $stmt->fetch(PDO::FETCH_ASSOC);

    // Cantidad de propiedades
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM propiedades WHERE id_propietario = :id_propietario");
    $stmt->execute(['id_propietario' => $id_propietario]);
    $propiedades = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Cantidad de arrendatarios (contratos activos)
    $stmt = $pdo->prepare("SELECT COUNT(DISTINCT c.id_arrendatario) as total FROM contratos c JOIN propiedades p ON c.id_propiedad = p.id WHERE p.id_propietario = :id_propietario AND c.estado = 'Activo'");
    $stmt->execute(['id_propietario' => $id_propietario]);
    $arrendatarios = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Cantidad de postulaciones
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM postulaciones p JOIN propiedades pr ON p.id_propiedad = pr.id WHERE pr.id_propietario = :id_propietario");
    $stmt->execute(['id_propietario' => $id_propietario]);
    $postulaciones = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Postulaciones por mes (últimos 6 meses)
    $dataPoints = [];
    $currentMonth = date('m');
    $currentYear = date('Y');
    for ($i = 5; $i >= 0; $i--) {
        $month = ($currentMonth - $i) <= 0 ? ($currentMonth - $i + 12) : ($currentMonth - $i);
        $year = $month > $currentMonth ? $currentYear - 1 : $currentYear;
        $monthStr = str_pad($month, 2, '0', STR_PAD_LEFT);
        $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM postulaciones p JOIN propiedades pr ON p.id_propiedad = pr.id WHERE pr.id_propietario = :id_propietario AND DATE_FORMAT(p.fecha_postulacion, '%Y-%m') = :month_year");
        $stmt->execute(['id_propietario' => $id_propietario, 'month_year' => "$year-$monthStr"]);
        $count = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        $dataPoints[] = ['month' => date('M Y', strtotime("$year-$month-01")), 'value' => $count];
    }
} catch (PDOException $e) {
    $error = "Error al cargar los datos: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inicio - Propietario</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4Q6Gf2aSP4eDXB8Miphtr37CMZZQ5oXLH2yaXMJ2w8e2ZtHTl7GptT4jmndRuHDT" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <script src="https://cdn.jsdelivr.net/npm/animejs@3.2.1/lib/anime.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="../../../../public/assets/css/mainPropietario.css">
</head>

<body>
<div class="row">
    <div class="col-12">
        <div class="card" style="background-color: #2c2c2c; border-radius: 15px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);">
            <div class="card-body text-white">
                <h2 class="text-center mb-4">
                    <i class="fa-solid fa-user-tie"></i> Dashboard De <?= $nombre_usuario; ?>
                </h2>
            </div>
        </div>
    </div>
</div>

    <div class="container mt-5">
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger" role="alert"><?php echo $error; ?></div>
        <?php else: ?>

            <!-- Sección de Estadísticas -->
            <div class="row g-4 mb-5">
                <div class="col-md-4 col-sm-6">
                    <div class="card stat-card bg-success text-white">
                        <div class="card-body text-center">
                            <i class="bi bi-house-fill" style="font-size: 2rem;"></i>
                            <h5 class="card-title">Propiedades</h5>
                            <p class="card-text display-4" id="propiedades-count"><?php echo $propiedades; ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 col-sm-6">
                    <div class="card stat-card bg-primary text-white">
                        <div class="card-body text-center">
                            <i class="bi bi-people-fill" style="font-size: 2rem;"></i>
                            <h5 class="card-title">Arrendatarios</h5>
                            <p class="card-text display-4" id="arrendatarios-count"><?php echo $arrendatarios; ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 col-sm-6">
                    <div class="card stat-card bg-warning text-white">
                        <div class="card-body text-center">
                            <i class="bi bi-person-plus-fill" style="font-size: 2rem;"></i>
                            <h5 class="card-title">Postulaciones</h5>
                            <p class="card-text display-4" id="postulaciones-count"><?php echo $postulaciones; ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Gráfico de Postulaciones por Mes -->
            <div class="card bg-dark text-white mb-5">
                <div class="card-body">
                    <h5 class="card-title">Postulaciones por Mes (Últimos 6 Meses)</h5>
                    <canvas id="postulacionesChart" height="200"></canvas>
                </div>
            </div>

        <?php endif; ?>
    </div>

</body>
</html>

<script>
    // Animación de conteo con anime.js
    anime({
        targets: '#propiedades-count',
        value: [0, <?php echo $propiedades; ?>],
        round: 1,
        easing: 'easeInOutQuad',
        duration: 1000,
        update: function(anim) {
            document.getElementById('propiedades-count').textContent = Math.round(anim.animations[0].currentValue);
        }
    });
    anime({
        targets: '#arrendatarios-count',
        value: [0, <?php echo $arrendatarios; ?>],
        round: 1,
        easing: 'easeInOutQuad',
        duration: 1000,
        update: function(anim) {
            document.getElementById('arrendatarios-count').textContent = Math.round(anim.animations[0].currentValue);
        }
    });
    anime({
        targets: '#postulaciones-count',
        value: [0, <?php echo $postulaciones; ?>],
        round: 1,
        easing: 'easeInOutQuad',
        duration: 1000,
        update: function(anim) {
            document.getElementById('postulaciones-count').textContent = Math.round(anim.animations[0].currentValue);
        }
    });

    // Gráfico con Chart.js
    const ctx = document.getElementById('postulacionesChart').getContext('2d');
    const postulacionesChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode(array_column($dataPoints, 'month')); ?>,
            datasets: [{
                label: 'Postulaciones',
                data: <?php echo json_encode(array_column($dataPoints, 'value')); ?>,
                backgroundColor: 'rgba(255, 165, 0, 0.7)', // Naranja claro
                borderColor: 'rgba(255, 165, 0, 1)',
                borderWidth: 1
            }]
        },

        options: {
            plugins: {
                legend: {
                    labels: {
                        color: 'white'
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Número de Postulaciones'
                    },
                    grid: {
                        color: 'rgba(0,0,0,0.1)' // líneas grises claras
                    }
                },
                x: {
                    title: {
                        display: true,
                        text: 'Mes'
                    },
                    grid: {
                        color: 'rgba(0,0,0,0.1)'
                    }
                }
            },
            maintainAspectRatio: false,
            backgroundColor: 'white' // <-- Esto es para Chart.js 4.x+
        }
    });
</script>