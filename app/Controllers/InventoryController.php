<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Models\Inventory;
use App\Models\StockLog;
use App\Models\Branch;  

class InventoryController extends Controller
{
    protected $session;

    public function __construct()
    {
        $this->session = Session::getInstance();
    }

    // ---- GET /api/inventory?branch_id= ----
    /** View inventory for a branch */
    public function index()
    {
        $branchId = (int)($_GET['branch_id'] ?? 0);
        try {
            $stock = $branchId
                ? Inventory::getByBranch($branchId)
                : Inventory::getByBranch(null);
            $this->ok($stock);
        } catch (\Exception $e) {
            $this->fail($e->getMessage());
        }
    }

    // ---- GET /api/inventory/low-stock?branch_id= ----
    /** Get low stock items */
    public function lowStock()
    {
        $branchId = (int)($_GET['branch_id'] ?? 0) ?: null;
        try {
            $this->ok(Inventory::getLowStock($branchId));
        } catch (\Exception $e) {
            $this->fail($e->getMessage());
        }
    }

    // ---- GET /api/inventory/value?branch_id= ----
    /** Get total inventory value */
    public function value()
    {
        $branchId = (int)($_GET['branch_id'] ?? 0) ?: null;
        try {
            $this->ok(Inventory::getTotalValue($branchId));
        } catch (\Exception $e) {
            $this->fail($e->getMessage());
        }
    }

    // ---- GET /api/inventory/history?branch_id=&item_id= ----
    /** Get stock movement history */
    public function history()
    {
        $branchId = (int)($_GET['branch_id'] ?? 0) ?: null;
        $itemId   = (int)($_GET['item_id'] ?? 0) ?: null;
        try {
            $this->ok(StockLog::getHistory($branchId, $itemId, 100));
        } catch (\Exception $e) {
            $this->fail($e->getMessage());
        }
    }

    // ---- GET /api/inventory/report?branch_id= ----
    /** Generate movement summary report */
    public function report()
    {
        $branchId = (int)($_GET['branch_id'] ?? 0);
        if (!$branchId) return $this->fail('branch_id is required');
        try {
            $this->ok(StockLog::getReport($branchId));
        } catch (\Exception $e) {
            $this->fail($e->getMessage());
        }
    }

    // ---- POST /api/inventory/adjust ----
    /** Record a stock adjustment (+/-) */
    public function adjust()
    {
        $data     = $this->json_input();
        $itemId   = (int)($data['item_id'] ?? 0);
        $branchId = (int)($data['branch_id'] ?? 0);
        $change   = (int)($data['quantity_change'] ?? 0);
        $notes    = $data['notes'] ?? null;

        if (!$itemId || !$branchId || $change === 0) {
            return $this->fail('item_id, branch_id, and quantity_change are required');
        }

        try {
            $stock = Inventory::getOrCreate($itemId, $branchId);
            $stock->adjust($change);

            $userId = $this->session->getUser()['id'] ?? 0;
            StockLog::record($itemId, $branchId, 'adjustment', $change, $notes, $userId);

            $this->ok(['message' => 'Stock adjusted', 'new_quantity' => $stock->quantity]);
        } catch (\Exception $e) {
            $this->fail($e->getMessage());
        }
    }

    // ---- POST /api/inventory/damage ----
    /** Mark items as damaged */
    public function damage()
    {
        $data     = $this->json_input();
        $itemId   = (int)($data['item_id'] ?? 0);
        $branchId = (int)($data['branch_id'] ?? 0);
        $amount   = (int)($data['quantity'] ?? 0);
        $notes    = $data['notes'] ?? 'Marked as damaged';

        if (!$itemId || !$branchId || $amount <= 0) {
            return $this->fail('item_id, branch_id, and quantity are required');
        }

        try {
            $stock = Inventory::getOrCreate($itemId, $branchId);
            $stock->addDamaged($amount);

            $userId = $this->session->getUser()['id'] ?? 0;
            StockLog::record($itemId, $branchId, 'damage', -$amount, $notes, $userId);

            $this->ok(['message' => 'Items marked as damaged', 'damaged_quantity' => $stock->damaged_quantity]);
        } catch (\Exception $e) {
            $this->fail($e->getMessage());
        }
    }

    // ---- Helpers ----

    private function getUserBranch()
    {
        $user = $this->session->getUser();
        return $user['branch_id'] ?? 0;
    }

    private function json_input()
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
