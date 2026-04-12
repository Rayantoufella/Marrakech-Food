<?php
session_start();

// TÂCHE 1: Inclure les models DB, category, etc.
include_once __DIR__ . '/../../Models/DB.php';
include_once __DIR__ . '/../../Models/recette.php';
include_once __DIR__ . '/../../Models/category.php';

// TÂCHE 2: Connexion à la base de données
$db = new DB("localhost", "marrakech_food", "root", "");
$pdo = $db->getPDO();

// TÂCHE 3: Récupérer les catégories
$categories = [];

try {
    $stmt = $pdo->prepare('SELECT * FROM category');
    $stmt->execute();
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo $e->getMessage();
}

// Vérifier/créer un utilisateur par défaut
$defaultUserId = 1;
try {
    $stmt = $pdo->prepare('SELECT id FROM user WHERE id = :id');
    $stmt->bindValue(':id', $defaultUserId);
    $stmt->execute();
    $userExists = $stmt->fetch();

    if (!$userExists) {
        // Créer un utilisateur par défaut
        $stmt = $pdo->prepare('INSERT INTO user (id, name, email, password) VALUES (:id, :name, :email, :password)');
        $stmt->bindValue(':id', $defaultUserId);
        $stmt->bindValue(':name', 'Ahmed Karimi');
        $stmt->bindValue(':email', 'ahmed@marrakech-food.com');
        $stmt->bindValue(':password', password_hash('password', PASSWORD_BCRYPT));
        $stmt->execute();
    }
} catch (PDOException $e) {
    // Ignorer l'erreur si l'utilisateur existe déjà
}

// Initialiser les variables
$message = '';
$messageType = '';
$name = '';
$description = '';
$categoryId = '';

// TÂCHE 4: Vérifier si formulaire est soumis (POST)
if($_SERVER['REQUEST_METHOD'] === 'POST'){

    // TÂCHE 5: Récupérer les données du formulaire
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $description = isset($_POST['description']) ? trim($_POST['description']) : '';
    $categoryId = isset($_POST['category_id']) ? $_POST['category_id'] : '';
    $userId = isset($_POST['user_id']) ? $_POST['user_id'] : '';
    $imagePath = '';

    // TÂCHE 6: Valider les données (nom, description, category_id, user_id)
    if(empty($name) || empty($description) || empty($categoryId) || empty($userId)){
        $message = 'Tous les champs sont obligatoires.';
        $messageType = 'error';
    } else {

        // TÂCHE 7: Traiter l'upload de l'image (si fichier existe)
        if(isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK){
            $uploadDir = __DIR__ . '/../../public/uploads/recettes/';

            // Créer le dossier uploads/recettes/ si n'existe pas
            if(!is_dir($uploadDir)){
                mkdir($uploadDir, 0755, true);
            }

            // Générer un nom unique pour l'image
            $fileInfo = pathinfo($_FILES['image']['name']);
            $fileName = uniqid() . '_' . time() . '.' . $fileInfo['extension'];
            $uploadPath = $uploadDir . $fileName;

            // Types MIME autorisés
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

            // Vérifier le type MIME
            if(in_array($_FILES['image']['type'], $allowedTypes)){
                // Déplacer le fichier
                if(move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath)){
                    $imagePath = 'uploads/recettes/' . $fileName;
                } else {
                    $message = 'Erreur lors du téléchargement de l\'image.';
                    $messageType = 'error';
                }
            } else {
                $message = 'Type de fichier non autorisé! (JPEG, PNG, GIF, WebP)';
                $messageType = 'error';
            }
        }

        // TÂCHE 8: Insérer la recette dans la base de données
        if(empty($message)){
            try{
                $stmt = $pdo->prepare('INSERT INTO recette (name, description, category_id, user_id, image, created_at) VALUES (:name, :description, :category_id, :user_id, :image, NOW())');
                $stmt->bindParam(':name', $name);
                $stmt->bindParam(':description', $description);
                $stmt->bindParam(':category_id', $categoryId);
                $stmt->bindParam(':user_id', $userId);
                $stmt->bindParam(':image', $imagePath);
                $stmt->execute();

                // TÂCHE 9: Afficher un message de succès ou erreur
                $message = 'Recette ajoutée avec succès!';
                $messageType = 'success';

                // Réinitialiser le formulaire
                $name = '';
                $description = '';
                $categoryId = '';
            }catch(PDOException $e){
                $message = 'Erreur base de données: ' . $e->getMessage();
                $messageType = 'error';
            }
        }
    }
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Ajouter une recette - Marrakech Food Lovers</title>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        :root {
            --clr-primary:      #C0522B;
            --clr-primary-dark: #9A3D1C;
            --clr-primary-light:#E8795A;
            --clr-gold:         #D4A853;
            --clr-bg:           #FAF7F2;
            --clr-surface:      #FFFFFF;
            --clr-text:         #1A1208;
            --clr-muted:        #7A6652;
            --clr-border:       #E8DDD0;
            --clr-input-bg:     #F5F0E8;
            --clr-success:      #10b981;
            --clr-error:        #ef4444;
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

        /* ═══════════════════════════════════════
           SIDEBAR
        ═══════════════════════════════════════ */
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
            background: linear-gradient(135deg, var(--clr-primary), var(--clr-primary-light));
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            box-shadow: 0 8px 20px rgba(192, 82, 43, .4);
        }

        .sidebar-brand__icon i {
            color: #fff;
            font-size: 22px;
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
            color: rgba(255,255,255,.5);
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

        /* ═══════════════════════════════════════
           MAIN CONTENT
        ═══════════════════════════════════════ */
        .main-content {
            margin-left: 240px;
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        /* ─── TOP HEADER ─── */
        .top-header {
            background: linear-gradient(135deg, var(--clr-surface), #fdfbf7);
            border-bottom: 2px solid var(--clr-border);
            padding: 24px 32px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 4px 12px rgba(0,0,0,.06);
        }

        .header-title {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .header-title__main {
            font-family: 'Playfair Display', serif;
            font-size: 32px;
            font-weight: 700;
            background: linear-gradient(135deg, var(--clr-text), var(--clr-primary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .header-title__sub {
            font-size: 13px;
            color: var(--clr-muted);
            font-weight: 500;
        }

        .header-actions {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .btn-header {
            background: var(--clr-primary);
            color: #fff;
            border: none;
            padding: 11px 22px;
            border-radius: var(--radius-md);
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

        .btn-header:hover {
            background: var(--clr-primary-dark);
            box-shadow: 0 12px 32px rgba(192,82,43,.5);
            transform: translateY(-2px);
        }

        /* ─── MAIN FORM AREA ─── */
        .form-wrapper {
            flex: 1;
            padding: 40px 32px;
            overflow-y: auto;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .form-container {
            max-width: 850px;
            width: 100%;
        }

        .form-header {
            margin-bottom: 50px;
            text-align: center;
        }

        .form-header__title {
            font-family: 'Playfair Display', serif;
            font-size: 36px;
            font-weight: 700;
            background: linear-gradient(135deg, var(--clr-text), var(--clr-primary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 12px;
            letter-spacing: -0.5px;
        }

        .form-header__subtitle {
            font-size: 15px;
            color: var(--clr-muted);
            font-weight: 500;
        }

        .form-card {
            background: var(--clr-surface);
            border-radius: 20px;
            padding: 48px;
            box-shadow: 0 20px 60px rgba(0,0,0,.12);
            border: 1px solid rgba(226, 221, 208, .5);
            backdrop-filter: blur(10px);
        }

        /* Messages */
        .message {
            padding: 16px 20px;
            border-radius: var(--radius-md);
            margin-bottom: 28px;
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 14px;
            animation: slideDown .3s ease-out;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .message.success {
            background: rgba(16, 185, 129, 0.1);
            color: var(--clr-success);
            border-left: 4px solid var(--clr-success);
        }

        .message.error {
            background: rgba(239, 68, 68, 0.1);
            color: var(--clr-error);
            border-left: 4px solid var(--clr-error);
        }

        /* Form Group */
        .form-group {
            margin-bottom: 28px;
        }

        .form-group__label {
            display: block;
            margin-bottom: 12px;
            font-weight: 700;
            font-size: 15px;
            color: var(--clr-text);
            letter-spacing: -0.3px;
        }

        .form-group__label--required::after {
            content: ' *';
            color: var(--clr-error);
            font-weight: 900;
        }

        .form-group__input,
        .form-group__textarea,
        .form-group__select {
            width: 100%;
            padding: 16px 18px;
            border: 2px solid var(--clr-border);
            border-radius: 12px;
            background: linear-gradient(135deg, var(--clr-input-bg), #f8f5f0);
            font-family: 'Inter', sans-serif;
            font-size: 15px;
            color: var(--clr-text);
            outline: none;
            transition: all var(--transition);
        }

        .form-group__input::placeholder,
        .form-group__textarea::placeholder {
            color: #b8a898;
        }

        .form-group__input:focus,
        .form-group__textarea:focus,
        .form-group__select:focus {
            border-color: var(--clr-primary);
            background: #fff;
            box-shadow: 0 0 0 5px rgba(192, 82, 43, 0.15);
            transform: translateY(-2px);
        }

        .form-group__textarea {
            resize: vertical;
            min-height: 160px;
            font-size: 15px;
            line-height: 1.7;
        }

        .form-group__select {
            cursor: pointer;
            appearance: none;
            background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 12px center;
            background-size: 20px;
            padding-right: 40px;
        }

        .form-group__select {
            cursor: pointer;
        }

        /* Form Actions */
        .form-actions {
            display: flex;
            gap: 12px;
            margin-top: 40px;
        }

        .btn {
            flex: 1;
            padding: 16px 28px;
            border: none;
            border-radius: 12px;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            transition: all var(--transition);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            letter-spacing: 0.3px;
            text-transform: uppercase;
        }

        .btn--primary {
            background: linear-gradient(135deg, var(--clr-primary), #D06A3B);
            color: #fff;
            box-shadow: 0 10px 30px rgba(192, 82, 43, 0.4);
        }

        .btn--primary:hover {
            background: linear-gradient(135deg, var(--clr-primary-dark), #9A3D1C);
            box-shadow: 0 14px 40px rgba(192, 82, 43, 0.6);
            transform: translateY(-3px);
        }

        .btn--primary:active {
            transform: translateY(-1px);
        }

        .btn--secondary {
            background: linear-gradient(135deg, var(--clr-border), #ddd);
            color: var(--clr-text);
            border: 2px solid var(--clr-border);
            font-weight: 700;
        }

        .btn--secondary:hover {
            background: linear-gradient(135deg, #ddd, #ccc);
            border-color: var(--clr-muted);
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0,0,0,.1);
        }

        .btn i {
            font-size: 16px;
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 200px;
                left: -200px;
            }

            .main-content {
                margin-left: 0;
            }

            .form-wrapper {
                padding: 24px 16px;
            }

            .form-card {
                padding: 24px;
            }

            .header-title__main {
                font-size: 24px;
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
                <li><a href="../category/index.php" class="sidebar-nav__link">
                    <span class="sidebar-nav__icon"><i class="fas fa-chart-line"></i></span>
                    <span>Tableau de bord</span>
                </a></li>
                <li><a href="show.php" class="sidebar-nav__link">
                    <span class="sidebar-nav__icon"><i class="fas fa-book"></i></span>
                    <span>Mes recettes</span>
                </a></li>
                <li><a href="create.php" class="sidebar-nav__link active">
                    <span class="sidebar-nav__icon"><i class="fas fa-plus"></i></span>
                    <span>Ajouter une recette</span>
                </a></li>
            </ul>
        </section>

        <section class="sidebar-section">
            <h3 class="sidebar-section__title">Organisation</h3>
            <ul class="sidebar-nav">
                <li><a href="../category/showCat.php" class="sidebar-nav__link">
                    <span class="sidebar-nav__icon"><i class="fas fa-folder"></i></span>
                    <span>Catégories</span>
                </a></li>
                <li><a href="#" class="sidebar-nav__link">
                    <span class="sidebar-nav__icon"><i class="fas fa-star"></i></span>
                    <span>Favoris</span>
                </a></li>
            </ul>
        </section>

        <section class="sidebar-section">
            <h3 class="sidebar-section__title">Compte</h3>
            <ul class="sidebar-nav">
                <li><a href="#" class="sidebar-nav__link">
                    <span class="sidebar-nav__icon"><i class="fas fa-user"></i></span>
                    <span>Mon profil</span>
                </a></li>
                <li><a href="#" class="sidebar-nav__link">
                    <span class="sidebar-nav__icon"><i class="fas fa-cog"></i></span>
                    <span>Paramètres</span>
                </a></li>
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

        <!-- ─── TOP HEADER ─── -->
        <header class="top-header">
            <div class="header-title">
                <h1 class="header-title__main">Ajouter une recette</h1>
                <p class="header-title__sub">Créez et partagez votre recette préférée</p>
            </div>

            <div class="header-actions">
                <a href="show.php" class="btn-header">
                    <i class="fas fa-arrow-left"></i>
                    Retour
                </a>
            </div>
        </header>

        <!-- ─── FORM AREA ─── -->
        <div class="form-wrapper">
            <div class="form-container">
                <div class="form-header">
                    <h2 class="form-header__title">Formulaire de création</h2>
                    <p class="form-header__subtitle">Remplissez les informations de votre recette ci-dessous</p>
                </div>

                <div class="form-card">
                    <?php if (!empty($message)): ?>
                        <div class="message <?php echo $messageType; ?>">
                            <i class="fas fa-<?php echo $messageType === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                            <span><?php echo htmlspecialchars($message); ?></span>
                        </div>
                    <?php endif; ?>

                    <form method="POST" enctype="multipart/form-data">
                        <!-- TÂCHE: Créer un champ de formulaire pour le nom de la recette -->
                        <div class="form-group">
                            <label class="form-group__label form-group__label--required">Nom de la recette</label>
                            <input type="text" name="name" class="form-group__input" placeholder="Ex: Tajine de poulet aux citrons" value="<?php echo isset($name) ? htmlspecialchars($name) : ''; ?>" required>
                        </div>

                        <!-- TÂCHE: Créer un champ textarea pour la description -->
                        <div class="form-group">
                            <label class="form-group__label form-group__label--required">Description</label>
                            <textarea name="description" class="form-group__textarea" placeholder="Décrivez votre recette en détail..." required><?php echo isset($description) ? htmlspecialchars($description) : ''; ?></textarea>
                        </div>

                        <!-- TÂCHE: Créer un select pour les catégories -->
                        <div class="form-group">
                            <label class="form-group__label form-group__label--required">Catégorie</label>
                            <select name="category_id" class="form-group__select" required>
                                <option value="">-- Sélectionner une catégorie --</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo $cat['id']; ?>" <?php echo (isset($categoryId) && $categoryId == $cat['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($cat['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- TÂCHE: Créer un input pour l'upload d'image -->
                        <div class="form-group">
                            <label class="form-group__label">Photo de la recette</label>
                            <input type="file" name="image" class="form-group__input" accept="image/*">
                        </div>

                        <!-- User ID (caché) -->
                        <input type="hidden" name="user_id" value="<?php echo isset($_SESSION['user_id']) ? $_SESSION['user_id'] : '1'; ?>">

                        <!-- Actions -->
                        <div class="form-actions">
                            <button type="submit" class="btn btn--primary">
                                <i class="fas fa-save"></i>
                                Ajouter la recette
                            </button>
                            <a href="show.php" class="btn btn--secondary">
                                <i class="fas fa-times"></i>
                                Annuler
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>

</div>


</body>
</html>
