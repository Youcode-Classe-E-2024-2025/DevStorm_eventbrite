<?php
namespace App\Core;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class Controller {
    protected $twig;

    public function __construct() {
        // Use the correct absolute path to Views directory
        $loader = new FilesystemLoader(dirname(__DIR__) . '/Views');
        $this->twig = new Environment($loader);
    }

    protected function view(string $path, array $data = []): void {
        echo $this->twig->render($path . '.twig', $data);
    }
}
