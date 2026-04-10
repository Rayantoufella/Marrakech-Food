<?php
session_start();

// Inclure les modèles
require_once __DIR__ . '/../../Models/DB.php';
require_once __DIR__ . '/../../Models/user.php';

$error = '';
$success = '';

// Vérifier si le formulaire est soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // Validation
    if (empty($email)) {
        $error = 'L\'adresse email est requise.';
    } elseif (empty($password)) {
        $error = 'Le mot de passe est requis.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'L\'adresse email n\'est pas valide.';
    } else {
        // Connexion à la base de données
        $db = new DB("localhost", "marrakech_food", "root", "");
        $pdo = $db->getPDO();

        try {
            // Rechercher l'utilisateur par email
            $stmt = $pdo->prepare('SELECT id, name, email, password FROM user WHERE email = :email');
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // Vérifier le mot de passe
            if ($user && password_verify($password, $user['password'])) {
                // Créer la session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];

                // Rediriger vers le tableau de bord
                header('Location: /Marrakech_Food/Views/category/index.php');
                exit();
            } else {
                $error = 'Email ou mot de passe incorrect.';
            }
        } catch (PDOException $e) {
            $error = 'Erreur de connexion à la base de données.';
        }
    }
}
?>

<!doctype html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="description" content="Se connecter à votre compte Marrakech Food Lovers et accédez à votre espace culinaire personnel.">
    <title>Se connecter – Marrakech Food Lovers</title>

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
            background-image: url('../img/terrace.png');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
            opacity: 1;
            z-index: 1;
            filter: brightness(.85) contrast(1.15) saturate(1.25);
        }

        .left-panel:hover .left-panel__bg {
            filter: brightness(.80) contrast(1.2) saturate(1.3);
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

        /* Dark gradient */
        .left-panel::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(
                180deg,
                transparent 25%,
                rgba(10,5,2,.45) 100%
            );
            z-index: 2;
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

        .hero-quote {
            font-family: 'Playfair Display', serif;
            font-size: clamp(22px, 2.5vw, 32px);
            font-weight: 400;
            font-style: italic;
            color: rgba(255,255,255,.90);
            line-height: 1.6;
            text-shadow: 0 3px 18px rgba(0,0,0,.55);
            margin-bottom: 28px;
            max-width: 420px;
        }

        .hero-quote-author {
            font-size: 12px;
            color: rgba(255,255,255,.65);
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-top: 16px;
            position: relative;
            padding-left: 18px;
        }

        .hero-quote-author::before {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            transform: translateY(-50%);
            width: 12px;
            height: 2px;
            background: var(--clr-gold-light);
            border-radius: 2px;
        }

        /* ── RIGHT PANEL ── */
        .right-panel {
            flex: 0 0 480px;
            background: var(--clr-surface);
            display: flex;
            flex-direction: column;
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

        /* Welcome back badge */
        .welcome-back {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(232,121,90,.12);
            border: 1px solid rgba(232,121,90,.25);
            color: var(--clr-primary-light);
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 1px;
            text-transform: uppercase;
            padding: 6px 14px;
            border-radius: 40px;
            margin-bottom: 24px;
        }

        .welcome-back i { font-size: 10px; }

        /* Form header */
        .form-header {
            margin-bottom: 28px;
        }

        .form-header h1 {
            font-family: 'Playfair Display', serif;
            font-size: 32px;
            font-weight: 700;
            color: var(--clr-text);
            line-height: 1.2;
            margin-bottom: 8px;
        }

        .form-header__sub {
            font-size: 14px;
            color: var(--clr-muted);
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

        /* Remember + Forgot password row */
        .form-footer-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin: 16px 0 24px 0;
            gap: 12px;
        }

        .checkbox-wrapper {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .checkbox-wrapper input[type="checkbox"] {
            width: 16px;
            height: 16px;
            cursor: pointer;
            accent-color: var(--clr-primary);
        }

        .checkbox-wrapper label {
            margin: 0;
            font-size: 13px;
            color: var(--clr-muted);
            cursor: pointer;
            user-select: none;
        }

        .forgot-password {
            font-size: 13px;
            font-weight: 500;
            color: var(--clr-primary);
            text-decoration: none;
            transition: color var(--transition);
        }

        .forgot-password:hover {
            color: var(--clr-primary-dark);
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

        /* Bottom sign-up link */
        .signup-link {
            text-align: center;
            font-size: 13.5px;
            color: var(--clr-muted);
            margin-top: 20px;
        }

        .signup-link a {
            color: var(--clr-primary);
            text-decoration: none;
            font-weight: 600;
            transition: color var(--transition);
        }

        .signup-link a:hover {
            color: var(--clr-primary-dark);
            text-decoration: underline;
        }

        /* Community stats */
        .community-stats {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-top: 28px;
            padding-top: 24px;
            border-top: 1px solid var(--clr-border);
        }

        .community-avatars {
            display: flex;
            gap: -8px;
        }

        .community-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--clr-primary), var(--clr-primary-light));
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 12px;
            font-weight: 600;
            border: 2px solid #fff;
            margin-left: -8px;
        }

        .community-avatar:first-child {
            margin-left: 0;
        }

        .community-text {
            font-size: 12px;
            color: var(--clr-muted);
        }

        .community-text strong {
            color: var(--clr-text);
            font-weight: 600;
        }

        .community-stars {
            color: var(--clr-gold);
            font-size: 11px;
            margin-left: 4px;
        }

        /* Back arrow */
        .back-arrow {
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

        .back-arrow:hover {
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

        /* Alert messages */
        .alert-error {
            background: #FEE2E2;
            border: 1px solid #FECACA;
            color: #991B1B;
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 13px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert-success {
            background: #DCFCE7;
            border: 1px solid #BBF7D0;
            color: #166534;
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 13px;
            display: flex;
            align-items: center;
            gap: 10px;
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
                <span class="brand-badge__tagline">Cuisine Authentique</span>
            </div>
        </a>

        <!-- Hero content -->
        <div class="left-panel__content">
            <p class="hero-quote">
                "La cuisine est l'art de transformer les ingrédients en souvenirs"
            </p>
            <p class="hero-quote-author">— Tradition Marocaine</p>
        </div>
    </div>

    <!-- ════════════ RIGHT PANEL ════════════ -->
    <div class="right-panel">

        <!-- Back arrow -->
        <a href="#" class="back-arrow" title="Retour">
            <i class="fas fa-arrow-left"></i>
        </a>

        <div class="form-container">

            <!-- Welcome back badge -->
            <div class="welcome-back">
                <i class="fas fa-fire"></i>
                Bon retour parmi nous
            </div>

            <!-- Header -->
            <div class="form-header">
                <h1>Se connecter</h1>
                <p class="form-header__sub">Retrouvez votre espace culinaire personnel</p>
            </div>

            <!-- Affichage des erreurs/succès -->
            <?php if (!empty($error)): ?>
                <div style="background: #FEE2E2; border: 1px solid #FECACA; color: #991B1B; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; font-size: 13px; display: flex; align-items: center; gap: 10px;">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($success)): ?>
                <div style="background: #DCFCE7; border: 1px solid #BBF7D0; color: #166534; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; font-size: 13px; display: flex; align-items: center; gap: 10px;">
                    <i class="fas fa-check-circle"></i>
                    <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>

            <!-- Login Form -->
            <form id="login-form" action="login.php" method="POST" novalidate>

                <!-- Email -->
                <div class="form-group">
                    <label for="email">Adresse email</label>
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

                <!-- Password -->
                <div class="form-group">
                    <label for="password">Mot de passe</label>
                    <div class="input-wrapper">
                        <i class="fas fa-lock input-icon"></i>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            placeholder="••••••••"
                            autocomplete="current-password"
                            required
                        >
                        <button type="button" class="toggle-password" aria-label="Afficher le mot de passe">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>

                <!-- Remember + Forgot password -->
                <div class="form-footer-row">
                    <div class="checkbox-wrapper">
                        <input type="checkbox" id="remember" name="remember">
                        <label for="remember">Se souvenir de moi</label>
                    </div>
                    <a href="#" class="forgot-password">Mot de passe oublié ?</a>
                </div>

                <!-- Submit -->
                <button type="submit" id="submit-btn" class="submit-btn">
                    Se connecter
                    <i class="fas fa-arrow-right"></i>
                </button>

            </form>

            <!-- Sign-up link -->
            <p class="signup-link">
                Pas encore de compte ? <a href="register.php">Créer un compte</a>
            </p>

            <!-- Community stats -->
            <div class="community-stats">
                <div class="community-avatars">
                    <div class="community-avatar">SJ</div>
                    <div class="community-avatar">MK</div>
                    <div class="community-avatar">AH</div>
                </div>
                <div class="community-text">
                    Rejoignez <strong>2,400+</strong> passionnés de cuisine marocaine
                    <span class="community-stars">★★★★★</span>
                </div>
            </div>

        </div><!-- /.form-container -->
    </div><!-- /.right-panel -->

</div><!-- /.page-wrapper -->

<script>
    // Toggle password visibility
    document.querySelectorAll('.toggle-password').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const input = this.closest('.input-wrapper').querySelector('input');
            const icon = this.querySelector('i');

            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
    });

    // Form validation
    document.getElementById('login-form')?.addEventListener('submit', function(e) {
        const email = document.getElementById('email').value.trim();
        const password = document.getElementById('password').value;

        if (!email) {
            e.preventDefault();
            alert('Veuillez entrer votre adresse email.');
            document.getElementById('email').focus();
            return false;
        }

        if (!password) {
            e.preventDefault();
            alert('Veuillez entrer votre mot de passe.');
            document.getElementById('password').focus();
            return false;
        }
    });
</script>

</body>
</html>
