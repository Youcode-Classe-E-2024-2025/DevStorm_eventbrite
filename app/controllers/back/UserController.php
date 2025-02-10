<?php

namespace App\controllers\back;

use App\Core\Controller;
use App\Core\Session;
use App\Core\Security;
use App\Models\User;
use App\Core\Auth;

class UserController extends Controller {

    public function register(): void
    {
        $security = new Security();
        $tokenCsrf = $security->Csrftoken();
        $this->view('auth/register', ['Csrftoken' => $tokenCsrf]);
    }

    public function login(): void
    {
        $security = new Security();
        $tokenCsrf = $security->Csrftoken();
        $this->view('auth/login', ['Csrftoken' => $tokenCsrf]);
    }

    public function handleRegister(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $security = new Security();
            if (!$security->checkCsrfToken($_POST['csrf_token'])) {
                header('Location: /register?error=invalid_csrf_token');
                exit;
            }

            $user = new User();
            $user->username = $_POST['name'];
            $user->email = $_POST['email'];
            $user->password = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $user->role = $_POST['role'];

            if ($user->save()) {
                header('Location: /login?success=registration_complete');
            } else {
                header('Location: /register?error=registration_failed');
            }
            exit;
        }
    }

    public function handleLogin(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $security = new Security();
            if (!$security->checkCsrfToken($_POST['csrf_token'])) {
                header('Location: /login?error=invalid_csrf_token');
                exit;
            }

            $user = new User();
            $user->email = $_POST['email'];
            $user->password = $_POST['password'];

            if ($user->login()) {
                header('Location: /dashboard');
            } else {
                header('Location: /login?error=invalid_credentials');
            }
            exit;
        }
    }

    public function logout(): void 
    {
        $session = new Session();
        $session->destroy();
        header('Location: /login');
        exit;
    }

    public function Home(): void
    {
        $this->view('front/home');
    }
}
