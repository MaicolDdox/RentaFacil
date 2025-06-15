<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Renta Facil</title>
    <link rel="icon" href="../../../../public/assets/img/logoRF.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../../../../public/assets/css/sidebarr.css">
</head>


<body>
    <?php if (!isset($show_loading) || $show_loading): ?>
        <div id="loading-screen" class="loading-screen">
            <div class="loading-content">
                <div class="loading-logo">
                    <i class="fas fa-home"></i>
                </div>
                <div class="loading-text">Renta Fácil</div>
                <div class="loading-spinner"></div>
            </div>
        </div>
    <?php endif; ?>

    <div class="sidebar">
        <div class="logo">

            Renta Facil</div>
        <div class="user-info">
            <p>Bienvenid@, <?php echo htmlspecialchars($nombre_usuario); ?></p>
            <p>Rol: <?php echo htmlspecialchars($rol_usuario); ?></p>
        </div>