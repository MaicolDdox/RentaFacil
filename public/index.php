<?php
session_start();
include '../src/views/layouts/Main/headerIndex.php';
?>

<section class="hero-banner d-flex align-items-center text-center">
    <div class="container text-white">
        <h1 class="mb-4 display-5 fw-bold">Encuentra tu espacio ideal o arriéndalo fácilmente con RentaFácil.</h1>

        <!-- Contenedor de búsqueda -->
        <div class="search-box p-3 shadow-lg mx-auto">
            <div class="d-flex flex-wrap justify-content-center gap-2">
                <form action="../src/views/container/visitante/filtro.php" method="GET" class="w-100">
                    <div class="d-flex gap-2">
                        <select name="zona" class="form-control zona-busqueda" required>
                            <option value="">Selecciona una zona</option>
                            <option value="Norte">Norte</option>
                            <option value="Centro">Centro</option>
                            <option value="Sur">Sur</option>
                            <option value="Este">Este</option>
                            <option value="Oeste">Oeste</option>
                        </select>
                        <button type="submit" class="btn btn-dark">Buscar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>

<section class="about-section">
    <h2>¿Qué somos?</h2>
    <p>
        RentaFácil es una plataforma web que permite a propietarios publicar
        y administrar sus propiedades en alquiler, y a visitantes postularse
        fácilmente a las que les interesen.
    </p>
    <hr>
    <div class="a-iniciar-seccion" style="align-items: center; text-align: center;">
        <div class="ms-auto">
            <p>¿Tienes cuenta?</p>
            <a href="../src/views/auth/login.php"><strong>Inicia Sesión</strong></a>
        </div>
    </div>
</section>

<section class="info-grid my-5">
    <div class="container">
        <div class="row g-0">
            <!-- Imagen 1 -->
            <div class="col-md-6">
                <img src="./assets/img/1.png" alt="Propietario" class="img-fluid h-100 w-100 object-fit-cover">
            </div>

            <!-- Texto propietario -->
            <div class="col-md-6 d-flex align-items-center bg-dark text-white p-4">
                <div class="text-center w-100">
                    <h3 class="mb-3">Propietario</h3>
                    <p>Publica tus propiedades fácilmente y encuentra arrendatarios confiables. RentaFácil te brinda visibilidad y control total.</p>
                </div>
            </div>

            <!-- Texto arrendatario -->
            <div class="col-md-6 d-flex align-items-center bg-dark text-white p-4">
                <div class="text-center w-100">
                    <h3 class="mb-3">Arrendatario</h3>
                    <p>Explora una variedad de opciones en diferentes zonas y postúlate fácilmente para alquilar tu próximo hogar ideal.</p>
                </div>
            </div>

            <!-- Imagen 2 -->
            <div class="col-md-6">
                <img src="./assets/img/2.png" alt="Arrendatario" class="img-fluid h-100 w-100 object-fit-cover">
            </div>
        </div>
    </div>
</section>

<?php include '../src/views/layouts/Main/footerIndex.php'; ?>