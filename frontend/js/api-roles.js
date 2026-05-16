// ===================================
// API-ROLES.JS — Role Management API
// ===================================

/**
 * Role and Permission Management API Module
 * Handles all role and permission operations
 */
const RolesAPI = {
  
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
        return { success: false, message: data.message || 'Error',
           errors: data.errors, status: response.status };
      }

      return data;
    } catch (error) {
      console.error('API Error:', error);
      return { success: false, message: 'Network error. Please try again.' };
    }
  },

  /**
   * List all roles with permissions
   * GET /api/roles
   */
  async list() {
    return this.request('/api/roles');
  },

  /**
   * List all available permissions
   * GET /api/permissions
   */
  async permissions() {
    return this.request('/api/permissions');
  },

  /**
   * Create new role
   * POST /api/roles
   */
  async create(roleData) {
    return this.request('/api/roles', {
      method: 'POST',
      body: JSON.stringify(roleData)
    });
  },

  /**
   * Update role
   * PUT /api/roles/{roleId}
   */
  async update(roleId, roleData) {
    return this.request(`/api/roles/${roleId}`, {
      method: 'PUT',
      body: JSON.stringify(roleData)
    });
  },

  /**
   * Delete role
   * DELETE /api/roles/{roleId}
   */
  async delete(roleId) {
    return this.request(`/api/roles/${roleId}`, {
      method: 'DELETE'
    });
  }
};
