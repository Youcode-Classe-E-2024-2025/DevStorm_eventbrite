<?php

namespace App\Controllers\back;

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
        $this->view('register',['Csrftoken' => $tokenCsrf]);
    }
    public function login(): void
    {
        $security = new Security();
        $tokenCsrf = $security->Csrftoken();
        $this->view('register',['Csrftoken' => $tokenCsrf]);
    }

    public function handleRegister(): void
    {
        var_dump($_POST);

        if (isset($_POST['submit'])) {
            $csrfToken = $_POST['csrf_token'];
            $security = new Security();
            $CsrfCheck = $security->checkCsrfToken($csrfToken);
            if (!$CsrfCheck) {
                header('Location: /register?error=invalid_csrf_token');
                exit;
            }
            $username = $_POST['name']; 
            $email = $_POST['email'];
            $password = $_POST['password'];
            $role = $_POST['role'];

            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            $user = new User();
            $user->username = $username;
            $user->email = $email;
            $user->password = $hashedPassword;
            $user->role = $role;
            $user->save();
            
            header('Location: /login');
            exit;
        }
    }

    public function handleLogin(): void
    {
        $csrfToken = $_POST['csrf_token'];
        $security = new Security();
        $CsrfCheck = $security->checkCsrfToken($csrfToken);
        if (!$CsrfCheck) {
            header('Location: /register?error=invalid_csrf_token');
            exit;
        }
            $email = $_POST['email'];
            $password = $_POST['password'];

            $user = new User();
            $user->email = $email;
            $user->password = $password;

            if($user->login()) {
                header('Location: /home');
            } else {
                header('Location: /login?error=invalid_credentials');
            }
    }

    public function logout(): void {
        $session = new Session();
        $session->destroy();
        header('Location: /register');
    }


   public function Home(): void{
         $this->view('front/home');
   }
}
