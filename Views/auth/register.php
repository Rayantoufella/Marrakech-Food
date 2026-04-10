<?php ?>

<!doctype html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="description" content="Créez votre compte Marrakech Food Lovers et rejoignez la communauté culinaire marocaine.">
    <title>Créer un compte – Marrakech Food Lovers</title>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        /* ══════════════════════════════════════════
           RESET & ROOT VARIABLES
        ══════════════════════════════════════════ */
        *, *::before, *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        :root {
            --clr-primary:      #C0522B;
            --clr-primary-dark: #9A3D1C;
            --clr-primary-light:#E8795A;
            --clr-gold:         #D4A853;
            --clr-gold-light:   #F0C878;
            --clr-bg:           #FAF7F2;
            --clr-surface:      #FFFFFF;
            --clr-text:         #1A1208;
            --clr-muted:        #7A6652;
            --clr-border:       #E8DDD0;
            --clr-input-bg:     #F5F0E8;

            --radius-sm:  8px;
            --radius-md:  14px;
            --radius-lg:  24px;
            --radius-xl:  40px;

            --shadow-card:  0 20px 60px rgba(0,0,0,.12);
            --shadow-btn:   0 8px 24px rgba(192,82,43,.40);
            --shadow-input: 0 2px 8px rgba(192,82,43,.10);

            --transition: .25s cubic-bezier(.4,0,.2,1);
        }

        html, body {
            height: 100%;
            font-family: 'Inter', sans-serif;
            background: var(--clr-bg);
            color: var(--clr-text);
        }

        /* ══════════════════════════════════════════
           PAGE LAYOUT – two columns
        ══════════════════════════════════════════ */
        .page-wrapper {
            display: flex;
            min-height: 100vh;
        }

        /* ── LEFT PANEL ── */
        .left-panel {
            flex: 1 1 55%;
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
        }

        .left-panel__bg {
            position: absolute;
            inset: 0;
            background-image: url('../img/Marrakesh.png');
            background-size: cover;
            background-position: center top;
            transform: scale(1.04);
            transition: transform 8s ease;
            filter: brightness(.82) saturate(1.1);
        }

        .left-panel:hover .left-panel__bg {
            transform: scale(1);
        }

        /* Decorative Moroccan geometric overlay */
        .left-panel__pattern {
            position: absolute;
            inset: 0;
            background-image:
                repeating-linear-gradient(
                    45deg,
                    rgba(212,168,83,.06) 0px,
                    rgba(212,168,83,.06) 1px,
                    transparent 1px,
                    transparent 28px
                ),
                repeating-linear-gradient(
                    -45deg,
                    rgba(212,168,83,.06) 0px,
                    rgba(212,168,83,.06) 1px,
                    transparent 1px,
                    transparent 28px
                );
        }

        /* Dark gradient from bottom */
        .left-panel::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(
                180deg,
                transparent 30%,
                rgba(10,5,2,.65) 100%
            );
        }

        /* Brand badge top-left */
        .brand-badge {
            position: absolute;
            top: 32px;
            left: 36px;
            z-index: 5;
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
        }

        .brand-badge__icon {
            width: 44px;
            height: 44px;
            background: var(--clr-primary);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 16px rgba(192,82,43,.5);
        }

        .brand-badge__icon i {
            color: #fff;
            font-size: 18px;
        }

        .brand-badge__text {
            display: flex;
            flex-direction: column;
        }

        .brand-badge__name {
            font-family: 'Playfair Display', serif;
            font-size: 15px;
            font-weight: 700;
            color: #fff;
            line-height: 1.1;
            text-shadow: 0 2px 8px rgba(0,0,0,.6);
        }

        .brand-badge__tagline {
            font-size: 10px;
            color: var(--clr-gold-light);
            letter-spacing: .8px;
            text-transform: uppercase;
            text-shadow: 0 1px 6px rgba(0,0,0,.7);
        }

        /* Hero content at bottom */
        .left-panel__content {
            position: relative;
            z-index: 5;
            padding: 44px 48px;
        }

        .hero-tag {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(212,168,83,.22);
            border: 1px solid rgba(212,168,83,.45);
            backdrop-filter: blur(8px);
            color: var(--clr-gold-light);
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 1.4px;
            text-transform: uppercase;
            padding: 5px 14px;
            border-radius: 40px;
            margin-bottom: 18px;
        }

        .hero-tag i { font-size: 10px; }

        .hero-title {
            font-family: 'Playfair Display', serif;
            font-size: clamp(34px, 4vw, 54px);
            font-weight: 700;
            color: #fff;
            line-height: 1.12;
            text-shadow: 0 3px 18px rgba(0,0,0,.55);
            margin-bottom: 14px;
        }

        .hero-title span {
            background: linear-gradient(120deg, var(--clr-gold-light), var(--clr-primary-light));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .hero-subtitle {
            font-size: 15px;
            color: rgba(255,255,255,.80);
            max-width: 380px;
            line-height: 1.65;
            margin-bottom: 28px;
        }

        /* Stat counters */
        .hero-stats {
            display: flex;
            gap: 32px;
        }

        .stat {
            display: flex;
            flex-direction: column;
        }

        .stat__number {
            font-family: 'Playfair Display', serif;
            font-size: 26px;
            font-weight: 700;
            color: var(--clr-gold-light);
            line-height: 1;
        }

        .stat__label {
            font-size: 11px;
            color: rgba(255,255,255,.65);
            text-transform: uppercase;
            letter-spacing: .8px;
            margin-top: 4px;
        }

        /* Decorative gold divider above stats */
        .hero-divider {
            width: 50px;
            height: 2px;
            background: linear-gradient(90deg, var(--clr-gold), transparent);
            margin-bottom: 22px;
            border-radius: 4px;
        }

        /* ── RIGHT PANEL ── */
        .right-panel {
            flex: 0 0 480px;
            background: var(--clr-surface);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 48px 52px;
            position: relative;
            overflow: hidden;
        }

        /* Subtle Moroccan star watermark */
        .right-panel::before {
            content: '✦';
            position: absolute;
            bottom: -30px;
            right: -30px;
            font-size: 220px;
            color: var(--clr-border);
            opacity: .4;
            pointer-events: none;
            line-height: 1;
        }

        .right-panel::after {
            content: '✦';
            position: absolute;
            top: -40px;
            left: -40px;
            font-size: 160px;
            color: var(--clr-border);
            opacity: .25;
            pointer-events: none;
            line-height: 1;
        }

        .form-container {
            width: 100%;
            max-width: 380px;
            position: relative;
            z-index: 1;
        }

        /* Form header */
        .form-header {
            margin-bottom: 36px;
        }

        .form-header__eyebrow {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 10px;
        }

        .form-header__eyebrow span {
            display: block;
            width: 28px;
            height: 2px;
            background: var(--clr-primary);
            border-radius: 2px;
        }

        .form-header__eyebrow p {
            font-size: 12px;
            font-weight: 600;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            color: var(--clr-primary);
        }

        .form-header h1 {
            font-family: 'Playfair Display', serif;
            font-size: 30px;
            font-weight: 700;
            color: var(--clr-text);
            line-height: 1.2;
            margin-bottom: 7px;
        }

        .form-header__sub {
            font-size: 14px;
            color: var(--clr-muted);
        }

        .form-header__sub a {
            color: var(--clr-primary);
            text-decoration: none;
            font-weight: 500;
            border-bottom: 1px solid transparent;
            transition: border-color var(--transition);
        }

        .form-header__sub a:hover {
            border-bottom-color: var(--clr-primary);
        }

        /* Form fields */
        .form-group {
            margin-bottom: 18px;
        }

        .form-group label {
            display: block;
            font-size: 13px;
            font-weight: 500;
            color: var(--clr-text);
            margin-bottom: 7px;
            letter-spacing: .2px;
        }

        .input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-wrapper .input-icon {
            position: absolute;
            left: 15px;
            color: var(--clr-muted);
            font-size: 14px;
            pointer-events: none;
            transition: color var(--transition);
        }

        .input-wrapper input {
            width: 100%;
            height: 50px;
            border: 1.8px solid var(--clr-border);
            border-radius: var(--radius-sm);
            background: var(--clr-input-bg);
            padding: 0 44px 0 42px;
            font-family: 'Inter', sans-serif;
            font-size: 14px;
            color: var(--clr-text);
            outline: none;
            transition: border-color var(--transition), background var(--transition), box-shadow var(--transition);
        }

        .input-wrapper input::placeholder {
            color: #B8A898;
            font-size: 13px;
        }

        .input-wrapper input:focus {
            border-color: var(--clr-primary);
            background: #fff;
            box-shadow: var(--shadow-input);
        }

        .input-wrapper input:focus + .input-icon,
        .input-wrapper:focus-within .input-icon {
            color: var(--clr-primary);
        }

        /* toggle password button */
        .toggle-password {
            position: absolute;
            right: 13px;
            background: none;
            border: none;
            cursor: pointer;
            color: var(--clr-muted);
            font-size: 14px;
            padding: 4px 6px;
            border-radius: 4px;
            transition: color var(--transition), background var(--transition);
        }

        .toggle-password:hover {
            color: var(--clr-primary);
            background: rgba(192,82,43,.08);
        }

        /* Error messages */
        .field-error {
            font-size: 12px;
            color: #dc2626;
            margin-top: 5px;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        /* Password strength bar */
        .strength-bar {
            display: flex;
            gap: 4px;
            margin-top: 8px;
        }

        .strength-bar__seg {
            flex: 1;
            height: 3px;
            border-radius: 10px;
            background: var(--clr-border);
            transition: background .3s;
        }

        .strength-label {
            font-size: 11px;
            color: var(--clr-muted);
            margin-top: 5px;
        }

        /* Submit button */
        .submit-btn {
            width: 100%;
            height: 52px;
            background: linear-gradient(135deg, var(--clr-primary) 0%, var(--clr-primary-dark) 100%);
            color: #fff;
            border: none;
            border-radius: var(--radius-sm);
            font-family: 'Inter', sans-serif;
            font-size: 15px;
            font-weight: 600;
            letter-spacing: .8px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-top: 6px;
            box-shadow: var(--shadow-btn);
            transition: transform var(--transition), box-shadow var(--transition), background var(--transition);
            position: relative;
            overflow: hidden;
        }

        .submit-btn::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(255,255,255,.12), transparent);
            opacity: 0;
            transition: opacity var(--transition);
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 14px 32px rgba(192,82,43,.5);
        }

        .submit-btn:hover::after {
            opacity: 1;
        }

        .submit-btn:active {
            transform: translateY(0);
        }

        /* Divider */
        .divider {
            display: flex;
            align-items: center;
            gap: 12px;
            margin: 22px 0;
        }

        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: var(--clr-border);
        }

        .divider span {
            font-size: 12px;
            color: var(--clr-muted);
            white-space: nowrap;
        }

        /* Bottom sign-in link */
        .signin-link {
            text-align: center;
            font-size: 13.5px;
            color: var(--clr-muted);
            margin-top: 20px;
        }

        .signin-link a {
            color: var(--clr-primary);
            text-decoration: none;
            font-weight: 600;
            transition: color var(--transition);
        }

        .signin-link a:hover {
            color: var(--clr-primary-dark);
            text-decoration: underline;
        }

        /* Scroll indicator dots */
        .dots {
            display: flex;
            justify-content: center;
            gap: 6px;
            margin-top: 28px;
        }

        .dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: var(--clr-border);
        }

        .dot.active {
            background: var(--clr-primary);
            width: 18px;
            border-radius: 10px;
        }

        /* Close button for modal-like style */
        .close-btn {
            position: absolute;
            top: 20px;
            right: 20px;
            width: 34px;
            height: 34px;
            background: var(--clr-input-bg);
            border: 1.5px solid var(--clr-border);
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--clr-muted);
            font-size: 14px;
            transition: background var(--transition), color var(--transition);
            text-decoration: none;
            z-index: 10;
        }

        .close-btn:hover {
            background: var(--clr-primary);
            color: #fff;
            border-color: var(--clr-primary);
        }

        /* ══════════════════════════════════════════
           RESPONSIVE
        ══════════════════════════════════════════ */
        @media (max-width: 900px) {
            .page-wrapper { flex-direction: column; }

            .left-panel {
                flex: 0 0 280px;
                min-height: 280px;
            }

            .right-panel {
                flex: 1 0 auto;
                padding: 36px 28px 48px;
            }
        }

        @media (max-width: 480px) {
            .right-panel { padding: 28px 20px 40px; }
            .hero-stats   { gap: 20px; }
        }

        /* ══════════════════════════════════════════
           ANIMATIONS
        ══════════════════════════════════════════ */
        @keyframes slideUp {
            from { opacity: 0; transform: translateY(22px); }
            to   { opacity: 1; transform: translateY(0);    }
        }

        .form-container {
            animation: slideUp .55s cubic-bezier(.4,0,.2,1) both;
        }

        .left-panel__content {
            animation: slideUp .7s .2s cubic-bezier(.4,0,.2,1) both;
        }
    </style>
</head>
<body>

<div class="page-wrapper">

    <!-- ════════════ LEFT PANEL ════════════ -->
    <div class="left-panel">
        <div class="left-panel__bg"></div>
        <div class="left-panel__pattern"></div>

        <!-- Brand badge -->
        <a href="#" class="brand-badge">
            <div class="brand-badge__icon">
                <i class="fas fa-utensils"></i>
            </div>
            <div class="brand-badge__text">
                <span class="brand-badge__name">Marrakech Food Lovers</span>
                <span class="brand-badge__tagline">Partagez vos recettes avec passion</span>
            </div>
        </a>

        <!-- Hero content -->
        <div class="left-panel__content">
            <div class="hero-tag">
                <i class="fas fa-star"></i>
                Cuisine Marocaine Authentique
            </div>

            <h2 class="hero-title">
                L'Art de <span>Vivre</span><br>à Marrakech
            </h2>

            <p class="hero-subtitle">
                Découvrez les secrets de la cuisine ancestrale de Marrakech — des recettes transmises de génération en génération.
            </p>

            <div class="hero-divider"></div>

            <div class="hero-stats">
                <div class="stat">
                    <span class="stat__number">500+</span>
                    <span class="stat__label">Recettes</span>
                </div>
                <div class="stat">
                    <span class="stat__number">12K</span>
                    <span class="stat__label">Membres</span>
                </div>
                <div class="stat">
                    <span class="stat__number">50+</span>
                    <span class="stat__label">Catégories</span>
                </div>
            </div>
        </div>
    </div>

    <!-- ════════════ RIGHT PANEL ════════════ -->
    <div class="right-panel">

        <!-- Close button (optional back link) -->
        <a href="#" class="close-btn" title="Retour">
            <i class="fas fa-times"></i>
        </a>

        <div class="form-container">
            <!-- Header -->
            <div class="form-header">
                <div class="form-header__eyebrow">
                    <span></span>
                    <p>Nouveau compte</p>
                </div>
                <h1>Créer un compte</h1>
                <p class="form-header__sub">
                    Rejoignez <a href="#">notre communauté culinaire</a>
                </p>
            </div>

            <!-- Registration Form -->
            <form id="register-form" action="../public/index.php?action=register_submit" method="POST" novalidate>

                <!-- Nom complet -->
                <div class="form-group">
                    <label for="name">Nom complet</label>
                    <div class="input-wrapper">
                        <i class="fas fa-user input-icon"></i>
                        <input
                            type="text"
                            id="name"
                            name="name"
                            placeholder="Votre nom complet"
                            autocomplete="name"
                            required
                        >
                    </div>
                </div>

                <!-- Email -->
                <div class="form-group">
                    <label for="email">Adresse e-mail</label>
                    <div class="input-wrapper">
                        <i class="fas fa-envelope input-icon"></i>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            placeholder="exemple@domaine.com"
                            autocomplete="email"
                            required
                        >
                    </div>
                </div>

                <!-- Mot de passe -->
                <div class="form-group">
                    <label for="password">Mot de passe</label>
                    <div class="input-wrapper">
                        <i class="fas fa-lock input-icon"></i>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            placeholder="••••••••"
                            autocomplete="new-password"
                            required
                        >
                        <button type="button" class="toggle-password" aria-label="Afficher le mot de passe">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>

                <!-- Confirmer le mot de passe -->
                <div class="form-group">
                    <label for="confirmation">Confirmer le mot de passe</label>
                    <div class="input-wrapper">
                        <i class="fas fa-lock input-icon"></i>
                        <input
                            type="password"
                            id="confirmation"
                            name="confirmation"
                            placeholder="••••••••"
                            autocomplete="new-password"
                            required
                        >
                        <button type="button" class="toggle-password" aria-label="Afficher le mot de passe">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>

                <!-- Submit -->
                <button type="submit" id="submit-btn" class="submit-btn">
                    S'inscrire
                    <i class="fas fa-arrow-right"></i>
                </button>

            </form>

            <!-- Sign-in link -->
            <p class="signin-link">
                Déjà un compte ? <a href="login.php">Se connecter</a>
            </p>

            <!-- Decorative dots -->
            <div class="dots">
                <div class="dot active"></div>
                <div class="dot"></div>
                <div class="dot"></div>
            </div>

        </div><!-- /.form-container -->
    </div><!-- /.right-panel -->

</div><!-- /.page-wrapper -->


</body>
</html>

