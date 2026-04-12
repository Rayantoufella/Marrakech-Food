<?php

/**
 * TÂCHES À ACCOMPLIR
 * ==================
 */

// TÂCHE 1: Inclure les modèles DB, category, etc.
include_once __DIR__ . '/../../Models/DB.php';
include_once __DIR__ . '/../../Models/category.php';
include_once __DIR__ . '/../../Models/recette.php';
include_once __DIR__ . '/../../Models/user.php';


// TÂCHE 2: Connexion à la base de données
$db = new DB("localhost", "marrakech_food", "root", "");
$pdo = $db->getPDO();


// TÂCHE 3: Récupérer TOUTES les catégories de la BD
// ...
$categories = [];

try{
    $stmt = $pdo->prepare("SELECT * FROM category");
    $stmt->execute();
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
}catch(PDOException $e){
    echo 'Erreur : ' . $e->getMessage() . '<br>';
}



// TÂCHE 4: Compter le nombre TOTAL de recettes
// ...
$recetteCount = 0 ;

try {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM recette");
    $stmt->execute();
    $recetteCount = $stmt->fetchColumn();
}catch(PDOException $e) {
    echo 'Erreur : ' . $e->getMessage() . '<br>';
}

// TÂCHE 5: Vérifier si l'utilisateur a cliqué sur un filtre (category dans l'URL)
// Exemple: ?category=2
// ...
$categoryFilter = null;

if (isset($_GET['category'])) {
    $categoryFilter = $_GET['category'];
}




// TÂCHE 6: Setup pagination
// - 5 recettes par page
// - Récupérer la page actuelle de l'URL (?page=1)
// - Calculer l'offset (position de départ)
// ...
$itemsPerPage = 5;
$currentPage = 1;
$offset = 0;

if (isset($_GET['page'])) {
    $currentPage = $_GET['page'];
    $offset = ($currentPage - 1) * $itemsPerPage;
}



// TÂCHE 7: SI filtre est actif (categoryFilter != null)
//    - Compter les recettes filtrées par category_id
//    - Récupérer les recettes avec LIMIT et OFFSET
// SINON
//    - Compter TOUTES les recettes
//    - Récupérer TOUTES les recettes avec LIMIT et OFFSET
// ...
$recettes = [];
$totalRecetteCount = 0;

if ($categoryFilter != null){
    try{
        // COMPTER les recettes filtrées
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM recette WHERE category_id = :category_id");
        $stmt->bindParam(':category_id', $categoryFilter, PDO::PARAM_INT);
        $stmt->execute();
        $totalRecetteCount = $stmt->fetchColumn();

        // RÉCUPÉRER les recettes filtrées avec pagination
        $stmt = $pdo->prepare("SELECT * FROM recette WHERE category_id = :category_id ORDER BY created_at DESC LIMIT :limit OFFSET :offset");
        $stmt->bindParam(':category_id', $categoryFilter, PDO::PARAM_INT);
        $stmt->bindParam(':limit', $itemsPerPage, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $recettes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }catch(PDOException $e){
        echo 'Erreur : ' . $e->getMessage() . '<br>';
    }
}else{
    try{
        // COMPTER TOUTES les recettes
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM recette");
        $stmt->execute();
        $totalRecetteCount = $stmt->fetchColumn();

        // RÉCUPÉRER TOUTES les recettes avec pagination
        $stmt = $pdo->prepare("SELECT * FROM recette ORDER BY created_at DESC LIMIT :limit OFFSET :offset");
        $stmt->bindParam(':limit', $itemsPerPage, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $recettes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }catch(PDOException $e){
        echo 'Erreur : ' . $e->getMessage() . '<br>';
    }
}


// TÂCHE 8: Calculer le nombre total de PAGES

$totalPages = ceil($totalRecetteCount / $itemsPerPage);


// TÂCHE 9: Calculer l'intervalle d'affichage

$startItem = ($currentPage - 1) * $itemsPerPage + 1;
$endItem = min($currentPage * $itemsPerPage, $totalRecetteCount);


// TRAITER LA SUPPRESSION D'UNE RECETTE
if(isset($_GET['delete']) && !empty($_GET['delete'])){
    $recetteId = intval($_GET['delete']);

    try{
        $stmt = $pdo->prepare("DELETE FROM recette WHERE id = :id");
        $stmt->bindParam(':id', $recetteId, PDO::PARAM_INT);
        $stmt->execute();

        // Rediriger après suppression
        header("Location: show.php");
        exit();
    }catch(PDOException $e){
        echo 'Erreur lors de la suppression: ' . $e->getMessage();
    }
}

?>


<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Mes recettes - Marrakech Food Lovers</title>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        :root {
            --clr-primary:      #C0522B;
            --clr-primary-dark: #9A3D1C;
            --clr-primary-light:#E8795A;
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

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        html, body {
            font-family: 'Inter', sans-serif;
            background: var(--clr-bg);
            color: var(--clr-text);
            height: 100%;
        }

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

        /* ── MAIN CONTENT ── */
        .main-content {
            margin-left: 240px;
            flex: 1;
            display: flex;
            flex-direction: column;
            background: var(--clr-bg);
        }

        /* ── TOP HEADER ── */
        .top-header {
            background: var(--clr-surface);
            border-bottom: 1px solid var(--clr-border);
            border-left: 1px solid var(--clr-border);
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
            gap: 16px;
        }

        .search-bar {
            position: relative;
            width: 200px;
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
            text-decoration: none;
        }

        .btn-primary:hover {
            background: var(--clr-primary-dark);
            box-shadow: 0 12px 32px rgba(192,82,43,.5);
        }

        .btn-primary i {
            font-size: 14px;
        }

        /* ── CONTENT AREA ── */
        main {
            flex: 1;
            padding: 32px;
            overflow-y: auto;
        }

        .page-header {
            margin-bottom: 32px;
        }

        .page-header__top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 24px;
        }

        .page-header__info {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .page-header__count {
            font-size: 13px;
            color: var(--clr-muted);
        }

        .page-header__actions {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .filter-dropdown {
            position: relative;
        }

        .dropdown-btn {
            background: var(--clr-surface);
            color: var(--clr-text);
            border: 1.5px solid var(--clr-border);
            padding: 10px 16px;
            border-radius: var(--radius-md);
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all var(--transition);
        }

        .dropdown-btn:hover {
            border-color: var(--clr-primary);
        }

        .view-toggle {
            display: flex;
            gap: 8px;
        }

        .toggle-btn {
            background: var(--clr-surface);
            border: 1.5px solid var(--clr-border);
            width: 36px;
            height: 36px;
            border-radius: var(--radius-md);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--clr-muted);
            transition: all var(--transition);
        }

        .toggle-btn:hover, .toggle-btn.active {
            background: var(--clr-primary);
            color: #fff;
            border-color: var(--clr-primary);
        }

        /* ── CATEGORY FILTERS ── */
        .category-filters {
            display: flex;
            gap: 12px;
            margin-bottom: 24px;
            flex-wrap: wrap;
        }

        .category-btn {
            background: var(--clr-surface);
            border: 2px solid var(--clr-border);
            color: var(--clr-text);
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: all var(--transition);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .category-btn:hover {
            border-color: var(--clr-primary);
            color: var(--clr-primary);
        }

        .category-btn.active {
            background: var(--clr-primary);
            border-color: var(--clr-primary);
            color: #fff;
        }

        /* ── TABLE ── */
        .recipes-table {
            width: 100%;
            border-collapse: collapse;
            background: var(--clr-surface);
            border-radius: var(--radius-lg);
            overflow: hidden;
            box-shadow: var(--shadow-card);
        }

        .recipes-table thead {
            background: #f9f7f4;
            border-bottom: 1px solid var(--clr-border);
        }

        .recipes-table th {
            padding: 16px;
            text-align: left;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: var(--clr-muted);
        }

        .recipes-table tbody tr {
            border-bottom: 1px solid var(--clr-border);
            transition: background var(--transition);
        }

        .recipes-table tbody tr:hover {
            background: #fafaf9;
        }

        .recipes-table td {
            padding: 16px;
            font-size: 14px;
            color: var(--clr-text);
        }

        .recipe-name {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .recipe-avatar {
            width: 44px;
            height: 44px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            flex-shrink: 0;
        }

        .recipe-avatar.plats {
            background: #FF9A56;
            color: #fff;
        }

        .recipe-avatar.entrees {
            background: #1EAD9C;
            color: #fff;
        }

        .recipe-avatar.desserts {
            background: #E8147B;
            color: #fff;
        }

        .recipe-avatar.boissons {
            background: #6366F1;
            color: #fff;
        }

        .recipe-info {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .recipe-title {
            font-weight: 600;
            color: var(--clr-text);
        }

        .recipe-date {
            font-size: 12px;
            color: var(--clr-muted);
        }

        .category-badge {
            display: inline-block;
            background: #f9f7f4;
            color: var(--clr-primary);
            padding: 6px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
        }

        .time-info {
            display: flex;
            align-items: center;
            gap: 6px;
            color: var(--clr-muted);
        }

        .time-info i {
            font-size: 12px;
        }

        .portions-info {
            display: flex;
            align-items: center;
            gap: 6px;
            color: var(--clr-muted);
        }

        .portions-info i {
            font-size: 12px;
        }

        .difficulty {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 12px;
            font-weight: 600;
        }

        .difficulty.facile {
            color: #10b981;
        }

        .difficulty.moyen {
            color: #f59e0b;
        }

        .difficulty.difficile {
            color: #ef4444;
        }

        .action-btns {
            display: flex;
            gap: 8px;
        }

        .action-btn {
            background: none;
            border: none;
            cursor: pointer;
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--clr-muted);
            border-radius: 6px;
            transition: all var(--transition);
            font-size: 14px;
        }

        .action-btn:hover {
            background: #f9f7f4;
            color: var(--clr-primary);
        }

        .action-btn.delete:hover {
            color: #ef4444;
        }

        /* ── PAGINATION ── */
        .pagination {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-top: 32px;
            padding: 20px 0;
            border-top: 1px solid var(--clr-border);
        }

        .pagination-info {
            font-size: 13px;
            color: var(--clr-muted);
        }

        .pagination-controls {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .pagination-btn {
            background: var(--clr-surface);
            border: 1.5px solid var(--clr-border);
            color: var(--clr-text);
            min-width: 40px;
            height: 40px;
            padding: 0 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 6px;
            cursor: pointer;
            font-size: 13px;
            font-weight: 600;
            transition: all var(--transition);
            text-decoration: none;
        }

        .pagination-btn:hover:not(:disabled) {
            border-color: var(--clr-primary);
            color: var(--clr-primary);
            background: rgba(192, 82, 43, 0.05);
        }

        .pagination-btn.active {
            background: var(--clr-primary);
            border-color: var(--clr-primary);
            color: #fff;
        }

        .pagination-btn:disabled {
            opacity: 0.4;
            cursor: not-allowed;
            color: var(--clr-muted);
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 200px;
                position: absolute;
                left: -200px;
                z-index: 1000;
            }

            .main-content {
                margin-left: 0;
            }

            .recipes-table {
                font-size: 12px;
            }

            .recipes-table th, .recipes-table td {
                padding: 12px 8px;
            }

            .header-actions {
                gap: 8px;
            }

            .search-bar {
                width: 150px;
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

        <section class="sidebar-section">
            <h3 class="sidebar-section__title">Principal</h3>
            <ul class="sidebar-nav">
                <li class="sidebar-nav__item">
                    <a href="../category/index.php" class="sidebar-nav__link">
                        <span class="sidebar-nav__icon"><i class="fas fa-chart-line"></i></span>
                        <span>Tableau de bord</span>
                    </a>
                </li>
                <li class="sidebar-nav__item">
                    <a href="show.php" class="sidebar-nav__link active">
                        <span class="sidebar-nav__icon"><i class="fas fa-book"></i></span>
                        <span>Mes recettes</span>
                        <span class="sidebar-nav__badge"><?php echo $recetteCount; ?></span>
                    </a>
                </li>
                <li class="sidebar-nav__item">
                    <a href="create.php" class="sidebar-nav__link">
                        <span class="sidebar-nav__icon"><i class="fas fa-plus"></i></span>
                        <span>Ajouter une recette</span>
                    </a>
                </li>
            </ul>
        </section>

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

        <div class="sidebar-user">
            <div class="sidebar-user__avatar">AK</div>
            <div class="sidebar-user__info">
                <div class="sidebar-user__name">Ahmed Karimi</div>
                <div class="sidebar-user__role">Chef Amateur</div>
            </div>
        </div>
    </aside>

    <!-- ════════════ MAIN CONTENT ════════════ -->
    <div class="main-content">

        <!-- ── TOP HEADER ── -->
        <header class="top-header">
            <div class="header-title">
                <h1 class="header-title__main">Mes recettes</h1>
                <p class="header-title__sub">Gérez et organisez votre collection culinaire</p>
            </div>

            <div class="header-actions">
                <form method="POST" style="display: flex; gap: 12px; align-items: center; flex: 1;">
                    <div class="search-bar">
                        <i class="fas fa-search search-bar__icon"></i>
                        <input type="text" name="search" placeholder="Rechercher une recette..." >
                    </div>
                </form>

                <button class="btn-primary">
                    <i class="fas fa-bell"></i>
                </button>

                <a href="../category/showCat.php" class="btn-primary">
                    <i class="fas fa-arrow-left"></i>
                    Retour
                </a>
            </div>
        </header>

        <!-- ── MAIN CONTENT ── -->
        <main>
            <div class="page-header">
                <div class="page-header__top">
                    <div class="page-header__info">
                        <div class="page-header__count"><?php echo $recetteCount; ?> recettes</div>
                    </div>

                    <div class="page-header__actions">
                        <div class="filter-dropdown">
                            <button class="dropdown-btn">
                                Toutes les catégo...
                                <i class="fas fa-chevron-down"></i>
                            </button>
                        </div>

                        <div class="view-toggle">
                            <button class="toggle-btn active">
                                <i class="fas fa-list"></i>
                            </button>
                            <button class="toggle-btn">
                                <i class="fas fa-th"></i>
                            </button>
                        </div>

                        <a href="create.php" class="btn-primary">
                            <i class="fas fa-plus"></i>
                            Ajouter une recette
                        </a>
                    </div>
                </div>

                <!-- Category Filters -->
                <div class="category-filters">
                    <a href="show.php" class="category-btn active">
                        <span><i class="fas fa-utensils"></i></span>
                        <span>Toutes</span>
                    </a>
                    <?php foreach ($categories as $cat): ?>
                        <a href="?category=<?php echo $cat['id']; ?>" class="category-btn <?php echo ($categoryFilter == $cat['id']) ? 'active' : ''; ?>">
                            <span><i class="fas fa-leaf"></i></span>
                            <span><?php echo htmlspecialchars($cat['name']); ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Recipes Table -->
            <table class="recipes-table">
                <thead>
                    <tr>
                        <th style="width: 30px;"><input type="checkbox"></th>
                        <th>RECETTE</th>
                        <th>CATÉGORIE</th>
                        <th>TEMPS</th>
                        <th>DATE</th>
                        <th>ACTIONS</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($recettes)): ?>
                        <?php foreach ($recettes as $recette): ?>
                        <tr>
                            <td><input type="checkbox"></td>
                            <td>
                                <div class="recipe-name">
                                    <div class="recipe-avatar plats">
                                        <i class="fas fa-utensils"></i>
                                    </div>
                                    <div class="recipe-info">
                                        <div class="recipe-title"><?php echo htmlspecialchars($recette['name']); ?></div>
                                        <div class="recipe-date">Il y a 2 jours</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="category-badge">Plats</span>
                            </td>
                            <td>
                                <div class="time-info">
                                    <i class="fas fa-clock"></i>
                                    <span>45 min</span>
                                </div>
                            </td>
                            <td>
                                <div class="portions-info">
                                    <i class="fas fa-users"></i>
                                    <span>4 pers.</span>
                                </div>
                            </td>
                            <td>
                                <div class="difficulty moyen">
                                    <i class="fas fa-signal"></i>
                                    <span>Moyen</span>
                                </div>
                            </td>
                            <td><?php echo date('d M Y', strtotime($recette['created_at'])); ?></td>
                            <td>
                                <div class="action-btns">
                                    <a href="edit.php?id=<?php echo $recette['id']; ?>" class="action-btn">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="show.php?delete=<?php echo $recette['id']; ?>" class="action-btn delete" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette recette ?');">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 40px; color: var(--clr-muted);">
                                Aucune recette trouvée
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <!-- Pagination -->
            <div class="pagination">
                <div class="pagination-info">Affichage de <?php echo $startItem; ?> à <?php echo $endItem; ?> sur <?php echo $totalRecetteCount; ?> recettes</div>
                <div class="pagination-controls">
                    <?php if ($currentPage > 1): ?>
                        <a href="?page=<?php echo $currentPage - 1; ?>" class="pagination-btn">Précédent</a>
                    <?php else: ?>
                        <button class="pagination-btn" disabled>Précédent</button>
                    <?php endif; ?>

                    <button class="pagination-btn active"><?php echo $currentPage; ?></button>

                    <?php if ($currentPage < $totalPages): ?>
                        <a href="?page=<?php echo $currentPage + 1; ?>" class="pagination-btn">Suivant</a>
                    <?php else: ?>
                        <button class="pagination-btn" disabled>Suivant</button>
                    <?php endif; ?>
                </div>
            </div>
        </main>

    </div>

</div>

</body>
</html>
