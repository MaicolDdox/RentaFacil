<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Boostrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css"
        rel="stylesheet" integrity="sha384-4Q6Gf2aSP4eDXB8Miphtr37CMZZQ5oXLH2yaXMJ2w8e2ZtHTl7GptT4jmndRuHDT"
        crossorigin="anonymous">

    <!-- iconos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- logo -->
    <link rel="icon" href="../public/assets/img/logoRF.png" type="image/x-icon">
    <link rel="stylesheet" href="./assets/css/home.css">
    <title>Renta Fácil</title>
</head>


<body>
    <!-- Loading Screen -->
    <div id="loading-screen" class="loading-screen">
        <div class="loading-content">
            <div class="loading-logo">
                <i class="fas fa-home"></i>
            </div>
            <div class="loading-text">Renta Fácil</div>
            <div class="loading-spinner"></div>
        </div>
    </div>

    <header class="fixed-top">
        <nav class="navbar navbar-expand-lg navbar-custom">
            <div class="container-fluid">
                <!-- Logo + Nombre del sitio -->
                <div class="navbar-brand-container" data-aos="fade-right">
                    <div class="logo-container">
                        <i class="fas fa-home logo-icon"></i>
                    </div>
                    <a class="navbar-brand text-white fw-bold" href="#">Renta Fácil</a>
                </div>

                <!-- Botón para responsive -->
                <button class="navbar-toggler custom-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                    aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <!-- Contenido colapsable -->
                <div class="collapse navbar-collapse" id="navbarNav">
                    <div class="container-fluid d-flex justify-content-between align-items-center w-100">

                        <!-- Menú centrado -->
                        <ul class="navbar-nav mx-auto text-center" data-aos="fade-down" data-aos-delay="200">
                            <li class="nav-item">
                                <a class="nav-link nav-link-custom" href="#resumen">
                                    <i class="fas fa-chart-line me-2"></i>Resumen
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link nav-link-custom" href="#propietario">
                                    <i class="fas fa-user-tie me-2"></i>Propietario
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link nav-link-custom" href="#arrendatario">
                                    <i class="fas fa-users me-2"></i>Arrendatario
                                </a>
                            </li>
                        </ul>

                        <!-- Botón "Empezar" a la derecha -->
                        <div class="d-flex ms-auto" data-aos="fade-left" data-aos-delay="400">
                            <a href="../src/views/auth/register.php" class="btn btn-cta">
                                <i class="fas fa-rocket me-2"></i>Empezar
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </nav>
    </header>