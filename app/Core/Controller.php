<?php

namespace App\Core;

use App\Core\View;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class Controller
{
    public  $twig;

    public function __construct()
    {
        $this->twig= View::getTwig();
    }

    public function view(string $view, array $data = [])
    {
        echo $this->twig->render($view . '.twig', $data); 
    }

    public function redirect(string $url)
    {
        header('Location: ' . $url);
        exit;
    }
}