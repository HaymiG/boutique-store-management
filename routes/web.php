<?php

/**
 * Application Routes
 * Define all routes for the application
 */

namespace App\Routes;

use App\Core\Router;

/**
 * Initialize router and register all routes
 */
return function (Router $router) {

    // ============================================
    // AUTH ROUTES (Public)
    // ============================================
    $router->get('/', 'AuthController@showHome', 'home');
    $router->get('/login', 'AuthController@showLogin', 'login');
    $router->post('/login', 'AuthController@login', 'login.store');
    $router->post('/logout', 'AuthController@logout', 'logout');
    $router->get('/register', 'AuthController@showRegister', 'register');
    $router->post('/register', 'AuthController@register', 'register.store');

    // ============================================
    // PROTECTED ROUTES (Require Authentication)
    // ============================================

    // DASHBOARD
    $router->get('/dashboard', 'DashboardController@index', 'dashboard');

    // ============================================
    // MANAGER ROUTES (Disabled - controllers not yet implemented)
    // ============================================
    /* DISABLED - coming soon
    $router->group(['prefix' => '/manager', 'middleware' => 'auth.manager'], function ($router) {
        // Coming soon...
    });
    */

    // ============================================
    // STORE KEEPER ROUTES (Disabled - controllers not yet implemented)
    // ============================================
    /* DISABLED - coming soon
    $router->group(['prefix' => '/store-keeper', 'middleware' => 'auth.store_keeper'], function ($router) {
        // Coming soon...
    });

    $router->group(['prefix' => '/seller', 'middleware' => 'auth.seller'], function ($router) {
        // Coming soon...
    });
    */

    // ============================================
    // API ROUTES - AUTHENTICATION
    // ============================================
    $router->post('/api/login', 'UserController@apiLogin', 'api.login');
    $router->post('/api/logout', 'UserController@apiLogout', 'api.logout');
    $router->get('/api/user', 'UserController@apiGetUser', 'api.user');

    // ============================================
    // API ROUTES - PASSWORD RESET
    // ============================================
    $router->post('/api/password/forgot', 'PasswordResetController@apiForgotPassword', 'api.password.forgot');
    $router->post('/api/password/reset', 'PasswordResetController@apiResetPassword', 'api.password.reset');
    $router->get('/api/password/verify-token', 'PasswordResetController@apiVerifyToken', 'api.password.verify');

    // ============================================
    // API ROUTES (Optional - JSON responses) - DISABLED
    // ============================================
    /*
    $router->group(['prefix' => '/api/v1'], function ($router) {

        // Inventory API
        $router->get('/inventory', 'Api\InventoryController@index', 'api.inventory.index');
        $router->get('/inventory/{id}', 'Api\InventoryController@show', 'api.inventory.show');
        $router->post('/inventory', 'Api\InventoryController@store', 'api.inventory.store');
        $router->put('/inventory/{id}', 'Api\InventoryController@update', 'api.inventory.update');

        // Sales API
        $router->get('/sales', 'Api\SalesController@index', 'api.sales.index');
        $router->post('/sales', 'Api\SalesController@store', 'api.sales.store');

        // Branch API
        $router->get('/branches', 'Api\BranchController@index', 'api.branches.index');
    });
    */

    // ============================================
    // ERROR ROUTES - DISABLED (not yet implemented)
    // ============================================
    /*
    $router->get('/404', 'ErrorController@notFound', 'error.404');
    $router->get('/500', 'ErrorController@serverError', 'error.500');
    */
};
