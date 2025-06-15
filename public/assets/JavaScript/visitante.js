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
    