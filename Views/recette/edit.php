<?php
session_start();

// TÂCHE 1: Inclure les models DB, recette, category, etc.
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

// Initialiser les variables
$message = '';
$messageType = '';
$recette = [];
$name = '';
$description = '';
$categoryId = '';
$recetteId = '';

// TÂCHE 4: Vérifier si l'ID recette est fourni en paramètre (GET)
if(!isset($_GET['id'])){
    header('Location: show.php');
    exit();
}

$recetteId = $_GET['id'];

// TÂCHE 5: Récupérer la recette de la base de données
try {
    $stmt = $pdo->prepare('SELECT * FROM recette WHERE id = :id');
    $stmt->bindParam(':id', $recetteId, PDO::PARAM_INT);
    $stmt->execute();
    $recette = $stmt->fetch(PDO::FETCH_ASSOC);

    if(!$recette){
        $message = 'Recette introuvable.';
        $messageType = 'error';
    } else {
        $name = $recette['name'];
        $description = $recette['description'];
        $categoryId = $recette['category_id'];
    }
} catch (PDOException $e) {
    $message = 'Erreur: ' . $e->getMessage();
    $messageType = 'error';
}

// TÂCHE 6: Vérifier si formulaire est soumis (POST)
if($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($recette)){

    // TÂCHE 7: Récupérer les données du formulaire
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $description = isset($_POST['description']) ? trim($_POST['description']) : '';
    $categoryId = isset($_POST['category_id']) ? $_POST['category_id'] : '';
    $imagePath = $recette['image'];

    // TÂCHE 8: Valider les données
    if(empty($name) || empty($description) || empty($categoryId)){
        $message = 'Tous les champs sont obligatoires.';
        $messageType = 'error';
    } else {

        // TÂCHE 9: Traiter l'upload de l'image (si nouveau fichier)
        if(isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK){
            $uploadDir = __DIR__ . '/../../public/uploads/recettes/';

            if(!is_dir($uploadDir)){
                mkdir($uploadDir, 0755, true);
            }

            $fileInfo = pathinfo($_FILES['image']['name']);
            $fileName = uniqid() . '_' . time() . '.' . $fileInfo['extension'];
            $uploadPath = $uploadDir . $fileName;

            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

            if(in_array($_FILES['image']['type'], $allowedTypes)){
                if(move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath)){
                    // Supprimer l'ancienne image si elle existe
                    if(!empty($recette['image'])){
                        $oldImagePath = __DIR__ . '/../../public/' . $recette['image'];
                        if(file_exists($oldImagePath)){
                            unlink($oldImagePath);
                        }
                    }
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

        // TÂCHE 10: Mettre à jour la recette dans la base de données
        if(empty($message)){
            try{
                $stmt = $pdo->prepare('UPDATE recette SET name = :name, description = :description, category_id = :category_id, image = :image WHERE id = :id');
                $stmt->bindParam(':name', $name);
                $stmt->bindParam(':description', $description);
                $stmt->bindParam(':category_id', $categoryId);
                $stmt->bindParam(':image', $imagePath);
                $stmt->bindParam(':id', $recetteId);
                $stmt->execute();

                $message = 'Recette modifiée avec succès!';
                $messageType = 'success';
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
    <title>Modifier une recette - Marrakech Food Lovers</title>

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
            --clr-input-bg:     #F5F0E8;
            --clr-success:      #10b981;
            --clr-error:        #ef4444;
            --clr-sidebar-bg:   #2A1F1B;
            --clr-sidebar-hover:#3D2E27;
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
        }

        .sidebar-section {
            margin-bottom: 28px;
        }

        .sidebar-section__title {
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
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

        .main-content {
            margin-left: 240px;
            flex: 1;
            display: flex;
            flex-direction: column;
        }

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
        }

        .header-actions {
            display: flex;
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
            transform: translateY(-2px);
        }

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
        }

        .form-header__subtitle {
            font-size: 15px;
            color: var(--clr-muted);
        }

        .form-card {
            background: var(--clr-surface);
            border-radius: 20px;
            padding: 48px;
            box-shadow: 0 20px 60px rgba(0,0,0,.12);
            border: 1px solid rgba(226, 221, 208, .5);
        }

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
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
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

        .form-group {
            margin-bottom: 28px;
        }

        .form-group__label {
            display: block;
            margin-bottom: 12px;
            font-weight: 700;
            font-size: 15px;
            color: var(--clr-text);
        }

        .form-group__label--required::after {
            content: ' *';
            color: var(--clr-error);
        }

        .form-group__input,
        .form-group__textarea,
        .form-group__select {
            width: 100%;
            padding: 16px 18px;
            border: 2px solid var(--clr-border);
            border-radius: 12px;
            background: linear-gradient(135deg, var(--clr-input-bg), #f8f5f0);
            font-size: 15px;
            color: var(--clr-text);
            outline: none;
            transition: all var(--transition);
        }

        .form-group__input:focus,
        .form-group__textarea:focus,
        .form-group__select:focus {
            border-color: var(--clr-primary);
            background: #fff;
            box-shadow: 0 0 0 5px rgba(192, 82, 43, 0.15);
        }

        .form-group__textarea {
            resize: vertical;
            min-height: 160px;
            line-height: 1.7;
        }

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
            text-transform: uppercase;
        }

        .btn--primary {
            background: linear-gradient(135deg, var(--clr-primary), #D06A3B);
            color: #fff;
            box-shadow: 0 10px 30px rgba(192, 82, 43, 0.4);
        }

        .btn--primary:hover {
            background: linear-gradient(135deg, var(--clr-primary-dark), #9A3D1C);
            transform: translateY(-3px);
        }

        .btn--secondary {
            background: linear-gradient(135deg, var(--clr-border), #ddd);
            color: var(--clr-text);
            border: 2px solid var(--clr-border);
        }

        .btn--secondary:hover {
            background: linear-gradient(135deg, #ddd, #ccc);
            transform: translateY(-3px);
        }

        .info-text {
            font-size: 13px;
            color: var(--clr-muted);
            margin-top: 8px;
        }

        @media (max-width: 768px) {
            .sidebar { width: 200px; left: -200px; }
            .main-content { margin-left: 0; }
            .form-wrapper { padding: 24px 16px; }
            .form-card { padding: 24px; }
            .header-title__main { font-size: 24px; }
        }
    </style>
</head>
<body>

<div class="dashboard-wrapper">

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
                <li><a href="create.php" class="sidebar-nav__link">
                    <span class="sidebar-nav__icon"><i class="fas fa-plus"></i></span>
                    <span>Ajouter une recette</span>
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

    <div class="main-content">

        <header class="top-header">
            <div class="header-title">
                <h1 class="header-title__main">Modifier une recette</h1>
                <p class="header-title__sub">Mettez à jour votre recette</p>
            </div>
            <div class="header-actions">
                <a href="show.php" class="btn-header">
                    <i class="fas fa-arrow-left"></i>
                    Retour
                </a>
            </div>
        </header>

        <div class="form-wrapper">
            <div class="form-container">
                <div class="form-header">
                    <h2 class="form-header__title">Éditer la recette</h2>
                    <p class="form-header__subtitle">Mettez à jour les informations de votre recette</p>
                </div>

                <div class="form-card">
                    <?php if (!empty($message)): ?>
                        <div class="message <?php echo $messageType; ?>">
                            <i class="fas fa-<?php echo $messageType === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                            <span><?php echo htmlspecialchars($message); ?></span>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($recette)): ?>
                    <form method="POST" enctype="multipart/form-data">
                        <div class="form-group">
                            <label class="form-group__label form-group__label--required">Nom de la recette</label>
                            <input type="text" name="name" class="form-group__input" placeholder="Ex: Tajine de poulet aux citrons" value="<?php echo htmlspecialchars($name); ?>" required>
                        </div>

                        <div class="form-group">
                            <label class="form-group__label form-group__label--required">Description</label>
                            <textarea name="description" class="form-group__textarea" placeholder="Décrivez votre recette..." required><?php echo htmlspecialchars($description); ?></textarea>
                        </div>

                        <div class="form-group">
                            <label class="form-group__label form-group__label--required">Catégorie</label>
                            <select name="category_id" class="form-group__select" required>
                                <option value="">-- Sélectionner --</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo $cat['id']; ?>" <?php echo ($categoryId == $cat['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($cat['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-group__label">Photo</label>
                            <input type="file" name="image" class="form-group__input" accept="image/*">
                            <?php if (!empty($recette['image'])): ?>
                                <p class="info-text">
                                    <i class="fas fa-info-circle"></i> Image actuelle: <?php echo basename($recette['image']); ?>
                                </p>
                            <?php endif; ?>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn btn--primary">
                                <i class="fas fa-save"></i>
                                Mettre à jour
                            </button>
                            <a href="show.php" class="btn btn--secondary">
                                <i class="fas fa-times"></i>
                                Annuler
                            </a>
                        </div>
                    </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    </div>

</div>

</body>
</html>
