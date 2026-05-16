// ===================================
// API-USERS.JS — User Management API
// ===================================

/**
 * User Management API Module
 * Handles all user CRUD operations
 */
const UsersAPI = {

  baseURL: window.location.origin,

  /**
   * Get CSRF token from localStorage
   */
  getCsrfToken() {
    return localStorage.getItem('csrfToken');
  },

  /**
   * Make API request with auth headers
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
        return {
          success: false, message: data.message || 'Error',
          errors: data.errors, status: response.status
        };
      }

      return data;
    } catch (error) {
      console.error('API Error:', error);
      return { success: false, message: 'Network error. Please try again.' };
    }
  },

  /**
   * List all users with pagination and filtering
   * GET /api/users?page=1&limit=10&search=name&role=manager&status=active
   */
  async list(page = 1, limit = 10, search = '', role = '', status = 'active') {
    const params = new URLSearchParams({
      page,
      limit
    });
    if (search) params.append('search', search);
    if (role) params.append('role', role);
    if (status !== null && status !== undefined) params.append('status', status);

    return this.request(`/api/users?${params}`);
  },

  /**
   * Get single user details
   * GET /api/users/{userId}
   */
  async get(userId) {
    return this.request(`/api/users/${userId}`);
  },

  /**
   * Create new user
   * POST /api/users
   */
  async create(userData) {
    return this.request('/api/users', {
      method: 'POST',
      body: JSON.stringify(userData)
    });
  },

  /**
   * Update user
   * PUT /api/users/{userId}
   */
  async update(userId, userData) {
    return this.request(`/api/users/${userId}`, {
      method: 'PUT',
      body: JSON.stringify(userData)
    });
  },

  /**
   * Delete user (soft delete)
   * DELETE /api/users/{userId}
   */
  async delete(userId) {
    return this.request(`/api/users/${userId}`, {
      method: 'DELETE'
    });
  },

  /**
   * Reset user password
   * POST /api/users/{userId}/reset-password
   */
  async resetPassword(userId, newPassword) {
    return this.request(`/api/users/${userId}/reset-password`, {
      method: 'POST',
      body: JSON.stringify({ new_password: newPassword })
    });
  },

  /**
   * Unlock user account
   * POST /api/users/{userId}/unlock
   */
  async unlock(userId) {
    return this.request(`/api/users/${userId}/unlock`, {
      method: 'POST',
      body: JSON.stringify({})
    });
  }
};
