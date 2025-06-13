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

// Procesar creación de contrato
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['crear_contrato'])) {
    $id_propiedad = $_POST['id_propiedad'];
    $id_arrendatario = $_POST['id_arrendatario'];
    $fecha_inicio = $_POST['fecha_inicio'];
    $fecha_fin = $_POST['fecha_fin'];
    $id_contrato_enviado = $_POST['id_contrato_enviado'];

    // Validar fechas
    if (strtotime($fecha_inicio) >= strtotime($fecha_fin)) {
        $error = "La fecha de inicio debe ser anterior a la fecha de fin.";
    } else {
        try {
            $pdo->beginTransaction();

            // Verificar que la propiedad pertenece al propietario y está disponible
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM propiedades WHERE id = :id_propiedad AND id_propietario = :id_propietario AND estado = 'Disponible'");
            $stmt->execute(['id_propiedad' => $id_propiedad, 'id_propietario' => $id_propietario]);
            if ($stmt->fetchColumn() == 0) {
                $error = "La propiedad no está disponible o no tienes permiso para gestionarla.";
            } else {
                // Crear contrato
                $stmt = $pdo->prepare("INSERT INTO contratos (id_propiedad, id_arrendatario, fecha_inicio, fecha_fin, estado, created_at, updated_at) VALUES (:id_propiedad, :id_arrendatario, :fecha_inicio, :fecha_fin, 'Activo', NOW(), NOW())");
                $stmt->execute([
                    'id_propiedad' => $id_propiedad,
                    'id_arrendatario' => $id_arrendatario,
                    'fecha_inicio' => $fecha_inicio,
                    'fecha_fin' => $fecha_fin
                ]);

                $id_contrato = $pdo->lastInsertId();

                // Actualizar estado de la propiedad a Ocupado
                $stmt = $pdo->prepare("UPDATE propiedades SET estado = 'Ocupado', updated_at = NOW() WHERE id = :id_propiedad");
                $stmt->execute(['id_propiedad' => $id_propiedad]);

                // Actualizar el contrato enviado
                $stmt = $pdo->prepare("UPDATE contratos_enviados SET estado = 'Revisado', id_contrato_asociado = :id_contrato WHERE id = :id_contrato_enviado");
                $stmt->execute(['id_contrato' => $id_contrato, 'id_contrato_enviado' => $id_contrato_enviado]);

                $pdo->commit();
                $success = "Contrato creado correctamente.";
                // Recargar la página para reflejar los cambios
                header("Location: dashboardPropietario.php?page=contratos");
                exit;
            }
        } catch (PDOException $e) {
            $pdo->rollBack();
            $error = "Error al crear el contrato: " . $e->getMessage();
        }
    }
}

// Procesar eliminación de contrato
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['finalizar'])) {
    $contrato_id = $_POST['contrato_id'];

    try {
        $pdo->beginTransaction();

        // Obtener id_propiedad e id_arrendatario del contrato
        $stmt = $pdo->prepare("SELECT id_propiedad, id_arrendatario FROM contratos WHERE id = :contrato_id");
        $stmt->execute(['contrato_id' => $contrato_id]);
        $contrato = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($contrato) {
            $propiedad_id = $contrato['id_propiedad'];
            $id_arrendatario = $contrato['id_arrendatario'];

            // Verificar que la propiedad pertenece al propietario
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM propiedades WHERE id = :propiedad_id AND id_propietario = :id_propietario");
            $stmt->execute(['propiedad_id' => $propiedad_id, 'id_propietario' => $id_propietario]);
            if ($stmt->fetchColumn() > 0) {
                // Eliminar registros relacionados en contratos_enviados (si existen)
                $stmt = $pdo->prepare("DELETE FROM contratos_enviados WHERE id_contrato_asociado = :contrato_id");
                $stmt->execute(['contrato_id' => $contrato_id]);

                // Eliminar registros relacionados en estado_pagos
                $stmt = $pdo->prepare("DELETE FROM estado_pagos WHERE id_contrato = :contrato_id");
                $stmt->execute(['contrato_id' => $contrato_id]);

                // Eliminar registros relacionados en pagos (si existen)
                $stmt = $pdo->prepare("DELETE FROM pagos WHERE id_contrato = :contrato_id");
                $stmt->execute(['contrato_id' => $contrato_id]);

                // Eliminar el contrato
                $stmt = $pdo->prepare("DELETE FROM contratos WHERE id = :contrato_id");
                $stmt->execute(['contrato_id' => $contrato_id]);

                // Actualizar estado de la propiedad a Disponible
                $stmt = $pdo->prepare("UPDATE propiedades SET estado = 'Disponible', updated_at = NOW() WHERE id = :propiedad_id");
                $stmt->execute(['propiedad_id' => $propiedad_id]);

                $pdo->commit();
                $success = "Contrato eliminado y propiedad liberada correctamente.";
                // Recargar la página para reflejar los cambios
                header("Location: dashboardPropietario.php?page=contratos");
                exit;
            } else {
                $pdo->rollBack();
                $error = "No tienes permiso para eliminar este contrato.";
            }
        } else {
            $pdo->rollBack();
            $error = "Contrato no encontrado.";
        }
    } catch (PDOException $e) {
        $pdo->rollBack();
        $error = "Error al eliminar el contrato: " . $e->getMessage();
    }
}

// Obtener contratos pendientes
try {
    $stmt = $pdo->prepare("SELECT ce.id, u.nombre AS arrendatario_nombre, p.direccion, ce.archivo_pdf, ce.fecha_envio 
                           FROM contratos_enviados ce 
                           JOIN arrendatarios a ON ce.id_arrendatario = a.id 
                           JOIN usuarios u ON a.id_usuario = u.id 
                           JOIN propiedades p ON ce.id_propiedad = p.id 
                           WHERE ce.id_propietario = :id_propietario AND ce.estado = 'Pendiente'");
    $stmt->execute(['id_propietario' => $id_propietario]);
    $contratos_pendientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Obtener propiedades disponibles y arrendatarios
    $stmt = $pdo->prepare("SELECT id, direccion FROM propiedades WHERE id_propietario = :id_propietario AND estado = 'Disponible'");
    $stmt->execute(['id_propietario' => $id_propietario]);
    $propiedades = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $pdo->prepare("SELECT a.id, u.nombre, a.id_propiedad FROM usuarios u INNER JOIN arrendatarios a ON u.id = a.id_usuario WHERE a.id_propietario = :id_propietario");
    $stmt->execute(['id_propietario' => $id_propietario]);
    $arrendatarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Obtener contratos activos con información del PDF asociado
    $stmt = $pdo->prepare("SELECT c.id, p.direccion, p.precio, u.nombre AS arrendatario_nombre, c.fecha_inicio, c.fecha_fin, c.estado, i.url_imagen, ce.archivo_pdf 
                           FROM contratos c 
                           JOIN propiedades p ON c.id_propiedad = p.id 
                           JOIN arrendatarios a ON c.id_arrendatario = a.id 
                           JOIN usuarios u ON a.id_usuario = u.id 
                           LEFT JOIN imagenes_propiedad i ON p.id = i.id_propiedad AND i.orden = 1 
                           LEFT JOIN contratos_enviados ce ON c.id = ce.id_contrato_asociado 
                           WHERE p.id_propietario = :id_propietario AND c.estado = 'Activo'");
    $stmt->execute(['id_propietario' => $id_propietario]);
    $contratos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Error al cargar datos: " . $e->getMessage();
}

// Procesar la renovación del contrato
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['renovar_contrato'])) {
    $id_contrato = $_POST['id_contrato'];
    $nueva_fecha_fin = $_POST['nueva_fecha_fin'];

    try {
        $stmt = $pdo->prepare("UPDATE contratos SET fecha_fin = :fecha_fin WHERE id = :id_contrato AND estado = 'Activo'");
        $stmt->execute(['fecha_fin' => $nueva_fecha_fin, 'id_contrato' => $id_contrato]);
        $success = "Contrato renovado correctamente hasta el $nueva_fecha_fin.";
    } catch (PDOException $e) {
        $error = "Error al renovar el contrato: " . $e->getMessage();
    }
}

?>

<div class="container mt-5">
    <div class="card" style="background-color: #2c2c2c; border-radius: 15px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);">
        <div class="card-body text-white">
            <h2 class="text-center mb-4">
                <i class="fas fa-file-contract me-2"></i>Contratos
            </h2>
            <!-- Sección de contratos pendientes -->
            <div class="mt-4">
                <h3><i class="fas fa-clock me-2"></i>Contratos Pendientes</h3>
                <?php if (!empty($contratos_pendientes)): ?>
                    <div class="list-group">
                        <?php foreach ($contratos_pendientes as $pendiente): ?>
                            <a href="download.php?id=<?php echo $pendiente['id']; ?>" class="list-group-item list-group-item-action" target="_blank">
                                <div class="d-flex w-100 justify-content-between">
                                    <h5 class="mb-1"><?php echo htmlspecialchars($pendiente['arrendatario_nombre']); ?> - <?php echo htmlspecialchars($pendiente['direccion']); ?></h5>
                                    <small><?php echo $pendiente['fecha_envio']; ?></small>
                                </div>
                                <p class="mb-1">Enviado por: <?php echo htmlspecialchars($pendiente['arrendatario_nombre']); ?></p>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="text-center">No hay contratos pendientes.</p>
                <?php endif; ?>
            </div>

            <!-- Modal para crear contrato -->
            <div class="mt-4">
                <button class="btn btn-primary w-100" data-bs-toggle="modal" data-bs-target="#crearContratoModal">
                    <i class="fas fa-file-signature fa-lg me-2"></i>Crear Contrato
                </button>
            </div>

            <div class="modal fade" id="crearContratoModal" tabindex="-1" aria-labelledby="crearContratoModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content" style="background-color: #2c2c2c; border-radius: 15px;">
                        <div class="modal-header border-0">
                            <h2 class="modal-title text-white w-100 text-center" id="crearContratoModalLabel">
                                <i class="fas fa-file-signature me-2"></i>Crear Contrato
                            </h2>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                        </div>
                        <div class="modal-body text-white">
                            <?php if (!empty($error)): ?>
                                <div class="alert alert-danger" role="alert"><?php echo $error; ?></div>
                            <?php elseif (!empty($success)): ?>
                                <div class="alert alert-success" role="alert"><?php echo $success; ?></div>
                            <?php endif; ?>

                            <form method="POST" action="dashboardPropietario.php?page=contratos" id="formCrearContrato" enctype="multipart/form-data">
                                <input type="hidden" name="crear_contrato" value="1">
                                <div class="mb-3">
                                    <label for="id_arrendatario" class="form-label">
                                        <i class="fas fa-user"></i> Arrendatario
                                    </label>
                                    <select class="form-control" id="id_arrendatario" name="id_arrendatario" required>
                                        <option value="">Selecciona un arrendatario</option>
                                        <?php foreach ($arrendatarios as $arrendatario): ?>
                                            <option value="<?php echo $arrendatario['id']; ?>" data-id-propiedad="<?php echo $arrendatario['id_propiedad'] ?? ''; ?>">
                                                <?php echo htmlspecialchars($arrendatario['nombre']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="id_propiedad" class="form-label">
                                        <i class="fas fa-home"></i> Propiedad
                                    </label>
                                    <select class="form-control" id="id_propiedad" name="id_propiedad" required>
                                        <option value="">Selecciona una propiedad</option>
                                        <?php foreach ($propiedades as $propiedad): ?>
                                            <option value="<?php echo $propiedad['id']; ?>"><?php echo htmlspecialchars($propiedad['direccion']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="fecha_inicio" class="form-label">
                                        <i class="fas fa-calendar-alt"></i> Fecha de Inicio
                                    </label>
                                    <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio" required>
                                </div>
                                <div class="mb-3">
                                    <label for="fecha_fin" class="form-label">
                                        <i class="fas fa-calendar-check"></i> Fecha de Fin
                                    </label>
                                    <input type="date" class="form-control" id="fecha_fin" name="fecha_fin" required>
                                </div>
                                <div class="mb-3">
                                    <label for="id_contrato_enviado" class="form-label">
                                        <i class="fas fa-file-pdf"></i> Contrato Enviado
                                    </label>
                                    <select class="form-control" id="id_contrato_enviado" name="id_contrato_enviado" required>
                                        <option value="">Selecciona un contrato enviado</option>
                                        <?php foreach ($contratos_pendientes as $pendiente): ?>
                                            <option value="<?php echo $pendiente['id']; ?>">
                                                <?php echo htmlspecialchars($pendiente['arrendatario_nombre']) . ' - ' . date('d/m/Y', strtotime($pendiente['fecha_envio'])); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-plus-circle me-2"></i>Crear Contrato
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>



            <!-- Lista de contratos activos -->
            <div class="mt-5">
                <h3><i class="fas fa-file-contract me-2"></i>Contratos Activos</h3>
                <?php if (!empty($contratos)): ?>
                    <div class="row">
                        <?php foreach ($contratos as $contrato): ?>
                            <div class="col-md-4 mb-4">
                                <div class="card h-100" style="background-color: rgb(236, 233, 233);">
                                    <img src="<?php echo '../../../public/' . htmlspecialchars($contrato['url_imagen'] ?? ''); ?>" class="card-img-top" alt="Imagen" style="height: 200px; object-fit: cover;" onerror="this.src='../../../public/assets/img/default.jpg';">
                                    <div class="card-body">
                                        <h5 class="card-title">
                                            <i class="fas fa-map-marker-alt me-2"></i><?php echo htmlspecialchars($contrato['direccion']); ?>
                                        </h5>
                                        <p class="card-text">
                                            <i class="fas fa-dollar-sign"></i> Precio: <?php echo number_format($contrato['precio'], 2); ?>
                                        </p>
                                        <p class="card-text">
                                            <i class="fas fa-user"></i> Arrendatario: <?php echo htmlspecialchars($contrato['arrendatario_nombre']); ?>
                                        </p>
                                        <p class="card-text">
                                            <i class="fas fa-calendar-alt"></i> Inicio: <?php echo $contrato['fecha_inicio']; ?>
                                        </p>
                                        <p class="card-text">
                                            <i class="fas fa-calendar-check"></i> Fin: <?php echo $contrato['fecha_fin']; ?>
                                        </p>
                                        <div class="d-grid gap-2">
                                            <?php if ($contrato['archivo_pdf'] && file_exists($contrato['archivo_pdf'])): ?>
                                                <a href="download.php?id_contrato_asociado=<?php echo $contrato['id']; ?>" class="btn btn-success" target="_blank">
                                                    <i class="fas fa-download me-2"></i>Descargar Contrato
                                                </a>
                                            <?php endif; ?>
                                            <form method="POST" action="dashboardPropietario.php?page=contratos" style="display:inline;" id="finishForm<?php echo $contrato['id']; ?>">
                                                <input type="hidden" name="finalizar" value="1">
                                                <input type="hidden" name="contrato_id" value="<?php echo $contrato['id']; ?>">
                                                <button type="button" class="btn btn-danger w-100" onclick="confirmFinish(<?php echo $contrato['id']; ?>)">
                                                    <i class="fas fa-times-circle me-2"></i>Finalizar
                                                </button>
                                            </form>
                                            <form>
                                                <!-- Elimina los inputs ocultos y el form innecesario aquí -->
                                                <button style="color: #ffff;" type="button" class="btn btn-info w-100" data-bs-toggle="modal" data-bs-target="#renovarContratoModal" onclick="setContratoIdRenovar(<?php echo $contrato['id']; ?>)">
                                                    <i class="fa fa-rotate-right me-2"></i>Renovar
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>

                        <!-- Modal Renovar Contrato -->
                        <div class="modal fade" id="renovarContratoModal" tabindex="-1" aria-labelledby="renovarContratoModalLabel" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content" style="background-color: #2c2c2c; border-radius: 15px;">
                                    <div class="modal-header border-0">
                                        <h2 class="modal-title text-white w-100 text-center" id="renovarContratoModalLabel">
                                            <i class="fa fa-rotate-right me-2"></i>Renovar Contrato
                                        </h2>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                                    </div>
                                    <div class="modal-body text-white">
                                        <form method="POST" action="dashboardPropietario.php?page=contratos">
                                            <input type="hidden" name="renovar_contrato" value="1">
                                            <input type="hidden" name="id_contrato" id="idContratoRenovar">
                                            <div class="mb-3">
                                                <label for="nueva_fecha_fin" class="form-label">
                                                    <i class="fas fa-calendar-check"></i> Nueva Fecha de Fin
                                                </label>
                                                <input type="date" class="form-control" id="nueva_fecha_fin" name="nueva_fecha_fin" required>
                                            </div>
                                            <button style="color: #ffff;" type="button" class="btn btn-info w-100" onclick="confirmarRenovacion()">
                                                <i class="fa fa-rotate-right me-2"></i>Renovar
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <p class="text-center">No hay contratos activos.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
    function setContratoIdRenovar(id) {
        document.getElementById('idContratoRenovar').value = id;
    }
</script>

<script>
    function confirmarRenovacion() {
        const fechaFin = document.getElementById('nueva_fecha_fin').value;
        if (!fechaFin) {
            Swal.fire('Error', 'Debes seleccionar una nueva fecha de fin.', 'error');
            return;
        }
        Swal.fire({
            title: '¿Renovar contrato?',
            text: '¿Deseas renovar este contrato hasta ' + fechaFin + '?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#17a2b8',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, renovar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                // Envía el formulario del modal
                document.querySelector('#renovarContratoModal form').submit();
            }
        });
    }
</script>

<!-- Script para confirmación de finalización y autocompletar propiedad -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function confirmFinish(id) {
        Swal.fire({
            title: '¿Estás seguro?',
            text: '¿Deseas finalizar este contrato y liberar la propiedad?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Confirmar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('finishForm' + id).submit();
            }
        });
    }

    // Autocompletar el campo de propiedad al seleccionar un arrendatario
    document.getElementById('id_arrendatario').addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const idPropiedad = selectedOption.getAttribute('data-id-propiedad');
        const selectPropiedad = document.getElementById('id_propiedad');

        if (idPropiedad) {
            for (let option of selectPropiedad.options) {
                if (option.value == idPropiedad) {
                    option.selected = true;
                    break;
                }
            }
        } else {
            selectPropiedad.selectedIndex = 0; // Resetear a "Selecciona una propiedad"
        }
    });
</script>

<!-- Archivo separado para descarga de PDF de contratos activos -->
<?php
if (isset($_GET['download']) && isset($_GET['id'])) {
    $id = $_GET['id'];
    $stmt = $pdo->prepare("SELECT archivo_pdf FROM contratos_enviados WHERE id_contrato_asociado = :id AND id_propietario = :id_propietario");
    $stmt->execute(['id' => $id, 'id_propietario' => $id_propietario]);
    $pendiente = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($pendiente && file_exists($pendiente['archivo_pdf'])) {
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="contrato_activo_' . $id . '.pdf"');
        header('Content-Length: ' . filesize($pendiente['archivo_pdf']));
        readfile($pendiente['archivo_pdf']);
        exit;
    } else {
        die('Archivo no encontrado.');
    }
}
?>