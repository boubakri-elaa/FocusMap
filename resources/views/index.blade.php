<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>FocusMap - Visualise Tes Ambitions</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #fffaf0; /* Linen */
            color: #333;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            overflow-x: hidden; /* Empêche le défilement horizontal */
        }

        .header {
            background: linear-gradient(135deg, #ff6f61cc, #ffa07acc); /* Corail semi-transparent */
            color: white;
            text-align: center;
            padding: 80px 20px;
            position: relative;
            overflow: hidden;
        }

        .header h1 {
            font-size: 3.5em;
            margin-bottom: 20px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
            letter-spacing: 1.5px;
        }

        .header p {
            font-size: 1.5em;
            margin-bottom: 30px;
            font-weight: 300;
            opacity: 0.9;
        }

        .cta-buttons a {
            display: inline-block;
            padding: 15px 35px;
            margin: 10px;
            border-radius: 10px;
            font-weight: bold;
            text-decoration: none;
            transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .cta-buttons .register-btn {
            background-color: #ff3e3e; /* Rouge tomate */
            color: white;
            border: 2px solid #ff3e3e;
        }

        .cta-buttons .register-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
        }

        .cta-buttons .login-btn {
            background-color: #ff69b4; /* Rose vif */
            color: white;
            border: 2px solid #ff69b4;
        }

        .cta-buttons .login-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
        }

        .features {
            padding: 80px 20px;
            text-align: center;
            background-color: #fff;
            box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.05);
        }

        .features h2 {
            color: #ff6f61; /* Corail */
            font-size: 2.8em;
            margin-bottom: 50px;
            letter-spacing: 1px;
        }

        .feature-item {
            margin-bottom: 60px;
        }

        .feature-item h3 {
            color: #ff3e3e; /* Rouge tomate */
            margin-top: 30px;
            margin-bottom: 15px;
            font-size: 1.8em;
        }

        .feature-item p {
            color: #555;
            font-size: 1.1em;
            line-height: 1.7;
        }

        .animated-icon {
            font-size: 4em;
            color: #ffa07a; /* Saumon clair */
            /* Styles initiaux pour l'animation (seront manipulés par JS) */
            opacity: 0;
            transform: translateY(20px);
            transition: opacity 0.5s ease, transform 0.5s ease;
        }

        .feature-item.animated .animated-icon {
            opacity: 1;
            transform: translateY(0);
        }

        .footer {
            background-color: #f8f8f8;
            padding: 30px 20px;
            text-align: center;
            color: #777;
            font-size: 0.95em;
            border-top: 1px solid #eee;
        }

        /* Effet de vagues en arrière-plan du header */
        .wave {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 80px;
            background: url('data:image/svg+xml,%3Csvg viewBox="0 24 150 28%22 preserveAspectRatio="none" stroke="%23ffa07a" stroke-width="2" fill="none"%3E%3Cpath d="M-23.71 27.29C9.35 12.23 30.27 4.76 54.71 5.79C79.15 6.82 103.13 12.6 122.03 24.56C140.93 36.52 166.8 49.32 193.34 48.59C219.88 47.85 244.86 35.4 269.9 35.4C295 35.4 321.58 47.85 348.16 48.59C374.74 49.32 400.61 36.52 419.51 24.56C438.41 12.6 462.39 6.82 486.83 5.79C511.27 4.76 532.19 12.23 565.29 27.29L598.4 52H-23.71Z" /%3E%3C/svg%3E') repeat-x bottom;
            animation: wave 5s cubic-bezier( 0.36, 0.45, 0.63, 0.53) infinite;
            opacity: 0.6;
        }

        @keyframes wave {
            0% { background-position: 0 bottom; }
            100% { background-position: 600px bottom; }
        }
    </style>
</head>
<body>
    <header class="header">
    <div class="container">
    
    <h1>FocusMap : Visualise Tes Ambitions, Conquiers Tes Objectifs.</h1>
    <p>Un planner intelligent, une mindmap visuelle et un suivi de motivation, tout en un.</p>
    <div class="cta-buttons">
        <a href="{{ route('register') }}" class="btn btn-primary register-btn">S'inscrire</a>
        <a href="/login" class="login-btn">Se connecter</a>
    </div>
</div>
        <div class="wave"></div>
    </header>

    <section class="features">
        <div class="container">
            <h2>Découvrez la Puissance de FocusMap</h2>
            <div class="row">
                <div class="col-md-4 feature-item">
                    <div class="animated-icon" data-animation="mindmap-3d">🧠</div>
                    <h3>Visualisation Intuitive</h3>
                    <p>Créez des cartes mentales interactives pour organiser vos objectifs et leurs étapes avec une animation 3D captivante.</p>
                </div>
                <div class="col-md-4 feature-item">
                    <div class="animated-icon" data-animation="map-pin-globe">📍</div>
                    <h3>Localisation de Vos Ambitions</h3>
                    <p>Situez vos objectifs sur une carte du monde animée, qu'ils soient physiques ou symboliques.</p>
                </div>
                <div class="col-md-4 feature-item">
                    <div class="animated-icon" data-animation="progress-bar-dynamic">📊</div>
                    <h3>Suivi Personnalisé</h3>
                    <p>Monitorez votre avancement avec des barres de progression dynamiques et des visualisations animées.</p>
                </div>
            </div>
            <div class="row mt-5">
                <div class="col-md-6 feature-item">
                    <div class="animated-icon" data-animation="timeline-scroll">⏳</div>
                    <h3>Timeline de Motivation</h3>
                    <p>Visualisez votre parcours dans une timeline animée qui célèbre vos progrès et vous motive.</p>
                </div>
                <div class="col-md-6 feature-item">
                    <div class="animated-icon" data-animation="share-connect-3d">🤝</div>
                    <h3>Partage et Collaboration (Optionnel)</h3>
                    <p>Partagez vos objectifs et collaborez avec des amis grâce à des animations 3D interactives.</p>
                </div>
            </div>
        </div>
    </section>

    <footer class="footer">
        <div class="container">
            <p>&copy; 2025 FocusMap - Visualise Tes Ambitions</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const observer = new IntersectionObserver((entries, observer) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('animated');
                        observer.unobserve(entry.target);
                    }
                });
            }, {
                threshold: 0.5 // Déclencher l'animation quand 50% de l'élément est visible
            });

            document.querySelectorAll('.feature-item').forEach(item => {
                observer.observe(item);
            });

            // Vous pouvez ajouter ici l'initialisation de vos librairies d'animation JS
            // par exemple, pour des animations plus complexes basées sur les attributs data-animation
        });
    </script>
</body>
</html>