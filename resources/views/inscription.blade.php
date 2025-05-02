<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Inscription - FocusMap</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #ffe4b3; /* Fond jaune très clair (doux) */
            color: #333; /* Texte sombre */
        }
        .register-container {
            max-width: 500px;
            margin: auto;
            padding: 40px;
            background-color: #fff;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.2);
            border-top: 5px solid #ffb51f; /* Bande jaune vif en haut (attirant) */
        }
        .form-group {
            margin-bottom: 25px;
        }
        .btn-primary {
            background-color: #fe470a; /* Primaire orange/rouge vif (encourageant) */
            border-color: #fe470a;
            color: #fff;
            font-weight: bold;
            padding: 12px 20px;
            border-radius: 8px;
        }
        .btn-primary:hover {
            background-color: #d93600; /* Assombrir au survol */
            border-color: #d93600;
        }
        h2 {
            color: #ff789f; /* Titre rose (attirant) */
            text-align: center;
            margin-bottom: 35px;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.1);
        }
        label {
            color: #555;
            font-weight: bold;
            margin-bottom: 5px;
            display: block;
        }
        .invalid-feedback {
            color: #fe470a; /* Erreurs en orange/rouge vif */
        }
        input.form-control {
            border-radius: 5px;
            padding: 10px;
            border: 1px solid #ddd;
        }
        input.form-control:focus {
            border-color: #fe470a;
            box-shadow: 0 0 5px rgba(254, 71, 10, 0.5);
        }

        /* Nouveaux styles CSS pour un design très joli avec la palette fffaf0, ff6f61, ff3e3e, ffa07a, ff69b4 (similaire à la page de connexion) */
        body {
            background-color: #fffaf0; /* Linen */
            color: #444;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background-image: linear-gradient(135deg, rgba(255, 111, 97, 0.1), rgba(255, 160, 122, 0.1)); /* Dégradé subtil */
            overflow: hidden;
        }

        .register-container {
            max-width: 400px;
            padding: 40px;
            background-color: #fff;
            border-radius: 12px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
            border-top: 4px solid #ff3e3e; /* Rouge tomate pour accent */
            animation: slideDown 0.6s ease-out; /* Animation d'apparition */
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        h2 {
            color: #ff69b4; /* Rose vif pour le titre */
            text-align: center;
            margin-bottom: 30px;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.05);
            letter-spacing: 0.8px;
        }

        .form-group {
            margin-bottom: 20px; /* Légèrement moins de marge */
        }

        label {
            color: #555;
            font-weight: 600;
            margin-bottom: 8px;
            display: block;
        }

        input.form-control {
            border-radius: 6px;
            padding: 10px 12px;
            border: 1px solid #ddd;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }

        input.form-control:focus {
            border-color: #ffa07a; /* Saumon pour le focus */
            box-shadow: 0 0 8px rgba(255, 160, 122, 0.3);
        }

        .invalid-feedback {
            color: #ff3e3e;
            margin-top: 3px;
            font-size: 0.9em;
        }

        .btn-primary {
            background-color: #ff6f61; /* Corail */
            border-color: #ff6f61;
            color: #fff;
            font-weight: 500;
            padding: 12px 25px;
            border-radius: 6px;
            transition: background-color 0.3s ease, transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
        }

        .btn-primary:hover {
            background-color: #ff3e3e; /* Rouge tomate au survol */
            border-color: #ff3e3e;
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
        }

        .mt-3 a {
            color: #ff6f61;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        .mt-3 a:hover {
            color: #ff3e3e;
        }

        /* Animations d'arrière-plan subtiles */
        .bg-animation {
            position: absolute;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            z-index: -1;
        }

        .bg-blob {
            position: absolute;
            background-color: rgba(255, 111, 97, 0.15); /* Rouge tomate transparent */
            border-radius: 50%;
            animation: blobAnimation 6s infinite alternate;
            opacity: 0;
        }

        .bg-blob:nth-child(1) {
            width: 100px;
            height: 100px;
            top: 15%;
            left: 10%;
            animation-delay: 0s;
            opacity: 0.7;
        }

        .bg-blob:nth-child(2) {
            width: 150px;
            height: 150px;
            top: 60%;
            right: 15%;
            animation-delay: 1.5s;
            background-color: rgba(255, 160, 122, 0.15); /* Saumon transparent */
            opacity: 0.6;
        }

        .bg-blob:nth-child(3) {
            width: 80px;
            height: 80px;
            bottom: 10%;
            left: 40%;
            animation-delay: 3s;
            background-color: rgba(255, 105, 180, 0.15); /* Rose vif transparent */
            opacity: 0.5;
        }

        @keyframes blobAnimation {
            0% {
                transform: translate(0, 0) scale(1);
                opacity: 0.7;
            }
            100% {
                transform: translate(30px, -30px) scale(1.1);
                opacity: 0.9;
            }
        }
    </style>
</head>
<body>
    <div class="bg-animation">
        <div class="bg-blob"></div>
        <div class="bg-blob"></div>
        <div class="bg-blob"></div>
    </div>
    <div class="container mt-5">
        <div class="register-container">
            <h2 class="mb-4">Rejoins l'aventure FocusMap !</h2>
            <form method="POST" action="{{ route('register') }}">
                @csrf

                <div class="form-group">
                    <label for="nom" class="form-label">Nom complet</label>
                    <input type="text" id="nom" name="nom" class="form-control @error('nom') is-invalid @enderror" value="{{ old('nom') }}" required autofocus>
                    @error('nom')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="email" class="form-label">Adresse Email</label>
                    <input type="email" id="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" required>
                    @error('email')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="mot_de_passe" class="form-label">Crée ton mot de passe</label>
                    <input type="password" id="mot_de_passe" name="mot_de_passe" class="form-control @error('mot_de_passe') is-invalid @enderror" required>
                    @error('mot_de_passe')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="mot_de_passe_confirmation" class="form-label">Confirme ton mot de passe</label>
                    <input type="password" id="mot_de_passe_confirmation" name="mot_de_passe_confirmation" class="form-control" required>
                </div>

                <button type="submit" class="btn btn-primary btn-lg btn-block">Embarque avec nous !</button>
                <p class="mt-3 text-center text-muted"><small>En rejoignant FocusMap, tu donnes vie à tes objectifs.</small></p>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const registerContainer = document.querySelector('.register-container');
            registerContainer.style.opacity = 0;
            setTimeout(() => {
                registerContainer.style.transition = 'opacity 0.5s ease-out, transform 0.5s ease-out';
                registerContainer.style.opacity = 1;
                registerContainer.style.transform = 'translateY(0)';
            }, 100);
        });
    </script>
</body>
</html>