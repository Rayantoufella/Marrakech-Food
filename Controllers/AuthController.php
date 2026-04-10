<?php

require_once __DIR__ . '/../Models/user.php';
require_once __DIR__ . '/../Models/DB.php';

class AuthController
{
    private $userModel;
    private $pdo;



    public function __construct()
    {

        $this->userModel = new user(null, '', '', '');

        $db = new DB('localhost', 'marrakech_food', 'root', '');
        $this->pdo = $db->connect();
    }

    private function ensureSessionStarted()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function showRegisterForm()
    {
        include __DIR__ . '/../Views/auth/register.php';
    }


    public function showRgistreForm()
    {
        $this->showRegisterForm();
    }

    public function register()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {



            $name = trim(isset($_POST['name']) ? $_POST['name'] : '');
            $email = trim(isset($_POST['email']) ? $_POST['email'] : '');
            $password = trim(isset($_POST['password']) ? $_POST['password'] : '');
            $confirmPassword = trim(isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '');

            $errors = [];

            if ($name === '' || $email === '' || $password === '' || $confirmPassword === '') {
                $errors[] = 'All fields are required';
            }

            if ($password !== $confirmPassword) {
                $errors[] = 'Passwords do not match';
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'This email is invalid';
            }

            if (!empty($errors)) {
                $this->showRegisterForm();
            }

            $this->userModel->setName($name);
            $this->userModel->setEmail($email);
            $this->userModel->setPassword(password_hash($password, PASSWORD_DEFAULT));

            $result = $this->userModel->create();

            $this->ensureSessionStarted();


            if ($result) {
                $_SESSION['success'] = 'Registration successful';
                header('Location: ../index.php?action=login');
            } else {
                $_SESSION['error'] = 'Registration failed';
            }
        }
    }
}

