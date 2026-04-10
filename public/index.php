<?php


session_start();

require_once '../Models/User.php' ;
require_once '../Controllers/AuthController.php' ;


$authController = new AuthController() ;

$action = isset($_GET['action']) ? $_GET['action'] : '' ;

switch ($action) {
    case 'register':
        $authController->showRegisterForm() ;
        break;
    case 'register_submit':
        $authController->Register() ;
        break;
    case 'login':
        $authController->showLoginForm() ;
        break;
    case 'login_submit':
        $authController->Login() ;
        break;
    case 'logout':
        $authController->Logout() ;
        break;
    default:
}
