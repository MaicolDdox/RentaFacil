// Inicialización cuando el DOM está listo
document.addEventListener("DOMContentLoaded", () => {
    // Inicializar AOS (Animate On Scroll)
    AOS.init({
        duration: 1000,
        easing: "ease-in-out",
        once: true,
        offset: 100,
    })

    // Ocultar loading screen después de 2 segundos
    setTimeout(() => {
        const loadingScreen = document.getElementById("loading-screen")
        if (loadingScreen) {
            loadingScreen.classList.add("hidden")
        }
    }, 2000)

    // Navbar scroll effect
    const navbar = document.querySelector(".navbar-custom")
    let lastScrollTop = 0

    window.addEventListener("scroll", () => {
        const scrollTop = window.pageYOffset || document.documentElement.scrollTop

        if (scrollTop > 100) {
            navbar.classList.add("scrolled")
        } else {
            navbar.classList.remove("scrolled")
        }

        // Hide/show navbar on scroll
        if (scrollTop > lastScrollTop && scrollTop > 200) {
            navbar.style.transform = "translateY(-100%)"
        } else {
            navbar.style.transform = "translateY(0)"
        }
        lastScrollTop = scrollTop
    })

    // Smooth scrolling para enlaces internos
    document.querySelectorAll('a[href^="#"]').forEach((anchor) => {
        anchor.addEventListener("click", function (e) {
            e.preventDefault()
            const target = document.querySelector(this.getAttribute("href"))
            if (target) {
                const offsetTop = target.offsetTop - 80 // Ajuste para navbar fijo
                window.scrollTo({
                    top: offsetTop,
                    behavior: "smooth",
                })
            }
        })
    })

    // Counter animation para estadísticas
    const animateCounters = () => {
        const counters = document.querySelectorAll(".stat-number")

        counters.forEach((counter) => {
            const target = Number.parseInt(counter.getAttribute("data-count"))
            const increment = target / 100
            let current = 0

            const updateCounter = () => {
                if (current < target) {
                    current += increment
                    counter.textContent = Math.floor(current)
                    requestAnimationFrame(updateCounter)
                } else {
                    counter.textContent = target
                }
            }

            updateCounter()
        })
    }

    // Intersection Observer para activar contadores
    const statsSection = document.querySelector(".hero-stats")
    if (statsSection) {
        const observer = new IntersectionObserver(
            (entries) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) {
                        animateCounters()
                        observer.unobserve(entry.target)
                    }
                })
            }, {
            threshold: 0.5
        },
        )

        observer.observe(statsSection)
    }

    // Scroll to top button functionality
    const scrollToTopBtn = document.getElementById("scrollToTop")

    window.addEventListener("scroll", () => {
        if (window.pageYOffset > 300) {
            scrollToTopBtn.classList.add("visible")
        } else {
            scrollToTopBtn.classList.remove("visible")
        }
    })

    scrollToTopBtn.addEventListener("click", () => {
        window.scrollTo({
            top: 0,
            behavior: "smooth",
        })
    })

    // Parallax effect para hero section
    const heroSection = document.querySelector(".hero-banner")
    const heroParticles = document.querySelector(".hero-particles")

    window.addEventListener("scroll", () => {
        const scrolled = window.pageYOffset
        const rate = scrolled * -0.5

        if (heroParticles) {
            heroParticles.style.transform = `translateY(${rate}px)`
        }
    })

    // Form validation mejorada
    const searchForm = document.querySelector("form")
    if (searchForm) {
        searchForm.addEventListener("submit", function (e) {
            const select = this.querySelector('select[name="zona"]')
            if (!select.value || select.value === "") {
                e.preventDefault()

                // Añadir efecto de shake al select
                select.classList.add("shake")
                select.style.borderColor = "#dc3545"

                // Mostrar mensaje de error
                showNotification("Por favor selecciona un municipio", "error")

                setTimeout(() => {
                    select.classList.remove("shake")
                    select.style.borderColor = ""
                }, 500)
            }
        })
    }

    // Newsletter subscription
    const newsletterForm = document.querySelector(".newsletter-signup .input-group")
    if (newsletterForm) {
        const newsletterBtn = newsletterForm.querySelector(".btn-newsletter")
        const newsletterInput = newsletterForm.querySelector(".newsletter-input")

        newsletterBtn.addEventListener("click", (e) => {
            e.preventDefault()
            const email = newsletterInput.value.trim()

            if (validateEmail(email)) {
                // Simular suscripción exitosa
                showNotification("¡Gracias por suscribirte! Te mantendremos informado.", "success")
                newsletterInput.value = ""
            } else {
                showNotification("Por favor ingresa un email válido", "error")
            }
        })
    }

    // Typing effect para el hero title
    const heroTitle = document.querySelector(".hero-title")
    if (heroTitle) {
        const originalText = heroTitle.innerHTML
        heroTitle.innerHTML = ""

        setTimeout(() => {
            typeWriter(heroTitle, originalText, 50)
        }, 2500) // Después del loading screen
    }

    // Lazy loading para imágenes
    const images = document.querySelectorAll("img[data-src]")
    const imageObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                const img = entry.target
                img.src = img.dataset.src
                img.classList.remove("lazy")
                imageObserver.unobserve(img)
            }
        })
    })

    images.forEach((img) => imageObserver.observe(img))

    // Testimonials carousel auto-play
    initTestimonialsCarousel()

    // Easter egg - Konami code
    const konamiCode = []
    const konamiSequence = [38, 38, 40, 40, 37, 39, 37, 39, 66, 65] // ↑↑↓↓←→←→BA

    document.addEventListener("keydown", (e) => {
        konamiCode.push(e.keyCode)
        if (konamiCode.length > konamiSequence.length) {
            konamiCode.shift()
        }

        if (konamiCode.join(",") === konamiSequence.join(",")) {
            activateEasterEgg()
        }
    })
})

// Funciones auxiliares
function validateEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
    return re.test(email)
}

function showNotification(message, type = "info") {
    // Crear elemento de notificación
    const notification = document.createElement("div")
    notification.className = `notification notification-${type}`
    notification.innerHTML = `
        <div class="notification-content">
            <i class="fas fa-${type === "success" ? "check-circle" : type === "error" ? "exclamation-circle" : "info-circle"}"></i>
            <span>${message}</span>
        </div>
    `

    // Añadir estilos
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: ${type === "success" ? "#28a745" : type === "error" ? "#dc3545" : "#17a2b8"};
        color: white;
        padding: 1rem 1.5rem;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        z-index: 10000;
        transform: translateX(100%);
        transition: transform 0.3s ease;
    `

    document.body.appendChild(notification)

    // Animar entrada
    setTimeout(() => {
        notification.style.transform = "translateX(0)"
    }, 100)

    // Remover después de 4 segundos
    setTimeout(() => {
        notification.style.transform = "translateX(100%)"
        setTimeout(() => {
            document.body.removeChild(notification)
        }, 300)
    }, 4000)
}

function typeWriter(element, text, speed) {
    let i = 0

    function type() {
        if (i < text.length) {
            element.innerHTML += text.charAt(i)
            i++
            setTimeout(type, speed)
        }
    }
    type()
}

function initTestimonialsCarousel() {
    const testimonials = document.querySelectorAll(".testimonial-card")
    if (testimonials.length === 0) return

    let currentIndex = 0

    setInterval(() => {
        testimonials[currentIndex].style.transform = "scale(1)"
        currentIndex = (currentIndex + 1) % testimonials.length
        testimonials[currentIndex].style.transform = "scale(1.05)"
    }, 5000)
}

function activateEasterEgg() {
    // Crear confetti effect
    const colors = ["#ff6b6b", "#4ecdc4", "#45b7d1", "#96ceb4", "#ffeaa7"]

    for (let i = 0; i < 50; i++) {
        createConfetti(colors[Math.floor(Math.random() * colors.length)])
    }

    showNotification("¡Código Konami activado! 🎉 ¡Eres un verdadero gamer!", "success")
}

function createConfetti(color) {
    const confetti = document.createElement("div")
    confetti.style.cssText = `
        position: fixed;
        width: 10px;
        height: 10px;
        background: ${color};
        top: -10px;
        left: ${Math.random() * 100}vw;
        z-index: 10000;
        border-radius: 50%;
        pointer-events: none;
        animation: confetti-fall 3s linear forwards;
    `

    document.body.appendChild(confetti)

    setTimeout(() => {
        document.body.removeChild(confetti)
    }, 3000)
}

// Añadir CSS para animaciones adicionales
const additionalStyles = `
    @keyframes shake {
        0%, 100% { transform: translateX(0); }
        25% { transform: translateX(-5px); }
        75% { transform: translateX(5px); }
    }
    
    .shake {
        animation: shake 0.5s ease-in-out;
    }
    
    @keyframes confetti-fall {
        0% {
            transform: translateY(-100vh) rotate(0deg);
            opacity: 1;
        }
        100% {
            transform: translateY(100vh) rotate(720deg);
            opacity: 0;
        }
    }
    
    .notification-content {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .lazy {
        opacity: 0;
        transition: opacity 0.3s;
    }
    
    .lazy.loaded {
        opacity: 1;
    }
`
// Inyectar estilos adicionales
const styleSheet = document.createElement("style")
styleSheet.textContent = additionalStyles
document.head.appendChild(styleSheet)

// Service Worker registration (opcional para PWA)
if ("serviceWorker" in navigator) {
    window.addEventListener("load", () => {
        navigator.serviceWorker
            .register("/sw.js")
            .then((registration) => {
                console.log("SW registered: ", registration)
            })
            .catch((registrationError) => {
                console.log("SW registration failed: ", registrationError)
            })
    })
}