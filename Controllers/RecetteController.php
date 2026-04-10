
<?php

class RecetteController {
    private $recetteModel;
    private $categoryModel;

    public function __construct($recetteModel, $categoryModel) {
        $this->recetteModel = $recetteModel;
        $this->categoryModel = $categoryModel;
    }

    // Check if user is logged in
    private function requireAuth() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit();
        }
    }

    // GET /recettes — Display all MY recipes
    public function index() {
        $this->requireAuth();
        $userId = $_SESSION['user_id'];
        $recettes = $this->recetteModel->getAllByUser($userId);
        $categories = $this->categoryModel->getAll();
        require 'views/recette/index.php';
    }

    // GET /recettes/create — Show add form
    public function create() {
        $this->requireAuth();
        $categories = $this->categoryModel->getAll();
        require 'views/recette/create.php';
    }

    // POST /recettes/store — Save new recipe
    public function store() {
        $this->requireAuth();

        $name        = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $cat_id      = $_POST['cat_id'] ?? '';
        $user_id     = $_SESSION['user_id'];

        // Validation
        if (empty($name) || empty($description) || empty($cat_id)) {
            $_SESSION['error'] = "Tous les champs sont obligatoires.";
            header('Location: /recettes/create');
            exit();
        }

        $data = [
            'name'        => $name,
            'description' => $description,
            'cat_id'      => $cat_id,
            'user_id'     => $user_id,
        ];

        $success = $this->recetteModel->create($data);

        if ($success) {
            $_SESSION['success'] = "Recette ajoutée avec succès.";
        } else {
            $_SESSION['error'] = "Erreur lors de la création de la recette.";
        }

        header('Location: /recettes');
        exit();
    }

    // GET /recettes/show/{id} — Display recipe details
    public function show($id) {
        $this->requireAuth();

        $recette = $this->recetteModel->getById($id);

        if (!$recette) {
            $_SESSION['error'] = "Recette introuvable.";
            header('Location: /recettes');
            exit();
        }

        require 'views/recette/show.php';
    }

    // GET /recettes/edit/{id} — Show edit form
    public function edit($id) {
        $this->requireAuth();

        $recette = $this->recetteModel->getById($id);

        if (!$recette) {
            $_SESSION['error'] = "Recette introuvable.";
            header('Location: /recettes');
            exit();
        }

        // Ownership check
        if ($recette['user_id'] != $_SESSION['user_id']) {
            $_SESSION['error'] = "Accès refusé. Vous n'êtes pas le propriétaire.";
            header('Location: /recettes');
            exit();
        }

        $categories = $this->categoryModel->getAll();
        require 'views/recette/edit.php';
    }

    // POST /recettes/update/{id} — Save modifications
    public function update($id) {
        $this->requireAuth();

        $recette = $this->recetteModel->getById($id);

        if (!$recette || $recette['user_id'] != $_SESSION['user_id']) {
            $_SESSION['error'] = "Accès refusé ou recette introuvable.";
            header('Location: /recettes');
            exit();
        }

        $name        = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $cat_id      = $_POST['cat_id'] ?? '';

        if (empty($name) || empty($description) || empty($cat_id)) {
            $_SESSION['error'] = "Tous les champs sont obligatoires.";
            header("Location: /recettes/edit/$id");
            exit();
        }

        $data = [
            'name'        => $name,
            'description' => $description,
            'cat_id'      => $cat_id,
        ];

        $success = $this->recetteModel->update($id, $data);

        if ($success) {
            $_SESSION['success'] = "Recette modifiée avec succès.";
        } else {
            $_SESSION['error'] = "Erreur lors de la modification.";
        }

        header('Location: /recettes');
        exit();
    }

    // GET /recettes/delete/{id} — Delete a recipe
    public function delete($id) {
        $this->requireAuth();

        $recette = $this->recetteModel->getById($id);

        if (!$recette || $recette['user_id'] != $_SESSION['user_id']) {
            $_SESSION['error'] = "Accès refusé ou recette introuvable.";
            header('Location: /recettes');
            exit();
        }

        $success = $this->recetteModel->delete($id);

        if ($success) {
            $_SESSION['success'] = "Recette supprimée avec succès.";
        } else {
            $_SESSION['error'] = "Erreur lors de la suppression.";
        }

        header('Location: /recettes');
        exit();
    }

    // GET /recettes/cat/{id} — Filter recipes by category
    public function filterByCategory($catId) {
        $this->requireAuth();

        $userId   = $_SESSION['user_id'];
        $recettes = $this->recetteModel->getByCategory($userId, $catId);
        $categories = $this->categoryModel->getAll();

        require 'views/recette/index.php';
    }
}