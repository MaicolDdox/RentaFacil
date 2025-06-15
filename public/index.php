<?php
session_start();

?>

<?php include '../src/views/layouts/Main/header.php'; ?>

<section class="hero-banner d-flex align-items-center text-center" id="hero">
    <div class="hero-overlay"></div>
    <div class="hero-particles"></div>
    <div class="container text-white position-relative">
        <div class="hero-content" data-aos="fade-up" data-aos-duration="1000">
            <h1 class="hero-title mb-4">
                Encuentra tu espacio ideal o arriéndalo fácilmente
            </h1>
            <p class="hero-subtitle mb-5">
                La plataforma más confiable para conectar propietarios y arrendatarios en Colombia
            </p>

            <!-- Contenedor de búsqueda -->
            <div class="search-box p-4 shadow-lg mx-auto" data-aos="fade-up" data-aos-delay="300">
                <div class="search-header mb-3">
                    <i class="fas fa-search search-icon"></i>
                    <span class="search-title">Busca tu hogar ideal</span>
                </div>
                <form action="../src/views/container/visitante/filtro.php" method="GET" class="w-100">
                    <div class="search-form-container">
                        <div class="form-group-custom">
                            <i class="fas fa-map-marker-alt input-icon"></i>
                            <select name="zona" class="form-control zona-busqueda" required>
                                <option disabled selected>Selecciona un Municipio</option>
                                <option value="Neiva">Neiva (capital)</option>
                                <option value="Pitalito">Pitalito</option>
                                <option value="Garzón">Garzón</option>
                                <option value="LaPlata">La Plata</option>
                                <option value="Campoalegre">Campoalegre</option>
                                <option value="Rivera">Rivera</option>
                                <option value="Gigante">Gigante</option>
                                <option value="Palermo">Palermo</option>
                                <option value="Aipe">Aipe</option>
                                <option value="Tello">Tello</option>
                                <option value="Baraya">Baraya</option>
                                <option value="Villavieja">Villavieja</option>
                                <option value="Yaguará">Yaguará</option>
                                <option value="Hobo">Hobo</option>
                                <option value="Íquira">Íquira</option>
                                <option value="Teruel">Teruel</option>
                                <option value="Tesalia">Tesalia</option>
                                <option value="Paicol">Paicol</option>
                                <option value="Agrado">Agrado</option>
                                <option value="Altamira">Altamira</option>
                                <option value="Timaná">Timaná</option>
                                <option value="Pital">Pital</option>
                                <option value="SanAgustín">San Agustín</option>
                                <option value="Isnos">Isnos</option>
                                <option value="Saladoblanco">Saladoblanco</option>
                                <option value="LaArgentina">La Argentina</option>
                                <option value="Acevedo">Acevedo</option>
                                <option value="Suaza">Suaza</option>
                                <option value="Tarqui">Tarqui</option>
                                <option value="Palestina">Palestina</option>
                                <option value="Guadalupe">Guadalupe</option>
                                <option value="SantaMaría">Santa María</option>
                                <option value="Colombia">Colombia</option>
                                <option value="Elías">Elías</option>
                                <option value="Nátaga">Nátaga</option>
                                <option value="Oporapa">Oporapa</option>
                                <option value="Algeciras">Algeciras</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-search">
                            <i class="fas fa-search me-2"></i>Buscar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>

<section class="about-section" id="resumen" data-aos="fade-up">
    <div class="about-bg-pattern"></div>
    <div class="container position-relative">
        <div class="row align-items-center">
            <div class="col-lg-6" data-aos="fade-right">
                <div class="about-icon-container mb-4">
                    <i class="fas fa-question-circle about-icon"></i>
                </div>
                <h2 class="about-title">¿Qué somos?</h2>
                <p class="about-description">
                    RentaFácil es una plataforma web innovadora que permite a propietarios publicar
                    y administrar sus propiedades en alquiler de manera eficiente, mientras que los visitantes
                    pueden postularse fácilmente a las propiedades que les interesen.
                </p>
                <div class="about-features mt-4">
                    <div class="feature-item">
                        <i class="fas fa-check-circle feature-icon"></i>
                        <span>Gestión simplificada de propiedades</span>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-check-circle feature-icon"></i>
                        <span>Proceso de postulación rápido</span>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-check-circle feature-icon"></i>
                        <span>Conexión directa y segura</span>
                    </div>
                </div>
            </div>
            <div class="col-lg-6" data-aos="fade-left" data-aos-delay="200">
                <div class="about-visual">
                    <div class="about-card">
                        <i class="fas fa-home card-icon"></i>
                        <h4>Plataforma Confiable</h4>
                        <p>Conectamos personas de manera segura y eficiente</p>
                    </div>
                </div>
            </div>
        </div>

        <hr class="section-divider">

        <div class="login-section text-center" data-aos="fade-up">
            <div class="login-card">
                <i class="fas fa-user-circle login-icon"></i>
                <p class="login-question">¿Ya tienes cuenta?</p>
                <a href="../src/views/auth/login.php" class="btn btn-login">
                    <i class="fas fa-sign-in-alt me-2"></i>Inicia Sesión
                </a>
            </div>
        </div>
    </div>
</section>

<section class="info-grid my-5" id="servicios">
    <div class="container-fluid p-0">
        <div class="row g-0">
            <!-- Imagen 1 -->
            <div class="col-md-6 position-relative overflow-hidden" data-aos="fade-right">
                <div class="info-image-container">
                    <img src="../public/assets/img/1.png" alt="Propietario" class="info-image">
                    <div class="image-overlay">
                        <div class="overlay-content">
                            <i class="fas fa-user-tie overlay-icon"></i>
                            <h3>Para Propietarios</h3>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Texto propietario -->
            <div class="col-md-6 d-flex align-items-center info-text-section" id="propietario" data-aos="fade-left">
                <div class="info-content">
                    <div class="info-icon-container">
                        <i class="fas fa-building info-section-icon"></i>
                    </div>
                    <h3 class="info-title">Propietario</h3>
                    <p class="info-description">
                        Publica tus propiedades fácilmente y encuentra arrendatarios confiables.
                        RentaFácil te brinda máxima visibilidad y control total sobre tus inmuebles.
                    </p>
                    <div class="info-benefits">
                        <div class="benefit-item">
                            <i class="fas fa-chart-line benefit-icon"></i>
                            <span>Máxima visibilidad</span>
                        </div>
                        <div class="benefit-item">
                            <i class="fas fa-shield-alt benefit-icon"></i>
                            <span>Arrendatarios verificados</span>
                        </div>
                        <div class="benefit-item">
                            <i class="fas fa-cog benefit-icon"></i>
                            <span>Control total</span>
                        </div>
                    </div>
                    <a href="#" class="btn btn-info-cta">
                        <i class="fas fa-plus me-2"></i>Publicar Propiedad
                    </a>
                </div>
            </div>

            <!-- Texto arrendatario -->
            <div class="col-md-6 d-flex align-items-center info-text-section info-text-alt" id="arrendatario" data-aos="fade-right">
                <div class="info-content">
                    <div class="info-icon-container">
                        <i class="fas fa-users info-section-icon"></i>
                    </div>
                    <h3 class="info-title">Arrendatario</h3>
                    <p class="info-description">
                        Explora una amplia variedad de opciones en diferentes zonas y postúlate
                        fácilmente para alquilar tu próximo hogar ideal.
                    </p>
                    <div class="info-benefits">
                        <div class="benefit-item">
                            <i class="fas fa-search benefit-icon"></i>
                            <span>Búsqueda avanzada</span>
                        </div>
                        <div class="benefit-item">
                            <i class="fas fa-heart benefit-icon"></i>
                            <span>Favoritos y alertas</span>
                        </div>
                        <div class="benefit-item">
                            <i class="fas fa-mobile-alt benefit-icon"></i>
                            <span>Postulación rápida</span>
                        </div>
                    </div>
                    <a href="#" class="btn btn-info-cta">
                        <i class="fas fa-search me-2"></i>Buscar Propiedades
                    </a>
                </div>
            </div>

            <!-- Imagen 2 -->
            <div class="col-md-6 position-relative overflow-hidden" data-aos="fade-left">
                <div class="info-image-container">
                    <img src="../public/assets/img/2.png" alt="Arrendatario" class="info-image">
                    <div class="image-overlay">
                        <div class="overlay-content">
                            <i class="fas fa-users overlay-icon"></i>
                            <h3>Para Arrendatarios</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Nueva sección de testimonios -->
<section class="testimonials-section py-5" data-aos="fade-up">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="section-title">Lo que dicen nuestros usuarios</h2>
            <p class="section-subtitle">Experiencias reales de quienes confían en RentaFácil</p>
        </div>
        <div class="row">
            <div class="col-md-4 mb-4" data-aos="fade-up" data-aos-delay="100">
                <div class="testimonial-card">
                    <div class="testimonial-stars">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                    </div>
                    <p class="testimonial-text">
                        "Encontré mi apartamento ideal en menos de una semana. La plataforma es muy fácil de usar."
                    </p>
                    <div class="testimonial-author">
                        <div class="author-avatar">
                            <i class="fas fa-user"></i>
                        </div>
                        <div class="author-info">
                            <h5>María González</h5>
                            <span>Arrendataria</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4" data-aos="fade-up" data-aos-delay="200">
                <div class="testimonial-card">
                    <div class="testimonial-stars">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                    </div>
                    <p class="testimonial-text">
                        "Como propietario, he logrado alquilar mis propiedades más rápido que nunca."
                    </p>
                    <div class="testimonial-author">
                        <div class="author-avatar">
                            <i class="fas fa-user"></i>
                        </div>
                        <div class="author-info">
                            <h5>Carlos Rodríguez</h5>
                            <span>Propietario</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4" data-aos="fade-up" data-aos-delay="300">
                <div class="testimonial-card">
                    <div class="testimonial-stars">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                    </div>
                    <p class="testimonial-text">
                        "Excelente servicio al cliente y una plataforma muy confiable y segura."
                    </p>
                    <div class="testimonial-author">
                        <div class="author-avatar">
                            <i class="fas fa-user"></i>
                        </div>
                        <div class="author-info">
                            <h5>Ana Martínez</h5>
                            <span>Usuario</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>


<!-- Botón de scroll to top -->
<button id="scrollToTop" class="scroll-to-top">
    <i class="fas fa-chevron-up"></i>
</button>

<?php include '../src/views/layouts/Main/footer.php'; ?>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-j1CDi7MgGQ12Z7Qab0qlWQ/Qqz24Gc6BM0thvEMVjHnfYGF0rmFCozFSxQBxwHKO"
    crossorigin="anonymous"></script>
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>


<script src="./assets/JavaScript/home.js"></script>