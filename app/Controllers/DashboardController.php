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
        // Just render the dashboard - let frontend handle auth via localStorage
        // Frontend will check auth and redirect to login if needed
        include FRONTEND_PATH . '/pages/dashboard.html';
    }
}
