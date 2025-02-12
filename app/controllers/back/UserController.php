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


    public function login(): void
    {
        if (Auth::isLoggedIn()){
            $this->redirect("/");
        }
        // var_dump($_SESSION);die;
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

    public function register(): void
    {
        if (Auth::isLoggedIn()){
            $this->redirect("/");
        }
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

    public function handleRegister(): void
    {

        if (!Security::checkCsrfToken($_POST['csrf_token'])) {
            Session::setFlashMessage('red', 'CSRF token validation failed. Possible CSRF attack.');
            Security::Csrftoken();
            $this->redirect('/register');
        }

        $name = Security::XSS($_POST['name']);
        $email = Security::XSS($_POST['email']);
        $role = Security::XSS($_POST['role']);
        $password = $_POST['password'];
        // $confirm_password = $_POST['confirm_password'];

        //  Validator  
        $validator = new Validator();

        // validation
        $validator->validateString($name, 'name');
        $validator->validateEmail($email);
        $validator->validatePassword($password);
        $validator->validateRole($role);
        // $validator->validateConfirmPassword($password, $confirm_password);

        if ($validator->isValid()) {
            $this->userModel = new User('',$name, $email, password_hash($password, PASSWORD_BCRYPT),Role::from($role));
            if ($this->userModel->save()) {
                Session::setFlashMessage('green', 'Registration successful. Please login.');
                $this->redirect('/login');
            } else {
                Session::setFlashMessage('red', 'Registration failed. Please try again.');
                $this->redirect('/register');
            }
        } else {
            Session::setFlashMessage('red', 'Registration failed. All feild are required');
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
            $this->userModel = new User('','',$email, $password);
            $user = $this->userModel->login();

            if ($user) {

                Auth::login($user);
                Session::setFlashMessage('green', 'Login successful. Welcome back! ' . $user['name']);

                if ($user['role'] === Role::ADMIN->value) {
                    $this->redirect('/admin/dashboard');
                } else if($user['role']===Role::ORGANISATEUR->value) {
                    $this->redirect('/organizer/dashboard');
                }else{
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

    public function handleGoogleAuth()
    {

            $dotenv = \Dotenv\Dotenv::createImmutable(__DIR__ . '/../../../');
            $dotenv->load();

            $client = new Google_Client();
            $client->setClientId($_ENV['GOOGLE_CLIENT_ID']);
            $client->setClientSecret($_ENV['GOOGLE_CLIENT_SECRET']);
            $client->setRedirectUri($_ENV['GOOGLE_REDIRECT_URI']);
            $client->addScope("email");
            $client->addScope("profile");

            if (!isset($_GET['code'])) {
                header("Location: " . filter_var($client->createAuthUrl(), FILTER_SANITIZE_URL));
                exit();
            }

            $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);

            if (isset($token['error'])) {
                throw new Exception("Google authentication failed: " . htmlspecialchars($token['error_description']));
            }

            $client->setAccessToken($token['access_token']);
            $google_oauth = new Google_Service_Oauth2($client);
            $google_account = $google_oauth->userinfo->get();

            $email = $google_account->email;
            $name = $google_account->name;
            $avatar = $google_account->picture;
            $role = Role::PARTICIPANT->value; // Assuming default role

            $userModel = new User();
            $user = $userModel->getUserByEmail($email);
            if (!$user) {

                $randomPassword = bin2hex(random_bytes(8));
                $user = new User('', $name, $email, password_hash($randomPassword, PASSWORD_BCRYPT), Role::from($role), $avatar);
                if (!$user->save()) {
                    Session::setFlashMessage('red', 'Registration failed. Please try again.');
                    $this->redirect('/register');
                }
            }
            Auth::login($user);
            header("Location: /");
            exit();
    }



}
