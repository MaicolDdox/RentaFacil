<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css"
        rel="stylesheet" integrity="sha384-4Q6Gf2aSP4eDXB8Miphtr37CMZZQ5oXLH2yaXMJ2w8e2ZtHTl7GptT4jmndRuHDT"
        crossorigin="anonymous">
    <link rel="icon" href=".../../../../../../public/assets/img/logoRF.png" type="image/x-icon">
    <link rel="stylesheet" href=".../../../../../../public/assets/css/iniciox.css">
    <title>Inicio</title>
</head>

<body>
    <header>
        <nav class="navbar navbar-expand-lg">
            <div class="container-fluid">
                <!-- Logo + Nombre del sitio -->
                <img src=".../../../../../../public/assets/img/logoRF.png" alt="Logo" class="logo">
                <a class="navbar-brand text-white" href="#">Renta Fácil</a>

                <!-- Botón para responsive -->
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                    aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <!-- Contenido colapsable -->
                <div class="collapse navbar-collapse" id="navbarNav">
                    <div class="container-fluid d-flex justify-content-between align-items-center w-100">

                        <!-- Menú centrado -->
                        <ul class="navbar-nav mx-auto text-center">
                            <li class="nav-item">
                                <a class="nav-link text-white" href=".../../../../../../public/index.php">Resumen</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link text-white" href=".../../../../../../public/index.php">Propietario</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link text-white" href=".../../../../../../public/index.php">Arrendatario</a>
                            </li>
                        </ul>

                        <!-- Botón "Empezar" a la derecha -->
                        <div class="d-flex ms-auto">
                            <a href="../../Auth/register.php" class="btn">Empezar</a>
                        </div>
                    </div>
                </div>
            </div>
        </nav>
    </header>