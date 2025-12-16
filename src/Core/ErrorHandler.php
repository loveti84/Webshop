<?php

namespace Core;

class ErrorHandler
{
    public static function showErrorPage(): void
    {
        http_response_code(500);
        error_log('Error page displayed');
        
        $errorPagePath = BASE_PATH . '/src/assets/html/error.html';
        
        if (file_exists($errorPagePath)) {
            readfile($errorPagePath);
        } else {
            //still if the file is deleted somehow
            echo '<!DOCTYPE html><html><head><title>Error</title></head><body><h1>Oeps! Er is iets misgegaan</h1><p><a href="/webshop/products">Naar Homepage</a></p></body></html>';
        }
        
        exit;
    }

    public static function show404Page(): void
    {
        http_response_code(404);
        error_log('404 page displayed');
        
        $notFoundPagePath = BASE_PATH . '/src/assets/html/404.html';
        
        if (file_exists($notFoundPagePath)) {
            readfile($notFoundPagePath);
        } else {
            //still if the file is deleted somehow
            echo '<!DOCTYPE html><html><head><title>404</title></head><body><h1>Pagina niet gevonden</h1><p><a href="/webshop/products">Naar Homepage</a></p></body></html>';
        }
        
        exit;
    }
}
