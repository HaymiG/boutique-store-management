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
    $router->get('/', 'AuthController@showLogin', 'home');
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
    // MANAGER ROUTES
    // ============================================
    
    // User Management
    $router->get('/manager/users', 'AdminUserController@index', 'manager.users.index');
    $router->get('/manager/users/create', 'AdminUserController@create', 'manager.users.create');
    $router->post('/manager/users', 'AdminUserController@store', 'manager.users.store');
    $router->get('/manager/users/{id}', 'AdminUserController@show', 'manager.users.show');
    $router->get('/manager/users/{id}/edit', 'AdminUserController@edit', 'manager.users.edit');
    $router->put('/manager/users/{id}', 'AdminUserController@update', 'manager.users.update');
    $router->post('/manager/users/{id}', 'AdminUserController@update', 'manager.users.update.post');
    $router->delete('/manager/users/{id}', 'AdminUserController@destroy', 'manager.users.destroy');
    $router->post('/manager/users/{id}/reset-password', 'AdminUserController@resetPassword', 'manager.users.resetpw');

    // Role & Permission Management
    $router->get('/manager/roles', 'RoleController@index', 'manager.roles.index');

    // Branch Management
    $router->get('/manager/branches', 'BranchController@index', 'manager.branches.index');
    $router->get('/manager/branches/create', 'BranchController@create', 'manager.branches.create');
    $router->post('/manager/branches', 'BranchController@store', 'manager.branches.store');
    $router->get('/manager/branches/{id}/edit', 'BranchController@edit', 'manager.branches.edit');
    $router->put('/manager/branches/{id}', 'BranchController@update', 'manager.branches.update');
    $router->delete('/manager/branches/{id}', 'BranchController@destroy', 'manager.branches.destroy');

    // Reports
    $router->get('/manager/reports/sales', 'ReportController@salesReport', 'manager.reports.sales');
    $router->get('/manager/reports/inventory', 'ReportController@inventoryReport', 'manager.reports.inventory');

    // ============================================
    // STORE KEEPER ROUTES
    // ============================================
    $router->get('/store-keeper/inventory', 'InventoryController@index', 'keeper.inventory.index');
    $router->get('/store-keeper/stock', 'StockController@index', 'keeper.stock.index');

    // ============================================
    // SELLER ROUTES
    // ============================================
    $router->get('/seller/sales/create', 'SalesController@create', 'seller.sales.create');
    $router->get('/seller/inventory', 'InventoryController@view', 'seller.inventory.view');
    $router->get('/seller/sales/daily', 'SalesController@myDailySales', 'seller.sales.daily');

    // ============================================
    // ERROR ROUTES
    // ============================================
    $router->get('/404', 'ErrorController@notFound', 'error.404');
    $router->get('/500', 'ErrorController@serverError', 'error.500');

};
?>
