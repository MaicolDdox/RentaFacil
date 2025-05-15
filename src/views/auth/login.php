<?php include '../layouts/Auth/headerAuth.php' ?>
<!-- Primera Sections -->
<!-- Gif -->
<div class="col-md-8 col-lg-7 col-xl-6">
  <img src=".../../../../../public/assets/img/login.gif" class="img-fluid" alt="Sample image" />
</div>
<!-- Segunda Sections -->
<!-- Header -->
<div class="col-md-7 col-lg-5 col-xl-5 offset-xl-1 ">
  <div class="header">
    <img class="imgLogoLoguin" src=".../../../../../public/assets/img/logo.png" alt="">
    <h1 class="text-center title">Renta Facil</h1>
    <hr>
  </div>
  <!-- Formulario de logueo-->
  <form>

    <!-- titulo -->
    <div>
      <h2 class="text-center title-text">Inicie sesión con el correo electrónico</h2>
    </div>

    <!-- Email input -->
    <div data-mdb-input-init class="form-outline mb-4">
      <input type="email" id="form1Example13" class="form-control form-control-lg" />
      <label class="form-label text" for="form1Example13">Correo Electronico</label>
    </div>

    <!-- Password input -->
    <div data-mdb-input-init class="form-outline mb-4">
      <input type="password" id="form1Example23" class="form-control form-control-lg" />
      <label class="form-label text" for="form1Example23">Contraseña</label>
    </div>

    <!-- Checkbox -->
    <div class="d-flex justify-content-around align-items-center mb-4">
      <div class="form-check">
        <input class="form-check-input" type="checkbox" value="" id="form1Example3" checked />
        <label class="form-check-label text" for="form1Example3">Recordar</label>
      </div>
      <a class="text" href="#!">Olvido La Contraseña?</a>
      <a href="./register.php" style="color:rgb(50, 125, 238);">¿No tienes cuenta? Registrarse</a>
    </div>

    <!-- Submit button -->
    <button type="submit" data-mdb-button-init data-mdb-ripple-init class="btn btn-light btn-lg btn-block">
      Iniciar sección</button>

      <!-- volver a la pagina principal -->
      <a href="./../../../public/index.php" class="btn btn-danger btn-lg btn-block">Volver</a>
  </form>
</div>
<?php include '../layouts/Auth/footerAuth.php' ?>