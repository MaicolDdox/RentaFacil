</div>
<div class="content">
    <?php include $contenido; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<script>
document.addEventListener("DOMContentLoaded", function() {
    setTimeout(() => {
        const loadingScreen = document.getElementById("loading-screen");
        if (loadingScreen) {
            loadingScreen.classList.add("hidden");
            // Opcional: quitar del DOM después de la transición
            setTimeout(() => loadingScreen.style.display = "none", 500);
        }
    }, 500);
});
</script>