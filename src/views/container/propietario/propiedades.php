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
    $precio = trim($_POST['precio'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $zona = trim($_POST['zona'] ?? '');

    // Validaciones
    if (empty($direccion) || empty($precio) || empty($descripcion) || empty($zona)) {
        $error = "Por favor, completa todos los campos obligatorios.";
    } elseif (!is_numeric($precio) || $precio <= 0) {
        $error = "El precio debe ser un número positivo.";
    } else {
        try {
            // Insertar en la tabla propiedades (estado siempre 'Disponible' al crear)
            $stmt = $pdo->prepare("INSERT INTO propiedades (id_propietario, direccion, estado, precio, descripcion, zona, created_at, updated_at) VALUES (:id_propietario, :direccion, 'Disponible', :precio, :descripcion, :zona, NOW(), NOW())");
            $stmt->execute([
                'id_propietario' => $id_propietario,
                'direccion' => htmlspecialchars($direccion),
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
    $precio = trim($_POST['precio'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $zona = trim($_POST['zona'] ?? '');

    // Validaciones
    if (empty($direccion) || empty($precio) || empty($descripcion) || empty($zona)) {
        $error = "Por favor, completa todos los campos obligatorios.";
    } elseif (!is_numeric($precio) || $precio <= 0) {
        $error = "El precio debe ser un número positivo.";
    } else {
        try {
            // Actualizar la propiedad (estado no se edita aquí, se gestionará con contratos)
            $stmt = $pdo->prepare("UPDATE propiedades SET direccion = :direccion, precio = :precio, descripcion = :descripcion, zona = :zona, updated_at = NOW() WHERE id = :propiedad_id AND id_propietario = :id_propietario");
            $stmt->execute([
                'direccion' => htmlspecialchars($direccion),
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
        $stmt = $pdo->prepare("SELECT p.id, p.direccion, p.estado, p.precio, p.descripcion, p.zona, i.url_imagen FROM propiedades p LEFT JOIN imagenes_propiedad i ON p.id = i.id_propiedad AND i.orden = 1 WHERE p.id_propietario = :id_propietario");
        $stmt->execute(['id_propietario' => $id_propietario]);
        $propiedades = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $error = "Error al cargar las propiedades: " . $e->getMessage();
    }
}
?>

<div class="row">
    <div class="col-12">
        <div class="card" style="background-color: #2c2c2c; border-radius: 15px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);">
            <div class="card-body text-white">
                <h2 class="text-center mb-4">Propiedades</h2>
            </div>
        </div>
    </div>
    <div class="container mt-5">


        <!-- Modal para crear propiedad -->
        <div class="modal fade" id="crearPropiedadModal" tabindex="-1" aria-labelledby="crearPropiedadModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div style="background-color: #232323; color:#fff; border-radius: 15px; box-shadow: 0 4px 16px rgba(0, 0, 0, 0.3);" class="modal-content">
                    <form method="POST" action="dashboardPropietario.php?page=propiedades" enctype="multipart/form-data">
                        <input type="hidden" name="crear" value="1">
                        <div class="modal-header">
                            <h5 class="modal-title" id="crearPropiedadModalLabel">Crear Propiedad</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                        </div>
                        <div class="modal-body">
                            <!-- Dirección -->
                            <div class="mb-3">
                                <label for="direccion" class="form-label">Dirección</label>
                                <input type="text" id="direccion" name="direccion" class="form-control" required>
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
                                    <option disabled selected>Selecciona un Municipio</option>
                                    <option value="Acevedo">Acevedo</option>
                                    <option value="Agrado">Agrado</option>
                                    <option value="Aipe">Aipe</option>
                                    <option value="Algeciras">Algeciras</option>
                                    <option value="Altamira">Altamira</option>
                                    <option value="Baraya">Baraya</option>
                                    <option value="Campoalegre">Campoalegre</option>
                                    <option value="Colombia">Colombia</option>
                                    <option value="Elías">Elías</option>
                                    <option value="Garzón">Garzón</option>
                                    <option value="Gigante">Gigante</option>
                                    <option value="Guadalupe">Guadalupe</option>
                                    <option value="Hobo">Hobo</option>
                                    <option value="Íquira">Íquira</option>
                                    <option value="Isnos">Isnos</option>
                                    <option value="LaArgentina">La Argentina</option>
                                    <option value="LaPlata">La Plata</option>
                                    <option value="Nátaga">Nátaga</option>
                                    <option value="Neiva">Neiva (capital)</option>
                                    <option value="Oporapa">Oporapa</option>
                                    <option value="Paicol">Paicol</option>
                                    <option value="Palermo">Palermo</option>
                                    <option value="Palestina">Palestina</option>
                                    <option value="Pital">Pital</option>
                                    <option value="Pitalito">Pitalito</option>
                                    <option value="Rivera">Rivera</option>
                                    <option value="Saladoblanco">Saladoblanco</option>
                                    <option value="SanAgustín">San Agustín</option>
                                    <option value="SantaMaría">Santa María</option>
                                    <option value="Suaza">Suaza</option>
                                    <option value="Tarqui">Tarqui</option>
                                    <option value="Tello">Tello</option>
                                    <option value="Teruel">Teruel</option>
                                    <option value="Tesalia">Tesalia</option>
                                    <option value="Timaná">Timaná</option>
                                    <option value="Villavieja">Villavieja</option>
                                    <option value="Yaguará">Yaguará</option>
                                </select>
                            </div>
                            <!-- Imágenes -->
                            <div class="mb-3">
                                <label for="imagenes" class="form-label">Imágenes (máximo 5MB cada una)</label>
                                <input type="file" id="imagenes" name="imagenes[]" class="form-control" multiple accept="image/jpeg,image/png,image/jpg">
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-primary">Crear Propiedad</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Tabla de propiedades -->
        <div class="row">
            <div class="col-12">
                <div class="card" style="background-color: #2c2c2c; border-radius: 15px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);">
                    <div class="card-body text-white">
                        <h2 class="text-center mb-4">Lista de Propiedades</h2>
                        <!-- Botón para abrir el modal de crear propiedad -->
                        <div class="row justify-content-center mb-4">
                            <div class="col-md-6 text-center">
                                <button type="button" class="btn btn-primary btn-lg" data-bs-toggle="modal" data-bs-target="#crearPropiedadModal">
                                    <i class="fa-solid fa-house-circle-check"></i>
                                </button>
                            </div>
                        </div>
                        <?php if (!empty($propiedades)): ?>
                            <table class="table table-dark table-hover">
                                <thead>
                                    <tr>
                                        <th>Dirección</th>
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
                                                <button type="button" class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#detailModal<?php echo $propiedad['id']; ?>">
                                                    <i class="fa-solid fa-house"></i>
                                                </button>
                                                <!-- Botón Editar -->
                                                <button type="button" class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editModal<?php echo $propiedad['id']; ?>">
                                                    <i class="fa-solid fa-house-circle-exclamation"></i>
                                                </button>
                                                <!-- Botón Eliminar -->
                                                <form method="POST" action="dashboardPropietario.php?page=propiedades" style="display:inline;" id="deleteForm<?php echo $propiedad['id']; ?>">
                                                    <input type="hidden" name="eliminar" value="1">
                                                    <input type="hidden" name="propiedad_id" value="<?php echo $propiedad['id']; ?>">
                                                    <button type="button" class="btn btn-danger btn-sm" onclick="confirmDelete(<?php echo $propiedad['id']; ?>)">
                                                        <i class="fa-solid fa-house-circle-xmark"></i>
                                                    </button>
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
                                                        <form method="POST" action="dashboardPropietario.php?page=propiedades" enctype="multipart/form-data">
                                                            <input type="hidden" name="editar" value="1">
                                                            <input type="hidden" name="propiedad_id" value="<?php echo $propiedad['id']; ?>">
                                                            <!-- Dirección -->
                                                            <div class="mb-3">
                                                                <label for="direccion_<?php echo $propiedad['id']; ?>" class="form-label">Dirección</label>
                                                                <input type="text" id="direccion_<?php echo $propiedad['id']; ?>" name="direccion" class="form-control" value="<?php echo htmlspecialchars($propiedad['direccion']); ?>" required>
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
                                                                    <option value="Acevedo" <?php echo $propiedad['zona'] === 'Acevedo' ? 'selected' : ''; ?>>Acevedo</option>
                                                                    <option value="Agrado" <?php echo $propiedad['zona'] === 'Agrado' ? 'selected' : ''; ?>>Agrado</option>
                                                                    <option value="Aipe" <?php echo $propiedad['zona'] === 'Aipe' ? 'selected' : ''; ?>>Aipe</option>
                                                                    <option value="Algeciras" <?php echo $propiedad['zona'] === 'Algeciras' ? 'selected' : ''; ?>>Algeciras</option>
                                                                    <option value="Altamira" <?php echo $propiedad['zona'] === 'Altamira' ? 'selected' : ''; ?>>Altamira</option>
                                                                    <option value="Baraya" <?php echo $propiedad['zona'] === 'Baraya' ? 'selected' : ''; ?>>Baraya</option>
                                                                    <option value="Campoalegre" <?php echo $propiedad['zona'] === 'Campoalegre' ? 'selected' : ''; ?>>Campoalegre</option>
                                                                    <option value="Colombia" <?php echo $propiedad['zona'] === 'Colombia' ? 'selected' : ''; ?>>Colombia</option>
                                                                    <option value="Elías" <?php echo $propiedad['zona'] === 'Elías' ? 'selected' : ''; ?>>Elías</option>
                                                                    <option value="Garzón" <?php echo $propiedad['zona'] === 'Garzón' ? 'selected' : ''; ?>>Garzón</option>
                                                                    <option value="Gigante" <?php echo $propiedad['zona'] === 'Gigante' ? 'selected' : ''; ?>>Gigante</option>
                                                                    <option value="Guadalupe" <?php echo $propiedad['zona'] === 'Guadalupe' ? 'selected' : ''; ?>>Guadalupe</option>
                                                                    <option value="Hobo" <?php echo $propiedad['zona'] === 'Hobo' ? 'selected' : ''; ?>>Hobo</option>
                                                                    <option value="Íquira" <?php echo $propiedad['zona'] === 'Íquira' ? 'selected' : ''; ?>>Íquira</option>
                                                                    <option value="Isnos" <?php echo $propiedad['zona'] === 'Isnos' ? 'selected' : ''; ?>>Isnos</option>
                                                                    <option value="LaArgentina" <?php echo $propiedad['zona'] === 'LaArgentina' ? 'selected' : ''; ?>>La Argentina</option>
                                                                    <option value="LaPlata" <?php echo $propiedad['zona'] === 'LaPlata' ? 'selected' : ''; ?>>La Plata</option>
                                                                    <option value="Nátaga" <?php echo $propiedad['zona'] === 'Nátaga' ? 'selected' : ''; ?>>Nátaga</option>
                                                                    <option value="Neiva" <?php echo $propiedad['zona'] === 'Neiva' ? 'selected' : ''; ?>>Neiva (capital)</option>
                                                                    <option value="Oporapa" <?php echo $propiedad['zona'] === 'Oporapa' ? 'selected' : ''; ?>>Oporapa</option>
                                                                    <option value="Paicol" <?php echo $propiedad['zona'] === 'Paicol' ? 'selected' : ''; ?>>Paicol</option>
                                                                    <option value="Palermo" <?php echo $propiedad['zona'] === 'Palermo' ? 'selected' : ''; ?>>Palermo</option>
                                                                    <option value="Palestina" <?php echo $propiedad['zona'] === 'Palestina' ? 'selected' : ''; ?>>Palestina</option>
                                                                    <option value="Pital" <?php echo $propiedad['zona'] === 'Pital' ? 'selected' : ''; ?>>Pital</option>
                                                                    <option value="Pitalito" <?php echo $propiedad['zona'] === 'Pitalito' ? 'selected' : ''; ?>>Pitalito</option>
                                                                    <option value="Rivera" <?php echo $propiedad['zona'] === 'Rivera' ? 'selected' : ''; ?>>Rivera</option>
                                                                    <option value="Saladoblanco" <?php echo $propiedad['zona'] === 'Saladoblanco' ? 'selected' : ''; ?>>Saladoblanco</option>
                                                                    <option value="SanAgustín" <?php echo $propiedad['zona'] === 'SanAgustín' ? 'selected' : ''; ?>>San Agustín</option>
                                                                    <option value="SantaMaría" <?php echo $propiedad['zona'] === 'SantaMaría' ? 'selected' : ''; ?>>Santa María</option>
                                                                    <option value="Suaza" <?php echo $propiedad['zona'] === 'Suaza' ? 'selected' : ''; ?>>Suaza</option>
                                                                    <option value="Tarqui" <?php echo $propiedad['zona'] === 'Tarqui' ? 'selected' : ''; ?>>Tarqui</option>
                                                                    <option value="Tello" <?php echo $propiedad['zona'] === 'Tello' ? 'selected' : ''; ?>>Tello</option>
                                                                    <option value="Teruel" <?php echo $propiedad['zona'] === 'Teruel' ? 'selected' : ''; ?>>Teruel</option>
                                                                    <option value="Tesalia" <?php echo $propiedad['zona'] === 'Tesalia' ? 'selected' : ''; ?>>Tesalia</option>
                                                                    <option value="Timaná" <?php echo $propiedad['zona'] === 'Timaná' ? 'selected' : ''; ?>>Timaná</option>
                                                                    <option value="Villavieja" <?php echo $propiedad['zona'] === 'Villavieja' ? 'selected' : ''; ?>>Villavieja</option>
                                                                    <option value="Yaguará" <?php echo $propiedad['zona'] === 'Yaguará' ? 'selected' : ''; ?>>Yaguará</option>
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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js" integrity="sha384-4Q6Gf2aSP4eDXB8Miphtr37CMZZQ5oXLH2yaXMJ2w8e2ZtHTl7GptT4jmndRuHDT" crossorigin="anonymous"></script>
    <!-- Bootstrap Icons CDN for plus icon (optional, can remove if not needed) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">


    <!-- Mostrar SweetAlert si hay éxito o error -->
    <?php if (!empty($success)): ?>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script>
            Swal.fire({
                icon: 'success',
                title: '¡Éxito!',
                text: '<?php echo $success; ?>',
            }).then(() => {
                window.location.href = 'dashboardPropietario.php?page=propiedades'; // Redirigir a la misma página para actualizar la lista
            });
        </script>
    <?php elseif (!empty($error)): ?>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: '<?php echo $error; ?>',
            }).then(() => {
                window.location.href = 'dashboardPropietario.php?page=propiedades'; // Redirigir a la misma página para actualizar la lista
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