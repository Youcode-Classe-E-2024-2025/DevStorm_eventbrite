<?php

namespace App\controllers\back;

use App\Core\Controller;
use App\Core\Session;
use App\Core\Security;
use App\models\User;
use App\Core\Auth;
use App\Core\Validator;
use App\enums\Role;

class UserController extends Controller
{

    private $userModel;

    public function register(): void
    {
        $security = new Security();
        $tokenCsrf = $security->Csrftoken();
        $this->view(
            'auth/register',
            [
                'Csrftoken' => $tokenCsrf,
                'green' =>Session::getFlashMessage('green'),
                'red' => Session::getFlashMessage('red')
            ]
        );
    }

    public function login(): void
    {
        $security = new Security();
        $tokenCsrf = $security->Csrftoken();
        $this->view(
            'auth/login',
            [
                'Csrftoken' => $tokenCsrf,
                'green' =>Session::getFlashMessage('green'),
                'red' => Session::getFlashMessage('red')
            ]
        );
    }

    public function handleRegister(): void
    {

        $security = new Security();
        if (!$security->checkCsrfToken($_POST['csrf_token'])) {
            header('Location: /register?error=invalid_csrf_token');
            exit;
        }

        $name = Security::XSS($_POST['name']);
        $email = Security::XSS($_POST['email']);
        $password = $_POST['password'];
        // $confirm_password = $_POST['confirm_password'];

        //  Validator  
        $validator = new Validator();

        // validation
        $validator->validateString($name, 'name');
        $validator->validateEmail($email);
        $validator->validatePassword($password);
        // $validator->validateConfirmPassword($password, $confirm_password);

        if ($validator->isValid()) {
            $this->userModel = new User($name, $email, password_hash($password, PASSWORD_BCRYPT), Role::PARTICIPANT);
            if ($this->userModel->save()) {
                Session::setFlashMessage('green', 'Registration successful. Please login.');
                $this->redirect('/login');
            } else {
                Session::setFlashMessage('red', 'Registration failed. Please try again.');
                $this->redirect('/register');
            }
        } else {

            $_SESSION['errors'] = $validator->getErrors();
            $_SESSION['old'] = [
                'name' => $name,
                'email' => $email
            ];
            $this->redirect('/register');
        }
    }

    public function handleLogin(): void
    {

        if (!Security::checkCsrfToken($_POST['csrf_token'])) {
            Session::setFlashMessage('red', 'CSRF token validation failed. Possible CSRF attack.');
            Security::Csrftoken();
            $this->redirect('/login');
        }

        // Sanitize inputs
        $email = Security::XSS($_POST['email']);
        $password = $_POST['password'];

        // Validate inputs
        $validator = new Validator();
        $validator->validateEmail($email);
        $validator->validatePassword($password);

        if ($validator->isValid()) {
            $this->userModel = new User('',$email, $password);
            $user = $this->userModel->login();

            if ($user) {

                Session::setData('user', $user);
                Session::setFlashMessage('green', 'Login successful. Welcome back! ' . $user['name']);

                if ($user['role'] === Role::ADMIN->value) {
                    $this->redirect('/admin/dashboard');
                } else {
                    $this->redirect('/');
                }
            } else {
                Session::setFlashMessage('red', 'Invalid email or password.');
                $this->redirect('/login');
            }
        } else {
            Session::setData('errors', $validator->getErrors());
            Session::setData(
                'old',
                ['email' => $email,]
            );

            $this->redirect('/login');
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
