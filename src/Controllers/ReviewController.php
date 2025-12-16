<?php

namespace Controllers;

use Core\Controller;
use Repositories\ProductReviewRepository;
use Repositories\ProductRepository;
use Repositories\UserRepository;
use PDOException;

class ReviewController extends Controller
{
    private ProductReviewRepository $reviewRepo;
    private ProductRepository $productRepo;
    private UserRepository $userRepo;

    public function __construct(
        ProductReviewRepository $reviewRepo,
        ProductRepository $productRepo,
        UserRepository $userRepo
    ) {
        $this->reviewRepo = $reviewRepo;
        $this->productRepo = $productRepo;
        $this->userRepo = $userRepo;
    }

    public function create()
    {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!$data) {
            $this->error('Ongeldige JSON-aanvraag', 400);
        }
        
        $validator = $this->validator()
            ->string($data['username'] ?? null, 'Username', 50, 3)
            ->string($data['name'] ?? null, 'Name', 100, 1)
            ->int($data['product_id'] ?? null, 'Product ID', 1)
            ->string($data['text'] ?? null, 'Review text', 1000, minLength: 0)
            ->int($data['score'] ?? null, 'Score', 1, 5);

        $validator->custom('Username', function($username) {
            return $username && preg_match('/^[a-zA-Z0-9_-]+$/', $username);
        }, 'Gebruikersnaam mag alleen letters, cijfers, underscores en streepjes bevatten');

        if ($validator->fails()) {
            error_log('Validatiefouten: ' . $validator->errorMessage());
            $this->error($validator->errorMessage(), 400);
        }

        $username = $validator->get('Username');
        $name = $validator->get('Name');
        $productId = $validator->get('Product ID');
        $text = $validator->get('Review text');
        $score = $validator->get('Score');

        try {
            if (!$this->productRepo->exists($productId)) {
                $this->error('Product niet gevonden', 404);
            }

            $user = $this->userRepo->findByUsername($username);
            //the user error is not appended to the validator
            if (!$user) {
                $userId = $this->userRepo->create([
                    'username' => $username,
                    'name' => $name
                ]);
            } else {
                $userId = $user['id'];
            }

            if ($this->reviewRepo->userHasreviewed($userId, $productId)) {
                $this->error('Je hebt dit product al beoordeeld', 400);
            }

            $reviewId = $this->reviewRepo->create([
                'product_id' => $productId,
                'user_id' => $userId,
                'text' => $text,
                'score' => $score
            ]);

            $this->success(['id' => $reviewId], 'Review succesvol ingediend');
        } catch (PDOException $e) {
            error_log('Database error in ReviewController::create - ' . $e->getMessage());
            $this->error('Er is een fout opgetreden bij het indienen van de review', 500);
        }
    }
}
