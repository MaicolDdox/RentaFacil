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

// Obtener contratos activos, pagos y estado de pagos
$contratos = [];
$pagos = [];
$estados_pagos = [];
try {
    // Contratos activos del propietario
    $stmt = $pdo->prepare("
        SELECT c.id AS contrato_id, c.id_arrendatario, p.id AS propiedad_id, p.direccion, p.precio, u.nombre AS arrendatario_nombre
        FROM contratos c
        JOIN propiedades p ON c.id_propiedad = p.id
        JOIN arrendatarios a ON c.id_arrendatario = a.id
        JOIN usuarios u ON a.id_usuario = u.id
        WHERE p.id_propietario = :id_propietario AND c.estado = 'Activo'
    ");
    $stmt->execute(['id_propietario' => $id_propietario]);
    $contratos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Pagos realizados
    $stmt = $pdo->prepare("
        SELECT p.id AS pago_id, p.id_contrato, p.monto, p.fecha_pago, p.estado, u.nombre AS arrendatario_nombre, pr.direccion
        FROM pagos p
        JOIN contratos c ON p.id_contrato = c.id
        JOIN propiedades pr ON c.id_propiedad = pr.id
        JOIN arrendatarios a ON c.id_arrendatario = a.id
        JOIN usuarios u ON a.id_usuario = u.id
        WHERE pr.id_propietario = :id_propietario
        ORDER BY p.fecha_pago DESC
    ");
    $stmt->execute(['id_propietario' => $id_propietario]);
    $pagos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Actualizar o crear registros en estado_pagos para el mes actual
    $currentMonth = date('Y-m'); // Mayo 2025
    foreach ($contratos as $contrato) {
        // Verificar si ya existe un registro en estado_pagos para este contrato y mes
        $stmt = $pdo->prepare("
            SELECT id, monto_pagado, estado
            FROM estado_pagos
            WHERE id_contrato = :id_contrato AND periodo = :periodo
        ");
        $stmt->execute(['id_contrato' => $contrato['contrato_id'], 'periodo' => $currentMonth]);
        $estado_pago = $stmt->fetch(PDO::FETCH_ASSOC);

        // Calcular el monto pagado para el mes actual
        $monto_pagado = 0;
        foreach ($pagos as $pago) {
            $pagoMonth = date('Y-m', strtotime($pago['fecha_pago']));
            if ($pago['id_contrato'] == $contrato['contrato_id'] && $pagoMonth == $currentMonth) {
                $monto_pagado += $pago['monto'];
            }
        }

        // Determinar el estado
        $estado = 'Debe Pago';
        if ($monto_pagado >= $contrato['precio']) {
            $estado = 'Pagado';
        } elseif ($monto_pagado > 0) {
            $estado = 'Parcial';
        }

        if ($estado_pago) {
            // Actualizar registro existente
            $stmt = $pdo->prepare("
                UPDATE estado_pagos
                SET monto_pagado = :monto_pagado, estado = :estado, updated_at = NOW()
                WHERE id = :id
            ");
            $stmt->execute([
                'monto_pagado' => $monto_pagado,
                'estado' => $estado,
                'id' => $estado_pago['id']
            ]);
        } else {
            // Crear nuevo registro
            $stmt = $pdo->prepare("
                INSERT INTO estado_pagos (id_contrato, id_arrendatario, id_propiedad, periodo, monto_esperado, monto_pagado, estado, created_at, updated_at)
                VALUES (:id_contrato, :id_arrendatario, :id_propiedad, :periodo, :monto_esperado, :monto_pagado, :estado, NOW(), NOW())
            ");
            $stmt->execute([
                'id_contrato' => $contrato['contrato_id'],
                'id_arrendatario' => $contrato['id_arrendatario'],
                'id_propiedad' => $contrato['propiedad_id'],
                'periodo' => $currentMonth,
                'monto_esperado' => $contrato['precio'],
                'monto_pagado' => $monto_pagado,
                'estado' => $estado
            ]);
        }
    }

    // Obtener estados de pago para el mes actual
    $stmt = $pdo->prepare("
        SELECT ep.id_contrato, ep.monto_esperado, ep.monto_pagado, ep.estado, u.nombre AS arrendatario_nombre, p.direccion
        FROM estado_pagos ep
        JOIN contratos c ON ep.id_contrato = c.id
        JOIN propiedades p ON c.id_propiedad = p.id
        JOIN arrendatarios a ON c.id_arrendatario = a.id
        JOIN usuarios u ON a.id_usuario = u.id
        WHERE p.id_propietario = :id_propietario AND ep.periodo = :periodo
    ");
    $stmt->execute(['id_propietario' => $id_propietario, 'periodo' => $currentMonth]);
    $estados_pagos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Error al cargar los datos de pagos: " . $e->getMessage();
}
?>

<?php include '../../layouts/container/propietario/headerPropietario.php'; ?>

<div class="container mt-5">
    <div class="card" style="background-color: #2c2c2c; border-radius: 15px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);">
        <div class="card-body text-white">
            <h2 class="text-center mb-4">Gestión de Pagos</h2>
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger" role="alert"><?php echo $error; ?></div>
            <?php elseif (!empty($success)): ?>
                <div class="alert alert-success" role="alert"><?php echo $success; ?></div>
            <?php endif; ?>

            <!-- Sección 1: Pagos Realizados -->
            <h3 class="mt-4">Pagos Realizados</h3>
            <?php if (!empty($pagos)): ?>
                <div class="table-responsive">
                    <table class="table table-dark table-striped">
                        <thead>
                            <tr>
                                <th>Arrendatario</th>
                                <th>Propiedad</th>
                                <th>Monto Pagado</th>
                                <th>Fecha de Pago</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pagos as $pago): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($pago['arrendatario_nombre'] ?? 'Desconocido'); ?></td>
                                    <td><?php echo htmlspecialchars($pago['direccion'] ?? 'Desconocida'); ?></td>
                                    <td><?php echo number_format($pago['monto'], 2); ?></td>
                                    <td><?php echo htmlspecialchars($pago['fecha_pago']); ?></td>
                                    <td><?php echo htmlspecialchars($pago['estado']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="text-center">No hay pagos registrados.</p>
            <?php endif; ?>

            <!-- Sección 2: Estado de Pagos -->
            <h3 class="mt-4">Estado de Pagos</h3>
            <?php if (!empty($estados_pagos)): ?>
                <div class="table-responsive">
                    <table class="table table-dark table-striped">
                        <thead>
                            <tr>
                                <th>Arrendatario</th>
                                <th>Propiedad</th>
                                <th>Monto Esperado</th>
                                <th>Monto Pagado</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($estados_pagos as $estado): ?>
                                <tr class="<?php echo $estado['estado'] == 'Debe Pago' ? 'table-danger' : ($estado['estado'] == 'Parcial' ? 'table-warning' : ''); ?>">
                                    <td><?php echo htmlspecialchars($estado['arrendatario_nombre']); ?></td>
                                    <td><?php echo htmlspecialchars($estado['direccion']); ?></td>
                                    <td><?php echo number_format($estado['monto_esperado'], 2); ?></td>
                                    <td><?php echo number_format($estado['monto_pagado'], 2); ?></td>
                                    <td><?php echo htmlspecialchars($estado['estado']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="text-center">No hay estados de pago registrados para el mes actual.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../../layouts/container/propietario/footerPropietario.php'; ?>