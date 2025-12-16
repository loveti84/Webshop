<?php

namespace Controllers;

use Core\Controller;
use Repositories\UserRepository;
use Repositories\ProductReviewRepository;
use Core\Validator;
use COre\ValidationException;
class UserController extends Controller
{
    private UserRepository $userRepo;
    private ProductReviewRepository $reviewRepo;

    public function __construct(UserRepository $userRepo, ProductReviewRepository $reviewRepo)
    {
        $this->userRepo = $userRepo;
        $this->reviewRepo = $reviewRepo;
    }

    public function register()
    {
        // Parse JSON request body
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!$data) {
            $this->error('Ongeldige JSON request body', 400);
        }
    
        $validator = $this->validator()
            ->string($data['username'] ?? null, 'Gebruikersnaam', 50, minLength: 3)
            ->string($data['name'] ?? null, 'Naam', 100, minLength: 1);
        $validator->custom('Gebruikersnaam', function($username) {
            return $username && preg_match('/^[a-zA-Z0-9_-]+$/', $username);
        }, 'Gebruikersnaam mag alleen letters, cijfers, underscore en streepje bevatten');
        if ($validator->fails()) {
            error_log('Validation errors: ' . $validator->errorMessage());
            $this->error($validator->errorMessage(), 400);
        }
        $username = $validator->get('Gebruikersnaam');
        $name = $validator->get('Naam');

        if ($this->userRepo->usernameExists($username)) {
            $this->error('Gebruikersnaam bestaat al', 409);
        }

        $userId = $this->userRepo->create([
            'name' => $name,
            'username' => $username
        ]);

        $user = $this->userRepo->getById($userId);

        // Store user in session
        $_SESSION['user'] = [
            'id' => $user['id'],
            'username' => $user['username'],
            'name' => $user['name']
        ];

        $this->success(['user' => $_SESSION['user']], 'Gebruiker aangemaakt');
    }

    public function login()
    {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!$data) {
            $this->error('Ongeldige JSON request body', 400);
        }
        
        $validator = $this->validator()
            ->string($data['username'] ?? null, 'Gebruikersnaam', 50, minLength: 3);
        
        if ($validator->fails()) {
            $this->error($validator->errorMessage(), 400);
        }
        
        $username = $validator->get('Gebruikersnaam');
        $user = $this->userRepo->findByUsername($username);

        if (!$user) {
            $this->error('Gebruiker niet gevonden', 404);
        }

        // Store user in session
        $_SESSION['user'] = [
            'id' => $user['id'],
            'username' => $user['username'],
            'name' => $user['name']
        ];

        $this->success(['user' => $_SESSION['user']], 'Inloggen gelukt');
    }

    public function logout()
    {
        // Clear user from session
        unset($_SESSION['user']);
        
        $this->success(null, 'Uitgelogd');
    }

    public function current()
    {
        if (!isset($_SESSION['user'])) {
            $this->error('Not authenticated', 401);
        }

        $this->success(['user' => $_SESSION['user']]);
    }

    public function me()
    {
        if (!isset($_SESSION['user'])) {
            $this->error('Niet ingelogd', 401);
        }

        $userId = $_SESSION['user']['id'];
        $user = $this->userRepo->getById($userId);
        $stats = $this->userRepo->getStats($userId);

        $this->success([
            'user' => $user,
            'stats' => $stats
        ]);
    }

    public function show($id)
    {
        $user = $this->userRepo->getById($id);
        
        if (!$user) {
            $this->error('Gebruiker niet gevonden', 404);
        }

        $reviews = $this->userRepo->getById($id); // Note: Need ProductReviewRepository injected
        $stats = $this->userRepo->getStats($id);        $this->success([
            'user' => $user,
            'reviews' => $reviews,
            'stats' => $stats
        ]);
    }
}
