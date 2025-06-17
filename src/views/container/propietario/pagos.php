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

// Incluir la configuración de la base de datos
require '../../../config/config.php';

$success = '';
$error = '';

// Obtener el ID del propietario
try {
    $stmt = $pdo->prepare("SELECT id FROM propietarios WHERE id_usuario = :id_usuario");
    $stmt->execute(['id_usuario' => $_SESSION['user_id']]);
    $propietario = $stmt->fetch(PDO::FETCH_ASSOC);
    $id_propietario = $propietario['id'];
} catch (PDOException $e) {
    $error = "Error al obtener el propietario: " . $e->getMessage();
}

// Procesar la creación de un nuevo mes y conceptos
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['agregar_mes'])) {
    $id_contrato = $_POST['id_contrato'];
    $periodo = $_POST['periodo'];
    $conceptos = $_POST['conceptos'] ?? [];

    try {
        $pdo->beginTransaction();
        foreach ($conceptos as $concepto => $monto) {
            if ($monto > 0) {
                $stmt = $pdo->prepare("INSERT INTO conceptos_pago (id_contrato, periodo, concepto, monto_por_pagar, estado) VALUES (:id_contrato, :periodo, :concepto, :monto_por_pagar, :estado)");
                $stmt->execute([
                    'id_contrato' => $id_contrato,
                    'periodo' => $periodo,
                    'concepto' => $concepto,
                    'monto_por_pagar' => $monto,
                    'estado' => 'Pendiente'
                ]);
            }
        }
        $pdo->commit();
        $success = "Mes y conceptos agregados correctamente.";
    } catch (PDOException $e) {
        $pdo->rollBack();
        $error = "Error al agregar el mes: " . $e->getMessage();
    }
}

// Procesar la actualización del estado de un concepto
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['actualizar_estado'])) {
    $id_concepto = $_POST['id_concepto'];
    $estado = $_POST['estado'];

    try {
        $stmt = $pdo->prepare("UPDATE conceptos_pago SET estado = :estado, updated_at = NOW() WHERE id = :id_concepto");
        $stmt->execute(['estado' => $estado, 'id_concepto' => $id_concepto]);
        $success = "Estado actualizado correctamente.";
    } catch (PDOException $e) {
        $error = "Error al actualizar el estado: " . $e->getMessage();
    }
}

// Obtener contratos activos y conceptos
$contratos = [];
try {
    $stmt = $pdo->prepare("SELECT c.id AS contrato_id, c.id_arrendatario, p.id AS propiedad_id, p.direccion, p.precio, u.nombre AS arrendatario_nombre, c.fecha_inicio, c.fecha_fin
        FROM contratos c
        JOIN propiedades p ON c.id_propiedad = p.id
        JOIN arrendatarios a ON c.id_arrendatario = a.id
        JOIN usuarios u ON a.id_usuario = u.id
        WHERE p.id_propietario = :id_propietario AND c.estado = 'Activo'
    ");
    $stmt->execute(['id_propietario' => $id_propietario]);
    $contratos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($contratos as $index => $contrato) {
        $stmt = $pdo->prepare("SELECT periodo FROM conceptos_pago WHERE id_contrato = :id_contrato GROUP BY periodo ORDER BY periodo");
        $stmt->execute(['id_contrato' => $contrato['contrato_id']]);
        $periodos = $stmt->fetchAll(PDO::FETCH_COLUMN);

        $meses = [];
        foreach ($periodos as $periodo) {
            $stmt = $pdo->prepare("SELECT cp.*, cp2.archivo, cp2.tipo_archivo, cp2.estado AS estado_comprobante
                FROM conceptos_pago cp
                LEFT JOIN comprobantes_pago cp2 ON cp.id = cp2.id_concepto_pago
                WHERE cp.id_contrato = :id_contrato AND cp.periodo = :periodo");
            $stmt->execute(['id_contrato' => $contrato['contrato_id'], 'periodo' => $periodo]);
            $meses[$periodo] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        $contratos[$index]['meses'] = $meses;
    }
} catch (PDOException $e) {
    $error = "Error al cargar los datos de contratos: " . $e->getMessage();
}

// Función para obtener el nombre del mes en español
function getSpanishMonth($monthNum)
{
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

<!-- FontAwesome CDN -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
<link rel="stylesheet" href="../../../../public/assets/css/propietarioPagos.css">
<div class="container mt-5">
    <div class="card">
        <div class="card-body">
            <h2 class="text-center mb-4"><i class="fas fa-money-bill-wave me-2"></i>Gestión de Pagos</h2>
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger" role="alert"><i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?></div>
            <?php elseif (!empty($success)): ?>
                <div class="alert alert-success" role="alert"><i class="fas fa-check-circle me-2"></i><?php echo $success; ?></div>
            <?php endif; ?>

            <!-- Lista de Contratos Activos -->
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
                                    <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#agregarMesModal<?php echo $contrato['contrato_id']; ?>">
                                        <i class="fas fa-plus me-2"></i>Agregar Mes
                                    </button>
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
                                                        <button class="accordion-button collapsed" style="color: #fff;" type="button" data-bs-toggle="collapse" data-bs-target="#collapseMes<?php echo $contrato['contrato_id'] . $mesCount; ?> ">
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
                                                                        <th><i class="fas fa-eye me-1"></i>Comprobante</th>
                                                                        <th><i class="fas fa-cogs me-1"></i>Acciones</th>
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
                                                                        $comprobante = isset($concepto['archivo']) ? $concepto['archivo'] : null;
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
                                                                            <td>
                                                                                <?php if ($comprobante): ?>
                                                                                    <a href="../../../public/assets/comprobantes/<?php echo htmlspecialchars($comprobante); ?>" target="_blank" class="btn btn-info btn-sm"><i class="fas fa-eye"></i> Mirar</a>
                                                                                <?php else: ?>
                                                                                    <span class="text-muted">No disponible</span>
                                                                                <?php endif; ?>
                                                                            </td>
                                                                            <td>
                                                                                <form method="POST" style="display:inline;">
                                                                                    <input type="hidden" name="actualizar_estado" value="1">
                                                                                    <input type="hidden" name="id_concepto" value="<?php echo $concepto['id']; ?>">
                                                                                    <button type="submit" name="estado" value="Completado" class="btn btn-success btn-sm" title="Marcar como Completado"><i class="fas fa-check"></i></button>
                                                                                    <button type="submit" name="estado" value="Retrasado" class="btn btn-warning btn-sm" title="Marcar como Retrasado"><i class="fas fa-exclamation-triangle"></i></button>
                                                                                </form>
                                                                            </td>
                                                                        </tr>
                                                                        <?php $total += $concepto['monto_por_pagar']; ?>
                                                                    <?php endforeach; ?>
                                                                    <tr class="total-row">
                                                                        <td colspan="2"><strong><i class="fas fa-calculator me-1"></i>Total</strong></td>
                                                                        <td><strong>$<?php echo number_format($total, 2); ?></strong></td>
                                                                        <td></td>
                                                                        <td></td>
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

                        <!-- Modal para agregar mes -->
                        <div class="modal fade" id="agregarMesModal<?php echo $contrato['contrato_id']; ?>" tabindex="-1" aria-labelledby="agregarMesModalLabel<?php echo $contrato['contrato_id']; ?>" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content" style="background-color: #2c2c2c;">
                                    <div class="modal-header">
                                        <h5 class="modal-title text-white" id="agregarMesModalLabel<?php echo $contrato['contrato_id']; ?>"><i class="fas fa-plus me-2"></i>Agregar Mes</h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                                    </div>
                                    <div class="modal-body text-white">
                                        <?php if (!empty($error)): ?>
                                            <div class="alert alert-danger" role="alert"><i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?></div>
                                        <?php endif; ?>
                                        <form method="POST" action="">
                                            <input type="hidden" name="agregar_mes" value="1">
                                            <input type="hidden" name="id_contrato" value="<?php echo $contrato['contrato_id']; ?>">
                                            <div class="mb-3">
                                                <label for="periodo<?php echo $contrato['contrato_id']; ?>" class="form-label"><i class="fas fa-calendar-alt me-1"></i>Periodo (MES/AÑO)</label>
                                                <input type="month" class="form-control" id="periodo<?php echo $contrato['contrato_id']; ?>" name="periodo" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label"><i class="fas fa-list me-1"></i>Conceptos</label>
                                                <div class="row">
                                                    <?php $conceptos = ['arriendo', 'agua', 'luz', 'gas', 'internet', 'seguro', 'daños', 'mantenimiento']; ?>
                                                    <?php
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
                                                    ?>
                                                    <?php foreach ($conceptos as $c): ?>
                                                        <div class="col-md-6 mb-2">
                                                            <div class="input-group">
                                                                <span class="input-group-text">
                                                                    <i class="fas <?php echo $iconosConcepto[$c]; ?> me-1"></i><?php echo ucfirst($c); ?>
                                                                </span>
                                                                <input type="number" class="form-control" name="conceptos[<?php echo $c; ?>]" step="0.01" placeholder="Monto" min="0">
                                                            </div>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            </div>
                                            <button type="submit" class="btn btn-primary w-100"><i class="fas fa-save me-2"></i>Guardar</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="text-center"><i class="fas fa-info-circle me-1"></i>No hay contratos activos.</p>
            <?php endif; ?>
        </div>
    </div>
</div>