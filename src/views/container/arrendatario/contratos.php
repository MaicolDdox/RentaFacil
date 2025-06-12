<?php
// Iniciar la sesión
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


require '../../../config/config.php';

// Verificar si el usuario ha iniciado sesión y es arrendatario
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'arrendatario') {
    header("Location: ../../auth/login.php");   
    exit;
}

// Obtener datos del arrendatario
$nombre_usuario = $_SESSION['nombre'];
$id_arrendatario = $_SESSION['user_id'];

// Manejar la descarga del PDF
if (isset($_GET['descargar_pdf'])) {
    $archivo_pdf = 'ContratoArrendamiento.pdf';
    $ruta_pdf = '../../../public/assets/pdf/PlantillaContrato/' . $archivo_pdf;

    if (file_exists($ruta_pdf)) {
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $archivo_pdf . '"');
        header('Content-Length: ' . filesize($ruta_pdf));
        readfile($ruta_pdf);
        exit;
    } else {
        die('El archivo no se encontró.');
    }
}

// Manejar la importación del PDF
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['contrato_pdf'])) {
    // Validar archivo
    $allowed_types = ['application/pdf'];
    if (!in_array($_FILES['contrato_pdf']['type'], $allowed_types)) {
        $error = 'Solo se permiten archivos PDF.';
    } elseif ($_FILES['contrato_pdf']['size'] > 5000000) { // 5MB límite
        $error = 'El archivo excede el tamaño máximo de 5MB.';
    }

    if (empty($error)) {
        // Generar nombre único y mover archivo
        $archivo_nombre = 'contrato_' . $id_arrendatario . '_' . date('YmdHis') . '.pdf';
        $ruta_destino = '../../../public/assets/pdf/contratos/' . $archivo_nombre;

        if (move_uploaded_file($_FILES['contrato_pdf']['tmp_name'], $ruta_destino)) {
            try {
                $pdo->beginTransaction();

                // Obtener id_propietario y id_propiedad del arrendatario
                $stmt = $pdo->prepare("SELECT id, id_propietario, id_propiedad FROM arrendatarios WHERE id_usuario = :id_usuario");
                $stmt->execute(['id_usuario' => $id_arrendatario]);
                $arrendatario = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($arrendatario) {
                    $stmt = $pdo->prepare("INSERT INTO contratos_enviados (id_arrendatario, id_propietario, id_propiedad, archivo_pdf, fecha_envio) VALUES (:id_arrendatario, :id_propietario, :id_propiedad, :archivo_pdf, NOW())");
                    $stmt->execute([
                        'id_arrendatario' => $arrendatario['id'], // Usar el id de arrendatarios
                        'id_propietario' => $arrendatario['id_propietario'],
                        'id_propiedad' => $arrendatario['id_propiedad'],
                        'archivo_pdf' => $ruta_destino
                    ]);

                    $pdo->commit();
                    $success = 'Contrato enviado correctamente. El propietario será notificado.';
                } else {
                    $error = 'No se encontró una relación con un propietario o propiedad. Por favor, contacte al administrador para asociar su cuenta.';
                    unlink($ruta_destino); // Eliminar archivo si falla
                }
            } catch (PDOException $e) {
                $pdo->rollBack();
                $error = 'Error al guardar el contrato: ' . $e->getMessage();
                unlink($ruta_destino); // Eliminar archivo si falla
            }
        } else {
            $error = 'Error al subir el archivo.';
        }
    }
}
?>


<a href=""></a>
<div class="container mt-5">
    <div class="card" style="background-color: #2c2c2c; border-radius: 15px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);">
        <div class="card-body text-white">
            <h2 class="text-center mb-4">
                <i class="fa-solid fa-file-contract me-2"></i>Contratos
            </h2>
            <div class="text-center mb-4">
                <a href="contratos.php?descargar_pdf=1" class="btn btn-primary">
                    <i class="fa-solid fa-download me-2"></i>Descargar Contrato en Blanco
                </a>
            </div>
            <!-- Formulario de importación -->
            <div class="mt-4" id="importarContrato">
                <h3>
                    <i class="fa-solid fa-file-import me-2"></i>Importar Contrato Llenado
                </h3>
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger" role="alert"><?php echo $error; ?></div>
                <?php elseif (!empty($success)): ?>
                    <div class="alert alert-success" role="alert"><?php echo $success; ?></div>
                <?php endif; ?>
                <form method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="contrato_pdf" class="form-label">
                            <i class="fa-solid fa-file-pdf me-2"></i>Seleccionar PDF
                        </label>
                        <input type="file" class="form-control" id="contrato_pdf" name="contrato_pdf" accept=".pdf" required>
                    </div>
                    <button type="submit" class="btn btn-success">
                        <i class="fa-solid fa-paper-plane me-2"></i>Enviar Contrato
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

