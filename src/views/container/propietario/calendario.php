<?php
// Iniciar la sesión
session_start();

// Verificar si el usuario está logueado y es propietario
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'propietario') {
    header("Location: ../../auth/login.php");
    exit;
}

// Incluir la configuración de la base de datos
require '../../../config/config.php';

// Obtener el ID del propietario
try {
    $stmt = $pdo->prepare("SELECT id FROM propietarios WHERE id_usuario = :id_usuario");
    $stmt->execute(['id_usuario' => $_SESSION['user_id']]);
    $propietario = $stmt->fetch(PDO::FETCH_ASSOC);
    $id_propietario = $propietario['id'];
} catch (PDOException $e) {
    $error = "Error al obtener el propietario: " . $e->getMessage();
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

// Obtener contratos que finalizan en el mes seleccionado
try {
    $stmt = $pdo->prepare("
        SELECT c.id, c.fecha_fin, p.direccion, u.nombre AS arrendatario_nombre
        FROM contratos c
        JOIN propiedades p ON c.id_propiedad = p.id
        JOIN arrendatarios a ON c.id_arrendatario = a.id
        JOIN usuarios u ON a.id_usuario = u.id
        WHERE p.id_propietario = :id_propietario
        AND c.estado = 'Activo'
        AND YEAR(c.fecha_fin) = :year
        AND MONTH(c.fecha_fin) = :month
    ");
    $stmt->execute([
        'id_propietario' => $id_propietario,
        'year' => $year,
        'month' => $month
    ]);
    $contratos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Organizar contratos por día
    $contratosPorDia = [];
    foreach ($contratos as $contrato) {
        $day = (int)(new DateTime($contrato['fecha_fin']))->format('j');
        $contratosPorDia[$day][] = $contrato;
    }
} catch (PDOException $e) {
    $error = "Error al cargar contratos: " . $e->getMessage();
}
?>

<?php include '../../layouts/container/propietario/headerPropietario.php'; ?>

<style>
    /* Estilos para el calendario */
    .calendar-container {
        background-color: #2c2c2c;
        border-radius: 15px;
        padding: 20px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    }
    .calendar-table {
        width: 100%;
        border-collapse: collapse;
    }
    .calendar-table th, .calendar-table td {
        text-align: center;
        padding: 10px;
        color: white !important;
    }
    .calendar-table th {
        background-color: #444;
    }
    .calendar-table td {
        background-color: #333;
        border: 1px solid #555;
        height: 80px;
        vertical-align: top;
        position: relative;
    }
    .calendar-table td.empty {
        background-color: #222;
    }
    .calendar-table td.has-event {
        background-color: #dc3545; /* Rojo para días con eventos */
        cursor: pointer;
    }
    .calendar-table td.has-event:hover .event-tooltip {
        display: block;
    }
    .event-tooltip {
        display: none;
        position: absolute;
        top: 100%;
        left: 50%;
        transform: translateX(-50%);
        background-color: #444;
        color: white;
        padding: 5px 10px;
        border-radius: 5px;
        z-index: 1000;
        white-space: nowrap;
        font-size: 0.9em;
    }
    .calendar-nav {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }
    .calendar-nav a {
        color: white;
        text-decoration: none;
        padding: 5px 10px;
        background-color: #555;
        border-radius: 5px;
    }
    .calendar-nav a:hover {
        background-color: #666;
    }
    .calendar-nav h2 {
        margin: 0;
        color: white !important;
    }
    .container, .container * {
        color: white !important;
    }
</style>

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
            <a href="?month=<?php echo $prevMonth; ?>&year=<?php echo $prevYear; ?>">&lt; Anterior</a>
            <h2><?php echo $firstDayOfMonth->format('F Y'); ?></h2>
            <a href="?month=<?php echo $nextMonth; ?>&year=<?php echo $nextYear; ?>">Siguiente &gt;</a>
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
                            $hasEvent = isset($contratosPorDia[$currentDay]);
                            echo "<td" . ($hasEvent ? " class='has-event'" : "") . ">";
                            echo $currentDay;

                            // Mostrar tooltip con información de contratos
                            if ($hasEvent) {
                                echo "<div class='event-tooltip'>";
                                foreach ($contratosPorDia[$currentDay] as $contrato) {
                                    echo "<div>Propiedad: " . htmlspecialchars($contrato['direccion']) . "</div>";
                                    echo "<div>Arrendatario: " . htmlspecialchars($contrato['arrendatario_nombre']) . "</div>";
                                    echo "<div>Finaliza: " . $contrato['fecha_fin'] . "</div>";
                                    echo "<br>";
                                }
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
    </div>
</div>

<?php include '../../layouts/container/propietario/footerPropietario.php'; ?>