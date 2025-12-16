<?php

namespace Controllers;

use Core\Controller;
use Core\ErrorHandler;

class ViewController extends Controller
{
    public function products()
    {
        $this->renderView('products.html');
    }

    public function product()
    {
        $this->renderView('product.html');
    }

    public function popular()
    {
        $this->renderView('popular.html');
    }

    public function login()
    {
        $this->renderView('login.html');
    }

    private function renderView($filename)
    {
        $filePath = BASE_PATH . '/src/assets/html/' . $filename;
        
        
        if (!file_exists($filePath)) {
            http_response_code(404);
            ErrorHandler::show404Page();
            exit;
        }
        readfile($filePath);
        error_log("ViewController: HTML output completed for $filename");
        exit;
    }
}
