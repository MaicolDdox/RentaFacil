<?php
session_start();
require '../../../config/config.php';

$success = '';
$error = '';
$propiedad = null;
$zona = isset($_GET['zona']) ? trim($_GET['zona']) : '';

try {
    if (!empty($zona)) {
        $stmt = $pdo->prepare("SELECT p.id, p.direccion, p.estado, p.precio, p.descripcion, p.zona, i.url_imagen FROM propiedades p LEFT JOIN imagenes_propiedad i ON p.id = i.id_propiedad AND i.orden = 1 WHERE p.estado = 'Disponible' AND p.zona = :zona");
        $stmt->execute(['zona' => $zona]);
    } else {
        $stmt = $pdo->prepare("SELECT p.id, p.direccion, p.estado, p.precio, p.descripcion, p.zona, i.url_imagen FROM propiedades p LEFT JOIN imagenes_propiedad i ON p.id = i.id_propiedad AND i.orden = 1 WHERE p.estado = 'Disponible'");
        $stmt->execute();
    }
    $propiedades = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Error al cargar las propiedades: " . $e->getMessage();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['postularse'])) {
    $propiedad_id = $_POST['propiedad_id'];
    $nombre_postulante = trim($_POST['nombre_postulante'] ?? '');
    $correo = trim($_POST['correo'] ?? '');
    $telefono_postulante = trim($_POST['telefono_postulante'] ?? '');

    if (empty($nombre_postulante) || empty($correo) || empty($telefono_postulante)) {
        $error = "Por favor, completa todos los campos requeridos.";
    } elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $error = "Por favor, ingresa un correo electrónico válido.";
    } else {
        try {
            $stmt = $pdo->prepare("SELECT id FROM propiedades WHERE id = :propiedad_id AND estado = 'Disponible'");
            $stmt->execute(['propiedad_id' => $propiedad_id]);
            $property = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$property) {
                $error = "La propiedad no está disponible o no existe.";
            } else {
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM postulaciones WHERE correo = :correo AND id_propiedad = :propiedad_id");
                $stmt->execute(['correo' => $correo, 'propiedad_id' => $propiedad_id]);
                if ($stmt->fetchColumn() > 0) {
                    $error = "Ya has enviado una postulación para esta propiedad con este correo.";
                } else {
                    $stmt = $pdo->prepare("INSERT INTO postulaciones (id_propiedad, nombre_postulante, correo, telefono_postulante, fecha_postulacion) VALUES (:id_propiedad, :nombre_postulante, :correo, :telefono_postulante, NOW())");
                    $stmt->execute([
                        'id_propiedad' => $propiedad_id,
                        'nombre_postulante' => htmlspecialchars($nombre_postulante),
                        'correo' => htmlspecialchars($correo),
                        'telefono_postulante' => htmlspecialchars($telefono_postulante)
                    ]);
                    if ($stmt->rowCount() > 0) {
                        $success = "Postulación enviada correctamente. El propietario se pondrá en contacto contigo.";
                        $error = '';
                    } else {
                        $error = "Error al guardar la postulación.";
                    }
                }
            }
        } catch (PDOException $e) {
            $error = "Error al enviar la postulación: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css"
        rel="stylesheet" integrity="sha384-4Q6Gf2aSP4eDXB8Miphtr37CMZZQ5oXLH2yaXMJ2w8e2ZtHTl7GptT4jmndRuHDT"
        crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="icon" href=".../../../../../../public/assets/img/logoRF.png" type="image/x-icon">
    <title>Postulaciones - Renta Fácil</title>
</head>

<style>
    /* Variables CSS - Misma paleta del diseño principal */
    :root {
        --primary-color: #0f0f0f;
        --secondary-color: #1e293b;
        --accent-color: #3b82f6;
        --accent-light: #60a5fa;
        --text-light: #ffffff;
        --text-dark: #1f2937;
        --text-gray: #6b7280;
        --silver: #c0c0c0;
        --platinum: #e5e7eb;
        --steel-blue: #475569;
        --dark-gray: #374151;
        --light-gray: #f8fafc;
        --gradient-primary: linear-gradient(135deg, #1e293b 0%, #334155 100%);
        --gradient-secondary: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
        --gradient-accent: linear-gradient(135deg,rgb(97, 140, 193) 0%,rgb(184, 200, 228) 10%);
        --gradient-silver: linear-gradient(135deg, #e5e7eb 0%, #d1d5db 100%);
        --gradient-dark: linear-gradient(135deg, #111827 0%, #1f2937 100%);
        --shadow-light: 0 4px 6px rgba(0, 0, 0, 0.1);
        --shadow-medium: 0 8px 25px rgba(0, 0, 0, 0.15);
        --shadow-heavy: 0 15px 35px rgba(0, 0, 0, 0.2);
        --border-radius: 12px;
        --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    /* Reset y base */
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: "Inter", sans-serif;
        line-height: 1.6;
        color: var(--text-dark);
        overflow-x: hidden;
        background: linear-gradient(135deg, #0f0f0f 0%, #1f2937 50%, #111827 100%);
        min-height: 100vh;
        display: flex;
        flex-direction: column;
    }

    /* Navbar mejorado */
    .navbar {
        background: rgba(15, 15, 15, 0.95) !important;
        backdrop-filter: blur(10px);
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        padding: 1rem 0;
        transition: var(--transition);
    }

    .navbar.scrolled {
        background: rgba(15, 15, 15, 0.98) !important;
        box-shadow: var(--shadow-medium);
    }

    .logo {
        width: 50px;
        height: 50px;
        margin-right: 0.5rem;
        border-radius: 50%;
        transition: var(--transition);
        box-shadow: 0 0 20px rgba(59, 130, 246, 0.3);
    }

    .logo:hover {
        transform: rotate(360deg) scale(1.1);
        box-shadow: 0 0 30px rgba(59, 130, 246, 0.5);
    }

    .navbar-brand {
        font-size: 1.5rem;
        font-weight: 700;
        text-decoration: none;
        background: var(--gradient-accent);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .nav-link {
        color: var(--text-light) !important;
        font-weight: 500;
        padding: 0.5rem 1rem !important;
        border-radius: var(--border-radius);
        transition: var(--transition);
        position: relative;
        overflow: hidden;
    }

    .nav-link::before {
        content: "";
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: var(--gradient-primary);
        transition: var(--transition);
        z-index: -1;
    }

    .nav-link:hover::before {
        left: 0;
    }

    .nav-link:hover {
        color: white !important;
        transform: translateY(-2px);
    }

    .navbar-toggler {
        border: 2px solid rgba(255, 255, 255, 0.3);
        border-radius: var(--border-radius);
        padding: 0.5rem;
        transition: var(--transition);
    }

    .navbar-toggler:hover {
        border-color: var(--accent-color);
        transform: scale(1.05);
    }

    .navbar-toggler-icon {
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3e%3cpath stroke='rgba%28255, 255, 255, 1%29' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e");
    }

    .btn {
        background: var(--gradient-secondary);
        color: white;
        border: none;
        padding: 0.75rem 1.5rem;
        border-radius: var(--border-radius);
        font-weight: 600;
        text-decoration: none;
        transition: var(--transition);
        position: relative;
        overflow: hidden;
        box-shadow: 0 4px 15px rgba(59, 130, 246, 0.3);
    }

    .btn::before {
        content: "";
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: var(--gradient-accent);
        transition: var(--transition);
        z-index: -1;
    }

    .btn:hover::before {
        left: 0;
    }

    .btn:hover {
        color: white;
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(59, 130, 246, 0.4);
    }

    /* Contenedor principal */
    .main-container {
        position: relative;
        padding: 120px 0 80px;
        overflow: hidden;
        flex: 1;
    }

    .main-container::before {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        animation: float 6s ease-in-out infinite;
        z-index: 1;
    }

    @keyframes float {

        0%,
        100% {
            transform: translateY(0px);
        }

        50% {
            transform: translateY(-20px);
        }
    }

    .main-container .container {
        position: relative;
        z-index: 2;
    }

    .main-container h2 {
        font-size: clamp(2rem, 4vw, 3rem);
        font-weight: 700;
        margin-bottom: 2rem;
        background: var(--gradient-accent);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        color: var(--text-light);
    }

    /* Tarjetas de propiedades mejoradas */
    .property-card {
        background: rgba(255, 255, 255, 0.05);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 20px;
        overflow: hidden;
        transition: var(--transition);
        height: 100%;
        box-shadow: var(--shadow-light);
    }

    .property-card:hover {
        transform: translateY(-10px);
        box-shadow: var(--shadow-heavy);
        border-color: rgba(59, 130, 246, 0.3);
    }

    .property-card .card-img-top {
        height: 250px;
        object-fit: cover;
        transition: var(--transition);
    }

    .property-card:hover .card-img-top {
        transform: scale(1.05);
    }

    .property-card .card-body {
        padding: 1.5rem;
        color: var(--text-light);
    }

    .property-card .card-title {
        font-size: 1.25rem;
        font-weight: 600;
        margin-bottom: 1rem;
        color: var(--text-light);
    }

    .property-card .card-text {
        margin-bottom: 0.5rem;
        opacity: 0.9;
        color: var(--platinum);
    }

    .property-card .price-text {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--accent-light);
        margin-bottom: 1rem;
    }

    /* Botones de las tarjetas */
    .btn-info {
        background: var(--gradient-secondary);
        border: none;
        color: white;
        padding: 0.5rem 1rem;
        border-radius: var(--border-radius);
        font-weight: 600;
        transition: var(--transition);
        text-decoration: none;
        box-shadow: 0 4px 15px rgba(59, 130, 246, 0.3);
    }

    .btn-info:hover {
        background: var(--gradient-accent);
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(59, 130, 246, 0.4);
    }

    .btn-primary {
        background: var(--gradient-primary);
        border: none;
        color: white;
        padding: 0.5rem 1rem;
        border-radius: var(--border-radius);
        font-weight: 600;
        transition: var(--transition);
        text-decoration: none;
        box-shadow: 0 4px 15px rgba(30, 41, 59, 0.3);
    }

    .btn-primary:hover {
        background: var(--gradient-accent);
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(59, 130, 246, 0.4);
    }

    .btn-secondary {
        background: var(--gradient-silver);
        border: none;
        color: var(--text-dark);
        padding: 0.5rem 1rem;
        border-radius: var(--border-radius);
        font-weight: 600;
        transition: var(--transition);
        text-decoration: none;
    }

    .btn-secondary:hover {
        background: var(--gradient-primary);
        color: white;
        transform: translateY(-2px);
    }

    /* Modales mejorados */
    .modal-content {
        background: rgba(15, 15, 15, 0.95) !important;
        backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 20px;
        box-shadow: var(--shadow-heavy);
    }

    .modal-header {
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        padding: 1.5rem;
    }

    .modal-title {
        font-weight: 700;
        background: var(--gradient-accent);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .btn-close-white {
        filter: invert(1) grayscale(100%) brightness(200%);
    }

    .modal-body {
        padding: 1.5rem;
        color: var(--text-light);
    }

    .modal-body img {
        border-radius: var(--border-radius);
        box-shadow: var(--shadow-medium);
    }

    .modal-footer {
        border-top: 1px solid rgba(255, 255, 255, 0.1);
        padding: 1.5rem;
    }

    /* Formularios mejorados */
    .form-control {
        background: rgba(255, 255, 255, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.2);
        border-radius: var(--border-radius);
        color: var(--text-light);
        padding: 0.75rem 1rem;
        transition: var(--transition);
    }

    .form-control:focus {
        background: rgba(255, 255, 255, 0.15);
        border-color: var(--accent-color);
        color: var(--text-light);
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }

    .form-control::placeholder {
        color: rgba(255, 255, 255, 0.6);
    }

    .form-label {
        color: var(--text-light);
        font-weight: 500;
        margin-bottom: 0.5rem;
    }

    /* Alertas mejoradas */
    .alert {
        border-radius: var(--border-radius);
        border: none;
        font-weight: 500;
        margin-bottom: 2rem;
        backdrop-filter: blur(10px);
    }

    .alert-danger {
        background: rgba(220, 53, 69, 0.1);
        color: #ff6b6b;
        border: 1px solid rgba(220, 53, 69, 0.3);
    }

    .alert-success {
        background: rgba(40, 167, 69, 0.1);
        color: #51cf66;
        border: 1px solid rgba(40, 167, 69, 0.3);
    }

    /* Galería de imágenes en modal */
    .image-gallery {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-top: 1rem;
    }

    .image-gallery img {
        width: 120px;
        height: 80px;
        object-fit: cover;
        border-radius: 8px;
        transition: var(--transition);
        cursor: pointer;
    }

    .image-gallery img:hover {
        transform: scale(1.1);
        box-shadow: var(--shadow-medium);
    }

    /* Mensaje de no resultados */
    .no-results {
        text-align: center;
        padding: 4rem 2rem;
        color: var(--text-light);
        opacity: 0.8;
    }

    .no-results i {
        font-size: 4rem;
        color: var(--accent-light);
        margin-bottom: 1rem;
    }

    /* Footer mejorado */
    footer {
        background: var(--primary-color);
        color: var(--text-light);
        padding: 3rem 0 1.5rem;
        border-top: 1px solid rgba(255, 255, 255, 0.1);
        margin-top: auto;
    }

    .brand-name {
        font-size: 1.5rem;
        font-weight: 700;
        background: var(--gradient-accent);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        margin-bottom: 0.5rem;
    }

    .text-small {
        font-size: 0.9rem;
        opacity: 0.8;
        line-height: 1.4;
        color: var(--platinum);
    }

    .contact-info {
        opacity: 0.9;
    }

    .contact-info strong {
        color: var(--accent-light) !important;
    }

    /* Scroll to Top Button */
    .scroll-to-top {
        position: fixed;
        bottom: 30px;
        right: 30px;
        width: 50px;
        height: 50px;
        background: var(--gradient-secondary);
        color: white;
        border: none;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: var(--transition);
        opacity: 0;
        visibility: hidden;
        z-index: 1000;
        box-shadow: 0 4px 15px rgba(59, 130, 246, 0.3);
    }

    .scroll-to-top.visible {
        opacity: 1;
        visibility: visible;
    }

    .scroll-to-top:hover {
        background: var(--gradient-accent);
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(59, 130, 246, 0.4);
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .main-container {
            padding: 100px 0 60px;
        }

        .main-container h2 {
            font-size: 2rem;
        }

        .property-card .card-img-top {
            height: 200px;
        }

        footer {
            text-align: center;
        }

        .image-gallery img {
            width: 100px;
            height: 70px;
        }
    }

    /* Animaciones adicionales */
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .fade-in-up {
        animation: fadeInUp 0.6s ease-out;
    }

    /* Smooth scrolling */
    html {
        scroll-behavior: smooth;
    }
</style>

<body>
    <div class="page-wrapper d-flex flex-column min-vh-100">
        <header class="fixed-top">
            <nav class="navbar navbar-expand-lg">
                <div class="container-fluid">
                    <!-- Logo + Nombre del sitio -->
                    <div class="d-flex align-items-center" data-aos="fade-right">
                        <img src=".../../../../../../public/assets/img/logoRF.png" alt="Logo" class="logo">
                        <a class="navbar-brand text-white" href="#">Renta Fácil</a>
                    </div>

                    <!-- Botón para responsive -->
                    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                        aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                        <span class="navbar-toggler-icon"></span>
                    </button>

                    <!-- Contenido colapsable -->
                    <div class="collapse navbar-collapse" id="navbarNav">
                        <div class="container-fluid d-flex justify-content-between align-items-center w-100">

                            <!-- Menú centrado -->
                            <ul class="navbar-nav mx-auto text-center" data-aos="fade-down" data-aos-delay="200">
                                <li class="nav-item">
                                    <a class="nav-link" href=".../../../../../../public/index.php">
                                        <i class="fas fa-chart-line me-2"></i>Resumen
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href=".../../../../../../public/index.php">
                                        <i class="fas fa-user-tie me-2"></i>Propietario
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href=".../../../../../../public/index.php">
                                        <i class="fas fa-users me-2"></i>Arrendatario
                                    </a>
                                </li>
                            </ul>

                            <!-- Botón "Empezar" a la derecha -->
                            <div class="d-flex ms-auto" data-aos="fade-left" data-aos-delay="400">
                                <a href="../../Auth/register.php" class="btn">
                                    <i class="fas fa-rocket me-2"></i>Empezar
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </nav>
        </header>




        <div class="flex-grow-1 d-flex flex-column">
            <div class="container flex-grow-1 d-flex flex-clumn">
                <div class="container">


                    <div class="main-container">
                        <div class="text-center mb-5" data-aos="fade-up">
                            <h2>
                                <i class="fas fa-hand-paper me-3"></i>
                                Resultados de búsqueda<?php echo !empty($zona) ? " en $zona" : ''; ?>
                            </h2>
                            <p class="lead text-light opacity-75">
                                <?php echo count($propiedades); ?> Propiedades disponibles para postulacion
                            </p>
                        </div>
                    </div>





                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger text-center fade-in-up" data-aos="fade-up">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <?php echo $error; ?>
                        </div>
                    <?php elseif (!empty($success)): ?>
                        <div class="alert alert-success text-center fade-in-up" data-aos="fade-up">
                            <i class="fas fa-check-circle me-2"></i>
                            <?php echo $success; ?>
                        </div>
                    <?php endif; ?>

                    <div class="row g-4 justify-content-center flex-grow-1">
                        <?php if (!empty($propiedades)): ?>
                            <?php foreach ($propiedades as $index => $propiedad): ?>
                                <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="<?php echo $index * 100; ?>">
                                    <div class="card property-card">
                                        <img src="<?php echo '../../../public/' . htmlspecialchars($propiedad['url_imagen'] ?? ''); ?>"
                                            class="card-img-top"
                                            alt="Imagen de propiedad"
                                            onerror="this.src='../../../public/assets/img/default.jpg';">
                                        <div class="card-body d-flex flex-column">
                                            <h5 class="card-title">
                                                <i class="fas fa-home me-2 text-primary"></i>
                                                <?php echo htmlspecialchars($propiedad['direccion']); ?>
                                            </h5>
                                            <p class="card-text mb-1">
                                                <i class="fas fa-info-circle me-2"></i>
                                                Estado: <span class="text-success"><?php echo htmlspecialchars($propiedad['estado']); ?></span>
                                            </p>
                                            <p class="card-text mb-3">
                                                <i class="fas fa-map-marker-alt me-2"></i>
                                                Zona: <?php echo htmlspecialchars($propiedad['zona']); ?>
                                            </p>
                                            <div class="price-text">
                                                <i class="fas fa-dollar-sign me-1"></i>
                                                $<?php echo number_format($propiedad['precio'], 0, ',', '.'); ?>
                                            </div>
                                            <div class="mt-auto d-flex justify-content-between gap-2">
                                                <button type="button" class="btn btn-info btn-sm flex-fill" data-bs-toggle="modal" data-bs-target="#detalleModal<?php echo $propiedad['id']; ?>">
                                                    <i class="fas fa-eye me-1"></i> Detalles
                                                </button>
                                                <button type="button" class="btn btn-primary btn-sm flex-fill" data-bs-toggle="modal" data-bs-target="#postularModal<?php echo $propiedad['id']; ?>">
                                                    <i class="fas fa-hand-paper me-1"></i> Postularse
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Modal Detalles -->
                                <div class="modal fade" id="detalleModal<?php echo $propiedad['id']; ?>" tabindex="-1" aria-labelledby="detalleModalLabel<?php echo $propiedad['id']; ?>" aria-hidden="true">
                                    <div class="modal-dialog modal-lg modal-dialog-centered">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="detalleModalLabel<?php echo $propiedad['id']; ?>">
                                                    <i class="fas fa-info-circle me-2"></i>Detalles de la Propiedad
                                                </h5>
                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                                            </div>
                                            <div class="modal-body">
                                                <img src="<?php echo '../../../public/' . htmlspecialchars($propiedad['url_imagen'] ?? ''); ?>"
                                                    class="card-img-top mb-3"
                                                    alt="Imagen de propiedad"
                                                    style="height: 300px; object-fit: cover;"
                                                    onerror="this.src='../../../public/assets/img/default.jpg';">

                                                <h5 class="card-title mb-3">
                                                    <i class="fas fa-map-marker-alt me-2 text-primary"></i>
                                                    <?php echo htmlspecialchars($propiedad['direccion']); ?>
                                                </h5>

                                                <div class="row mb-3">
                                                    <div class="col-md-6">
                                                        <p class="card-text">
                                                            <i class="fas fa-info-circle me-2 text-info"></i>
                                                            <strong>Estado:</strong> <span class="text-success"><?php echo htmlspecialchars($propiedad['estado']); ?></span>
                                                        </p>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <p class="card-text">
                                                            <i class="fas fa-map me-2 text-warning"></i>
                                                            <strong>Zona:</strong> <?php echo htmlspecialchars($propiedad['zona']); ?>
                                                        </p>
                                                    </div>
                                                </div>

                                                <p class="card-text mb-3">
                                                    <i class="fas fa-dollar-sign me-2 text-success"></i>
                                                    <strong>Precio:</strong> <span class="text-primary fs-5">$<?php echo number_format($propiedad['precio'], 0, ',', '.'); ?></span>
                                                </p>

                                                <p class="card-text mb-3">
                                                    <i class="fas fa-file-alt me-2 text-secondary"></i>
                                                    <strong>Descripción:</strong> <?php echo htmlspecialchars($propiedad['descripcion']); ?>
                                                </p>

                                                <div class="mb-3">
                                                    <p class="mb-2">
                                                        <i class="fas fa-images me-2 text-info"></i>
                                                        <strong>Galería de imágenes:</strong>
                                                    </p>
                                                    <div class="image-gallery">
                                                        <?php
                                                        $stmtImg = $pdo->prepare("SELECT url_imagen, descripcion FROM imagenes_propiedad WHERE id_propiedad = :propiedad_id ORDER BY orden");
                                                        $stmtImg->execute(['propiedad_id' => $propiedad['id']]);
                                                        $imagenes = $stmtImg->fetchAll(PDO::FETCH_ASSOC);
                                                        foreach ($imagenes as $imagen) {
                                                            $imgSrc = '../../../public/' . htmlspecialchars($imagen['url_imagen']);
                                                            echo "<img src='$imgSrc' alt='" . htmlspecialchars($imagen['descripcion']) . "' onerror=\"this.src='../../../public/assets/img/default.jpg';\">";
                                                        }
                                                        ?>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                                    <i class="fas fa-arrow-left me-2"></i>Volver
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Modal Postularse -->
                                <div class="modal fade" id="postularModal<?php echo $propiedad['id']; ?>" tabindex="-1" aria-labelledby="postularModalLabel<?php echo $propiedad['id']; ?>" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="postularModalLabel<?php echo $propiedad['id']; ?>">
                                                    <i class="fas fa-hand-paper me-2"></i>Postularse a la Propiedad
                                                </h5>
                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="text-center mb-4">
                                                    <h5 class="card-title">
                                                        <i class="fas fa-home me-2 text-primary"></i>
                                                        <?php echo htmlspecialchars($propiedad['direccion']); ?>
                                                    </h5>
                                                    <p class="text-muted">
                                                        <i class="fas fa-dollar-sign me-1"></i>
                                                        $<?php echo number_format($propiedad['precio'], 0, ',', '.'); ?>
                                                    </p>
                                                </div>

                                                <form method="POST" action="./filtro.php<?php echo !empty($zona) ? '?zona=' . urlencode($zona) : ''; ?>">
                                                    <input type="hidden" name="postularse" value="1">
                                                    <input type="hidden" name="propiedad_id" value="<?php echo $propiedad['id']; ?>">

                                                    <div class="mb-3">
                                                        <label for="nombre_postulante_<?php echo $propiedad['id']; ?>" class="form-label">
                                                            <i class="fas fa-user me-2"></i>Nombre Completo
                                                        </label>
                                                        <input type="text" class="form-control" id="nombre_postulante_<?php echo $propiedad['id']; ?>" name="nombre_postulante" placeholder="Ingresa tu nombre completo" required>
                                                    </div>

                                                    <div class="mb-3">
                                                        <label for="correo_<?php echo $propiedad['id']; ?>" class="form-label">
                                                            <i class="fas fa-envelope me-2"></i>Correo Electrónico
                                                        </label>
                                                        <input type="email" class="form-control" id="correo_<?php echo $propiedad['id']; ?>" name="correo" placeholder="Ingresa tu correo electrónico" required>
                                                    </div>

                                                    <div class="mb-4">
                                                        <label for="telefono_postulante_<?php echo $propiedad['id']; ?>" class="form-label">
                                                            <i class="fas fa-phone me-2"></i>Número de Teléfono
                                                        </label>
                                                        <input type="tel" class="form-control" id="telefono_postulante_<?php echo $propiedad['id']; ?>" name="telefono_postulante" placeholder="Ingresa tu número de teléfono" required>
                                                    </div>

                                                    <div class="d-flex justify-content-between gap-3">
                                                        <button type="button" class="btn btn-secondary flex-fill" data-bs-dismiss="modal">
                                                            <i class="fas fa-times me-2"></i>Cancelar
                                                        </button>
                                                        <button type="submit" class="btn btn-primary flex-fill">
                                                            <i class="fas fa-paper-plane me-2"></i>Enviar Postulación
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="col-12">
                                <div class="no-results" data-aos="fade-up">
                                    <i class="fas fa-search"></i>
                                    <h3>No hay propiedades disponibles</h3>
                                    <p><?php echo !empty($zona) ? "No se encontraron propiedades en $zona" : "No hay propiedades disponibles en este momento"; ?>.</p>
                                    <a href=".../../../../../../public/index.php" class="btn mt-3">
                                        <i class="fas fa-arrow-left me-2"></i>Volver al inicio
                                    </a>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>








            </div>
        </div>

        <!-- Footer mejorado -->
        <footer data-aos="fade-up">
            <div class="container">
                <div class="row text-center text-md-start">
                    <!-- Columna 1: Marca -->
                    <div class="col-md-4 mb-3">
                        <div class="brand-name">
                            <i class="fas fa-home me-2"></i>
                            Renta Fácil
                        </div>
                        <div class="text-small">
                            Plataforma de gestión de alquileres para<br>
                            propietarios y arrendatarios
                        </div>
                    </div>

                    <!-- Columna 2: Contacto -->
                    <div class="col-md-4 mb-3 contact-info">
                        <div class="mb-2">
                            <i class="fas fa-envelope me-2"></i>
                            <strong>Contacto:</strong> rentafacil@gmail.com
                        </div>
                        <div>
                            <i class="fas fa-phone me-2"></i>
                            <strong>Teléfono:</strong> +72 0000000001
                        </div>
                    </div>

                    <!-- Columna 3: Derechos -->
                    <div class="col-md-4 d-flex align-items-center justify-content-md-end justify-content-center text-small">
                        <div>
                            <i class="fas fa-copyright me-1"></i>
                            2025 RentaFácil. Todos los <br class="d-md-none"> derechos reservados
                        </div>
                    </div>
                </div>
            </div>
        </footer>
    </div>

    <!-- Botón de scroll to top -->
    <button id="scrollToTop" class="scroll-to-top">
        <i class="fas fa-chevron-up"></i>
    </button>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-j1CDi7MgGQ12Z7Qab0qlWQ/Qqz24Gc6BM0thvEMVjHnfYGF0rmFCozFSxQBxwHKO"
        crossorigin="anonymous"></script>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-j1CDi7MgGQ12Z7Qab0qlWQ/Qqz24Gc6BM0thvEMVjHnfYGF0rmFCozFSxQBxwHKO"
        crossorigin="anonymous"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        // Inicialización cuando el DOM está listo
        document.addEventListener("DOMContentLoaded", () => {
            // Inicializar AOS (Animate On Scroll)
            AOS.init({
                duration: 1000,
                easing: "ease-in-out",
                once: true,
                offset: 100,
            });

            // Navbar scroll effect
            const navbar = document.querySelector(".navbar");
            let lastScrollTop = 0;

            window.addEventListener("scroll", () => {
                const scrollTop = window.pageYOffset || document.documentElement.scrollTop;

                if (scrollTop > 100) {
                    navbar.classList.add("scrolled");
                } else {
                    navbar.classList.remove("scrolled");
                }

                // Hide/show navbar on scroll
                if (scrollTop > lastScrollTop && scrollTop > 200) {
                    navbar.style.transform = "translateY(-100%)";
                } else {
                    navbar.style.transform = "translateY(0)";
                }
                lastScrollTop = scrollTop;
            });

            // Scroll to top button functionality
            const scrollToTopBtn = document.getElementById("scrollToTop");

            window.addEventListener("scroll", () => {
                if (window.pageYOffset > 300) {
                    scrollToTopBtn.classList.add("visible");
                } else {
                    scrollToTopBtn.classList.remove("visible");
                }
            });

            scrollToTopBtn.addEventListener("click", () => {
                window.scrollTo({
                    top: 0,
                    behavior: "smooth",
                });
            });

            // Smooth scrolling para enlaces internos
            document.querySelectorAll('a[href^="#"]').forEach((anchor) => {
                anchor.addEventListener("click", function(e) {
                    e.preventDefault();
                    const target = document.querySelector(this.getAttribute("href"));
                    if (target) {
                        const offsetTop = target.offsetTop - 80;
                        window.scrollTo({
                            top: offsetTop,
                            behavior: "smooth",
                        });
                    }
                });
            });
        });
    </script>

    <?php if ((!empty($error) || !empty($success)) && isset($_POST['propiedad_id'])): ?>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                var modalId = 'postularModal<?php echo htmlspecialchars($_POST['propiedad_id']); ?>';
                var modalElement = document.getElementById(modalId);
                if (modalElement) {
                    var modal = new bootstrap.Modal(modalElement);
                    modal.show();
                }
            });
        </script>
    <?php endif; ?>
    <?php if (!empty($success)): ?>
        <script>
            Swal.fire({
                icon: 'success',
                title: '¡Éxito!',
                text: '<?php echo $success; ?>',
                confirmButtonText: 'Aceptar',
                willClose: () => {
                    window.location.href = 'filtro.php<?php echo !empty($zona) ? "?zona=" . urlencode($zona) : ""; ?>';
                }
            });
        </script>
    <?php endif; ?>
</body>

</html>