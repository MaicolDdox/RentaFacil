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
} catch (PDOException $e) {
    $error = "Error al obtener el arrendatario: " . $e->getMessage();
}

// Obtener el mes y año seleccionados (por defecto, el mes y año actuales)
$month = isset($_GET['month']) ? (int)$_GET['month'] : date('m');
$year = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');

// Validar mes y año
if ($month < 1 || $month > 12) $month = date('m');
if ($year < 1970 || $year > 9999) $year = date('Y');

// Obtener el primer y último día del mes
$firstDayOfMonth = new DateTime("$year-$month-01");
$lastDayOfMonth = new DateTime($firstDayOfMonth->format('Y-m-t'));
$firstDayOfWeek = (int)$firstDayOfMonth->format('N'); // 1 (Lunes) a 7 (Domingo)
$daysInMonth = (int)$firstDayOfMonth->format('t');

// Obtener el contrato activo del arrendatario
try {
    $stmt = $pdo->prepare("SELECT c.id, c.fecha_fin, p.direccion
        FROM contratos c
        JOIN propiedades p ON c.id_propiedad = p.id
        WHERE c.id_arrendatario = :id_arrendatario
        AND c.estado = 'Activo'
        LIMIT 1
    ");
    $stmt->execute(['id_arrendatario' => $id_arrendatario]);
    $contrato = $stmt->fetch(PDO::FETCH_ASSOC);

    // Determinar el día de finalización si existe un contrato
    if ($contrato) {
        $fechaFin = new DateTime($contrato['fecha_fin']);
        $finalizacionDia = (int)$fechaFin->format('j');
        $finalizacionMes = (int)$fechaFin->format('n');
        $finalizacionAnio = (int)$fechaFin->format('Y');
    } else {
        $finalizacionDia = null;
        $finalizacionMes = null;
        $finalizacionAnio = null;
    }
} catch (PDOException $e) {
    $error = "Error al cargar el contrato: " . $e->getMessage();
}
?>

<div class="row">
    <div class="col-12">
        <div class="card" style="background-color: #2c2c2c; border-radius: 15px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);">
            <div class="card-body text-white">
                <h2 class="text-center mb-4">
                    <i class="fas fa-calendar"></i> CALENDARIO
                </h2>
            </div>
        </div>
    </div>
</div>

<link rel="stylesheet" href="../../../../public/assets/css/calendario.css">
<div class="container mt-5">
    <div class="calendar-container">
        <!-- Navegación del calendario -->
        <div class="calendar-nav">
            <?php
            $prevMonth = $month == 1 ? 12 : $month - 1;
            $prevYear = $month == 1 ? $year - 1 : $year;
            $nextMonth = $month == 12 ? 1 : $month + 1;
            $nextYear = $month == 12 ? $year + 1 : $year;
            ?>
            <a href="?page=calendario&month=<?php echo $prevMonth; ?>&year=<?php echo $prevYear; ?>">
                < Anterior</a>
                    <h2><?php echo $firstDayOfMonth->format('F Y'); ?></h2>
                    <a href="?page=calendario&month=<?php echo $nextMonth; ?>&year=<?php echo $nextYear; ?>">Siguiente ></a>
        </div>

        <!-- Tabla del calendario -->
        <table class="calendar-table">
            <thead>
                <tr>
                    <th>Lun</th>
                    <th>Mar</th>
                    <th>Mié</th>
                    <th>Jue</th>
                    <th>Vie</th>
                    <th>Sáb</th>
                    <th>Dom</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $currentDay = 1;
                $position = 1;

                while ($currentDay <= $daysInMonth) {
                    echo "<tr>";
                    for ($i = 1; $i <= 7; $i++) {
                        if ($position < $firstDayOfWeek && $currentDay == 1) {
                            echo "<td class='empty'></td>";
                            $position++;
                        } elseif ($currentDay > $daysInMonth) {
                            echo "<td class='empty'></td>";
                        } else {
                            $hasEvent = (
                                $finalizacionDia === $currentDay &&
                                $finalizacionMes === (int)$month &&
                                $finalizacionAnio === (int)$year
                            );
                            echo "<td" . ($hasEvent ? " class='has-event'" : "") . ">";
                            echo $currentDay;

                            // Mostrar tooltip con información de finalización
                            if ($hasEvent && $contrato) {
                                echo "<div class='event-tooltip'>";
                                echo "<div>Propiedad: " . htmlspecialchars($contrato['direccion']) . "</div>";
                                echo "<div>Finaliza: " . $contrato['fecha_fin'] . "</div>";
                                echo "</div>";
                            }

                            echo "</td>";
                            $currentDay++;
                        }
                    }
                    echo "</tr>";
                }
                ?>
            </tbody>
        </table>
        <?php if (!$contrato): ?>
            <p class="text-center mt-3">No tienes un contrato activo registrado.</p>
        <?php endif; ?>
    </div>
</div>