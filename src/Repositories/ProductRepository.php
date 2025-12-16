<?php

namespace Repositories;

use PDO;

class ProductRepository
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function getAll(): array
    {
        $stmt = $this->db->prepare("SELECT * FROM products");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getById(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->execute([$id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public function exists(int $id): bool
    {
        return $this->getById($id) !== null;
    }

    public function getAllWithRatings(int $page = 1, int $perPage = 12): array
    {
        $offset = ($page - 1) * $perPage;
        
        // Get total count
        $countSql = "SELECT COUNT(DISTINCT p.id) as total FROM products p";
        $countStmt = $this->db->prepare($countSql);
        $countStmt->execute();
        $total = $countStmt->fetch()['total'];
        
        // Get paginated results
        $sql = "SELECT p.*, 
                       COALESCE(AVG(pr.score), 0) as avg_rating,
                       COUNT(pr.id) as review_count
                FROM products p
                LEFT JOIN product_reviews pr ON p.id = pr.product_id
                GROUP BY p.id
                ORDER BY p.created_at DESC
                LIMIT ? OFFSET ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$perPage, $offset]);
        $products = $stmt->fetchAll();
        
        return [
            'products' => $products,
            'pagination' => [
                'total' => $total,
                'per_page' => $perPage,
                'current_page' => $page,
                'total_pages' => ceil($total / $perPage)
            ]
        ];
    }

    public function search(string $keyword): array
    {
        $searchTerm = "%{$keyword}%";
        $stmt = $this->db->prepare(
            "SELECT * FROM products WHERE name LIKE ?"
        );
        $stmt->execute([$searchTerm]);
        return $stmt->fetchAll();
    }

    public function getPopular(int $limit = 10): array
    {
        $stmt = $this->db->prepare("SELECT * FROM products ORDER BY click DESC LIMIT ?");
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }

    public function incrementClick(int $id): bool
    {
        $stmt = $this->db->prepare("UPDATE products SET click = click + 1 WHERE id = ?");
        return $stmt->execute([$id]);
    }


    //ongebruikt, maar voor de duidelijkheid hier neergezet
    public function filterByRatingAndKeyWordInTitle(float $minRating = 0, string $keyword = ''): array
    {
        $sql = "SELECT p.*, 
                       COALESCE(AVG(pr.score), 0) as avg_rating,
                       COUNT(pr.id) as review_count
                FROM products p
                LEFT JOIN product_reviews pr ON p.id = pr.product_id";
//building query
//append where if exists (like %% works, but unnecessary check)
        $params = [];
        if (!empty($keyword)) {
            $sql .= " WHERE p.name LIKE ?";
            $searchTerm = "%{$keyword}%";
            $params[] = $searchTerm;
        }
         //group by and having
        $sql .= " GROUP BY p.id";
        if ($minRating > 0) {
            $sql .= " HAVING avg_rating >= ?";
            $params[] = $minRating;
        }
        
        $sql .= " ORDER BY p.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }


    public function filter(array $filters = [], string $sortBy = 'created_at', string $sortOrder = 'desc', int $page = 1, int $perPage = 12): array
    {      //base query
        $baseSql = "SELECT p.*, 
                       COALESCE(AVG(pr.score), 0) as avg_rating,
                       COUNT(pr.id) as review_count
                FROM products p
                LEFT JOIN product_reviews pr ON p.id = pr.product_id";
        
        //building query with parameters
        $params = [];
        $whereClause = '';
        $havingClause = '';
        
        // WHERE clause for keyword search
        if (!empty($filters['keyword'])) {
            //apend to sql
            $whereClause = " WHERE p.name LIKE ?";
            $params[] = "%{$filters['keyword']}%";
        }
        
        // HAVING for minimum rating
        if (isset($filters['min_rating']) && $filters['min_rating'] > 0) {
            $havingClause = " HAVING avg_rating >= ?";
            $havingParam = $filters['min_rating'];
        }
        
        // Get total count (before pagination)
        $countSql = "SELECT COUNT(DISTINCT p.id) as total FROM products p";
        if (!empty($whereClause)) {
            $countSql .= $whereClause;
        }
        $countStmt = $this->db->prepare($countSql);
        $countParams = [];
        if (!empty($filters['keyword'])) {
            $countParams[] = "%{$filters['keyword']}%";
        }
        $countStmt->execute($countParams);
        //for the feature see total result of the filter
        $totalBeforeRating = $countStmt->fetch()['total'];
        
        // if we have a rating filter, we need to count after grouping
        if (!empty($havingClause)) {
            $countWithRatingSql = $baseSql . $whereClause . " GROUP BY p.id" . $havingClause;
            //appended havong clause on Avg rating
            $countWithRatingStmt = $this->db->prepare($countWithRatingSql);
            //isset is is true because of validation back and front end (it will be greater than 0), end having clause is set)
            $countWithRatingStmt->execute($params + (isset($havingParam) ? [$havingParam] : []));
            $total = $countWithRatingStmt->rowCount();
        } else {
            $total = $totalBeforeRating;
        }
        
        // build query
        $sql = $baseSql . $whereClause;
        
        // GROUP BY is required queries on avarage rating
        $sql .= " GROUP BY p.id";
        
        if (!empty($havingClause)) {
            $sql .= $havingClause;
            $params[] = $havingParam;
        }
        // specific sorting on table columns
        $validSortColumns = [
            'rating' => 'avg_rating',
            'price' => 'p.price',
            'clicks' => 'p.click',
            'created_at' => 'p.created_at',
            'name' => 'p.name'
        ];
        
        $sortColumn = $validSortColumns[$sortBy] ?? 'p.created_at';
        $sortDirection = strtoupper($sortOrder) === 'ASC' ? 'ASC' : 'DESC';
        
        // Append order by
        $sql .= " ORDER BY {$sortColumn} {$sortDirection}";
        
        // Add pagination
        $offset = ($page - 1) * $perPage;


        //skip first <offset> rows fetch the next <perPage> rows 
        $sql .= " LIMIT ? OFFSET ?";
        $params[] = $perPage;
        $params[] = $offset;
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $products = $stmt->fetchAll();
        
        return [
            'products' => $products,
            'pagination' => [
                'total' => $total,
                'per_page' => $perPage,
                'current_page' => $page,
                'total_pages' => ceil($total / $perPage)
            ]
        ];
    }
}
