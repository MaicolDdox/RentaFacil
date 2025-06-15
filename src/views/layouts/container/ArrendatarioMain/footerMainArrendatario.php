
</body>

</html>
<script>
    // Animación de conteo con anime.js
    anime({
        targets: '#pendientes-count',
        value: [0, <?php echo $pagosPendientes; ?>],
        round: 1,
        easing: 'easeInOutQuad',
        duration: 1000,
        update: function(anim) {
            document.getElementById('pendientes-count').textContent = Math.round(anim.animations[0].currentValue);
        }
    });
    anime({
        targets: '#atrasados-count',
        value: [0, <?php echo $pagosAtrasados; ?>],
        round: 1,
        easing: 'easeInOutQuad',
        duration: 1000,
        update: function(anim) {
            document.getElementById('atrasados-count').textContent = Math.round(anim.animations[0].currentValue);
        }
    });
    anime({
        targets: '#contratos-count',
        value: [0, <?php echo $contratosActivos; ?>],
        round: 1,
        easing: 'easeInOutQuad',
        duration: 1000,
        update: function(anim) {
            document.getElementById('contratos-count').textContent = Math.round(anim.animations[0].currentValue);
        }
    });
</script>