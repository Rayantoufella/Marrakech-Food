<?php
// LOGIQUE À TRAITER:
// 1. Récupérer le nombre total de recettes
// 2. Récupérer le nombre total de catégories
// 3. Récupérer le nombre de favoris
// 4. Récupérer le temps moyen des recettes
// 5. Récupérer toutes les catégories pour les filtres
// 6. Récupérer les recettes récentes (limité à 3 ou plus)
// 7. Gérer le filtrage par catégorie si une est sélectionnée
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
        /* ══════════════════════════════════════════
           VARIABLES & RESET
        ══════════════════════════════════════════ */
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

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        html, body {
            font-family: 'Inter', sans-serif;
            background: var(--clr-bg);
            color: var(--clr-text);
        }

        /* ══════════════════════════════════════════
           PAGE CONTENT STYLES
        ══════════════════════════════════════════ */

        /* Stats Cards Section */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 24px;
            margin-bottom: 48px;
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

        .stat-card__value {
            font-size: 32px;
            font-weight: 700;
            color: var(--clr-text);
        }

        .stat-card__label {
            font-size: 13px;
            color: var(--clr-muted);
            margin-bottom: 12px;
        }

        .stat-card__change {
            font-size: 12px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .stat-card__change--positive {
            color: #10b981;
        }

        .stat-card__change--negative {
            color: #ef4444;
        }

        /* Filter Section */
        .filter-section {
            margin-bottom: 48px;
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
            transition: all var(--transition);
            text-decoration: none;
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

        .filter-btn__icon {
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
        }

        .filter-btn__badge {
            background: rgba(255,255,255,.3);
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
        }

        .filter-btn.active .filter-btn__badge {
            background: rgba(255,255,255,.4);
        }

        /* Recent Recipes Section */
        .recipes-section {
            margin-bottom: 48px;
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
            background: linear-gradient(135deg, #FF6B4A 0%, #FF8C6B 100%);
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 64px;
            color: rgba(255,255,255,.3);
            overflow: hidden;
        }

        .recipe-card__image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
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

        .recipe-card__meta-item i {
            font-size: 14px;
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
    <?php include __DIR__ . "/../layout/header.php"; ?>

    <main style="flex: 1; padding: 32px;">
        <!-- ════════════ STATS SECTION ════════════ -->
        <section class="stats-grid">
            <!-- Card 1: Total Recipes -->
            <div class="stat-card">
                <div class="stat-card__header">
                    <div>
                        <div class="stat-card__label">Recettes</div>
                        <div class="stat-card__value">
                            <!-- TODO: Afficher le nombre total de recettes -->
                            12
                        </div>
                    </div>
                    <div class="stat-card__icon">
                        <i class="fas fa-utensils"></i>
                    </div>
                </div>
                <div class="stat-card__change stat-card__change--positive">
                    <i class="fas fa-arrow-up"></i>
                    <!-- TODO: Afficher le changement (ex: +2 ce mois) -->
                    +2 ce mois
                </div>
            </div>

            <!-- Card 2: Total Categories -->
            <div class="stat-card">
                <div class="stat-card__header">
                    <div>
                        <div class="stat-card__label">Catégories</div>
                        <div class="stat-card__value">
                            <!-- TODO: Afficher le nombre total de catégories -->
                            4
                        </div>
                    </div>
                    <div class="stat-card__icon">
                        <i class="fas fa-folder"></i>
                    </div>
                </div>
                <div class="stat-card__change stat-card__change--positive">
                    <i class="fas fa-arrow-up"></i>
                    <!-- TODO: Afficher le changement -->
                    +1 ce mois
                </div>
            </div>

            <!-- Card 3: Favorites -->
            <div class="stat-card">
                <div class="stat-card__header">
                    <div>
                        <div class="stat-card__label">Favoris</div>
                        <div class="stat-card__value">
                            <!-- TODO: Afficher le nombre de favoris -->
                            5
                        </div>
                    </div>
                    <div class="stat-card__icon">
                        <i class="fas fa-star"></i>
                    </div>
                </div>
                <div class="stat-card__change stat-card__change--positive">
                    <i class="fas fa-arrow-up"></i>
                    <!-- TODO: Afficher le changement -->
                    +1 ce mois
                </div>
            </div>

            <!-- Card 4: Average Time -->
            <div class="stat-card">
                <div class="stat-card__header">
                    <div>
                        <div class="stat-card__label">Temps moyen</div>
                        <div class="stat-card__value">
                            <!-- TODO: Afficher le temps moyen en minutes -->
                            45<span style="font-size: 18px; margin-left: 4px;">min</span>
                        </div>
                    </div>
                    <div class="stat-card__icon">
                        <i class="fas fa-hourglass-end"></i>
                    </div>
                </div>
                <div class="stat-card__change stat-card__change--negative">
                    <i class="fas fa-arrow-down"></i>
                    <!-- TODO: Afficher le changement -->
                    -5 ce mois
                </div>
            </div>
        </section>

        <!-- ════════════ FILTER SECTION ════════════ -->
        <section class="filter-section">
            <h2 class="filter-section__title">Filtrer par catégorie</h2>
            <div class="filter-buttons">
                <!-- TODO: Boucle sur toutes les catégories et afficher les boutons de filtre -->
                <!-- Exemple de structure pour chaque catégorie: -->
                <a href="?category=all" class="filter-btn active">
                    <span class="filter-btn__icon"><i class="fas fa-utensils"></i></span>
                    <span>Toutes</span>
                    <span class="filter-btn__badge">12</span>
                </a>

                <!-- TODO: Génerer dynamiquement les autres catégories -->
                <!-- Les couleurs suggérées: Entrées (vert), Plats (orange), Desserts (rose), Boissons (violet) -->
                <button class="filter-btn" onclick="filterCategory('entrees')">
                    <span class="filter-btn__icon"><i class="fas fa-leaf"></i></span>
                    <span>Entrées</span>
                    <span class="filter-btn__badge">3</span>
                </button>

                <button class="filter-btn" onclick="filterCategory('plats')">
                    <span class="filter-btn__icon"><i class="fas fa-fire"></i></span>
                    <span>Plats</span>
                    <span class="filter-btn__badge">4</span>
                </button>

                <button class="filter-btn" onclick="filterCategory('desserts')">
                    <span class="filter-btn__icon"><i class="fas fa-cake-slice"></i></span>
                    <span>Desserts</span>
                    <span class="filter-btn__badge">3</span>
                </button>

                <button class="filter-btn" onclick="filterCategory('boissons')">
                    <span class="filter-btn__icon"><i class="fas fa-wine-glass"></i></span>
                    <span>Boissons</span>
                    <span class="filter-btn__badge">2</span>
                </button>
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

            <!-- TODO: Vérifier si des recettes existent -->
            <!-- Si oui: boucle sur les recettes et afficher les cartes -->
            <!-- Si non: afficher le empty-state -->

            <div class="recipes-grid">
                <!-- Recipe Card 1 -->
                <!-- TODO: Boucle PHP: foreach($recettes as $recette) { -->
                <div class="recipe-card">
                    <div class="recipe-card__image" style="background: linear-gradient(135deg, #FF9A56 0%, #FFB380 100%);">
                        <!-- TODO: Afficher l'image de la recette ou une placeholder -->
                        <i class="fas fa-utensils"></i>
                        <!-- <img src="image-path" alt="Recette"> -->
                    </div>
                    <div class="recipe-card__category">
                        <!-- TODO: Afficher la catégorie -->
                        Plats
                    </div>
                    <div class="recipe-card__content">
                        <h3 class="recipe-card__title">
                            <!-- TODO: Afficher le titre de la recette -->
                            Tajine d'Agneau aux Pruneaux
                        </h3>
                        <p class="recipe-card__description">
                            <!-- TODO: Afficher la description courte -->
                            Un classique de la cuisine marocaine, mijoté lentement avec des pruneaux sucrés et des amandes grillées.
                        </p>
                        <div class="recipe-card__footer">
                            <div class="recipe-card__meta">
                                <div class="recipe-card__meta-item">
                                    <i class="fas fa-clock"></i>
                                    <!-- TODO: Afficher le temps de préparation -->
                                    90 min
                                </div>
                                <div class="recipe-card__meta-item">
                                    <i class="fas fa-users"></i>
                                    <!-- TODO: Afficher le nombre de portions -->
                                    4 pers.
                                </div>
                                <div class="recipe-card__meta-item">
                                    <i class="fas fa-signal"></i>
                                    <!-- TODO: Afficher la difficulté -->
                                    Moyen
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recipe Card 2 -->
                <div class="recipe-card">
                    <div class="recipe-card__image" style="background: linear-gradient(135deg, #1EAD9C 0%, #2FBE9F 100%);">
                        <i class="fas fa-utensils"></i>
                    </div>
                    <div class="recipe-card__category">
                        <!-- TODO: Afficher la catégorie -->
                        Entrées
                    </div>
                    <div class="recipe-card__content">
                        <h3 class="recipe-card__title">
                            <!-- TODO: Afficher le titre de la recette -->
                            Salade Marocaine Fraîche
                        </h3>
                        <p class="recipe-card__description">
                            <!-- TODO: Afficher la description courte -->
                            Une salade colorée aux tomates, concombres, oignons et herbes fraîches, assaisonnée à l'huile d'argan.
                        </p>
                        <div class="recipe-card__footer">
                            <div class="recipe-card__meta">
                                <div class="recipe-card__meta-item">
                                    <i class="fas fa-clock"></i>
                                    <!-- TODO: Afficher le temps de préparation -->
                                    15 min
                                </div>
                                <div class="recipe-card__meta-item">
                                    <i class="fas fa-users"></i>
                                    <!-- TODO: Afficher le nombre de portions -->
                                    2 pers.
                                </div>
                                <div class="recipe-card__meta-item">
                                    <i class="fas fa-signal"></i>
                                    <!-- TODO: Afficher la difficulté -->
                                    Facile
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recipe Card 3 -->
                <div class="recipe-card">
                    <div class="recipe-card__image" style="background: linear-gradient(135deg, #E8147B 0%, #F2357C 100%);">
                        <i class="fas fa-utensils"></i>
                    </div>
                    <div class="recipe-card__category">
                        <!-- TODO: Afficher la catégorie -->
                        Desserts
                    </div>
                    <div class="recipe-card__content">
                        <h3 class="recipe-card__title">
                            <!-- TODO: Afficher le titre de la recette -->
                            Pastilla au Lait
                        </h3>
                        <p class="recipe-card__description">
                            <!-- TODO: Afficher la description courte -->
                            Un dessert délicat aux feuilles de brick croustillantes, crème pâtissière parfumée à la fleur d'oranger.
                        </p>
                        <div class="recipe-card__footer">
                            <div class="recipe-card__meta">
                                <div class="recipe-card__meta-item">
                                    <i class="fas fa-clock"></i>
                                    <!-- TODO: Afficher le temps de préparation -->
                                    60 min
                                </div>
                                <div class="recipe-card__meta-item">
                                    <i class="fas fa-users"></i>
                                    <!-- TODO: Afficher le nombre de portions -->
                                    6 pers.
                                </div>
                                <div class="recipe-card__meta-item">
                                    <i class="fas fa-signal"></i>
                                    <!-- TODO: Afficher la difficulté -->
                                    Difficile
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- TODO: } fin de la boucle -->
            </div>

            <!-- Empty State (à afficher si aucune recette) -->
            <!-- TODO: if(empty($recettes)) { -->
            <!--
            <div class="empty-state">
                <div class="empty-state__icon">
                    <i class="fas fa-inbox"></i>
                </div>
                <h3 class="empty-state__title">Aucune recette trouvée</h3>
                <p class="empty-state__text">Commencez par ajouter votre première recette</p>
            </div>
            -->
            <!-- TODO: } -->
        </section>
    </main>

    </div><!-- Fin du main-content -->
    </div><!-- Fin du dashboard-wrapper -->


</body>
</html>
