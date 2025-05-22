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

// Obtener el ID del propietario desde la tabla propietarios
try {
    $stmt = $pdo->prepare("SELECT id FROM propietarios WHERE id_usuario = :id_usuario");
    $stmt->execute(['id_usuario' => $_SESSION['user_id']]);
    $propietario = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$propietario) {
        $error = "No se encontró el propietario asociado a este usuario.";
    } else {
        $id_propietario = $propietario['id'];
    }
} catch (PDOException $e) {
    $error = "Error al obtener el propietario: " . $e->getMessage();
}

// Procesar la creación de una propiedad
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['crear']) && empty($error)) {
    $direccion = trim($_POST['direccion'] ?? '');
    $tipo = trim($_POST['tipo'] ?? '');
    $precio = trim($_POST['precio'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $zona = trim($_POST['zona'] ?? '');

    // Validaciones
    if (empty($direccion) || empty($tipo) || empty($precio) || empty($descripcion) || empty($zona)) {
        $error = "Por favor, completa todos los campos obligatorios.";
    } elseif (!is_numeric($precio) || $precio <= 0) {
        $error = "El precio debe ser un número positivo.";
    } else {
        try {
            // Insertar en la tabla propiedades (estado siempre 'Disponible' al crear)
            $stmt = $pdo->prepare("INSERT INTO propiedades (id_propietario, direccion, tipo, estado, precio, descripcion, zona, created_at, updated_at) VALUES (:id_propietario, :direccion, :tipo, 'Disponible', :precio, :descripcion, :zona, NOW(), NOW())");
            $stmt->execute([
                'id_propietario' => $id_propietario,
                'direccion' => htmlspecialchars($direccion),
                'tipo' => htmlspecialchars($tipo),
                'precio' => $precio,
                'descripcion' => htmlspecialchars($descripcion),
                'zona' => htmlspecialchars($zona)
            ]);

            $propiedad_id = $pdo->lastInsertId();

            // Procesar imágenes
            if (isset($_FILES['imagenes']) && !empty($_FILES['imagenes']['tmp_name'][0])) {
                $uploadDir = '../../../public/assets/img/propiedades/';
                // Asegurarse de que el directorio exista
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
                $maxSize = 5 * 1024 * 1024; // 5MB

                foreach ($_FILES['imagenes']['tmp_name'] as $key => $tmp_name) {
                    if ($_FILES['imagenes']['error'][$key] === UPLOAD_ERR_OK) {
                        $fileType = mime_content_type($tmp_name);
                        if (in_array($fileType, $allowedTypes) && $_FILES['imagenes']['size'][$key] <= $maxSize) {
                            $fileName = uniqid() . '_' . basename($_FILES['imagenes']['name'][$key]);
                            $targetFile = $uploadDir . $fileName;
                            if (move_uploaded_file($tmp_name, $targetFile)) {
                                $orden = $key + 1;
                                $stmt = $pdo->prepare("INSERT INTO imagenes_propiedad (id_propiedad, url_imagen, descripcion, orden) VALUES (:id_propiedad, :url_imagen, :descripcion, :orden)");
                                $stmt->execute([
                                    'id_propiedad' => $propiedad_id,
                                    'url_imagen' => 'assets/img/propiedades/' . $fileName,
                                    'descripcion' => 'Imagen ' . $orden,
                                    'orden' => $orden
                                ]);
                            } else {
                                $error .= " Error al mover la imagen: " . $_FILES['imagenes']['name'][$key];
                            }
                        } else {
                            $error .= " Imagen no válida o demasiado grande: " . $_FILES['imagenes']['name'][$key];
                        }
                    }
                }
            }

            if (empty($error)) {
                $success = "Propiedad creada correctamente.";
            }
        } catch (PDOException $e) {
            $error = "Error al crear la propiedad: " . $e->getMessage();
        }
    }
}

// Procesar la edición de una propiedad
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['editar']) && empty($error)) {
    $propiedad_id = $_POST['propiedad_id'];
    $direccion = trim($_POST['direccion'] ?? '');
    $tipo = trim($_POST['tipo'] ?? '');
    $precio = trim($_POST['precio'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $zona = trim($_POST['zona'] ?? '');

    // Validaciones
    if (empty($direccion) || empty($tipo) || empty($precio) || empty($descripcion) || empty($zona)) {
        $error = "Por favor, completa todos los campos obligatorios.";
    } elseif (!is_numeric($precio) || $precio <= 0) {
        $error = "El precio debe ser un número positivo.";
    } else {
        try {
            // Actualizar la propiedad (estado no se edita aquí, se gestionará con contratos)
            $stmt = $pdo->prepare("UPDATE propiedades SET direccion = :direccion, tipo = :tipo, precio = :precio, descripcion = :descripcion, zona = :zona, updated_at = NOW() WHERE id = :propiedad_id AND id_propietario = :id_propietario");
            $stmt->execute([
                'direccion' => htmlspecialchars($direccion),
                'tipo' => htmlspecialchars($tipo),
                'precio' => $precio,
                'descripcion' => htmlspecialchars($descripcion),
                'zona' => htmlspecialchars($zona),
                'propiedad_id' => $propiedad_id,
                'id_propietario' => $id_propietario
            ]);

            // Procesar nuevas imágenes (si se subieron)
            if (isset($_FILES['imagenes']) && !empty($_FILES['imagenes']['tmp_name'][0])) {
                $uploadDir = '../../../public/assets/img/propiedades/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
                $maxSize = 5 * 1024 * 1024; // 5MB

                foreach ($_FILES['imagenes']['tmp_name'] as $key => $tmp_name) {
                    if ($_FILES['imagenes']['error'][$key] === UPLOAD_ERR_OK) {
                        $fileType = mime_content_type($tmp_name);
                        if (in_array($fileType, $allowedTypes) && $_FILES['imagenes']['size'][$key] <= $maxSize) {
                            $fileName = uniqid() . '_' . basename($_FILES['imagenes']['name'][$key]);
                            $targetFile = $uploadDir . $fileName;
                            if (move_uploaded_file($tmp_name, $targetFile)) {
                                $orden = $pdo->query("SELECT COALESCE(MAX(orden), 0) + 1 FROM imagenes_propiedad WHERE id_propiedad = $propiedad_id")->fetchColumn();
                                $stmt = $pdo->prepare("INSERT INTO imagenes_propiedad (id_propiedad, url_imagen, descripcion, orden) VALUES (:id_propiedad, :url_imagen, :descripcion, :orden)");
                                $stmt->execute([
                                    'id_propiedad' => $propiedad_id,
                                    'url_imagen' => 'assets/img/propiedades/' . $fileName,
                                    'descripcion' => 'Imagen ' . $orden,
                                    'orden' => $orden
                                ]);
                            } else {
                                $error .= " Error al mover la imagen: " . $_FILES['imagenes']['name'][$key];
                            }
                        } else {
                            $error .= " Imagen no válida o demasiado grande: " . $_FILES['imagenes']['name'][$key];
                        }
                    }
                }
            }

            if (empty($error)) {
                $success = "Propiedad actualizada correctamente.";
            }
        } catch (PDOException $e) {
            $error = "Error al actualizar la propiedad: " . $e->getMessage();
        }
    }
}

// Procesar la eliminación de una propiedad
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['eliminar']) && empty($error)) {
    $propiedad_id = $_POST['propiedad_id'];
    try {
        // Eliminar imágenes asociadas
        $stmt = $pdo->prepare("SELECT url_imagen FROM imagenes_propiedad WHERE id_propiedad = :propiedad_id");
        $stmt->execute(['propiedad_id' => $propiedad_id]);
        $imagenes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($imagenes as $imagen) {
            $filePath = '../../../public/' . $imagen['url_imagen'];
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }

        $stmt = $pdo->prepare("DELETE FROM imagenes_propiedad WHERE id_propiedad = :propiedad_id");
        $stmt->execute(['propiedad_id' => $propiedad_id]);

        // Eliminar la propiedad
        $stmt = $pdo->prepare("DELETE FROM propiedades WHERE id = :propiedad_id AND id_propietario = :id_propietario");
        $stmt->execute(['propiedad_id' => $propiedad_id, 'id_propietario' => $id_propietario]);

        $success = "Propiedad eliminada correctamente.";
    } catch (PDOException $e) {
        $error = "Error al eliminar la propiedad: " . $e->getMessage();
    }
}

// Obtener la lista de propiedades
if (empty($error)) {
    try {
        $stmt = $pdo->prepare("SELECT p.id, p.direccion, p.tipo, p.estado, p.precio, p.descripcion, p.zona, i.url_imagen FROM propiedades p LEFT JOIN imagenes_propiedad i ON p.id = i.id_propiedad AND i.orden = 1 WHERE p.id_propietario = :id_propietario");
        $stmt->execute(['id_propietario' => $id_propietario]);
        $propiedades = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $error = "Error al cargar las propiedades: " . $e->getMessage();
    }
}
?>

<?php require '../../layouts/container/propietario/headerPropietario.php'; ?>

<div class="container mt-5">
    <!-- Formulario para crear propiedad -->
    <div class="row justify-content-center mb-5">
        <div class="col-md-6">
            <div class="card" style="background-color: #2c2c2c; border-radius: 15px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);">
                <div class="card-body text-white">
                    <h2 class="text-center mb-4">Crear Propiedad</h2>
                    <form method="POST" action="propiedades.php" enctype="multipart/form-data">
                        <input type="hidden" name="crear" value="1">
                        <!-- Dirección -->
                        <div class="mb-3">
                            <label for="direccion" class="form-label">Dirección</label>
                            <input type="text" id="direccion" name="direccion" class="form-control" required>
                        </div>
                        <!-- Tipo -->
                        <div class="mb-3">
                            <label for="tipo" class="form-label">Tipo</label>
                            <select id="tipo" name="tipo" class="form-select" required>
                                <option value="Apartamento">Apartamento</option>
                                <option value="Casa">Casa</option>
                                <option value="Local">Local</option>
                                <option value="Otro">Otro</option>
                            </select>
                        </div>
                        <!-- Precio -->
                        <div class="mb-3">
                            <label for="precio" class="form-label">Precio</label>
                            <input type="number" id="precio" name="precio" class="form-control" step="0.01" required>
                        </div>
                        <!-- Descripción -->
                        <div class="mb-3">
                            <label for="descripcion" class="form-label">Descripción</label>
                            <textarea id="descripcion" name="descripcion" class="form-control" rows="3" required></textarea>
                        </div>
                        <!-- Zona -->
                        <div class="mb-3">
                            <label for="zona" class="form-label">Zona</label>
                            <select id="zona" name="zona" class="form-select" required>
                                <option value="Norte">Norte</option>
                                <option value="Centro">Centro</option>
                                <option value="Sur">Sur</option>
                                <option value="Este">Este</option>
                                <option value="Oeste">Oeste</option>
                            </select>
                        </div>
                        <!-- Imágenes -->
                        <div class="mb-3">
                            <label for="imagenes" class="form-label">Imágenes (máximo 5MB cada una)</label>
                            <input type="file" id="imagenes" name="imagenes[]" class="form-control" multiple accept="image/jpeg,image/png,image/jpg">
                        </div>
                        <!-- Botón de enviar -->
                        <button type="submit" class="btn btn-primary w-100">Crear Propiedad</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de propiedades -->
    <div class="row">
        <div class="col-12">
            <div class="card" style="background-color: #2c2c2c; border-radius: 15px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);">
                <div class="card-body text-white">
                    <h2 class="text-center mb-4">Lista de Propiedades</h2>
                    <?php if (!empty($propiedades)): ?>
                        <table class="table table-dark table-hover">
                            <thead>
                                <tr>
                                    <th>Dirección</th>
                                    <th>Tipo</th>
                                    <th>Estado</th>
                                    <th>Precio</th>
                                    <th>Imagen</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($propiedades as $propiedad): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($propiedad['direccion']); ?></td>
                                        <td><?php echo htmlspecialchars($propiedad['tipo']); ?></td>
                                        <td><?php echo htmlspecialchars($propiedad['estado']); ?></td>
                                        <td><?php echo number_format($propiedad['precio'], 2); ?></td>
                                        <td>
                                            <?php if ($propiedad['url_imagen']): ?>
                                                <img src="<?php echo '../../../public/' . htmlspecialchars($propiedad['url_imagen']); ?>" alt="Imagen" style="width: 100px; height: auto;">
                                            <?php else: ?>
                                                Sin imagen
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <!-- Botón Detalles -->
                                            <button type="button" class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#detailModal<?php echo $propiedad['id']; ?>">Detalles</button>
                                            <!-- Botón Editar -->
                                            <button type="button" class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editModal<?php echo $propiedad['id']; ?>">Editar</button>
                                            <!-- Botón Eliminar -->
                                            <form method="POST" action="propiedades.php" style="display:inline;" id="deleteForm<?php echo $propiedad['id']; ?>">
                                                <input type="hidden" name="eliminar" value="1">
                                                <input type="hidden" name="propiedad_id" value="<?php echo $propiedad['id']; ?>">
                                                <button type="button" class="btn btn-danger btn-sm" onclick="confirmDelete(<?php echo $propiedad['id']; ?>)">Eliminar</button>
                                            </form>
                                        </td>
                                    </tr>

                                    <!-- Modal para Detalles -->
                                    <div class="modal fade" id="detailModal<?php echo $propiedad['id']; ?>" tabindex="-1" aria-labelledby="detailModalLabel" aria-hidden="true">
                                        <div class="modal-dialog modal-lg">
                                            <div class="modal-content" style="background-color: #2c2c2c; color: white;">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="detailModalLabel">Detalles de la Propiedad</h5>
                                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <p><strong>Dirección:</strong> <?php echo htmlspecialchars($propiedad['direccion']); ?></p>
                                                    <p><strong>Tipo:</strong> <?php echo htmlspecialchars($propiedad['tipo']); ?></p>
                                                    <p><strong>Estado:</strong> <?php echo htmlspecialchars($propiedad['estado']); ?></p>
                                                    <p><strong>Precio:</strong> <?php echo number_format($propiedad['precio'], 2); ?></p>
                                                    <p><strong>Descripción:</strong> <?php echo htmlspecialchars($propiedad['descripcion']); ?></p>
                                                    <p><strong>Zona:</strong> <?php echo htmlspecialchars($propiedad['zona']); ?></p>
                                                    <p><strong>Imágenes:</strong></p>
                                                    <?php
                                                    $stmt = $pdo->prepare("SELECT url_imagen, descripcion FROM imagenes_propiedad WHERE id_propiedad = :propiedad_id ORDER BY orden");
                                                    $stmt->execute(['propiedad_id' => $propiedad['id']]);
                                                    $imagenes = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                                    foreach ($imagenes as $imagen) {
                                                        echo "<img src='../../../public/" . htmlspecialchars($imagen['url_imagen']) . "' alt='" . htmlspecialchars($imagen['descripcion']) . "' style='width: 200px; height: auto; margin-right: 10px;'>";
                                                    }
                                                    ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Modal para Editar -->
                                    <div class="modal fade" id="editModal<?php echo $propiedad['id']; ?>" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
                                        <div class="modal-dialog modal-lg">
                                            <div class="modal-content" style="background-color: #2c2c2c; color: white;">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="editModalLabel">Editar Propiedad</h5>
                                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <form method="POST" action="propiedades.php" enctype="multipart/form-data">
                                                        <input type="hidden" name="editar" value="1">
                                                        <input type="hidden" name="propiedad_id" value="<?php echo $propiedad['id']; ?>">
                                                        <!-- Dirección -->
                                                        <div class="mb-3">
                                                            <label for="direccion_<?php echo $propiedad['id']; ?>" class="form-label">Dirección</label>
                                                            <input type="text" id="direccion_<?php echo $propiedad['id']; ?>" name="direccion" class="form-control" value="<?php echo htmlspecialchars($propiedad['direccion']); ?>" required>
                                                        </div>
                                                        <!-- Tipo -->
                                                        <div class="mb-3">
                                                            <label for="tipo_<?php echo $propiedad['id']; ?>" class="form-label">Tipo</label>
                                                            <select id="tipo_<?php echo $propiedad['id']; ?>" name="tipo" class="form-select" required>
                                                                <option value="Apartamento" <?php echo $propiedad['tipo'] === 'Apartamento' ? 'selected' : ''; ?>>Apartamento</option>
                                                                <option value="Casa" <?php echo $propiedad['tipo'] === 'Casa' ? 'selected' : ''; ?>>Casa</option>
                                                                <option value="Local" <?php echo $propiedad['tipo'] === 'Local' ? 'selected' : ''; ?>>Local</option>
                                                                <option value="Otro" <?php echo $propiedad['tipo'] === 'Otro' ? 'selected' : ''; ?>>Otro</option>
                                                            </select>
                                                        </div>
                                                        <!-- Precio -->
                                                        <div class="mb-3">
                                                            <label for="precio_<?php echo $propiedad['id']; ?>" class="form-label">Precio</label>
                                                            <input type="number" id="precio_<?php echo $propiedad['id']; ?>" name="precio" class="form-control" value="<?php echo $propiedad['precio']; ?>" step="0.01" required>
                                                        </div>
                                                        <!-- Descripción -->
                                                        <div class="mb-3">
                                                            <label for="descripcion_<?php echo $propiedad['id']; ?>" class="form-label">Descripción</label>
                                                            <textarea id="descripcion_<?php echo $propiedad['id']; ?>" name="descripcion" class="form-control" rows="3" required><?php echo htmlspecialchars($propiedad['descripcion']); ?></textarea>
                                                        </div>
                                                        <!-- Zona -->
                                                        <div class="mb-3">
                                                            <label for="zona_<?php echo $propiedad['id']; ?>" class="form-label">Zona</label>
                                                            <select id="zona_<?php echo $propiedad['id']; ?>" name="zona" class="form-select" required>
                                                                <option value="Norte" <?php echo $propiedad['zona'] === 'Norte' ? 'selected' : ''; ?>>Norte</option>
                                                                <option value="Centro" <?php echo $propiedad['zona'] === 'Centro' ? 'selected' : ''; ?>>Centro</option>
                                                                <option value="Sur" <?php echo $propiedad['zona'] === 'Sur' ? 'selected' : ''; ?>>Sur</option>
                                                                <option value="Este" <?php echo $propiedad['zona'] === 'Este' ? 'selected' : ''; ?>>Este</option>
                                                                <option value="Oeste" <?php echo $propiedad['zona'] === 'Oeste' ? 'selected' : ''; ?>>Oeste</option>
                                                            </select>
                                                        </div>
                                                        <!-- Imágenes -->
                                                        <div class="mb-3">
                                                            <label for="imagenes_<?php echo $propiedad['id']; ?>" class="form-label">Nuevas Imágenes (máximo 5MB cada una)</label>
                                                            <input type="file" id="imagenes_<?php echo $propiedad['id']; ?>" name="imagenes[]" class="form-control" multiple accept="image/jpeg,image/png,image/jpg">
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
                        <p class="text-center">No hay propiedades registradas.</p>
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
        text: 'Esta acción eliminará la propiedad y sus imágenes permanentemente.',
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
</script>

<?php require '../../layouts/container/propietario/footerPropietario.php'; ?>