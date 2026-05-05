// ===================================
// API.JS — Frontend API Helper
// ===================================

/**
 * API utility for communicating with the backend
 */
const API = {
  /**
   * Base URL for API calls
   */
  baseURL: window.location.origin,

  /**
   * Get CSRF token from localStorage
   */
  getCsrfToken() {
    return localStorage.getItem('csrfToken');
  },

  /**
   * Make authenticated API request
   */
  async request(endpoint, options = {}) {
    const url = `${this.baseURL}${endpoint}`;
    
    const headers = {
      'Content-Type': 'application/json',
      ...options.headers
    };

    // Add CSRF token if available
    const csrfToken = this.getCsrfToken();
    if (csrfToken) {
      headers['X-CSRF-Token'] = csrfToken;
    }

    try {
      const response = await fetch(url, {
        ...options,
        headers
      });

      const data = await response.json();

      if (!response.ok) {
        // Handle auth errors
        if (response.status === 401) {
          this.handleUnauthorized();
        }
        
        throw {
          status: response.status,
          message: data.message || 'Request failed',
          data
        };
      }

      return data;
    } catch (error) {
      console.error('API Error:', error);
      throw error;
    }
  },

  /**
   * GET request
   */
  get(endpoint) {
    return this.request(endpoint, { method: 'GET' });
  },

  /**
   * POST request
   */
  post(endpoint, body = {}) {
    return this.request(endpoint, {
      method: 'POST',
      body: JSON.stringify(body)
    });
  },

  /**
   * PUT request
   */
  put(endpoint, body = {}) {
    return this.request(endpoint, {
      method: 'PUT',
      body: JSON.stringify(body)
    });
  },

  /**
   * DELETE request
   */
  delete(endpoint) {
    return this.request(endpoint, { method: 'DELETE' });
  },

  /**
   * Handle unauthorized access (401)
   */
  handleUnauthorized() {
    // Clear auth data
    localStorage.removeItem('userRole');
    localStorage.removeItem('userName');
    localStorage.removeItem('userId');
    localStorage.removeItem('csrfToken');
    
    // Redirect to login
    window.location.href = '/login';
  },

  /**
   * Check if user is authenticated
   */
  isAuthenticated() {
    return !!localStorage.getItem('userId');
  },

  /**
   * Get current user data from localStorage
   */
  getCurrentUser() {
    return {
      id: localStorage.getItem('userId'),
      name: localStorage.getItem('userName'),
      role: localStorage.getItem('userRole')
    };
  },

  /**
   * Login user
   */
  login(email, password) {
    return this.post('/api/login', { email, password });
  },

  /**
   * Logout user
   */
  logout() {
    return this.post('/api/logout');
  },

  /**
   * Get current user from API
   */
  getUser() {
    return this.get('/api/user');
  }
};

// Make API available globally
window.API = API;
