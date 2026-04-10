<?php

session_start();

// ============================================================
// MODELS
// ============================================================
require_once '../Models/User.php';
require_once '../Models/Category.php';
require_once '../Models/Recette.php';

// ============================================================
// CONTROLLERS
// ============================================================
require_once '../Controllers/AuthController.php';
require_once '../Controllers/RecetteController.php';
require_once '../Controllers/CategoryController.php';

// ============================================================
// INSTANCES
// ============================================================
$authController     = new AuthController();
$recetteController  = new RecetteController();
$categoryController = new CategoryController();

// ============================================================
// ROUTING
// ============================================================
$action = isset($_GET['action']) ? $_GET['action'] : '';

switch ($action) {

    // -------- AUTH --------
    case 'register':
        $authController->showRegisterForm();
        break;

    case 'register_submit':
        $authController->Register();
        break;

    case 'login':
        $authController->showLoginForm();
        break;

    case 'login_submit':
        $authController->Login();
        break;

    case 'logout':
        $authController->Logout();
        break;

    // -------- RECETTES --------
    case 'recettes':
        $recetteController->index();
        break;

    case 'recette_create':
        $recetteController->create();
        break;

    case 'recette_store':
        $recetteController->store();
        break;

    case 'recette_show':
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $recetteController->show($id);
        break;

    case 'recette_edit':
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $recetteController->edit($id);
        break;

    case 'recette_update':
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $recetteController->update($id);
        break;

    case 'recette_delete':
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $recetteController->delete($id);
        break;

    case 'recette_filter':
        $catId = isset($_GET['cat_id']) ? (int)$_GET['cat_id'] : 0;
        $recetteController->filterByCategory($catId);
        break;

    // -------- CATEGORIES --------
    case 'categories':
        $categoryController->index();
        break;

    // -------- DEFAULT --------
    default:
        $authController->showLoginForm();
        break;
}


