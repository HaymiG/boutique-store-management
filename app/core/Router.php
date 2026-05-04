<?php
/**
 * Router Class - Custom URL Routing System
 * Handles route matching, dispatch, and request resolution
 */

namespace App\Core;

class Router
{
    /**
     * Array to store all registered routes
     */
    private $routes = [
        'GET' => [],
        'POST' => [],
        'PUT' => [],
        'DELETE' => [],
        'PATCH' => [],
    ];

    /**
     * Named routes for easy URL generation
     */
    private $namedRoutes = [];

    /**
     * Current matched route
     */
    private $currentRoute = null;

    /**
     * Route parameters extracted from URL
     */
    private $parameters = [];

    /**
     * Middleware stack
     */
    private $middleware = [];

    /**
     * Register a GET route
     */
    public function get($pattern, $handler, $name = null)
    {
        return $this->registerRoute('GET', $pattern, $handler, $name);
    }

    /**
     * Register a POST route
     */
    public function post($pattern, $handler, $name = null)
    {
        return $this->registerRoute('POST', $pattern, $handler, $name);
    }

    /**
     * Register a PUT route
     */
    public function put($pattern, $handler, $name = null)
    {
        return $this->registerRoute('PUT', $pattern, $handler, $name);
    }

    /**
     * Register a DELETE route
     */
    public function delete($pattern, $handler, $name = null)
    {
        return $this->registerRoute('DELETE', $pattern, $handler, $name);
    }

    /**
     * Register a PATCH route
     */
    public function patch($pattern, $handler, $name = null)
    {
        return $this->registerRoute('PATCH', $pattern, $handler, $name);
    }

    /**
     * Register a route for all HTTP methods
     */
    public function any($pattern, $handler, $name = null)
    {
        foreach (['GET', 'POST', 'PUT', 'DELETE', 'PATCH'] as $method) {
            $this->registerRoute($method, $pattern, $handler, $name);
        }
        return $this;
    }

    /**
     * Register a resource route (CRUD)
     */
    public function resource($resource, $controller)
    {
        $this->get("/$resource", "$controller@index", "{$resource}.index");
        $this->get("/$resource/create", "$controller@create", "{$resource}.create");
        $this->post("/$resource", "$controller@store", "{$resource}.store");
        $this->get("/$resource/{id}", "$controller@show", "{$resource}.show");
        $this->get("/$resource/{id}/edit", "$controller@edit", "{$resource}.edit");
        $this->put("/$resource/{id}", "$controller@update", "{$resource}.update");
        $this->delete("/$resource/{id}", "$controller@destroy", "{$resource}.destroy");
        return $this;
    }

    /**
     * Register a route group with common prefix and middleware
     */
    public function group($options, $callback)
    {
        $prefix = $options['prefix'] ?? '';
        $middlewareGroup = $options['middleware'] ?? [];

        // Temporarily set prefix and middleware
        $originalMiddleware = $this->middleware;
        $this->middleware = array_merge($this->middleware, (array)$middlewareGroup);

        // Execute callback (user registers routes within group)
        call_user_func($callback, $this, $prefix);

        // Restore middleware
        $this->middleware = $originalMiddleware;

        return $this;
    }

    /**
     * Register a single route
     */
    private function registerRoute($method, $pattern, $handler, $name = null)
    {
        $pattern = $this->normalizePattern($pattern);

        $route = [
            'pattern' => $pattern,
            'handler' => $handler,
            'regex' => $this->patternToRegex($pattern),
            'middleware' => $this->middleware,
        ];

        $this->routes[$method][] = $route;

        if ($name) {
            $this->namedRoutes[$name] = $route;
        }

        return $this;
    }

    /**
     * Normalize route pattern
     */
    private function normalizePattern($pattern)
    {
        return '/' . trim($pattern, '/');
    }

    /**
     * Convert route pattern to regex
     * Examples: /users/{id} -> /users/(\d+)
     */
    private function patternToRegex($pattern)
    {
        $pattern = preg_replace('/{(\w+)}/', '(?P<$1>[^/]+)', $pattern);
        return '#^' . $pattern . '$#';
    }

    /**
     * Dispatch the request
     */
    public function dispatch()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = $this->getRequestUri();

        // Find matching route
        $route = $this->matchRoute($method, $uri);

        if (!$route) {
            $this->abort(404, 'Route not found');
        }

        $this->currentRoute = $route;

        // Execute middleware before handler
        if (!$this->executeMiddleware($route['middleware'])) {
            $this->abort(403, 'Middleware rejected request');
        }

        // Dispatch to handler
        return $this->executeHandler($route['handler']);
    }

    /**
     * Match route against registered routes
     */
    private function matchRoute($method, $uri)
    {
        if (!isset($this->routes[$method])) {
            return null;
        }

        foreach ($this->routes[$method] as $route) {
            if (preg_match($route['regex'], $uri, $matches)) {
                // Extract named parameters
                foreach ($matches as $key => $value) {
                    if (!is_numeric($key)) {
                        $this->parameters[$key] = $value;
                    }
                }
                return $route;
            }
        }

        return null;
    }

    /**
     * Get the request URI
     */
    private function getRequestUri()
    {
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $basePath = dirname($_SERVER['SCRIPT_NAME']);
        
        if ($basePath !== '/') {
            $uri = substr($uri, strlen($basePath));
        }

        return $uri ?: '/';
    }

    /**
     * Execute middleware
     */
    private function executeMiddleware($middlewareList)
    {
        foreach ($middlewareList as $middleware) {
            if (!$this->executeMiddlewareClass($middleware)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Execute a single middleware class
     */
    private function executeMiddlewareClass($middleware)
    {
        $middlewarePath = APP_PATH . '/middleware/' . $middleware . '.php';
        
        if (!file_exists($middlewarePath)) {
            return true; // Middleware not found, skip
        }

        include $middlewarePath;
        return true;
    }

    /**
     * Execute the route handler
     */
    private function executeHandler($handler)
    {
        // Handler format: 'ControllerName@methodName'
        if (strpos($handler, '@') !== false) {
            list($controller, $method) = explode('@', $handler);
            return $this->callControllerMethod($controller, $method);
        }

        // Direct callable
        if (is_callable($handler)) {
            return call_user_func($handler, $this->parameters);
        }

        throw new \Exception("Invalid handler format: {$handler}");
    }

    /**
     * Call a controller method
     */
    private function callControllerMethod($controllerName, $methodName)
    {
        $controllerClass = 'App\\Controllers\\' . $controllerName;

        if (!class_exists($controllerClass)) {
            throw new \Exception("Controller not found: {$controllerClass}");
        }

        $controller = new $controllerClass();

        if (!method_exists($controller, $methodName)) {
            throw new \Exception("Method not found: {$controllerClass}@{$methodName}");
        }

        return call_user_func_array(
            [$controller, $methodName],
            $this->parameters
        );
    }

    /**
     * Get route parameters
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * Get a specific parameter
     */
    public function getParameter($key, $default = null)
    {
        return $this->parameters[$key] ?? $default;
    }

    /**
     * Generate URL for named route
     */
    public function route($name, $parameters = [])
    {
        if (!isset($this->namedRoutes[$name])) {
            throw new \Exception("Named route not found: {$name}");
        }

        $pattern = $this->namedRoutes[$name]['pattern'];

        foreach ($parameters as $key => $value) {
            $pattern = str_replace('{' . $key . '}', $value, $pattern);
        }

        return APP_URL . $pattern;
    }

    /**
     * Abort with error response
     */
    private function abort($code, $message)
    {
        http_response_code($code);
        
        if (APP_DEBUG) {
            die("[$code] $message");
        } else {
            // Load error view
            $this->loadErrorView($code);
        }

        exit;
    }

    /**
     * Load error view
     */
    private function loadErrorView($code)
    {
        $errorViewPath = APP_PATH . "/views/errors/{$code}.php";
        if (file_exists($errorViewPath)) {
            include $errorViewPath;
        } else {
            die("Error $code");
        }
    }

    /**
     * Get current matched route
     */
    public function getCurrentRoute()
    {
        return $this->currentRoute;
    }
}
?>
