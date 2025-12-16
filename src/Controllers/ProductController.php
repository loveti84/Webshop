<?php

namespace Controllers;

use Core\Controller;
use Core\Validator;
use Core\ValidationException;
use Error;
use PDOException;
use Repositories\ProductRepository;
use Repositories\ProductReviewRepository;

class ProductController extends Controller
{
    private ProductRepository $productRepo;
    private ProductReviewRepository $reviewRepo;

    public function __construct(ProductRepository $productRepo, ProductReviewRepository $reviewRepo)
    {
        $this->productRepo = $productRepo;
        $this->reviewRepo = $reviewRepo;
    }

    public function index()
    {
        try {
            // Validate pagination parameters
            $page = Validator::validateInt($_GET['page'] ?? 1, 'Page', 1, PHP_INT_MAX, false);
            $perPage = Validator::validateInt($_GET['per_page'] ?? 12, 'Per page', 1, 100, false);
        } catch (ValidationException $e) {
            $this->error($e->getMessage(), 400);
        }
        
        try {
            $result = $this->productRepo->getAllWithRatings($page, $perPage);
            $this->success($result['products'], $result['pagination']);
        } catch (PDOException $e) {
            error_log('Database error in ProductController::index - ' . $e->getMessage());
            $this->error('Er is een fout opgetreden bij het ophalen van de producten', 500);
        }
    }

    public function show()
    {
        try {
            $id = Validator::validateInt($_GET['id'] ?? null, 'Product ID', 1);
        } catch (ValidationException $e) {
            $this->error($e->getMessage(), 400);
        }
        
        try {
            $product = $this->productRepo->getById($id);
            
            if (!$product) {
                $this->error('Product niet gevonden', 404);
            }
            
            //only increment when the detail page is actually opend, not on a refresh
            error_log(print_r($_SERVER['HTTP_REFERER'], true));
        
            if (!in_array($_GET['id'] , $_SESSION['visited'])) {

                $this->productRepo->incrementClick($id);
                $_SESSION['visited'][] = $_GET['id'];
            }
            
            

            $reviews = $this->reviewRepo->getByProduct($id);
            $avgRating = $this->reviewRepo->getAverageScore($id);
            
            $this->success([
                'product' => $product,
                'reviews' => $reviews,
                'avg_rating' => round($avgRating, 2),
                'review_count' => count($reviews)
            ]);
        } catch (PDOException $e) {
            error_log('Database error in ProductController::show - ' . $e->getMessage());
            $this->error('Er is een fout opgetreden bij het ophalen van het product', 500);
        }
    }

    public function popular()
    {
        try {
            $products = $this->productRepo->getPopular(10);
            $this->success($products);
        } catch (PDOException $e) {
            error_log('Database error in ProductController::popular - ' . $e->getMessage());
            $this->error('Er is een fout opgetreden bij het ophalen van populaire producten', 500);
        }
    }

    public function filter()
    {
        // Static validation of filter parameters
        try {
            $minRating = Validator::validateFloat($_GET['min_rating'] ?? 0, 'Minimum rating', 0, 5, false);
            $keyword = Validator::validateString($_GET['q'] ?? '', 'Search keyword', 100, 0, false);
            $sortBy = Validator::validateString($_GET['sort_by'] ?? 'created_at', 'Sort by', 50, 0, false);
            $sortOrder = Validator::validateString($_GET['sort_order'] ?? 'desc', 'Sort order', 4, 0, false);
            $page = Validator::validateInt($_GET['page'] ?? 1, 'Page', 1, PHP_INT_MAX, false);
            $perPage = Validator::validateInt($_GET['per_page'] ?? 12, 'Per page', 1, 100, false);
        } catch (ValidationException $e) {
            $this->error($e->getMessage(), 400);
        }
        
        // Validate sort parameters
        $validSortOptions = ['rating', 'price', 'clicks', 'created_at', 'name'];
        if (!in_array($sortBy, $validSortOptions)) {
            $sortBy = 'created_at';
        }
        
        $validSortOrders = ['asc', 'desc'];
        if (!in_array(strtolower($sortOrder), $validSortOrders)) {
            $sortOrder = 'desc';
        }
        
        // Build filters array
        $filters = [];
        if (!empty($keyword)) {
            $filters['keyword'] = $keyword;
        }
        if ($minRating > 0) {
            $filters['min_rating'] = $minRating;
        }
        
        // Get filtered and sorted products
        try {
            $result = $this->productRepo->filter($filters, $sortBy, $sortOrder, $page, $perPage);
            $this->success($result['products'], $result['pagination']);
        } catch (PDOException $e) {
            error_log('Database error in ProductController::filter - ' . $e->getMessage());
            $this->error('Er is een fout opgetreden bij het filteren van producten', 500);
        }
    }
}
