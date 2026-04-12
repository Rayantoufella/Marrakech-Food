<?php
session_start();

// TÂCHE 1: Inclure les modèles DB, category, etc.
require_once __DIR__ . '/../../Models/DB.php';
require_once __DIR__ . '/../../Models/category.php';
require_once __DIR__ . '/../../Models/recette.php';

// TÂCHE 2: Connexion à la base de données
$db = new DB("localhost", "marrakech_food", "root", "");
$pdo = $db->getPDO();

// TÂCHE 3: Récupérer TOUTES les catégories
$categories = [];
try {
    $stmt = $pdo->prepare('SELECT * FROM category ORDER BY name');
    $stmt->execute();
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo 'Erreur : ' . $e->getMessage();
}

// TÂCHE 4: Compter le nombre total de catégories
$totalCategories = count($categories);

// TÂCHE 5: Compter le nombre total de recettes
$totalRecettes = 0;
try {
    $stmt = $pdo->prepare('SELECT COUNT(*) as total FROM recette');
    $stmt->execute();
    $totalRecettes = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
} catch (PDOException $e) {
    echo 'Erreur : ' . $e->getMessage();
}

// TÂCHE 6: Compter les recettes par catégorie
$recipesPerCategory = [];
try {
    foreach ($categories as $cat) {
        $stmt = $pdo->prepare('SELECT COUNT(*) as count FROM recette WHERE category_id = :cat_id');
        $stmt->bindParam(':cat_id', $cat['id']);
        $stmt->execute();
        $recipesPerCategory[$cat['id']] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    }
} catch (PDOException $e) {
    echo 'Erreur : ' . $e->getMessage();
}

// TÂCHE 7: Traiter la recherche de catégories
$searchTerm = isset($_GET['search']) ? trim($_GET['search']) : '';
$filteredCategories = $categories;

if (!empty($searchTerm)) {
    $filteredCategories = array_filter($categories, function($cat) use ($searchTerm) {
        return stripos($cat['name'], $searchTerm) !== false;
    });
}

// TÂCHE 8: Récupérer la première catégorie comme catégorie principale
$mainCategory = !empty($categories) ? $categories[0] : null;
$mainCategoryRecipes = 0;
if ($mainCategory) {
    $mainCategoryRecipes = $recipesPerCategory[$mainCategory['id']] ?? 0;
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Catégories - Marrakech Food Lovers</title>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        *, *::before, *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

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

        html, body {
            height: 100%;
            font-family: 'Inter', sans-serif;
            background: var(--clr-bg);
            color: var(--clr-text);
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

        .sidebar-nav__link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 20px;
            color: rgba(255,255,255,.75);
            text-decoration: none;
            font-size: 14px;
            transition: all var(--transition);
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
        }

        .sidebar-user__info {
            flex: 1;
        }

        .sidebar-user__name {
            font-size: 13px;
            font-weight: 600;
            color: #fff;
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
        }

        /* ── TOP HEADER ── */
        .top-header {
            background: var(--clr-surface);
            border-bottom: 1px solid var(--clr-border);
            padding: 20px 32px;
            display: flex;
            align-items: center;
            justify-content: space-between;
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
        }

        .header-title__sub {
            font-size: 13px;
            color: var(--clr-muted);
        }

        .header-actions {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .search-bar {
            position: relative;
            width: 250px;
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
            transition: all var(--transition);
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
            transition: all var(--transition);
            box-shadow: var(--shadow-btn);
            text-decoration: none;
        }

        .btn-primary:hover {
            background: var(--clr-primary-dark);
            box-shadow: 0 12px 32px rgba(192,82,43,.5);
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

        .page-header__title {
            font-family: 'Playfair Display', serif;
            font-size: 28px;
            font-weight: 700;
            color: var(--clr-text);
            margin-bottom: 8px;
        }

        .page-header__subtitle {
            font-size: 13px;
            color: var(--clr-muted);
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 24px;
            margin-bottom: 48px;
        }

        .stat-card {
            background: var(--clr-surface);
            border-radius: var(--radius-lg);
            padding: 28px;
            box-shadow: var(--shadow-card);
            transition: all var(--transition);
        }

        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 28px 80px rgba(0,0,0,.16);
        }

        .stat-card__label {
            font-size: 12px;
            color: var(--clr-muted);
            text-transform: uppercase;
            letter-spacing: 0.5px;
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
            float: right;
            margin-top: -10px;
        }

        /* Main Category Section */
        .main-category {
            background: var(--clr-surface);
            border-radius: var(--radius-lg);
            padding: 28px;
            box-shadow: var(--shadow-card);
            margin-bottom: 48px;
        }

        .main-category__label {
            font-size: 12px;
            color: var(--clr-muted);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 12px;
        }

        .main-category__content {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .main-category__title {
            font-family: 'Playfair Display', serif;
            font-size: 24px;
            font-weight: 700;
            color: var(--clr-text);
        }

        .main-category__count {
            font-size: 13px;
            color: var(--clr-muted);
        }

        .main-category__icon {
            width: 56px;
            height: 56px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            background: rgba(192,82,43,.1);
            color: var(--clr-primary);
        }

        /* Categories Grid */
        .categories-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 24px;
        }

        .category-card {
            background: var(--clr-surface);
            border-radius: var(--radius-lg);
            overflow: hidden;
            box-shadow: var(--shadow-card);
            transition: all var(--transition);
        }

        .category-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 28px 80px rgba(0,0,0,.16);
        }

        .category-card__image {
            width: 100%;
            height: 200px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 64px;
            color: rgba(255,255,255,.3);
            position: relative;
        }

        .category-card__image.entrees {
            background: linear-gradient(135deg, #FFD89B, #FFC970);
        }

        .category-card__image.plats {
            background: linear-gradient(135deg, #FFB3A0, #FF8A6B);
        }

        .category-card__image.desserts {
            background: linear-gradient(135deg, #FFB3D9, #FF8FBD);
        }

        .category-card__image.boissons {
            background: linear-gradient(135deg, #B3D9FF, #8FC3FF);
        }

        .category-card__content {
            padding: 24px;
        }

        .category-card__title {
            font-family: 'Playfair Display', serif;
            font-size: 18px;
            font-weight: 700;
            color: var(--clr-text);
            margin-bottom: 16px;
        }

        .category-card__title a {
            color: var(--clr-text);
            text-decoration: none;
            transition: color var(--transition);
        }

        .category-card__title a:hover {
            color: var(--clr-primary);
        }

        .category-card__count {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 13px;
            color: var(--clr-muted);
        }

        .category-card__count i {
            font-size: 14px;
        }

        .category-card__bar {
            width: 100%;
            height: 4px;
            background: var(--clr-border);
            border-radius: 2px;
            margin-top: 16px;
            overflow: hidden;
        }

        .category-card__bar-fill {
            height: 100%;
            background: var(--clr-primary);
            width: 60%;
            border-radius: 2px;
        }

        .recipes-count {
            position: absolute;
            top: 12px;
            right: 12px;
            background: #fff;
            color: var(--clr-primary);
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
        }

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
            font-weight: 700;
            color: var(--clr-text);
            margin-bottom: 8px;
        }

        .empty-state__text {
            font-size: 14px;
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

            .categories-grid {
                grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            }

            .header-title__main {
                font-size: 22px;
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
                <li>
                    <a href="index.php" class="sidebar-nav__link">
                        <span class="sidebar-nav__icon"><i class="fas fa-chart-line"></i></span>
                        <span>Tableau de bord</span>
                    </a>
                </li>
                <li>
                    <a href="../recette/show.php" class="sidebar-nav__link">
                        <span class="sidebar-nav__icon"><i class="fas fa-book"></i></span>
                        <span>Mes recettes</span>
                        <span class="sidebar-nav__badge"><?php echo $totalRecettes; ?></span>
                    </a>
                </li>
                <li>
                    <a href="../recette/create.php" class="sidebar-nav__link">
                        <span class="sidebar-nav__icon"><i class="fas fa-plus"></i></span>
                        <span>Ajouter une recette</span>
                    </a>
                </li>
            </ul>
        </section>

        <section class="sidebar-section">
            <h3 class="sidebar-section__title">Organisation</h3>
            <ul class="sidebar-nav">
                <li>
                    <a href="showcat.php" class="sidebar-nav__link active">
                        <span class="sidebar-nav__icon"><i class="fas fa-folder"></i></span>
                        <span>Catégories</span>
                    </a>
                </li>
                <li>
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
                <li>
                    <a href="#" class="sidebar-nav__link">
                        <span class="sidebar-nav__icon"><i class="fas fa-user"></i></span>
                        <span>Mon profil</span>
                    </a>
                </li>
                <li>
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
                <h1 class="header-title__main">Catégories</h1>
                <p class="header-title__sub">Organisez et explorez vos recettes</p>
            </div>

            <div class="header-actions">
                <form method="GET" style="display: flex; gap: 12px; align-items: center;">
                    <div class="search-bar">
                        <i class="fas fa-search search-bar__icon"></i>
                        <input type="text" name="search" placeholder="Rechercher une catégorie..." value="<?php echo htmlspecialchars($searchTerm); ?>">
                    </div>
                </form>

                <a href="../recette/show.php" class="btn-primary">
                    <i class="fas fa-arrow-right"></i>
                    Voir mes recettes
                </a>

                <a href="../recette/create.php" class="btn-primary">
                    <i class="fas fa-plus"></i>
                    Nouvelle recette
                </a>
            </div>
        </header>

        <!-- ── CONTENT AREA ── -->
        <main>
            <div class="page-header">
                <h2 class="page-header__title"><?php echo count($filteredCategories); ?> catégories disponibles</h2>
                <p class="page-header__subtitle"><?php echo $totalRecettes; ?> recettes</p>
            </div>

            <!-- Stats Grid -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-card__label">Total Recettes</div>
                    <div class="stat-card__value"><?php echo $totalRecettes; ?></div>
                    <div class="stat-card__icon">
                        <i class="fas fa-utensils"></i>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-card__label">Catégories Actives</div>
                    <div class="stat-card__value"><?php echo $totalCategories; ?></div>
                    <div class="stat-card__icon">
                        <i class="fas fa-layer-group"></i>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-card__label">Catégorie Principale</div>
                    <div class="stat-card__value"><?php echo $mainCategory ? htmlspecialchars($mainCategory['name']) : 'N/A'; ?></div>
                    <div class="stat-card__icon">
                        <i class="fas fa-book"></i>
                    </div>
                </div>
            </div>

            <!-- Main Category Section -->
            <?php if ($mainCategory): ?>
            <div class="main-category">
                <div class="main-category__label">Catégorie Principale</div>
                <div class="main-category__content">
                    <div>
                        <div class="main-category__title"><?php echo htmlspecialchars($mainCategory['name']); ?></div>
                        <div class="main-category__count"><?php echo $mainCategoryRecipes; ?> recette(s)</div>
                    </div>
                    <div class="main-category__icon">
                        <i class="fas fa-utensils"></i>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Categories Grid -->
            <?php if (!empty($filteredCategories)): ?>
            <div class="categories-grid">
                <?php foreach ($filteredCategories as $category): ?>
                <div class="category-card">
                    <div class="category-card__image <?php echo strtolower(preg_replace('/\s+/', '', $category['name'])); ?>">
                        <i class="fas fa-utensils"></i>
                        <div class="recipes-count">
                            <?php echo $recipesPerCategory[$category['id']] ?? 0; ?> recette<?php echo ($recipesPerCategory[$category['id']] ?? 0) > 1 ? 's' : ''; ?>
                        </div>
                    </div>
                    <div class="category-card__content">
                        <h3 class="category-card__title">
                            <a href="../recette/show.php?category=<?php echo $category['id']; ?>">
                                <?php echo htmlspecialchars($category['name']); ?>
                            </a>
                        </h3>
                        <div class="category-card__count">
                            <i class="fas fa-book"></i>
                            <?php echo $recipesPerCategory[$category['id']] ?? 0; ?> recette<?php echo ($recipesPerCategory[$category['id']] ?? 0) > 1 ? 's' : ''; ?>
                        </div>
                        <div class="category-card__bar">
                            <div class="category-card__bar-fill"></div>
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
                <h3 class="empty-state__title">Aucune catégorie trouvée</h3>
                <p class="empty-state__text">Aucune catégorie ne correspond à votre recherche</p>
            </div>
            <?php endif; ?>

        </main>
    </div>

</div>

</body>
</html>

