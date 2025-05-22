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

// Procesar creación de contrato
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['crear_contrato'])) {
    $id_propiedad = $_POST['id_propiedad'];
    $id_arrendatario = $_POST['id_arrendatario'];
    $fecha_inicio = $_POST['fecha_inicio'];
    $fecha_fin = $_POST['fecha_fin'];

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
                // Verificar que el arrendatario pertenece al propietario
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM arrendatarios WHERE id = :id_arrendatario AND id_propietario = :id_propietario");
                $stmt->execute(['id_arrendatario' => $id_arrendatario, 'id_propietario' => $id_propietario]);
                if ($stmt->fetchColumn() == 0) {
                    $error = "El arrendatario seleccionado no es válido o no tienes permiso para gestionarlo.";
                } else {
                    // Crear contrato
                    $stmt = $pdo->prepare("INSERT INTO contratos (id_propiedad, id_arrendatario, fecha_inicio, fecha_fin, estado, created_at, updated_at) VALUES (:id_propiedad, :id_arrendatario, :fecha_inicio, :fecha_fin, 'Activo', NOW(), NOW())");
                    $stmt->execute([
                        'id_propiedad' => $id_propiedad,
                        'id_arrendatario' => $id_arrendatario,
                        'fecha_inicio' => $fecha_inicio,
                        'fecha_fin' => $fecha_fin
                    ]);

                    // Actualizar estado de la propiedad a Ocupado
                    $stmt = $pdo->prepare("UPDATE propiedades SET estado = 'Ocupado', updated_at = NOW() WHERE id = :id_propiedad");
                    $stmt->execute(['id_propiedad' => $id_propiedad]);

                    // Actualizar id_propiedad del arrendatario si no tiene una asignada
                    $stmt = $pdo->prepare("UPDATE arrendatarios SET id_propiedad = :id_propiedad WHERE id = :id_arrendatario AND id_propiedad IS NULL");
                    $stmt->execute(['id_propiedad' => $id_propiedad, 'id_arrendatario' => $id_arrendatario]);

                    $pdo->commit();
                    $success = "Contrato creado correctamente.";
                }
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

        // Obtener id_propiedad del contrato
        $stmt = $pdo->prepare("SELECT id_propiedad FROM contratos WHERE id = :contrato_id");
        $stmt->execute(['contrato_id' => $contrato_id]);
        $contrato = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($contrato) {
            $propiedad_id = $contrato['id_propiedad'];

            // Verificar que la propiedad pertenece al propietario
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM propiedades WHERE id = :propiedad_id AND id_propietario = :id_propietario");
            $stmt->execute(['propiedad_id' => $propiedad_id, 'id_propietario' => $id_propietario]);
            if ($stmt->fetchColumn() > 0) {
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

// Obtener propiedades disponibles y arrendatarios
try {
    $stmt = $pdo->prepare("SELECT id, direccion FROM propiedades WHERE id_propietario = :id_propietario AND estado = 'Disponible'");
    $stmt->execute(['id_propietario' => $id_propietario]);
    $propiedades = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Obtener arrendatarios del propietario logueado
    $stmt = $pdo->prepare("SELECT a.id, u.nombre, a.id_propiedad FROM usuarios u INNER JOIN arrendatarios a ON u.id = a.id_usuario WHERE a.id_propietario = :id_propietario");
    $stmt->execute(['id_propietario' => $id_propietario]);
    $arrendatarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Obtener contratos activos
    $stmt = $pdo->prepare("SELECT c.id, p.direccion, p.tipo, p.precio, u.nombre AS arrendatario_nombre, c.fecha_inicio, c.fecha_fin, c.estado, i.url_imagen 
                           FROM contratos c 
                           JOIN propiedades p ON c.id_propiedad = p.id 
                           JOIN arrendatarios a ON c.id_arrendatario = a.id 
                           JOIN usuarios u ON a.id_usuario = u.id 
                           LEFT JOIN imagenes_propiedad i ON p.id = i.id_propiedad AND i.orden = 1 
                           WHERE p.id_propietario = :id_propietario AND c.estado = 'Activo'");
    $stmt->execute(['id_propietario' => $id_propietario]);
    $contratos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Error al cargar datos: " . $e->getMessage();
}
?>

<?php include '../../layouts/container/propietario/headerPropietario.php'; ?>

<div class="container mt-5">
    <!-- Formulario para crear contrato -->
    <div class="row">
        <div class="col-12">
            <div class="card" style="background-color: #2c2c2c; border-radius: 15px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);">
                <div class="card-body text-white">
                    <h2 class="text-center mb-4">Crear Contrato</h2>
                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger" role="alert"><?php echo $error; ?></div>
                    <?php elseif (!empty($success)): ?>
                        <div class="alert alert-success" role="alert"><?php echo $success; ?></div>
                    <?php endif; ?>

                    <form method="POST" action="contratos.php">
                        <input type="hidden" name="crear_contrato" value="1">
                        <div class="mb-3">
                            <label for="id_arrendatario" class="form-label">Arrendatario</label>
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
                            <label for="id_propiedad" class="form-label">Propiedad</label>
                            <select class="form-control" id="id_propiedad" name="id_propiedad" required>
                                <option value="">Selecciona una propiedad</option>
                                <?php foreach ($propiedades as $propiedad): ?>
                                    <option value="<?php echo $propiedad['id']; ?>"><?php echo htmlspecialchars($propiedad['direccion']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="fecha_inicio" class="form-label">Fecha de Inicio</label>
                            <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio" required>
                        </div>
                        <div class="mb-3">
                            <label for="fecha_fin" class="form-label">Fecha de Fin</label>
                            <input type="date" class="form-control" id="fecha_fin" name="fecha_fin" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Crear Contrato</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Lista de contratos activos -->
    <div class="row mt-5">
        <div class="col-12">
            <div class="card" style="background-color: #2c2c2c; border-radius: 15px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);">
                <div class="card-body text-white">
                    <h2 class="text-center mb-4">Contratos Activos</h2>
                    <?php if (!empty($contratos)): ?>
                        <div class="row">
                            <?php foreach ($contratos as $contrato): ?>
                                <div class="col-md-4 mb-4">
                                    <div class="card h-100" style="background-color:rgb(236, 233, 233);">
                                        <img src="<?php echo '../../../public/' . htmlspecialchars($contrato['url_imagen'] ?? ''); ?>" class="card-img-top" alt="Imagen" style="height: 200px; object-fit: cover;" onerror="this.src='../../../public/assets/img/default.jpg';">
                                        <div class="card-body">
                                            <h5 class="card-title"><?php echo htmlspecialchars($contrato['direccion']); ?></h5>
                                            <p class="card-text">Tipo: <?php echo htmlspecialchars($contrato['tipo']); ?></p>
                                            <p class="card-text">Precio: <?php echo number_format($contrato['precio'], 2); ?></p>
                                            <p class="card-text">Arrendatario: <?php echo htmlspecialchars($contrato['arrendatario_nombre']); ?></p>
                                            <p class="card-text">Inicio: <?php echo $contrato['fecha_inicio']; ?></p>
                                            <p class="card-text">Fin: <?php echo $contrato['fecha_fin']; ?></p>
                                            <form method="POST" action="contratos.php" style="display:inline;" id="finishForm<?php echo $contrato['id']; ?>">
                                                <input type="hidden" name="finalizar" value="1">
                                                <input type="hidden" name="contrato_id" value="<?php echo $contrato['id']; ?>">
                                                <button type="button" class="btn btn-danger w-100" onclick="confirmFinish(<?php echo $contrato['id']; ?>)">Finalizar</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-center">No hay contratos activos.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Script para confirmación de finalización con SweetAlert2 -->
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

<?php include '../../layouts/container/propietario/footerPropietario.php'; ?>