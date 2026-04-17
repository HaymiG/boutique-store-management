<?php
/**
 * Authentication Controller
 * Handles login, logout, and registration
 */

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Models\User;

class AuthController extends Controller
{
    /**
     * Skip authorization for auth routes (they're public)
     */
    protected function authorize()
    {
        // Auth routes are public — no authorization needed
    }

    /**
     * Show login form
     */
    public function showLogin()
    {
        // If already logged in, redirect to dashboard
        if ($this->isAuthenticated()) {
            $this->redirect('/dashboard');
        }

        $this->view('auth/login');
    }

    /**
     * Process login
     */
    public function login()
    {
        // Validate CSRF
        $this->validateCsrf();

        $email = trim($this->request('email', ''));
        $password = $this->request('password', '');
        $remember = $this->request('remember_me', false);

        // Basic validation
        $errors = $this->validate([
            'email' => $email,
            'password' => $password,
        ], [
            'email' => 'required|email',
            'password' => 'required|min:6',
        ]);

        if ($errors !== true) {
            $this->session->setFlash('error', 'Please provide a valid email and password.');
            $this->session->setFlash('old_email', $email);
            $this->redirect('/login');
            return;
        }

        // Find user by email
        $user = User::findByEmail($email);

        if (!$user) {
            $this->session->setFlash('error', 'Invalid email or password.');
            $this->session->setFlash('old_email', $email);
            $this->redirect('/login');
            return;
        }

        // Check if account is locked
        if ($user->isLocked()) {
            $remaining = ceil($user->getLockoutRemaining() / 60);
            $this->session->setFlash('error', "Account locked. Try again in {$remaining} minute(s).");
            $this->session->setFlash('old_email', $email);
            $this->redirect('/login');
            return;
        }

        // Check if account is active
        if (!$user->isActive()) {
            $this->session->setFlash('error', 'Your account has been deactivated. Contact your administrator.');
            $this->session->setFlash('old_email', $email);
            $this->redirect('/login');
            return;
        }

        // Verify password
        if (!$user->verifyPassword($password)) {
            $user->incrementLoginAttempts();
            $attemptsLeft = AUTH_MAX_LOGIN_ATTEMPTS - $user->login_attempts;
            
            if ($attemptsLeft > 0) {
                $this->session->setFlash('error', "Invalid email or password. {$attemptsLeft} attempt(s) remaining.");
            } else {
                $this->session->setFlash('error', 'Account locked due to too many failed attempts.');
            }
            
            $this->session->setFlash('old_email', $email);
            $this->redirect('/login');
            return;
        }

        // Authentication successful
        // Regenerate session ID to prevent session fixation
        $this->session->regenerate();

        // Store user data in session
        $this->session->setUser($user->toSessionData());

        // Update last login
        $user->updateLastLogin();

        // Handle "Remember Me"
        if ($remember) {
            // Extend session lifetime to 30 days
            ini_set('session.cookie_lifetime', 30 * 24 * 60 * 60);
        }

        // Redirect to intended URL or dashboard
        $intendedUrl = $this->session->get('_intended_url', '/dashboard');
        $this->session->remove('_intended_url');

        $this->session->setFlash('success', 'Welcome back, ' . $user->getFullName() . '!');
        $this->redirect($intendedUrl);
    }

    /**
     * Logout
     */
    public function logout()
    {
        $this->session->destroy();
        
        // Start a new session for the flash message
        $newSession = Session::getInstance();
        $newSession->setFlash('success', 'You have been logged out successfully.');
        
        $this->redirect('/login');
    }

    /**
     * Show registration form (manager only in production)
     */
    public function showRegister()
    {
        // If already logged in, redirect to dashboard
        if ($this->isAuthenticated()) {
            $this->redirect('/dashboard');
        }

        $this->view('auth/register');
    }

    /**
     * Process registration
     */
    public function register()
    {
        // Validate CSRF
        $this->validateCsrf();

        $data = [
            'first_name' => trim($this->request('first_name', '')),
            'last_name' => trim($this->request('last_name', '')),
            'username' => trim($this->request('username', '')),
            'email' => trim($this->request('email', '')),
            'password' => $this->request('password', ''),
            'password_confirmation' => $this->request('password_confirmation', ''),
        ];

        // Validation
        $errors = [];

        if (empty($data['first_name'])) {
            $errors['first_name'] = 'First name is required';
        }
        if (empty($data['last_name'])) {
            $errors['last_name'] = 'Last name is required';
        }
        if (empty($data['username'])) {
            $errors['username'] = 'Username is required';
        } elseif (!User::isUsernameUnique($data['username'])) {
            $errors['username'] = 'Username already taken';
        }
        if (empty($data['email'])) {
            $errors['email'] = 'Email is required';
        } elseif (!User::isValidEmail($data['email'])) {
            $errors['email'] = 'Invalid email format';
        } elseif (!User::isEmailUnique($data['email'])) {
            $errors['email'] = 'Email already registered';
        }
        if (empty($data['password'])) {
            $errors['password'] = 'Password is required';
        } elseif (!User::isValidPassword($data['password'])) {
            $errors['password'] = 'Password must be at least 8 characters with uppercase, lowercase, and number';
        }
        if ($data['password'] !== $data['password_confirmation']) {
            $errors['password_confirmation'] = 'Passwords do not match';
        }

        if (!empty($errors)) {
            $this->session->setFlash('errors', $errors);
            $this->session->setFlash('old', $data);
            $this->redirect('/register');
            return;
        }

        // Create user with default seller role
        $data['role_id'] = ROLES['seller'];
        $user = User::createUser($data);

        if ($user) {
            $this->session->setFlash('success', 'Account created successfully! Please log in.');
            $this->redirect('/login');
        } else {
            $this->session->setFlash('error', 'Registration failed. Please try again.');
            $this->redirect('/register');
        }
    }
}
?>
