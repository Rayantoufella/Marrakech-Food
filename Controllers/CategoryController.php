
<?php

class CategoryController {
    private $categoryModel;

    public function __construct($categoryModel) {
        $this->categoryModel = $categoryModel;
    }

    // Check if user is logged in
    private function requireAuth() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit();
        }
    }

    // GET /categories — Display all categories
    
    public function index() {
        $this->requireAuth();

        $categories = $this->categoryModel->getAll();

        if (!$categories) {
            $categories = [];
            $_SESSION['error'] = "Aucune catégorie trouvée.";
        }

        require 'views/category/index.php';
    }
}