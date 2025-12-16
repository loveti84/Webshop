<?php

namespace Core;
use Core\Validator;
class Controller
{
    protected function json($data, $statusCode = 200)
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }


    protected function success($data = [], $pagination = null, $message = 'Success')
    {
        $response = [
            'success' => true,
            'message' => $message,
            'data' => $data
        ];
        
        if ($pagination !== null) {
            $response['pagination'] = $pagination;
        }
        
        $this->json($response);
    }


    protected function error($message = 'Error', $statusCode = 400)
    {
        $this->json([
            'success' => false,
            'message' => $message
        ], $statusCode);
    }

   
    protected function validator(): Validator
    {
        return new Validator();
    }


    protected function isAuth()
    {
        return isset($_SESSION['user_id']);
    }


    protected function userId()
    {
        return $_SESSION['user_id'] ?? null;
    }

 







   
}
