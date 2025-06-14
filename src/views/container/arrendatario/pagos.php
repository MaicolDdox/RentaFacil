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

$error = '';
$success = '';

// Obtener el ID del arrendatario
try {
    $stmt = $pdo->prepare("SELECT id FROM arrendatarios WHERE id_usuario = :id_usuario");
    $stmt->execute(['id_usuario' => $_SESSION['user_id']]);
    $arrendatario = $stmt->fetch(PDO::FETCH_ASSOC);
    $id_arrendatario = $arrendatario['id'];
} catch (PDOException $e) {
    $error = "Error al obtener el arrendatario: " . $e->getMessage();
}

// Obtener contratos activos y conceptos de pago
$contratos = [];
try {
    $stmt = $pdo->prepare("SELECT c.id AS contrato_id, p.direccion, p.precio, u.nombre AS arrendatario_nombre, c.fecha_inicio, c.fecha_fin, cp.id AS concepto_id, cp.periodo, cp.concepto, cp.monto_por_pagar, cp.estado
        FROM contratos c
        JOIN propiedades p ON c.id_propiedad = p.id
        JOIN arrendatarios a ON c.id_arrendatario = a.id
        JOIN usuarios u ON a.id_usuario = u.id
        JOIN conceptos_pago cp ON c.id = cp.id_contrato
        WHERE a.id = :id_arrendatario AND c.estado = 'Activo'
        ORDER BY c.id, cp.periodo
    ");
    $stmt->execute(['id_arrendatario' => $id_arrendatario]);
    $pagos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!empty($pagos)) {
        foreach ($pagos as $pago) {
            $contrato_id = $pago['contrato_id'];
            if (!isset($contratos[$contrato_id])) {
                $contratos[$contrato_id] = [
                    'contrato_id' => $contrato_id,
                    'direccion' => $pago['direccion'],
                    'arrendatario_nombre' => $pago['arrendatario_nombre'],
                    'fecha_inicio' => $pago['fecha_inicio'],
                    'fecha_fin' => $pago['fecha_fin'],
                    'meses' => []
                ];
            }
            $periodo = $pago['periodo'];
            if (!isset($contratos[$contrato_id]['meses'][$periodo])) {
                $contratos[$contrato_id]['meses'][$periodo] = [];
            }
            $contratos[$contrato_id]['meses'][$periodo][] = [
                'id' => $pago['concepto_id'],
                'concepto' => $pago['concepto'],
                'monto_por_pagar' => $pago['monto_por_pagar'],
                'estado' => $pago['estado']
            ];
        }
    }
} catch (PDOException $e) {
    $error = "Error al cargar los datos de contratos: " . $e->getMessage();
}

// Función para obtener el nombre del mes en español
function getSpanishMonth($monthNum) {
    $months = [
        1 => 'Enero', 
        2 => 'Febrero', 
        3 => 'Marzo', 
        4 => 'Abril',
        5 => 'Mayo', 
        6 => 'Junio', 
        7 => 'Julio', 
        8 => 'Agosto',
        9 => 'Septiembre', 
        10 => 'Octubre', 
        11 => 'Noviembre', 
        12 => 'Diciembre'
    ];
    return $months[(int)$monthNum];
}
?>


<link rel="stylesheet" href="../../../../public/assets/css/propietarioPagos.css">
<div class="container mt-5">
    <div class="card" style="background-color: #2c2c2c; border-radius: 15px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);">
        <div class="card-body text-white">
            <h2 class="text-center mb-4"><i class="fas fa-money-bill-wave me-2"></i>Gestión de Pagos</h2>
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger" role="alert"><i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?></div>
            <?php elseif (!empty($success)): ?>
                <div class="alert alert-success" role="alert"><i class="fas fa-check-circle me-2"></i><?php echo $success; ?></div>
            <?php endif; ?>

            <h3 class="mt-4"><i class="fas fa-file-contract me-2"></i>Contratos Activos</h3>
            <?php if (!empty($contratos)): ?>
                <div class="accordion" id="accordionContratos">
                    <?php foreach ($contratos as $index => $contrato): ?>
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="headingContrato<?php echo $index; ?>">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseContrato<?php echo $index; ?>">
                                    <i class="fas fa-user me-2"></i><?php echo htmlspecialchars($contrato['arrendatario_nombre']); ?> - <i class="fas fa-home me-2"></i><?php echo htmlspecialchars($contrato['direccion']); ?>
                                    <span class="ms-2 text-muted"><i class="fas fa-calendar-alt me-1"></i>Inicio: <?php echo $contrato['fecha_inicio']; ?> | Fin: <?php echo $contrato['fecha_fin']; ?></span>
                                </button>
                            </h2>
                            <div id="collapseContrato<?php echo $index; ?>" class="accordion-collapse collapse" aria-labelledby="headingContrato<?php echo $index; ?>" data-bs-parent="#accordionContratos">
                                <div class="accordion-body">
                                    <div class="accordion" id="accordionMeses<?php echo $contrato['contrato_id']; ?>">
                                        <?php
                                        $mesCount = 1;
                                        if (isset($contrato['meses']) && is_array($contrato['meses'])) {
                                            foreach ($contrato['meses'] as $periodo => $mesData):
                                                $monthNum = date('m', strtotime($periodo . '-01'));
                                                $year = date('Y', strtotime($periodo . '-01'));
                                                $monthName = getSpanishMonth($monthNum);
                                        ?>
                                                <div class="accordion-item">
                                                    <h2 class="accordion-header" id="headingMes<?php echo $contrato['contrato_id'] . $mesCount; ?>">
                                                        <button class="accordion-button collapsed" style="color: #fff;" type="button" data-bs-toggle="collapse" data-bs-target="#collapseMes<?php echo $contrato['contrato_id'] . $mesCount; ?>">
                                                            <i class="fas fa-calendar me-2"></i>MES <?php echo $mesCount; ?> (<?php echo $monthName . ' ' . $year; ?>)
                                                        </button>
                                                    </h2>
                                                    <div id="collapseMes<?php echo $contrato['contrato_id'] . $mesCount; ?>" class="accordion-collapse collapse" aria-labelledby="headingMes<?php echo $contrato['contrato_id'] . $mesCount; ?>" data-bs-parent="#accordionMeses<?php echo $contrato['contrato_id']; ?>">
                                                        <div class="accordion-body">
                                                            <table class="table table-striped">
                                                                <thead>
                                                                    <tr>
                                                                        <th><i class="fas fa-list me-1"></i>Concepto</th>
                                                                        <th><i class="fas fa-dollar-sign me-1"></i>Monto por Pagar</th>
                                                                        <th><i class="fas fa-info-circle me-1"></i>Estado</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    <?php
                                                                    $total = 0;
                                                                    $iconosConcepto = [
                                                                        'arriendo' => 'fa-house-user',
                                                                        'agua' => 'fa-tint',
                                                                        'luz' => 'fa-bolt',
                                                                        'gas' => 'fa-fire',
                                                                        'internet' => 'fa-wifi',
                                                                        'seguro' => 'fa-shield-alt',
                                                                        'daños' => 'fa-tools',
                                                                        'mantenimiento' => 'fa-wrench'
                                                                    ];
                                                                    foreach ($mesData as $concepto):
                                                                        $icon = isset($iconosConcepto[$concepto['concepto']]) ? $iconosConcepto[$concepto['concepto']] : 'fa-tag';
                                                                    ?>
                                                                        <tr>
                                                                            <td>
                                                                                <i class="fas <?php echo $icon; ?> me-1"></i>
                                                                                <?php echo ucfirst($concepto['concepto']); ?>
                                                                            </td>
                                                                            <td>$<?php echo number_format($concepto['monto_por_pagar'], 2); ?></td>
                                                                            <td>
                                                                                <?php if ($concepto['estado'] === 'Completado'): ?>
                                                                                    <span class="badge bg-success"><i class="fas fa-check-circle me-1"></i><?php echo ucfirst($concepto['estado']); ?></span>
                                                                                <?php elseif ($concepto['estado'] === 'Retrasado'): ?>
                                                                                    <span class="badge bg-warning text-dark"><i class="fas fa-exclamation-triangle me-1"></i><?php echo ucfirst($concepto['estado']); ?></span>
                                                                                <?php else: ?>
                                                                                    <span class="badge bg-secondary"><i class="fas fa-clock me-1"></i><?php echo ucfirst($concepto['estado']); ?></span>
                                                                                <?php endif; ?>
                                                                            </td>
                                                                        </tr>
                                                                        <?php $total += $concepto['monto_por_pagar']; ?>
                                                                    <?php endforeach; ?>
                                                                    <tr class="total-row">
                                                                        <td colspan="2"><strong><i class="fas fa-calculator me-1"></i>Total</strong></td>
                                                                        <td><strong>$<?php echo number_format($total, 2); ?></strong></td>
                                                                    </tr>
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>
                                                <?php $mesCount++; ?>
                                        <?php endforeach;
                                        } else {
                                            echo '<p><i class="fas fa-info-circle me-1"></i>No hay meses registrados para este contrato.</p>';
                                        } ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="text-center"><i class="fas fa-info-circle me-1"></i>No hay conceptos de pago registrados para tu contrato activo.</p>
            <?php endif; ?>
        </div>
    </div>
</div>