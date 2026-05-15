<?php

/**
 * Product Controller
 * Handles API endpoints for products (items) and categories
 */

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Models\Item;
use App\Models\Category;

class ProductController extends Controller
{
    protected $session;

    public function __construct()
    {
        $this->session = Session::getInstance();
        parent::__construct();
    }

    // ==========================================
    // CATEGORY ENDPOINTS
    // ==========================================

    /** GET /api/products/categories */
    public function apiCategories()
    {
        try {
            $cats = Category::all();
            $this->ok(array_map(fn($c) => $c->toArray(), $cats));
        } catch (\Exception $e) {
            $this->fail($e->getMessage());
        }
    }

    /** POST /api/products/categories */
    public function apiCreateCategory()
    {
        $data = $this->jsonInput();
        if (empty($data['name'])) {
            return $this->fail('Category name is required');
        }
        try {
            $cat = Category::create($data);
            $this->ok($cat->toArray(), 201);
        } catch (\Exception $e) {
            $this->fail($e->getMessage());
        }
    }

    /** DELETE /api/products/categories/{id} */
    public function apiDeleteCategory($id)
    {
        $cat = Category::findById($id);
        if (!$cat) {
            return $this->fail('Category not found', 404);
        }
        try {
            $cat->delete();
            $this->ok(['message' => 'Category deleted']);
        } catch (\Exception $e) {
            $this->fail($e->getMessage());
        }
    }

    // ==========================================
    // PRODUCT ENDPOINTS
    // ==========================================

    /**
     * GET /api/products
     * Supports: ?search=&category_id=&page=
     */
    public function index()
    {
        $page    = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = 20;

        try {
            $all    = Item::filter($_GET);
            $total  = count($all);
            $slice  = array_slice($all, ($page - 1) * $perPage, $perPage);

            $this->ok([
                'data'        => array_map(fn($i) => $i->toArray(), $slice),
                'total'       => $total,
                'page'        => $page,
                'per_page'    => $perPage,
                'total_pages' => (int) ceil($total / $perPage),
            ]);
        } catch (\Exception $e) {
            $this->fail($e->getMessage());
        }
    }

    /** GET /api/products/{id} */
    public function show($id)
    {
        $item = Item::findById($id);
        if (!$item) {
            return $this->fail('Product not found', 404);
        }

        $data = $item->toArray();
        $data['stock_by_branch'] = $item->getAllStock();
        $data['profit_margin']   = $item->getProfitMargin();
        $this->ok($data);
    }

    /** POST /api/products */
    public function store()
    {
        $data = $this->jsonInput();
        try {
            $item = Item::create($data);
            $this->ok($item->toArray(), 201);
        } catch (\Exception $e) {
            $this->fail($e->getMessage());
        }
    }

    /** PUT /api/products/{id} */
    public function update($id)
    {
        $item = Item::findById($id);
        if (!$item) {
            return $this->fail('Product not found', 404);
        }
        try {
            $item->updateData($this->jsonInput());
            $this->ok($item->toArray());
        } catch (\Exception $e) {
            $this->fail($e->getMessage());
        }
    }

    /** DELETE /api/products/{id} — soft delete */
    public function destroy($id)
    {
        $item = Item::findById($id);
        if (!$item) {
            return $this->fail('Product not found', 404);
        }
        try {
            $item->updateData(['is_active' => 0]);
            $this->ok(['message' => 'Product deleted']);
        } catch (\Exception $e) {
            $this->fail($e->getMessage());
        }
    }

    // ==========================================
    // HELPERS
    // ==========================================

    private function jsonInput()
    {
        return json_decode(file_get_contents('php://input'), true) ?? [];
    }

    private function ok($data, $code = 200)
    {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'data' => $data]);
        exit;
    }

    private function fail($msg, $code = 400)
    {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => $msg]);
        exit;
    }
}
