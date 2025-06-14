<?php
session_start();

?>

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

    <title>Renta Fácil</title>
</head>

<style>
    /* Variables CSS */
    :root {
        --primary-color: #1a1a1a;
        --secondary-color: rgb(124, 204, 216);
        --accent-color: rgb(234, 234, 138);
        --text-light: #ffffff;
        --text-dark: #333333;
        --gradient-primary: linear-gradient(135deg, rgb(90, 151, 220) 0%, rgb(46, 44, 49) 100%);
        --gradient-secondary: linear-gradient(135deg, rgb(104, 130, 166) 0%, rgb(182, 181, 182) 100%);
        --gradient-accent: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        --shadow-light: 0 4px 6px rgba(0, 0, 0, 0.1);
        --shadow-medium: 0 8px 25px rgba(0, 0, 0, 0.15);
        --shadow-heavy: 0 15px 35px rgba(0, 0, 0, 0.2);
        --border-radius: 12px;
        --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    /* Reset y base */
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: "Inter", sans-serif;
        line-height: 1.6;
        color: var(--text-dark);
        overflow-x: hidden;
        background-color: #1a1a1a;
    }

    /* Loading Screen */
    .loading-screen {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 9999;
        transition: opacity 0.5s ease, visibility 0.5s ease;
    }

    .loading-content {
        text-align: center;
        color: white;
    }

    .loading-logo {
        font-size: 4rem;
        margin-bottom: 1rem;
        animation: pulse 2s infinite;
    }

    .loading-text {
        font-size: 1.5rem;
        font-weight: 600;
        margin-bottom: 2rem;
        letter-spacing: 2px;
    }

    .loading-spinner {
        width: 50px;
        height: 50px;
        border: 3px solid rgba(255, 255, 255, 0.3);
        border-top: 3px solid white;
        border-radius: 50%;
        animation: spin 1s linear infinite;
        margin: 0 auto;
    }

    @keyframes spin {
        0% {
            transform: rotate(0deg);
        }

        100% {
            transform: rotate(360deg);
        }
    }

    @keyframes pulse {

        0%,
        100% {
            transform: scale(1);
        }

        50% {
            transform: scale(1.1);
        }
    }

    /* Navbar mejorado */
    .navbar-custom {
        background: rgba(26, 26, 26, 0.95);
        backdrop-filter: blur(10px);
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        padding: 1rem 0;
        transition: var(--transition);
    }

    .navbar-custom.scrolled {
        background: rgba(26, 26, 26, 0.98);
        box-shadow: var(--shadow-medium);
    }

    .navbar-brand-container {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .logo-container {
        width: 50px;
        height: 50px;
        background: var(--gradient-primary);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: var(--transition);
    }

    .logo-container:hover {
        transform: rotate(360deg) scale(1.1);
    }

    .logo-icon {
        font-size: 1.5rem;
        color: white;
    }

    .navbar-brand {
        font-size: 1.5rem;
        font-weight: 700;
        text-decoration: none;
        background: var(--gradient-accent);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .nav-link-custom {
        color: var(--text-light) !important;
        font-weight: 500;
        padding: 0.5rem 1rem !important;
        border-radius: var(--border-radius);
        transition: var(--transition);
        position: relative;
        overflow: hidden;
    }

    .nav-link-custom::before {
        content: "";
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: var(--gradient-primary);
        transition: var(--transition);
        z-index: -1;
    }

    .nav-link-custom:hover::before {
        left: 0;
    }

    .nav-link-custom:hover {
        color: white !important;
        transform: translateY(-2px);
    }

    .custom-toggler {
        border: 2px solid rgba(255, 255, 255, 0.3);
        border-radius: var(--border-radius);
        padding: 0.5rem;
        transition: var(--transition);
    }

    .custom-toggler:hover {
        border-color: var(--secondary-color);
        transform: scale(1.05);
    }

    .navbar-toggler-icon {
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3e%3cpath stroke='rgba%28255, 255, 255, 1%29' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e");
    }

    .btn-cta {
        background: var(--gradient-primary);
        color: white;
        border: none;
        padding: 0.75rem 1.5rem;
        border-radius: var(--border-radius);
        font-weight: 600;
        text-decoration: none;
        transition: var(--transition);
        position: relative;
        overflow: hidden;
    }

    .btn-cta::before {
        content: "";
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: var(--gradient-secondary);
        transition: var(--transition);
        z-index: -1;
    }

    .btn-cta:hover::before {
        left: 0;
    }

    .btn-cta:hover {
        color: white;
        transform: translateY(-3px);
        box-shadow: var(--shadow-medium);
    }

    /* Hero Section mejorado */
    .hero-banner {
        min-height: 100vh;
        background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 50%, #1a1a1a 100%);
        position: relative;
        display: flex;
        align-items: center;
        overflow: hidden;
    }

    .hero-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: radial-gradient(circle at center, rgba(0, 0, 0, 0.3) 0%, rgba(0, 0, 0, 0.7) 100%);
        z-index: 1;
    }

    .hero-particles {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-image: radial-gradient(circle at 20% 80%, rgba(120, 119, 198, 0.3) 0%, transparent 50%),
            radial-gradient(circle at 80% 20%, rgba(255, 119, 198, 0.3) 0%, transparent 50%),
            radial-gradient(circle at 40% 40%, rgba(120, 219, 255, 0.3) 0%, transparent 50%);
        animation: float 6s ease-in-out infinite;
        z-index: 1;
    }

    @keyframes float {

        0%,
        100% {
            transform: translateY(0px);
        }

        50% {
            transform: translateY(-20px);
        }
    }

    .hero-content {
        position: relative;
        z-index: 2;
    }

    .hero-title {
        font-size: clamp(2.5rem, 5vw, 4rem);
        font-weight: 700;
        line-height: 1.2;
        margin-bottom: 1.5rem;
    }

    .text-gradient {
        background: var(--gradient-accent);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .hero-subtitle {
        font-size: 1.25rem;
        opacity: 0.9;
        max-width: 600px;
        margin: 0 auto;
    }

    /* Search Box mejorado */
    .search-box {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        border-radius: 20px;
        max-width: 700px;
        border: 1px solid rgba(255, 255, 255, 0.2);
        transition: var(--transition);
    }

    .search-box:hover {
        transform: translateY(-5px);
        box-shadow: var(--shadow-heavy);
    }

    .search-header {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        color: var(--text-dark);
    }

    .search-icon {
        font-size: 1.2rem;
        color: var(--secondary-color);
    }

    .search-title {
        font-weight: 600;
        font-size: 1.1rem;
    }

    .search-form-container {
        display: flex;
        gap: 1rem;
        align-items: center;
    }

    .form-group-custom {
        position: relative;
        flex: 1;
    }

    .input-icon {
        position: absolute;
        left: 15px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--secondary-color);
        z-index: 2;
    }

    .zona-busqueda {
        background-color: #f8f9fa;
        border: 2px solid transparent;
        border-radius: var(--border-radius);
        padding: 15px 15px 15px 45px;
        color: var(--text-dark);
        font-weight: 500;
        transition: var(--transition);
        width: 100%;
    }

    .zona-busqueda:focus {
        outline: none;
        border-color: var(--secondary-color);
        box-shadow: 0 0 0 3px rgba(23, 162, 184, 0.1);
        transform: translateY(-2px);
    }

    .btn-search {
        background: var(--gradient-primary);
        color: white;
        border: none;
        padding: 15px 30px;
        border-radius: var(--border-radius);
        font-weight: 600;
        transition: var(--transition);
        white-space: nowrap;
    }

    .btn-search:hover {
        background: var(--gradient-secondary);
        transform: translateY(-2px);
        box-shadow: var(--shadow-medium);
    }


    @keyframes scroll-wheel {
        0% {
            top: 8px;
            opacity: 1;
        }

        100% {
            top: 24px;
            opacity: 0;
        }
    }

    /* About Section mejorado */
    .about-section {
        background: var(--primary-color);
        color: var(--text-light);
        padding: 100px 0;
        position: relative;
        overflow: hidden;
    }

    .about-bg-pattern {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-image: radial-gradient(circle at 25% 25%, rgba(255, 255, 255, 0.05) 0%, transparent 50%),
            radial-gradient(circle at 75% 75%, rgba(255, 255, 255, 0.05) 0%, transparent 50%);
        z-index: 0;
    }

    .about-icon-container {
        width: 80px;
        height: 80px;
        background: var(--gradient-primary);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 2rem;
    }

    .about-icon {
        font-size: 2rem;
        color: white;
    }

    .about-title {
        font-size: 3rem;
        font-weight: 700;
        margin-bottom: 2rem;
        background: var(--gradient-accent);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .about-description {
        font-size: 1.2rem;
        line-height: 1.8;
        opacity: 0.9;
        margin-bottom: 2rem;
    }

    .about-features {
        list-style: none;
    }

    .feature-item {
        display: flex;
        align-items: center;
        gap: 1rem;
        margin-bottom: 1rem;
        padding: 0.5rem 0;
    }

    .feature-icon {
        font-size: 1.2rem;
    }

    .about-visual {
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100%;
    }

    .about-card {
        background: rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(10px);
        border-radius: 20px;
        padding: 3rem 2rem;
        text-align: center;
        border: 1px solid rgba(255, 255, 255, 0.2);
        transition: var(--transition);
    }

    .about-card:hover {
        transform: translateY(-10px);
        box-shadow: var(--shadow-heavy);
    }

    .card-icon {
        font-size: 3rem;
        margin-bottom: 1rem;
    }

    .section-divider {
        border: none;
        height: 2px;
        background: var(--gradient-accent);
        margin: 4rem 0;
        border-radius: 1px;
    }

    .login-section {
        margin-top: 3rem;
    }

    .login-card {
        background: rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(10px);
        border-radius: 20px;
        padding: 2rem;
        display: inline-block;
        border: 1px solid rgba(255, 255, 255, 0.2);
        transition: var(--transition);
    }

    .login-card:hover {
        transform: translateY(-5px);
    }

    .login-icon {
        font-size: 2rem;
        margin-bottom: 1rem;
    }

    .login-question {
        font-size: 1.1rem;
        margin-bottom: 1rem;
        opacity: 0.9;
    }

    .btn-login {
        background: var(--gradient-secondary);
        color: white;
        border: none;
        padding: 0.75rem 2rem;
        border-radius: var(--border-radius);
        font-weight: 600;
        text-decoration: none;
        transition: var(--transition);
    }

    .btn-login:hover {
        color: white;
        transform: translateY(-2px);
        box-shadow: var(--shadow-medium);
    }

    /* Info Grid mejorado */
    .info-image-container {
        position: relative;
        height: 600px;
        overflow: hidden;
    }

    .info-image {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: var(--transition);
    }

    .info-image-container:hover .info-image {
        transform: scale(1.1);
    }

    .image-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.6);
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: var(--transition);
    }

    .info-image-container:hover .image-overlay {
        opacity: 1;
    }

    .overlay-content {
        text-align: center;
        color: white;
    }

    .overlay-icon {
        font-size: 3rem;
        margin-bottom: 1rem;
    }

    .info-text-section {
        background: var(--primary-color);
        color: var(--text-light);
        padding: 4rem 3rem;
        min-height: 600px;
    }

    .info-text-alt {
        background: #2d2d2d;
    }

    .info-content {
        max-width: 500px;
        margin: 0 auto;
    }

    .info-icon-container {
        width: 70px;
        height: 70px;
        background: var(--gradient-primary);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 2rem;
    }

    .info-section-icon {
        font-size: 1.8rem;
        color: white;
    }

    .info-title {
        font-size: 2.5rem;
        font-weight: 700;
        margin-bottom: 1.5rem;
        background: var(--gradient-accent);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .info-description {
        font-size: 1.1rem;
        line-height: 1.8;
        opacity: 0.9;
        margin-bottom: 2rem;
    }

    .info-benefits {
        margin-bottom: 2rem;
    }

    .benefit-item {
        display: flex;
        align-items: center;
        gap: 1rem;
        margin-bottom: 1rem;
        padding: 0.5rem 0;
    }

    .benefit-icon {
        font-size: 1.1rem;
    }

    .btn-info-cta {
        background: var(--gradient-secondary);
        color: white;
        border: none;
        padding: 1rem 2rem;
        border-radius: var(--border-radius);
        font-weight: 600;
        text-decoration: none;
        transition: var(--transition);
        display: inline-block;
    }

    .btn-info-cta:hover {
        color: white;
        transform: translateY(-3px);
        box-shadow: var(--shadow-medium);
    }

    /* Testimonials Section */
    .testimonials-section {
        background: #f8f9fa;
        padding: 100px 0;
    }

    .section-title {
        font-size: 2.5rem;
        font-weight: 700;
        color: var(--text-dark);
        margin-bottom: 1rem;
    }

    .section-subtitle {
        font-size: 1.2rem;
        color: #666;
        margin-bottom: 3rem;
    }

    .testimonial-card {
        background: white;
        border-radius: 20px;
        padding: 2rem;
        box-shadow: var(--shadow-light);
        transition: var(--transition);
        height: 100%;
    }

    .testimonial-card:hover {
        transform: translateY(-10px);
        box-shadow: var(--shadow-medium);
    }

    .testimonial-stars {
        color: var(--accent-color);
        margin-bottom: 1rem;
    }

    .testimonial-text {
        font-style: italic;
        margin-bottom: 1.5rem;
        line-height: 1.6;
    }

    .testimonial-author {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .author-avatar {
        width: 50px;
        height: 50px;
        background: var(--gradient-primary);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
    }

    .author-info h5 {
        margin: 0;
        font-weight: 600;
    }

    .author-info span {
        color: #666;
        font-size: 0.9rem;
    }

    /* Footer mejorado */
    .footer-modern {
        background: var(--primary-color);
        color: var(--text-light);
        position: relative;
        overflow: hidden;
    }

    .footer-content {
        padding: 4rem 0 2rem;
        position: relative;
        z-index: 2;
    }

    .footer-brand {
        margin-bottom: 2rem;
    }

    .footer-logo {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin-bottom: 1rem;
    }

    .footer-logo i {
        font-size: 2rem;
    }

    .brand-name {
        font-size: 1.5rem;
        font-weight: 700;
        background: var(--gradient-accent);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .brand-description {
        opacity: 0.8;
        line-height: 1.6;
        margin-bottom: 1.5rem;
    }

    .social-links {
        display: flex;
        gap: 1rem;
    }

    .social-link {
        width: 40px;
        height: 40px;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        text-decoration: none;
        transition: var(--transition);
    }

    .social-link:hover {
        background: var(--gradient-primary);
        color: white;
        transform: translateY(-3px);
    }

    .footer-title {
        font-size: 1.2rem;
        font-weight: 600;
        margin-bottom: 1.5rem;
    }

    .footer-links {
        list-style: none;
        padding: 0;
    }

    .footer-links li {
        margin-bottom: 0.5rem;
    }

    .footer-links a {
        color: rgba(255, 255, 255, 0.8);
        text-decoration: none;
        transition: var(--transition);
    }

    .footer-links a:hover {
        padding-left: 5px;
    }

    .contact-info {
        margin-bottom: 1.5rem;
    }

    .contact-item {
        display: flex;
        align-items: center;
        gap: 1rem;
        margin-bottom: 1rem;
    }

    .contact-icon {
        width: 20px;
    }

    .newsletter-signup h6 {
        margin-bottom: 1rem;
    }

    .newsletter-input {
        background: rgba(255, 255, 255, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.2);
        color: white;
        border-radius: var(--border-radius) 0 0 var(--border-radius);
    }

    .newsletter-input::placeholder {
        color: rgba(255, 255, 255, 0.6);
    }

    .newsletter-input:focus {
        background: rgba(255, 255, 255, 0.15);
        border-color: var(--secondary-color);
        color: white;
        box-shadow: none;
    }

    .btn-newsletter {
        background: var(--gradient-primary);
        border: none;
        color: white;
        border-radius: 0 var(--border-radius) var(--border-radius) 0;
        transition: var(--transition);
    }

    .btn-newsletter:hover {
        background: var(--gradient-secondary);
    }

    .footer-bottom {
        background: rgba(0, 0, 0, 0.3);
        padding: 1.5rem 0;
        border-top: 1px solid rgba(255, 255, 255, 0.1);
    }

    .copyright {
        margin: 0;
        opacity: 0.8;
    }

    .footer-bottom-links {
        display: flex;
        gap: 2rem;
    }

    .footer-bottom-links a {
        color: rgba(255, 255, 255, 0.8);
        text-decoration: none;
        transition: var(--transition);
    }


    /* Scroll to Top Button */
    .scroll-to-top {
        position: fixed;
        bottom: 30px;
        right: 30px;
        width: 50px;
        height: 50px;
        background: var(--gradient-primary);
        color: white;
        border: none;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: var(--transition);
        opacity: 0;
        visibility: hidden;
        z-index: 1000;
    }

    .scroll-to-top.visible {
        opacity: 1;
        visibility: visible;
    }

    .scroll-to-top:hover {
        background: var(--gradient-secondary);
        transform: translateY(-3px);
        box-shadow: var(--shadow-medium);
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .hero-title {
            font-size: 2.5rem;
        }

        .hero-subtitle {
            font-size: 1.1rem;
        }

        .search-form-container {
            flex-direction: column;
        }

        .btn-search {
            width: 100%;
        }

        .about-section {
            padding: 60px 20px;
        }

        .about-title {
            font-size: 2rem;
        }

        .info-text-section {
            padding: 3rem 2rem;
        }

        .info-title {
            font-size: 2rem;
        }

        .footer-bottom-links {
            flex-direction: column;
            gap: 1rem;
            text-align: center;
        }

        .hero-stats {
            margin-top: 2rem;
        }

        .stat-number {
            font-size: 2rem;
        }
    }

    @media (max-width: 576px) {
        .navbar-custom {
            padding: 0.5rem 0;
        }

        .hero-banner {
            padding: 80px 0 40px;
        }

        .search-box {
            margin: 0 1rem;
            padding: 1.5rem;
        }

        .about-section {
            margin: 40px 20px;
            padding: 40px 20px;
        }

        .testimonials-section {
            padding: 60px 0;
        }

        .footer-content {
            padding: 3rem 0 1rem;
        }
    }

    /* Animaciones adicionales */
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @keyframes fadeInLeft {
        from {
            opacity: 0;
            transform: translateX(-30px);
        }

        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    @keyframes fadeInRight {
        from {
            opacity: 0;
            transform: translateX(30px);
        }

        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    /* Efectos de hover mejorados */
    .btn:hover {
        transform: translateY(-2px);
    }

    .card:hover {
        transform: translateY(-5px);
    }

    /* Mejoras de accesibilidad */
    .btn:focus,
    .form-control:focus {
        outline: 2px solid var(--secondary-color);
        outline-offset: 2px;
    }

    /* Smooth scrolling */
    html {
        scroll-behavior: smooth;
    }

    /* Preloader hide class */
    .loading-screen.hidden {
        opacity: 0;
        visibility: hidden;
    }
</style>


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

    <!-- Footer mejorado -->
    <footer class="footer-modern" data-aos="fade-up">
        <div class="footer-content">
            <div class="container">
                <div class="row">
                    <!-- Columna 1: Marca -->
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="footer-brand">
                            <div class="footer-logo">
                                <i class="fas fa-home"></i>
                                <span class="brand-name">Renta Fácil</span>
                            </div>
                            <p class="brand-description">
                                La plataforma líder en gestión de alquileres para propietarios y arrendatarios en Colombia.
                            </p>
                            <div class="social-links">
                                <a href="#" class="social-link">
                                    <i class="fab fa-facebook-f"></i>
                                </a>
                                <a href="#" class="social-link">
                                    <i class="fab fa-twitter"></i>
                                </a>
                                <a href="#" class="social-link">
                                    <i class="fab fa-instagram"></i>
                                </a>
                                <a href="#" class="social-link">
                                    <i class="fab fa-linkedin-in"></i>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Columna 2: Enlaces rápidos -->
                    <div class="col-lg-2 col-md-6 mb-4">
                        <h5 class="footer-title">Enlaces</h5>
                        <ul class="footer-links">
                            <li><a href="#resumen">Resumen</a></li>
                            <li><a href="#propietario">Propietarios</a></li>
                            <li><a href="#arrendatario">Arrendatarios</a></li>
                            <li><a href="#">Ayuda</a></li>
                        </ul>
                    </div>

                    <!-- Columna 3: Servicios -->
                    <div class="col-lg-2 col-md-6 mb-4">
                        <h5 class="footer-title">Servicios</h5>
                        <ul class="footer-links">
                            <li><a href="#">Publicar Propiedad</a></li>
                            <li><a href="#">Buscar Inmuebles</a></li>
                            <li><a href="#">Gestión de Contratos</a></li>
                            <li><a href="#">Soporte 24/7</a></li>
                        </ul>
                    </div>

                    <!-- Columna 4: Contacto -->
                    <div class="col-lg-4 col-md-6 mb-4">
                        <h5 class="footer-title">Contacto</h5>
                        <div class="contact-info">
                            <div class="contact-item">
                                <i class="fas fa-envelope contact-icon"></i>
                                <span>rentafacil@gmail.com</span>
                            </div>
                            <div class="contact-item">
                                <i class="fas fa-phone contact-icon"></i>
                                <span>+57 (2) 000-0001</span>
                            </div>
                            <div class="contact-item">
                                <i class="fas fa-map-marker-alt contact-icon"></i>
                                <span>Neiva, Huila, Colombia</span>
                            </div>
                        </div>
                        <div class="newsletter-signup mt-3">
                            <h6>Suscríbete a nuestro boletín</h6>
                            <div class="input-group">
                                <input type="email" class="form-control newsletter-input" placeholder="Tu email">
                                <button class="btn btn-newsletter" type="button">
                                    <i class="fas fa-paper-plane"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="footer-bottom">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <p class="copyright">© 2025 RentaFácil. Todos los derechos reservados</p>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <div class="footer-bottom-links">
                            <a href="#">Términos de Servicio</a>
                            <a href="#">Política de Privacidad</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <!-- Botón de scroll to top -->
    <button id="scrollToTop" class="scroll-to-top">
        <i class="fas fa-chevron-up"></i>
    </button>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-j1CDi7MgGQ12Z7Qab0qlWQ/Qqz24Gc6BM0thvEMVjHnfYGF0rmFCozFSxQBxwHKO"
        crossorigin="anonymous"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
</body>

</html>

<script>
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
            anchor.addEventListener("click", function(e) {
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
            searchForm.addEventListener("submit", function(e) {
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
</script>