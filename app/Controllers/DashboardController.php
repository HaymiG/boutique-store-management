<?php

/**
 * Dashboard Controller
 * Handles dashboard views
 */

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;

class DashboardController extends Controller
{
    protected $session;

    public function __construct()
    {
        $this->session = Session::getInstance();
    }

    /**
     * Show dashboard
     */
    public function index()
    {
        // Check if user is authenticated
        if (!$this->session->isAuthenticated()) {
            header('Location: /login');
            exit;
        }

        // Include dashboard page
        include FRONTEND_PATH . '/pages/dashboard.html';
    }
}
