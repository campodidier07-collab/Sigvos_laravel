<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIGVOS | Desarrollador</title>
    <link href="{{ asset('img/icono-pagina.png') }}" rel="icon" type="image/png">
    <link rel="stylesheet" href="{{ asset('css/output.css') }}">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { font-family: 'Outfit', sans-serif; background-color: #f6fbf8; margin: 0; overflow-x: hidden; }
        
        .hero-section {
            background-color: #1a3c2e;
            padding-bottom: 120px;
            position: relative;
        }

        .navbar {
            padding: 20px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .btn-back {
            background: rgba(255, 255, 255, 0.1);
            color: #fff;
            padding: 8px 16px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s;
        }
        .btn-back:hover {
            background: rgba(255, 255, 255, 0.2);
            color: #fff;
            text-decoration: none;
        }

        .hero-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1100px;
            margin: 40px auto 0;
            padding: 0 20px;
        }

        .badge-dev {
            background: rgba(255, 255, 255, 0.1);
            color: #fff;
            padding: 6px 12px;
            border-radius: 999px;
            font-size: 0.75rem;
            font-weight: 600;
            letter-spacing: 1px;
            display: inline-block;
            margin-bottom: 24px;
            border: 1px solid rgba(255,255,255,0.2);
        }

        .hero-title {
            color: #ffffff;
            font-size: 4.5rem;
            font-weight: 900;
            line-height: 1.1;
            margin-bottom: 24px;
        }
        .hero-title span {
            color: #3bb371; /* Green accent */
        }

        .hero-subtitle {
            color: #a4b5ad;
            font-size: 1.1rem;
            max-width: 450px;
            line-height: 1.6;
            margin-bottom: 40px;
        }

        .sigvos-pill {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 999px;
            padding: 10px 20px;
            display: inline-flex;
            align-items: center;
            gap: 16px;
        }
        .sigvos-pill-icon {
            background: #27ae60;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .sigvos-pill-icon img {
            width: 24px;
        }
        .sigvos-pill-text h4 {
            color: #fff;
            font-size: 0.9rem;
            margin: 0;
            font-weight: 800;
        }
        .sigvos-pill-text p {
            color: #a4b5ad;
            font-size: 0.7rem;
            margin: 0;
            font-weight: 500;
        }

        .dev-card-wrapper {
            position: relative;
        }

        .dev-card {
            background: #ffffff;
            border-radius: 24px;
            width: 320px;
            height: 380px;
            position: relative;
            overflow: visible; /* To allow floating elements */
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        }

        .dev-photo {
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: top;
            border-radius: 24px;
        }

        .badge-sena {
            position: absolute;
            top: 20px;
            right: -20px;
            background: #395446;
            color: #fff;
            padding: 8px 16px;
            border-radius: 999px;
            font-size: 0.8rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 6px;
            box-shadow: 0 10px 15px -3px rgba(0,0,0,0.3);
            border: 1px solid rgba(255,255,255,0.1);
        }
        .badge-sena::before {
            content: "";
            width: 8px;
            height: 8px;
            background: #27ae60;
            border-radius: 50%;
        }

        .dev-floating-name {
            position: absolute;
            bottom: -20px;
            left: -40px;
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            padding: 16px 24px;
            border-radius: 16px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            border: 1px solid rgba(255,255,255,1);
        }
        .dev-floating-name h4 {
            color: #111827;
            font-size: 1rem;
            font-weight: 800;
            margin: 0;
            line-height: 1.2;
        }
        .dev-floating-name p {
            color: #27ae60;
            font-size: 0.75rem;
            margin: 4px 0 0;
            font-weight: 700;
        }

        .wave {
            position: absolute;
            bottom: -1px;
            left: 0;
            width: 100%;
            overflow: hidden;
            line-height: 0;
        }

        .wave svg {
            position: relative;
            display: block;
            width: calc(100% + 1.3px);
            height: 100px;
        }

        .wave .shape-fill {
            fill: #f6fbf8;
        }

        .content-section {
            max-width: 1100px;
            margin: 30px auto 40px; /* Bajar la sección para que no quede pegada */
            position: relative;
            z-index: 10;
            padding: 0 20px;
        }

        .main-card {
            background: #fff;
            border-radius: 24px;
            padding: 40px;
            display: flex;
            box-shadow: 0 20px 40px rgba(26, 60, 46, 0.05);
            margin-bottom: 60px;
            border: 1px solid rgba(0,0,0,0.03);
        }

        .skills-section {
            flex: 1;
            padding-right: 40px;
            border-right: 1px dashed #e2e8f0;
        }
        .connect-section {
            flex: 0.4;
            padding-left: 40px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        .section-title {
            color: #1a3c2e;
            font-size: 1.1rem;
            font-weight: 800;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .skills-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 16px;
        }

        .skill-item {
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 12px 16px;
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 0.85rem;
            font-weight: 600;
            color: #334155;
            background: #fff;
            transition: all 0.2s;
        }
        .skill-item:hover {
            border-color: #3bb371;
            box-shadow: 0 4px 12px rgba(59, 179, 113, 0.1);
        }
        .skill-icon {
            font-size: 1.1rem;
            color: #4b6bfb;
        }

        .social-icons {
            display: flex;
            gap: 16px;
            margin-top: 16px;
        }
        .social-icon {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 1.2rem;
            transition: all 0.2s;
            text-decoration: none;
        }
        .social-icon:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 15px rgba(0,0,0,0.2);
            color: #fff;
        }
        .social-wa { background: #25D366; box-shadow: 0 4px 15px rgba(37, 211, 102, 0.3); }
        .social-fb { background: #1877F2; box-shadow: 0 4px 15px rgba(24, 119, 242, 0.3); }
        .social-ig { background: radial-gradient(circle at 30% 107%, #fdf497 0%, #fd5949 45%, #d6249f 60%, #285AEB 90%); box-shadow: 0 4px 15px rgba(214, 36, 159, 0.3); }

        .tech-header {
            text-align: center;
            margin-bottom: 40px;
        }
        .tech-badge {
            background: #e6f4ea;
            color: #27ae60;
            padding: 6px 16px;
            border-radius: 999px;
            font-size: 0.75rem;
            font-weight: 700;
            display: inline-block;
            margin-bottom: 16px;
            border: 1px solid #cce8d6;
        }
        .tech-title {
            color: #111827;
            font-size: 2.2rem;
            font-weight: 900;
            margin: 0 0 10px;
        }
        .tech-subtitle {
            color: #64748b;
            font-size: 0.95rem;
        }

        .tech-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 40px;
        }

        .tech-card {
            background: #fff;
            border-radius: 20px;
            padding: 30px 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
            border: 1px solid #e2e8f0;
            transition: all 0.2s;
            text-decoration: none;
        }
        .tech-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.05);
            border-color: #3bb371;
            text-decoration: none;
        }
        .tech-logo {
            height: 60px;
            object-fit: contain;
            margin-bottom: 20px;
        }
        .tech-name {
            color: #111827;
            font-weight: 800;
            font-size: 1.1rem;
            margin: 0 0 4px;
        }
        .tech-ver {
            color: #10b981;
            font-size: 0.75rem;
            font-weight: 700;
            margin-bottom: 16px;
        }
        .tech-link {
            color: #64748b;
            font-size: 0.8rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        @media (max-width: 900px) {
            .hero-content {
                flex-direction: column;
                text-align: center;
                gap: 60px;
            }
            .hero-title { font-size: 3rem; }
            .hero-subtitle { margin: 0 auto 30px; }
            .main-card { flex-direction: column; }
            .skills-section { padding-right: 0; border-right: none; border-bottom: 1px dashed #e2e8f0; padding-bottom: 30px; margin-bottom: 30px; }
            .connect-section { padding-left: 0; }
            .tech-grid { grid-template-columns: repeat(2, 1fr); }
            .dev-card-wrapper { margin: 0 auto; }
            .dev-floating-name { left: 50%; transform: translateX(-50%); bottom: -30px; width: 260px; }
        }
        @media (max-width: 500px) {
            .tech-grid { grid-template-columns: 1fr; }
            .skills-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

    <section class="hero-section">
        <nav class="navbar" style="justify-content: flex-end;">
            <a href="{{ url('/') }}" class="btn-back">
                <i class="fas fa-arrow-left"></i> Volver al inicio
            </a>
        </nav>

        <div class="hero-content">
            <div class="hero-text">
                <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 24px;">
                    <div style="background: rgba(255,255,255,0.1); padding: 8px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                        <img src="{{ asset('img/icono-pagina.png') }}" alt="Logo SIGVOS" style="width: 28px; height: 28px; object-fit: contain;">
                    </div>
                    <span style="color: #fff; font-size: 1.8rem; font-weight: 900; letter-spacing: -0.5px;">SIG<span style="color: #3bb371;">VOS</span></span>
                </div>
                <h1 class="hero-title">
                    Desarrollador<br>
                    del <span>Sistema</span>
                </h1>
                <p class="hero-subtitle">
                    Conoce a la persona detrás de SIGVOS — un sistema de gestión de cultivos construido con pasión y dedicación.
                </p>
            </div>

            <div class="dev-card-wrapper">
                <div class="dev-card">
                    <div class="badge-sena">Aprendiz SENA</div>
                    <img src="{{ asset('img/Fotodev.jpeg') }}" alt="Didier Arley Puentes Campo" class="dev-photo">
                    
                    <div class="dev-floating-name">
                        <h4>Didier Arley<br>Puentes Campo</h4>
                        <p>Desarrollador De Software</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- SVG Wave -->
        <div class="wave">
            <svg data-name="Layer 1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 120" preserveAspectRatio="none">
                <path d="M321.39,56.44c58-10.79,114.16-30.13,172-41.86,82.39-16.72,168.19-17.73,250.45-.39C823.78,31,906.67,72,985.66,92.83c70.05,18.48,146.53,26.09,214.34,3V120H0V95.8C59.71,118.08,130.83,119.38,193.18,97.71,236.75,82.52,279.16,64.29,321.39,56.44Z" class="shape-fill"></path>
            </svg>
        </div>
    </section>

    <div class="content-section">
        <div class="main-card">
            <div class="skills-section">
                <h3 class="section-title"><i class="fas fa-id-card text-success"></i> MIS HABILIDADES</h3>
                <div class="skills-grid">
                    <div class="skill-item">
                        <i class="fas fa-layer-group skill-icon" style="color: #3b82f6;"></i> Frontend & Backend
                    </div>
                    <div class="skill-item">
                        <i class="fas fa-paint-brush skill-icon" style="color: #ec4899;"></i> Diseño UI/UX
                    </div>
                    <div class="skill-item">
                        <i class="fas fa-clipboard-list skill-icon" style="color: #f59e0b;"></i> Análisis de Requisitos
                    </div>
                    <div class="skill-item">
                        <i class="fas fa-bug skill-icon" style="color: #10b981;"></i> Tester QA
                    </div>
                    <div class="skill-item">
                        <i class="fas fa-database skill-icon" style="color: #8b5cf6;"></i> Base de Datos
                    </div>
                    <div class="skill-item">
                        <i class="fas fa-file-alt skill-icon" style="color: #64748b;"></i> Documentación
                    </div>
                </div>
            </div>
            
            <div class="connect-section">
                <h3 class="section-title"><i class="fas fa-envelope-open-text text-success"></i> CONECTA CONMIGO</h3>
                <div class="social-icons">
                    <a href="https://wa.me/573156320923" target="_blank" class="social-icon social-wa">
                        <i class="fab fa-whatsapp"></i>
                    </a>
                    <a href="https://www.facebook.com/didier.campo" target="_blank" class="social-icon social-fb">
                        <i class="fab fa-facebook-f"></i>
                    </a>
                    <a href="https://www.instagram.com/didierpuentes2" target="_blank" class="social-icon social-ig">
                        <i class="fab fa-instagram"></i>
                    </a>
                </div>
            </div>
        </div>

        <div class="tech-header">
            <div class="tech-badge"><i class="fas fa-cog"></i> STACK TÉCNICO</div>
            <h2 class="tech-title">Tecnologías & Herramientas</h2>
            <p class="tech-subtitle">Todo lo que fue utilizado para construir SIGVOS</p>
        </div>

        <div class="tech-grid">
            <a href="https://laravel.com/" target="_blank" class="tech-card">
                <img src="https://cdn.jsdelivr.net/gh/devicons/devicon@latest/icons/laravel/laravel-original.svg" alt="Laravel" class="tech-logo">
                <h4 class="tech-name">Laravel</h4>
                <div class="tech-ver">v10+</div>
                <div class="tech-link">Ver más <i class="fas fa-external-link-alt"></i></div>
            </a>
            
            <a href="https://www.php.net/" target="_blank" class="tech-card">
                <img src="https://cdn.jsdelivr.net/gh/devicons/devicon@latest/icons/php/php-original.svg" alt="PHP" class="tech-logo">
                <h4 class="tech-name">PHP</h4>
                <div class="tech-ver">v8+</div>
                <div class="tech-link">Ver más <i class="fas fa-external-link-alt"></i></div>
            </a>
            
            <a href="https://www.mysql.com/" target="_blank" class="tech-card">
                <img src="https://cdn.jsdelivr.net/gh/devicons/devicon@latest/icons/mysql/mysql-original.svg" alt="MySQL" class="tech-logo">
                <h4 class="tech-name">MySQL</h4>
                <div class="tech-ver">v8+</div>
                <div class="tech-link">Ver más <i class="fas fa-external-link-alt"></i></div>
            </a>

            <a href="https://getbootstrap.com/" target="_blank" class="tech-card">
                <img src="https://cdn.jsdelivr.net/gh/devicons/devicon@latest/icons/bootstrap/bootstrap-original.svg" alt="Bootstrap" class="tech-logo">
                <h4 class="tech-name">Bootstrap</h4>
                <div class="tech-ver">v4/v5</div>
                <div class="tech-link">Ver más <i class="fas fa-external-link-alt"></i></div>
            </a>

            <a href="https://developer.mozilla.org/es/docs/Web/JavaScript" target="_blank" class="tech-card">
                <img src="https://cdn.jsdelivr.net/gh/devicons/devicon@latest/icons/javascript/javascript-original.svg" alt="JavaScript" class="tech-logo">
                <h4 class="tech-name">JavaScript</h4>
                <div class="tech-ver">ES6+</div>
                <div class="tech-link">Ver más <i class="fas fa-external-link-alt"></i></div>
            </a>

            <a href="https://github.com/" target="_blank" class="tech-card">
                <img src="https://cdn.jsdelivr.net/gh/devicons/devicon@latest/icons/github/github-original.svg" alt="GitHub" class="tech-logo">
                <h4 class="tech-name">GitHub</h4>
                <div class="tech-ver">VCS</div>
                <div class="tech-link">Ver más <i class="fas fa-external-link-alt"></i></div>
            </a>
        </div>
    </div>

</body>
</html>
