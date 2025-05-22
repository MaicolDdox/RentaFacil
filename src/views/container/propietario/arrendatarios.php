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

// Obtener el ID del propietario logueado
try {
    $stmt = $pdo->prepare("SELECT id FROM propietarios WHERE id_usuario = :id_usuario");
    $stmt->execute(['id_usuario' => $_SESSION['user_id']]);
    $id_propietario = $stmt->fetchColumn();
    if (!$id_propietario) {
        $error = "No se encontró el propietario.";
    }
} catch (PDOException $e) {
    $error = "Error al obtener el propietario: " . $e->getMessage();
}

// Procesar la creación de un arrendatario (manual o desde postulación)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['crear'])) {
    $nombre = trim($_POST['nombre'] ?? '');
    $correo = trim($_POST['correo'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $contrasena = trim($_POST['contrasena'] ?? '');
    $postulacion_id = isset($_POST['postulacion_id']) ? trim($_POST['postulacion_id']) : null;

    // Validaciones
    if (empty($nombre) || empty($correo) || empty($contrasena)) {
        $error = "Por favor, completa todos los campos obligatorios.";
    } elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $error = "El correo no es válido.";
    } elseif (strlen($contrasena) < 6) {
        $error = "La contraseña debe tener al menos 6 caracteres.";
    } else {
        try {
            // Verificar si el correo ya existe
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM usuarios WHERE correo = :correo");
            $stmt->execute(['correo' => $correo]);
            if ($stmt->fetchColumn() > 0) {
                $error = "El correo ya está registrado.";
            } else {
                $pdo->beginTransaction();

                // Insertar en la tabla usuarios
                $stmt = $pdo->prepare("INSERT INTO usuarios (nombre, correo, telefono, contrasena, created_at, updated_at, is_verified) VALUES (:nombre, :correo, :telefono, :contrasena, NOW(), NOW(), 1)");
                $stmt->execute([
                    'nombre' => htmlspecialchars($nombre),
                    'correo' => $correo,
                    'telefono' => $telefono ? htmlspecialchars($telefono) : null,
                    'contrasena' => password_hash($contrasena, PASSWORD_DEFAULT)
                ]);

                // Obtener el ID del usuario recién creado
                $user_id = $pdo->lastInsertId();

                // Obtener el id_propiedad de la postulación si existe
                $id_propiedad = null;
                if ($postulacion_id) {
                    $stmt = $pdo->prepare("SELECT id_propiedad FROM postulaciones WHERE id = :postulacion_id AND id_propiedad IN (SELECT id FROM propiedades WHERE id_propietario = :id_propietario)");
                    $stmt->execute(['postulacion_id' => $postulacion_id, 'id_propietario' => $id_propietario]);
                    $postulacion = $stmt->fetch(PDO::FETCH_ASSOC);
                    if ($postulacion) {
                        $id_propiedad = $postulacion['id_propiedad'];
                    } else {
                        $pdo->rollBack();
                        $error = "No tienes permiso para crear un arrendatario desde esta postulación.";
                        goto end_transaction;
                    }
                }

                // Insertar en la tabla arrendatarios con id_propietario
                $stmt = $pdo->prepare("INSERT INTO arrendatarios (id_usuario, id_propiedad, id_propietario) VALUES (:user_id, :id_propiedad, :id_propietario)");
                $stmt->execute([
                    'user_id' => $user_id,
                    'id_propiedad' => $id_propiedad,
                    'id_propietario' => $id_propietario
                ]);

                // Eliminar la postulación si se creó desde una
                if ($postulacion_id) {
                    $stmt = $pdo->prepare("DELETE FROM postulaciones WHERE id = :postulacion_id");
                    $stmt->execute(['postulacion_id' => $postulacion_id]);
                }

                $pdo->commit();
                $success = "Arrendatario creado correctamente.";
            }
        } catch (PDOException $e) {
            $pdo->rollBack();
            $error = "Error al crear el arrendatario: " . $e->getMessage();
        }
    }
    end_transaction:
}

// Procesar la edición de un arrendatario
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['editar'])) {
    $user_id = $_POST['user_id'];
    $nombre = trim($_POST['nombre'] ?? '');
    $correo = trim($_POST['correo'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $contrasena = trim($_POST['contrasena'] ?? '');

    // Validaciones
    if (empty($nombre) || empty($correo)) {
        $error = "Por favor, completa todos los campos obligatorios.";
    } elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $error = "El correo no es válido.";
    } else {
        try {
            // Verificar si el arrendatario pertenece al propietario
            $stmt = $pdo->prepare("SELECT id_propietario FROM arrendatarios WHERE id_usuario = :user_id");
            $stmt->execute(['user_id' => $user_id]);
            $propietario = $stmt->fetchColumn();
            if ($propietario != $id_propietario) {
                $error = "No tienes permiso para editar este arrendatario.";
            } else {
                // Verificar si el correo ya existe (y no pertenece al usuario actual)
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM usuarios WHERE correo = :correo AND id != :user_id");
                $stmt->execute(['correo' => $correo, 'user_id' => $user_id]);
                if ($stmt->fetchColumn() > 0) {
                    $error = "El correo ya está registrado.";
                } else {
                    // Actualizar los datos del usuario
                    if (!empty($contrasena)) {
                        $stmt = $pdo->prepare("UPDATE usuarios SET nombre = :nombre, correo = :correo, telefono = :telefono, contrasena = :contrasena, updated_at = NOW() WHERE id = :user_id");
                        $stmt->execute([
                            'nombre' => htmlspecialchars($nombre),
                            'correo' => $correo,
                            'telefono' => $telefono ? htmlspecialchars($telefono) : null,
                            'contrasena' => password_hash($contrasena, PASSWORD_DEFAULT),
                            'user_id' => $user_id
                        ]);
                    } else {
                        $stmt = $pdo->prepare("UPDATE usuarios SET nombre = :nombre, correo = :correo, telefono = :telefono, updated_at = NOW() WHERE id = :user_id");
                        $stmt->execute([
                            'nombre' => htmlspecialchars($nombre),
                            'correo' => $correo,
                            'telefono' => $telefono ? htmlspecialchars($telefono) : null,
                            'user_id' => $user_id
                        ]);
                    }
                    $success = "Arrendatario actualizado correctamente.";
                }
            }
        } catch (PDOException $e) {
            $error = "Error al actualizar el arrendatario: " . $e->getMessage();
        }
    }
}

// Procesar la eliminación de un arrendatario
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['eliminar'])) {
    $user_id = $_POST['user_id'];
    try {
        $pdo->beginTransaction();

        // Verificar si el arrendatario pertenece al propietario
        $stmt = $pdo->prepare("SELECT id_propietario FROM arrendatarios WHERE id_usuario = :user_id");
        $stmt->execute(['user_id' => $user_id]);
        $propietario = $stmt->fetchColumn();
        if ($propietario != $id_propietario) {
            $pdo->rollBack();
            $error = "No tienes permiso para eliminar este arrendatario.";
        } else {
            // Eliminar de arrendatarios
            $stmt = $pdo->prepare("DELETE FROM arrendatarios WHERE id_usuario = :user_id");
            $stmt->execute(['user_id' => $user_id]);

            // Eliminar de usuarios
            $stmt = $pdo->prepare("DELETE FROM usuarios WHERE id = :user_id");
            $stmt->execute(['user_id' => $user_id]);

            $pdo->commit();
            $success = "Arrendatario eliminado correctamente.";
        }
    } catch (PDOException $e) {
        $pdo->rollBack();
        $error = "Error al eliminar el arrendatario: " . $e->getMessage();
    }
}

// Obtener la lista de arrendatarios del propietario
try {
    $stmt = $pdo->prepare("SELECT u.id, u.nombre, u.correo, u.telefono FROM usuarios u INNER JOIN arrendatarios a ON u.id = a.id_usuario WHERE a.id_propietario = :id_propietario");
    $stmt->execute(['id_propietario' => $id_propietario]);
    $arrendatarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Error al cargar los arrendatarios: " . $e->getMessage();
}

// Obtener las postulaciones para el select
$postulaciones = [];
try {
    $stmt = $pdo->prepare("SELECT po.id AS postulacion_id, po.nombre_postulante, po.correo, po.telefono_postulante FROM postulaciones po JOIN propiedades p ON po.id_propiedad = p.id WHERE p.id_propietario = :id_propietario");
    $stmt->execute(['id_propietario' => $id_propietario]);
    $postulaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Error al cargar las postulaciones: " . $e->getMessage();
}
?>

<?php require '../../layouts/container/propietario/headerPropietario.php'; ?>

<div class="container mt-5">
    <!-- Formulario único para crear arrendatario -->
    <div class="row justify-content-center mb-5">
        <div class="col-md-6">
            <div class="card" style="background-color: #2c2c2c; border-radius: 15px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);">
                <div class="card-body text-white">
                    <h2 class="text-center mb-4">Crear Arrendatario</h2>
                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger" role="alert"><?php echo $error; ?></div>
                    <?php elseif (!empty($success)): ?>
                        <div class="alert alert-success" role="alert"><?php echo $success; ?></div>
                    <?php endif; ?>

                    <form method="POST" action="arrendatarios.php" id="crearArrendatario">
                        <input type="hidden" name="crear" value="1">
                        <!-- Campo para seleccionar postulación -->
                        <div class="mb-3">
                            <label for="postulacion_id" class="form-label">Seleccionar Postulación (opcional)</label>
                            <select class="form-control" id="postulacion_id" name="postulacion_id">
                                <option value="">Crear manualmente o seleccionar postulación</option>
                                <?php foreach ($postulaciones as $postulacion): ?>
                                    <option value="<?php echo $postulacion['postulacion_id']; ?>" data-telefono="<?php echo htmlspecialchars($postulacion['telefono_postulante'] ?? ''); ?>">
                                        <?php echo htmlspecialchars($postulacion['nombre_postulante'] . ' - ' . $postulacion['correo']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <!-- Nombre -->
                        <div class="mb-3">
                            <label for="nombre" class="form-label">Nombre</label>
                            <input type="text" id="nombre" name="nombre" class="form-control" required>
                        </div>
                        <!-- Correo -->
                        <div class="mb-3">
                            <label for="correo" class="form-label">Correo Electrónico</label>
                            <input type="email" id="correo" name="correo" class="form-control" required>
                        </div>
                        <!-- Teléfono -->
                        <div class="mb-3">
                            <label for="telefono" class="form-label">Teléfono</label>
                            <input type="text" id="telefono" name="telefono" class="form-control">
                        </div>
                        <!-- Contraseña -->
                        <div class="mb-3">
                            <label for="contrasena" class="form-label">Contraseña</label>
                            <input type="password" id="contrasena" name="contrasena" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Crear Arrendatario</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de arrendatarios -->
    <div class="row">
        <div class="col-12">
            <div class="card" style="background-color: #2c2c2c; border-radius: 15px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);">
                <div class="card-body text-white">
                    <h2 class="text-center mb-4">Lista de Arrendatarios</h2>
                    <?php if (!empty($arrendatarios)): ?>
                        <table class="table table-dark table-hover">
                            <thead>
                                <tr>
                                    <th>Nombre</th>
                                    <th>Correo</th>
                                    <th>Teléfono</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($arrendatarios as $arrendatario): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($arrendatario['nombre']); ?></td>
                                        <td><?php echo htmlspecialchars($arrendatario['correo']); ?></td>
                                        <td><?php echo htmlspecialchars($arrendatario['telefono'] ?? 'N/A'); ?></td>
                                        <td>
                                            <!-- Botón Editar -->
                                            <button type="button" class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editModal<?php echo $arrendatario['id']; ?>">Editar</button>
                                            <!-- Botón Eliminar -->
                                            <form method="POST" action="arrendatarios.php" style="display:inline;" id="deleteForm<?php echo $arrendatario['id']; ?>">
                                                <input type="hidden" name="eliminar" value="1">
                                                <input type="hidden" name="user_id" value="<?php echo $arrendatario['id']; ?>">
                                                <button type="button" class="btn btn-danger btn-sm" onclick="confirmDelete(<?php echo $arrendatario['id']; ?>)">Eliminar</button>
                                            </form>
                                        </td>
                                    </tr>

                                    <!-- Modal para Editar -->
                                    <div class="modal fade" id="editModal<?php echo $arrendatario['id']; ?>" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content" style="background-color: #2c2c2c; color: white;">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="editModalLabel">Editar Arrendatario</h5>
                                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <form method="POST" action="arrendatarios.php">
                                                        <input type="hidden" name="editar" value="1">
                                                        <input type="hidden" name="user_id" value="<?php echo $arrendatario['id']; ?>">
                                                        <!-- Nombre -->
                                                        <div class="mb-3">
                                                            <label for="nombre_<?php echo $arrendatario['id']; ?>" class="form-label">Nombre</label>
                                                            <input type="text" id="nombre_<?php echo $arrendatario['id']; ?>" name="nombre" class="form-control" value="<?php echo htmlspecialchars($arrendatario['nombre']); ?>" required>
                                                        </div>
                                                        <!-- Correo -->
                                                        <div class="mb-3">
                                                            <label for="correo_<?php echo $arrendatario['id']; ?>" class="form-label">Correo Electrónico</label>
                                                            <input type="email" id="correo_<?php echo $arrendatario['id']; ?>" name="correo" class="form-control" value="<?php echo htmlspecialchars($arrendatario['correo']); ?>" required>
                                                        </div>
                                                        <!-- Teléfono -->
                                                        <div class="mb-3">
                                                            <label for="telefono_<?php echo $arrendatario['id']; ?>" class="form-label">Teléfono</label>
                                                            <input type="text" id="telefono_<?php echo $arrendatario['id']; ?>" name="telefono" class="form-control" value="<?php echo htmlspecialchars($arrendatario['telefono'] ?? ''); ?>">
                                                        </div>
                                                        <!-- Contraseña -->
                                                        <div class="mb-3">
                                                            <label for="contrasena_<?php echo $arrendatario['id']; ?>" class="form-label">Nueva Contraseña (opcional)</label>
                                                            <input type="password" id="contrasena_<?php echo $arrendatario['id']; ?>" name="contrasena" class="form-control">
                                                        </div>
                                                        <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p class="text-center">No hay arrendatarios registrados.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Mostrar SweetAlert si hay éxito o error -->
<?php if (!empty($success)): ?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    Swal.fire({
        icon: 'success',
        title: '¡Éxito!',
        text: '<?php echo $success; ?>',
    });
</script>
<?php elseif (!empty($error)): ?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    Swal.fire({
        icon: 'error',
        title: 'Error',
        text: '<?php echo $error; ?>',
    });
</script>
<?php endif; ?>

<!-- Script para confirmación de eliminación con SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function confirmDelete(id) {
    Swal.fire({
        title: '¿Estás seguro?',
        text: 'Esta acción eliminará al arrendatario permanentemente.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Confirmar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('deleteForm' + id).submit();
        }
    });
}

// Autocompletar campos al seleccionar postulación
document.getElementById('postulacion_id').addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    if (selectedOption.value) {
        const [nombre, correo] = selectedOption.text.split(' - ');
        const telefono = selectedOption.getAttribute('data-telefono') || '';
        document.getElementById('nombre').value = nombre;
        document.getElementById('correo').value = correo;
        document.getElementById('telefono').value = telefono;
    } else {
        document.getElementById('nombre').value = '';
        document.getElementById('correo').value = '';
        document.getElementById('telefono').value = '';
    }
});

// Prellenar al cargar si hay una postulación seleccionada desde la URL
window.addEventListener('load', function() {
    const select = document.getElementById('postulacion_id');
    const urlParams = new URLSearchParams(window.location.search);
    const postulacionId = urlParams.get('postulacion_id');
    if (postulacionId) {
        for (let option of select.options) {
            if (option.value == postulacionId) {
                option.selected = true;
                const [nombre, correo] = option.text.split(' - ');
                const telefono = option.getAttribute('data-telefono') || '';
                document.getElementById('nombre').value = nombre;
                document.getElementById('correo').value = correo;
                document.getElementById('telefono').value = telefono;
                break;
            }
        }
    }
});
</script>

<?php require '../../layouts/container/propietario/footerPropietario.php'; ?>