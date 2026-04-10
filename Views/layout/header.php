<?php
// Logique PHP à traiter par l'utilisateur
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Tableau de bord - Marrakech Food Lovers</title>

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
            --clr-sidebar-bg:   #2A1F1B;
            --clr-sidebar-hover:#3D2E27;

            --radius-sm:  8px;
            --radius-md:  14px;
            --radius-lg:  24px;

            --shadow-card:  0 20px 60px rgba(0,0,0,.12);
            --shadow-btn:   0 8px 24px rgba(192,82,43,.40);

            --transition: .25s cubic-bezier(.4,0,.2,1);
        }

        html, body {
            height: 100%;
            font-family: 'Inter', sans-serif;
            background: var(--clr-bg);
            color: var(--clr-text);
        }

        /* ══════════════════════════════════════════
           MAIN LAYOUT
        ══════════════════════════════════════════ */
        .dashboard-wrapper {
            display: flex;
            min-height: 100vh;
        }

        /* ── SIDEBAR ── */
        .sidebar {
            width: 240px;
            background: var(--clr-sidebar-bg);
            color: #fff;
            padding: 24px 0;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            box-shadow: 2px 0 8px rgba(0,0,0,.15);
        }

        .sidebar::-webkit-scrollbar {
            width: 6px;
        }

        .sidebar::-webkit-scrollbar-track {
            background: rgba(255,255,255,.05);
        }

        .sidebar::-webkit-scrollbar-thumb {
            background: rgba(255,255,255,.2);
            border-radius: 3px;
        }

        /* Brand logo */
        .sidebar-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 0 20px;
            margin-bottom: 36px;
            text-decoration: none;
            color: #fff;
        }

        .sidebar-brand__icon {
            width: 44px;
            height: 44px;
            background: var(--clr-primary);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            box-shadow: 0 4px 12px rgba(192,82,43,.3);
        }

        .sidebar-brand__icon i {
            color: #fff;
            font-size: 20px;
        }

        .sidebar-brand__text {
            display: flex;
            flex-direction: column;
        }

        .sidebar-brand__name {
            font-family: 'Playfair Display', serif;
            font-size: 14px;
            font-weight: 700;
            line-height: 1.2;
        }

        .sidebar-brand__tagline {
            font-size: 10px;
            color: rgba(255,255,255,.6);
            text-transform: uppercase;
            letter-spacing: .5px;
        }

        /* Sidebar sections */
        .sidebar-section {
            margin-bottom: 28px;
        }

        .sidebar-section__title {
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: rgba(255,255,255,.5);
            padding: 0 20px;
            margin-bottom: 12px;
        }

        .sidebar-nav {
            list-style: none;
        }

        .sidebar-nav__item {
            position: relative;
        }

        .sidebar-nav__link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 20px;
            color: rgba(255,255,255,.75);
            text-decoration: none;
            font-size: 14px;
            transition: color var(--transition), background var(--transition);
            position: relative;
        }

        .sidebar-nav__link:hover {
            background: var(--clr-sidebar-hover);
            color: #fff;
        }

        .sidebar-nav__link.active {
            color: var(--clr-primary);
            background: rgba(192,82,43,.1);
            border-left: 3px solid var(--clr-primary);
            padding-left: 17px;
        }

        .sidebar-nav__icon {
            width: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
        }

        /* Badge for counters */
        .sidebar-nav__badge {
            margin-left: auto;
            background: var(--clr-primary);
            color: #fff;
            font-size: 11px;
            font-weight: 600;
            padding: 3px 8px;
            border-radius: 12px;
            min-width: 24px;
            text-align: center;
        }

        /* User profile at bottom */
        .sidebar-user {
            position: absolute;
            bottom: 20px;
            left: 0;
            right: 0;
            padding: 0 20px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .sidebar-user__avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--clr-primary), var(--clr-primary-light));
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-weight: 600;
            font-size: 14px;
            flex-shrink: 0;
        }

        .sidebar-user__info {
            flex: 1;
        }

        .sidebar-user__name {
            font-size: 13px;
            font-weight: 600;
            color: #fff;
            line-height: 1.2;
        }

        .sidebar-user__role {
            font-size: 11px;
            color: rgba(255,255,255,.6);
        }

        .sidebar-user__menu {
            background: none;
            border: none;
            color: rgba(255,255,255,.6);
            cursor: pointer;
            font-size: 14px;
            padding: 0;
            transition: color var(--transition);
        }

        .sidebar-user__menu:hover {
            color: #fff;
        }

        /* ── MAIN CONTENT ── */
        .main-content {
            margin-left: 240px;
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        /* ── TOP HEADER ── */
        .top-header {
            background: var(--clr-surface);
            border-bottom: 1px solid var(--clr-border);
            padding: 20px 32px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 24px;
            box-shadow: 0 2px 8px rgba(0,0,0,.05);
        }

        .header-title {
            display: flex;
            align-items: baseline;
            gap: 12px;
        }

        .header-title__main {
            font-family: 'Playfair Display', serif;
            font-size: 28px;
            font-weight: 700;
            color: var(--clr-text);
        }

        .header-title__sub {
            font-size: 13px;
            color: var(--clr-muted);
        }

        .header-actions {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        /* Search bar */
        .search-bar {
            position: relative;
            width: 280px;
        }

        .search-bar input {
            width: 100%;
            height: 40px;
            border: 1.5px solid var(--clr-border);
            border-radius: var(--radius-md);
            background: #f9f7f4;
            padding: 0 16px 0 36px;
            font-size: 13px;
            color: var(--clr-text);
            outline: none;
            transition: border-color var(--transition), background var(--transition);
        }

        .search-bar input::placeholder {
            color: var(--clr-muted);
        }

        .search-bar input:focus {
            border-color: var(--clr-primary);
            background: #fff;
        }

        .search-bar__icon {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--clr-muted);
            font-size: 14px;
            pointer-events: none;
        }

        /* Notification bell */
        .notification-btn {
            position: relative;
            background: none;
            border: none;
            cursor: pointer;
            font-size: 18px;
            color: var(--clr-muted);
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: color var(--transition), background var(--transition);
        }

        .notification-btn:hover {
            color: var(--clr-primary);
            background: rgba(192,82,43,.08);
        }

        .notification-btn__badge {
            position: absolute;
            top: 8px;
            right: 8px;
            width: 20px;
            height: 20px;
            background: #ef4444;
            color: #fff;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 10px;
            font-weight: 600;
        }

        /* Primary button */
        .btn-primary {
            background: var(--clr-primary);
            color: #fff;
            border: none;
            padding: 10px 24px;
            border-radius: var(--radius-sm);
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: background var(--transition), box-shadow var(--transition);
            box-shadow: var(--shadow-btn);
        }

        .btn-primary:hover {
            background: var(--clr-primary-dark);
            box-shadow: 0 12px 32px rgba(192,82,43,.5);
        }

        .btn-primary i {
            font-size: 14px;
        }

        /* ══════════════════════════════════════════
           RESPONSIVE
        ══════════════════════════════════════════ */
        @media (max-width: 768px) {
            .sidebar {
                width: 200px;
                z-index: 1000;
                left: -200px;
                transition: left var(--transition);
            }

            .sidebar.active {
                left: 0;
            }

            .main-content {
                margin-left: 0;
            }

            .top-header {
                padding: 16px 20px;
            }

            .search-bar {
                width: 200px;
            }

            .header-title__main {
                font-size: 22px;
            }
        }

        @media (max-width: 480px) {
            .search-bar {
                display: none;
            }

            .header-actions {
                gap: 12px;
            }

            .btn-primary {
                padding: 10px 16px;
                font-size: 12px;
            }
        }
    </style>
</head>
<body>

<div class="dashboard-wrapper">

    <!-- ════════════ SIDEBAR ════════════ -->
    <aside class="sidebar">
        <a href="#" class="sidebar-brand">
            <div class="sidebar-brand__icon">
                <i class="fas fa-utensils"></i>
            </div>
            <div class="sidebar-brand__text">
                <div class="sidebar-brand__name">Marrakech Food Lovers</div>
                <div class="sidebar-brand__tagline">Digitalité Agency</div>
            </div>
        </a>

        <!-- Principal section -->
        <section class="sidebar-section">
            <h3 class="sidebar-section__title">Principal</h3>
            <ul class="sidebar-nav">
                <li class="sidebar-nav__item">
                    <a href="#" class="sidebar-nav__link active">
                        <span class="sidebar-nav__icon"><i class="fas fa-chart-line"></i></span>
                        <span>Tableau de bord</span>
                    </a>
                </li>
                <li class="sidebar-nav__item">
                    <a href="#" class="sidebar-nav__link">
                        <span class="sidebar-nav__icon"><i class="fas fa-book"></i></span>
                        <span>Mes recettes</span>
                        <span class="sidebar-nav__badge">12</span>
                    </a>
                </li>
                <li class="sidebar-nav__item">
                    <a href="#" class="sidebar-nav__link">
                        <span class="sidebar-nav__icon"><i class="fas fa-plus"></i></span>
                        <span>Ajouter une recette</span>
                    </a>
                </li>
            </ul>
        </section>

        <!-- Organisation section -->
        <section class="sidebar-section">
            <h3 class="sidebar-section__title">Organisation</h3>
            <ul class="sidebar-nav">
                <li class="sidebar-nav__item">
                    <a href="#" class="sidebar-nav__link">
                        <span class="sidebar-nav__icon"><i class="fas fa-folder"></i></span>
                        <span>Catégories</span>
                    </a>
                </li>
                <li class="sidebar-nav__item">
                    <a href="#" class="sidebar-nav__link">
                        <span class="sidebar-nav__icon"><i class="fas fa-star"></i></span>
                        <span>Favoris</span>
                    </a>
                </li>
            </ul>
        </section>

        <!-- Compte section -->
        <section class="sidebar-section">
            <h3 class="sidebar-section__title">Compte</h3>
            <ul class="sidebar-nav">
                <li class="sidebar-nav__item">
                    <a href="#" class="sidebar-nav__link">
                        <span class="sidebar-nav__icon"><i class="fas fa-user"></i></span>
                        <span>Mon profil</span>
                    </a>
                </li>
                <li class="sidebar-nav__item">
                    <a href="#" class="sidebar-nav__link">
                        <span class="sidebar-nav__icon"><i class="fas fa-cog"></i></span>
                        <span>Paramètres</span>
                    </a>
                </li>
            </ul>
        </section>

        <!-- User profile at bottom -->
        <div class="sidebar-user">
            <div class="sidebar-user__avatar">AK</div>
            <div class="sidebar-user__info">
                <div class="sidebar-user__name">Ahmed Karimi</div>
                <div class="sidebar-user__role">Chef Amateurs</div>
            </div>
            <button class="sidebar-user__menu" title="Menu">
                <i class="fas fa-ellipsis-v"></i>
            </button>
        </div>
    </aside>

    <!-- ════════════ MAIN CONTENT ════════════ -->
    <div class="main-content">

        <!-- ── TOP HEADER ── -->
        <header class="top-header">
            <div class="header-title">
                <h1 class="header-title__main">Tableau de bord</h1>
                <p class="header-title__sub">Bienvenue, Ahmed — voici votre espace culinaire</p>
            </div>

            <div class="header-actions">
                <div class="search-bar">
                    <i class="fas fa-search search-bar__icon"></i>
                    <input type="text" placeholder="Rechercher une recette...">
                </div>

                <button class="notification-btn">
                    <i class="fas fa-bell"></i>
                    <span class="notification-btn__badge">3</span>
                </button>

                <button class="btn-primary">
                    <i class="fas fa-plus"></i>
                    Nouvelle recette
                </button>
            </div>
        </header>

        <!-- Main content area (à remplir) -->
        <main style="flex: 1; padding: 32px;">
            <!-- Le contenu principal sera inséré ici -->
        </main>

    </div>

</div>

</body>
</html>
