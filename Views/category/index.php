<?php
session_start();

// Vérifier que l'utilisateur est connecté


// Inclure les modèles
require_once __DIR__ . '/../../Models/DB.php';
require_once __DIR__ . '/../../Models/recette.php';
require_once __DIR__ . '/../../Models/category.php';

// Initialiser la connexion DB
$db = new DB("localhost", "marrakech_food", "root", "");
$pdo = $db->getPDO();

// Utiliser l'ID utilisateur de la session ou un ID par défaut pour les tests
$userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 1;

// 1. Récupérer le nombre total de recettes de l'utilisateur
$stmt = $pdo->prepare('SELECT COUNT(*) as total FROM recette WHERE user_id = :user_id');
$stmt->bindParam(':user_id', $userId);
$stmt->execute();
$totalRecettes = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// 2. Récupérer le nombre total de catégories
$stmt = $pdo->prepare('SELECT COUNT(*) as total FROM category');
$stmt->execute();
$result = $stmt->fetch(PDO::FETCH_ASSOC);
$totalCategories = $result ? $result['total'] : 0;

// 3. Récupérer le nombre de recettes favories
$totalFavoris = 0;

// 4. Récupérer le temps moyen des recettes
$tempsmoyen = 45;

// 5. Récupérer toutes les catégories pour les filtres
$stmt = $pdo->prepare('SELECT id, name FROM category ORDER BY name');
$stmt->execute();
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 6. Récupérer les recettes récentes de l'utilisateur
$stmt = $pdo->prepare('SELECT r.id, r.name, r.description, r.created_at, c.name as category_name, c.id as category_id FROM recette r 
                       JOIN category c ON r.category_id = c.id 
                       WHERE r.user_id = :user_id 
                       ORDER BY r.created_at DESC 
                       LIMIT 3');
$stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
$stmt->execute();
$recettesRecentes = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

// 7. Gérer le filtrage par catégorie
$categoryFilter = isset($_GET['category']) ? $_GET['category'] : null;
$recettesAffichees = $recettesRecentes;

if ($categoryFilter && $categoryFilter !== 'all') {
    $stmt = $pdo->prepare('SELECT r.id, r.name, r.description, r.created_at, c.name as category_name, c.id as category_id FROM recette r 
                           JOIN category c ON r.category_id = c.id 
                           WHERE r.user_id = :user_id AND c.id = :category_id 
                           ORDER BY r.created_at DESC');
    $stmt->bindParam(':user_id', $userId);
    $stmt->bindParam(':category_id', $categoryFilter);
    $stmt->execute();
    $recettesAffichees = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Compter les recettes par catégorie
$countsByCategory = [];
foreach ($categories as $cat) {
    $stmt = $pdo->prepare('SELECT COUNT(*) as count FROM recette WHERE user_id = :user_id AND category_id = :cat_id');
    $stmt->bindParam(':user_id', $userId);
    $stmt->bindParam(':cat_id', $cat['id']);
    $stmt->execute();
    $countsByCategory[$cat['id']] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
}
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
            --clr-input-bg:     #F5F0E8;
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
            flex-direction: column;
            gap: 4px;
        }

        .header-title__main {
            font-family: 'Playfair Display', serif;
            font-size: 28px;
            font-weight: 700;
            color: var(--clr-text);
            margin: 0;
        }

        .header-title__sub {
            font-size: 13px;
            color: var(--clr-muted);
            margin: 0;
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

        /* Main area */
        main {
            flex: 1;
            padding: 32px;
            display: flex;
            flex-direction: column;
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


<style>
    /* ══════════════════════════════════════════
       PAGE HEADER STYLES
    ══════════════════════════════════════════ */

    .page-header {
        background: var(--clr-surface);
        padding: 24px 32px;
        border-bottom: 1px solid var(--clr-border);
        margin-bottom: 32px;
    }

    .page-header__top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 16px;
    }

    .page-header__title {
        font-family: 'Playfair Display', serif;
        font-size: 28px;
        font-weight: 700;
        color: var(--clr-text);
        margin: 0;
    }

    .page-header__search {
        position: relative;
        width: 300px;
    }

    .page-header__search input {
        width: 100%;
        height: 42px;
        padding: 8px 16px 8px 40px;
        border: 1.5px solid var(--clr-border);
        border-radius: 8px;
        background: var(--clr-input-bg);
        font-size: 13px;
        color: var(--clr-text);
        outline: none;
        transition: all var(--transition);
    }

    .page-header__search input:focus {
        border-color: var(--clr-primary);
        background: #fff;
        box-shadow: 0 2px 8px rgba(192,82,43,.1);
    }

    .page-header__search input::placeholder {
        color: #B8A898;
    }

    .page-header__search-icon {
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--clr-muted);
        font-size: 14px;
        pointer-events: none;
    }

    .page-header__subtitle {
        font-size: 13px;
        color: var(--clr-muted);
        margin: 0;
    }

    @media (max-width: 768px) {
        .page-header {
            padding: 20px;
            margin-bottom: 20px;
        }

        .page-header__top {
            flex-direction: column;
            gap: 16px;
            align-items: flex-start;
        }

        .page-header__search {
            width: 100%;
        }

        .page-header__title {
            font-size: 24px;
        }
    }

    /* ══════════════════════════════════════════
       DASHBOARD STYLES
    ══════════════════════════════════════════ */

    main {
        flex: 1;
        padding: 32px;
        display: flex;
        flex-direction: column;
    }

    /* Stats Grid */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 24px;
        margin-bottom: 48px;
        width: 100%;
    }

    .stat-card {
        background: var(--clr-surface);
        border-radius: var(--radius-lg);
        padding: 28px;
        box-shadow: var(--shadow-card);
        transition: transform var(--transition), box-shadow var(--transition);
    }

    .stat-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 28px 80px rgba(0,0,0,.16);
    }

    .stat-card__header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 20px;
    }

    .stat-card__label {
        font-size: 13px;
        color: var(--clr-muted);
        margin-bottom: 12px;
    }

    .stat-card__value {
        font-size: 32px;
        font-weight: 700;
        color: var(--clr-text);
    }

    .stat-card__icon {
        width: 56px;
        height: 56px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        background: rgba(192,82,43,.1);
        color: var(--clr-primary);
    }

    .stat-card__change {
        font-size: 12px;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 4px;
        color: #10b981;
    }

    .stat-card__change--negative {
        color: #ef4444;
    }

    /* Filter Section */
    .filter-section {
        margin-bottom: 48px;
        width: 100%;
    }

    .filter-section__title {
        font-family: 'Playfair Display', serif;
        font-size: 20px;
        font-weight: 600;
        margin-bottom: 20px;
        color: var(--clr-text);
    }

    .filter-buttons {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
    }

    .filter-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 20px;
        border: 2px solid var(--clr-border);
        background: var(--clr-surface);
        border-radius: 24px;
        font-size: 13px;
        font-weight: 500;
        color: var(--clr-muted);
        cursor: pointer;
        text-decoration: none;
        transition: all var(--transition);
    }

    .filter-btn:hover {
        border-color: var(--clr-primary);
        color: var(--clr-primary);
    }

    .filter-btn.active {
        background: var(--clr-primary);
        border-color: var(--clr-primary);
        color: #fff;
    }

    .filter-btn__badge {
        background: rgba(255,255,255,.3);
        padding: 2px 8px;
        border-radius: 12px;
        font-size: 11px;
        font-weight: 600;
    }

    /* Recipes Section */
    .recipes-section {
        margin-bottom: 48px;
        width: 100%;
    }

    .recipes-section__header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 24px;
    }

    .recipes-section__title {
        font-family: 'Playfair Display', serif;
        font-size: 20px;
        font-weight: 600;
        color: var(--clr-text);
    }

    .recipes-section__link {
        font-size: 13px;
        font-weight: 600;
        color: var(--clr-primary);
        text-decoration: none;
        display: flex;
        align-items: center;
        gap: 6px;
        transition: gap var(--transition);
    }

    .recipes-section__link:hover {
        gap: 10px;
    }

    .recipes-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(360px, 1fr));
        gap: 28px;
    }

    /* Recipe Card */
    .recipe-card {
        background: var(--clr-surface);
        border-radius: var(--radius-lg);
        overflow: hidden;
        box-shadow: var(--shadow-card);
        transition: transform var(--transition), box-shadow var(--transition);
    }

    .recipe-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 28px 80px rgba(0,0,0,.16);
    }

    .recipe-card__image {
        width: 100%;
        height: 240px;
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 64px;
        color: rgba(255,255,255,.3);
        overflow: hidden;
    }

    .recipe-card__category {
        position: absolute;
        top: 12px;
        left: 12px;
        background: #fff;
        color: var(--clr-primary);
        padding: 6px 14px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 600;
    }

    .recipe-card__content {
        padding: 24px;
    }

    .recipe-card__title {
        font-family: 'Playfair Display', serif;
        font-size: 18px;
        font-weight: 600;
        margin-bottom: 12px;
        color: var(--clr-text);
    }

    .recipe-card__description {
        font-size: 13px;
        color: var(--clr-muted);
        margin-bottom: 20px;
        line-height: 1.5;
    }

    .recipe-card__footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding-top: 16px;
        border-top: 1px solid var(--clr-border);
    }

    .recipe-card__meta {
        display: flex;
        align-items: center;
        gap: 16px;
        font-size: 12px;
        color: var(--clr-muted);
    }

    .recipe-card__meta-item {
        display: flex;
        align-items: center;
        gap: 6px;
    }

    /* Empty state */
    .empty-state {
        text-align: center;
        padding: 60px 20px;
    }

    .empty-state__icon {
        font-size: 64px;
        color: var(--clr-muted);
        margin-bottom: 20px;
        opacity: .4;
    }

    .empty-state__title {
        font-family: 'Playfair Display', serif;
        font-size: 20px;
        font-weight: 600;
        color: var(--clr-text);
        margin-bottom: 8px;
    }

    .empty-state__text {
        font-size: 14px;
        color: var(--clr-muted);
    }

    /* Animations */
    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    main > section {
        animation: fadeIn .6s ease-in-out;
    }

    /* ══════════════════════════════════════════
       RESPONSIVE
    ══════════════════════════════════════════ */
    @media (max-width: 1024px) {
        .stats-grid {
            grid-template-columns: repeat(2, 1fr);
        }

        .recipes-grid {
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        }
    }

    @media (max-width: 768px) {
        main {
            padding: 20px;
        }

        .stats-grid {
            grid-template-columns: 1fr;
        }

        .filter-buttons {
            gap: 8px;
        }

        .filter-btn {
            padding: 8px 16px;
            font-size: 12px;
        }

        .recipes-grid {
            grid-template-columns: 1fr;
        }

        .recipes-section__header {
            flex-direction: column;
            align-items: flex-start;
            gap: 12px;
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
                        <span class="sidebar-nav__badge"><?php echo $totalRecettes; ?></span>
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

        <!-- Main content area -->
        <section class="stats-grid">
            <!-- Card 1: Total Recipes -->
            <div class="stat-card">
                <div class="stat-card__header">
                    <div>
                        <div class="stat-card__label">Recettes</div>
                        <div class="stat-card__value"><?php echo $totalRecettes; ?></div>
                    </div>
                    <div class="stat-card__icon">
                        <i class="fas fa-utensils"></i>
                    </div>
                </div>
                <div class="stat-card__change">
                    <i class="fas fa-arrow-up"></i>
                    +2 ce mois
                </div>
            </div>

            <!-- Card 2: Total Categories -->
            <div class="stat-card">
                <div class="stat-card__header">
                    <div>
                        <div class="stat-card__label">Catégories</div>
                        <div class="stat-card__value"><?php echo $totalCategories; ?></div>
                    </div>
                    <div class="stat-card__icon">
                        <i class="fas fa-folder"></i>
                    </div>
                </div>
                <div class="stat-card__change">
                    <i class="fas fa-arrow-up"></i>
                    +1 ce mois
                </div>
            </div>

            <!-- Card 3: Favorites -->
            <div class="stat-card">
                <div class="stat-card__header">
                    <div>
                        <div class="stat-card__label">Favoris</div>
                        <div class="stat-card__value"><?php echo $totalFavoris; ?></div>
                    </div>
                    <div class="stat-card__icon">
                        <i class="fas fa-star"></i>
                    </div>
                </div>
                <div class="stat-card__change">
                    <i class="fas fa-arrow-up"></i>
                    +1 ce mois
                </div>
            </div>

            <!-- Card 4: Average Time -->
            <div class="stat-card">
                <div class="stat-card__header">
                    <div>
                        <div class="stat-card__label">Temps moyen</div>
                        <div class="stat-card__value"><?php echo $tempsmoyen; ?><span style="font-size: 18px; margin-left: 4px;">min</span></div>
                    </div>
                    <div class="stat-card__icon">
                        <i class="fas fa-hourglass-end"></i>
                    </div>
                </div>
                <div class="stat-card__change stat-card__change--negative">
                    <i class="fas fa-arrow-down"></i>
                    -5 ce mois
                </div>
            </div>
        </section>

        <!-- ════════════ FILTER SECTION ════════════ -->
        <section class="filter-section">
            <h2 class="filter-section__title">Filtrer par catégorie</h2>
            <div class="filter-buttons">
                <!-- Bouton Toutes les catégories -->
                <a href="?category=all" class="filter-btn <?php echo (!$categoryFilter || $categoryFilter === 'all') ? 'active' : ''; ?>">
                    <span><i class="fas fa-utensils"></i></span>
                    <span>Toutes</span>
                    <span class="filter-btn__badge"><?php echo $totalRecettes; ?></span>
                </a>

                <!-- Génération dynamique des boutons de catégories -->
                <?php foreach ($categories as $cat): ?>
                    <a href="?category=<?php echo $cat['id']; ?>" class="filter-btn <?php echo ($categoryFilter == $cat['id']) ? 'active' : ''; ?>">
                        <span><i class="fas fa-leaf"></i></span>
                        <span><?php echo htmlspecialchars($cat['name']); ?></span>
                        <span class="filter-btn__badge"><?php echo isset($countsByCategory[$cat['id']]) ? $countsByCategory[$cat['id']] : 0; ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- ════════════ RECIPES SECTION ════════════ -->
        <section class="recipes-section">
            <div class="recipes-section__header">
                <h2 class="recipes-section__title">Mes recettes récentes</h2>
                <a href="#" class="recipes-section__link">
                    Voir tout <i class="fas fa-arrow-right"></i>
                </a>
            </div>

            <?php if (!empty($recettesAffichees)): ?>
            <div class="recipes-grid">
                <?php foreach ($recettesAffichees as $recette): ?>
                <div class="recipe-card">
                    <div class="recipe-card__image" style="background: linear-gradient(135deg, <?php
                        $colors = [
                            'Entrées' => '#1EAD9C, #2FBE9F',
                            'Plats' => '#FF9A56, #FFB380',
                            'Desserts' => '#E8147B, #F2357C',
                            'Boissons' => '#6366F1, #7C3AED'
                        ];
                        echo isset($colors[$recette['category_name']]) ? $colors[$recette['category_name']] : '#C0522B, #E8795A';
                    ?> 100%);">
                        <i class="fas fa-utensils"></i>
                        <div class="recipe-card__category"><?php echo htmlspecialchars($recette['category_name']); ?></div>
                    </div>
                    <div class="recipe-card__content">
                        <h3 class="recipe-card__title"><?php echo htmlspecialchars($recette['name']); ?></h3>
                        <p class="recipe-card__description">
                            <?php echo htmlspecialchars(substr($recette['description'], 0, 100)) . (strlen($recette['description']) > 100 ? '...' : ''); ?>
                        </p>
                        <div class="recipe-card__footer">
                            <div class="recipe-card__meta">
                                <div class="recipe-card__meta-item">
                                    <i class="fas fa-clock"></i>
                                    45 min
                                </div>
                                <div class="recipe-card__meta-item">
                                    <i class="fas fa-users"></i>
                                    4 pers.
                                </div>
                                <div class="recipe-card__meta-item">
                                    <i class="fas fa-signal"></i>
                                    Moyen
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
                <div class="empty-state">
                    <div class="empty-state__icon">
                        <i class="fas fa-inbox"></i>
                    </div>
                    <h3 class="empty-state__title">Aucune recette trouvée</h3>
                    <p class="empty-state__text">Commencez par ajouter votre première recette</p>
                </div>
            <?php endif; ?>
        </section>

        </main>
    </div>
</div>

</body>
</html>
