<?php

namespace App\Core;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class Controller
{
    protected  $twig;
    protected  $viewFolder;

    public function __construct( $viewFolder = 'front') {
        $this->viewFolder = $viewFolder;
        $loader = new \Twig\Loader\FilesystemLoader(__DIR__ . '/../views/' . $this->viewFolder);
        $this->twig = new \Twig\Environment($loader);
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
