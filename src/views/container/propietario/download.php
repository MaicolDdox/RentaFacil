<?php
// Iniciar la sesión
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Incluir la configuración de la base de datos
require '../../../config/config.php';

// Verificar si el usuario está logueado y es propietario
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'propietario') {
    header("Location: ../../auth/login.php");
    exit;
}

// Obtener el ID del propietario
$stmt = $pdo->prepare("SELECT id FROM propietarios WHERE id_usuario = :id_usuario");
$stmt->execute(['id_usuario' => $_SESSION['user_id']]);
$propietario = $stmt->fetch(PDO::FETCH_ASSOC);
$id_propietario = $propietario ? $propietario['id'] : null;



if (isset($_GET['id'])) {
    // Descarga por id de contratos_enviados (pendiente)
    $id = $_GET['id'];
    $stmt = $pdo->prepare("SELECT archivo_pdf FROM contratos_enviados WHERE id = :id AND id_propietario = :id_propietario");
    $stmt->execute(['id' => $id, 'id_propietario' => $id_propietario]);
    $pendiente = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($pendiente && file_exists($pendiente['archivo_pdf'])) {
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="contrato_pendiente_' . $id . '.pdf"');
        header('Content-Length: ' . filesize($pendiente['archivo_pdf']));
        readfile($pendiente['archivo_pdf']);
        exit;
    } else {
        die('Archivo no encontrado.');
    }
} elseif (isset($_GET['id_contrato_asociado'])) {
    // Descarga por id_contrato_asociado (contrato activo)
    $id_contrato = $_GET['id_contrato_asociado'];
    $stmt = $pdo->prepare("SELECT archivo_pdf FROM contratos_enviados WHERE id_contrato_asociado = :id_contrato AND id_propietario = :id_propietario");
    $stmt->execute(['id_contrato' => $id_contrato, 'id_propietario' => $id_propietario]);
    $contrato = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($contrato && file_exists($contrato['archivo_pdf'])) {
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="contrato_activo_' . $id_contrato . '.pdf"');
        header('Content-Length: ' . filesize($contrato['archivo_pdf']));
        readfile($contrato['archivo_pdf']);
        exit;
    } else {
        die('Archivo no encontrado.');
    }
}
?>