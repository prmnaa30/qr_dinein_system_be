# Cookie Configuration for Frontend Integration

This document explains the cookie configuration implemented in the backend and provides guidance for frontend integration.

## Backend Configuration

### Changes Made:

1. **Cookie Security Settings**: Updated the `setCookie()` method in `AuthController.php` to conditionally set the secure flag based on the environment
2. **SameSite Policy**: Changed from 'lax' to 'none' to allow cross-origin requests
3. **Domain Configuration**: Enhanced domain detection logic for better cross-origin compatibility

### Key Configuration Values:

- Cookie name: `auth_token`
- Cookie lifetime: 24 hours
- SameSite policy: `none` (allows cross-origin requests)
- HttpOnly: `true` (prevents XSS attacks)
- Secure: `false` in local environment, `true` in production HTTPS
- Domain: Dynamically determined based on environment

## Frontend Requirements

To ensure cookies persist properly in your frontend application, please implement the following:

### 1. Configure Axios (or fetch) to include credentials:

```javascript
// If using Axios
axios.defaults.withCredentials = true;

// Or for individual requests:
axios.get('/api/user', {
  withCredentials: true
});

// If using fetch:
fetch('/api/user', {
  credentials: 'include'
});
```

### 2. Ensure your development server is configured to proxy API requests:

For Vite (if using Vue) or similar tools, configure your dev server to proxy requests to avoid CORS issues:

```javascript
// vite.config.js
export default {
  server: {
    proxy: {
      '/api': {
        target: 'http://localhost:44649', // Your Laravel backend (check your actual backend port)
        changeOrigin: true,
        secure: false,
        withCredentials: true,
      }
    }
  }
}
```

### 3. API Flow:

1. Login/Register → Server sets `auth_token` cookie
2. Subsequent requests → Browser automatically sends cookie
3. Server validates cookie and attaches token to request headers via middleware

## Important Note for Frontend Team

Since we're using `SameSite=None`, your frontend must ensure that requests are made with credentials included. This is essential for the cookie to be sent with each request:

### Complete Implementation Example:

```javascript
// 1. Configure your HTTP client globally to include credentials:

// For Axios (recommended):
const apiClient = axios.create({
  baseURL: 'http://localhost:44649/api', // Update with your backend URL
  withCredentials: true,  // This is crucial!
});

// 2. Login/Registration flow:
const login = async (email, password) => {
  try {
    const response = await apiClient.post('/login', {
      email,
      password
    });
    
    // The auth_token cookie will be automatically stored by the browser
    console.log('Login successful:', response.data);
    return response.data;
  } catch (error) {
    console.error('Login failed:', error);
    throw error;
  }
};

// 3. Subsequent authenticated requests:
const getUserProfile = async () => {
  try {
    // The auth_token cookie will be automatically sent with this request
    const response = await apiClient.get('/user');
    return response.data;
  } catch (error) {
    console.error('Get user failed:', error);
    throw error;
  }
};

const logout = async () => {
  try {
    const response = await apiClient.post('/logout');
    console.log('Logout successful:', response.data);
    return response.data;
  } catch (error) {
    console.error('Logout failed:', error);
    throw error;
  }
};

// 4. Alternative using fetch API:
const makeAuthenticatedRequest = async (url, options = {}) => {
  const response = await fetch(`http://localhost:44649/api${url}`, {
    ...options,
    credentials: 'include',  // This is crucial!
    headers: {
      'Content-Type': 'application/json',
      ...options.headers,
    },
  });
  
  return response.json();
};
```

### Verification Checklist:

Before testing, ensure your frontend meets these requirements:

1. ✅ HTTP requests include credentials (`withCredentials: true` for Axios or `credentials: 'include'` for fetch)
2. ✅ API endpoints are called with the correct base URL (currently: `http://localhost:44649`)
3. ✅ No CORS errors in browser console
4. ✅ After login, check that `auth_token` cookie appears in browser dev tools under Application/Storage tab

### Common Frontend Patterns That Won't Work:

```javascript
// ❌ WRONG - This won't send cookies:
axios.get('/api/user');  

// ❌ WRONG - This creates a new instance without credentials:
const response = await axios.get('/api/user', { /* no withCredentials */ });

// ✅ CORRECT - Use configured client:
apiClient.get('/user');
```

### Additional Frontend Configuration

Make sure your development server is configured to proxy API requests properly to avoid CORS issues:

```javascript
// vite.config.js (for Vite-based projects like Vue 3)
export default {
  server: {
    proxy: {
      '/api': {
        target: 'http://localhost:44649', // Your Laravel backend
        changeOrigin: true,
        secure: false,
        rewrite: (path) => path.replace(/^\/api/, ''),
      }
    }
  }
}

// OR for Create React App (in package.json):
"proxy": "http://localhost:44649"

// OR for webpack-dev-server:
module.exports = {
  devServer: {
    proxy: {
      '/api': {
        target: 'http://localhost:44649',
        changeOrigin: true,
        secure: false,
        pathRewrite: {
          '^/api': '',
        },
      },
    },
  },
};
```

## Troubleshooting

### Common Issues:

1. **Cookies not being sent after refresh**:
   - Ensure frontend is sending `credentials: 'include'` with requests
   - Check that your requests are configured to include cookies

2. **CORS errors**:
   - Verify that `FRONTEND_URL` in the backend .env matches your frontend URL
   - Ensure the frontend is making requests to the correct backend endpoint

3. **Cookie not being set**:
   - Check browser console for any errors
   - Verify the backend port (currently: 44649)

4. **Cross-Origin Requests Blocked**:
   - Make sure you're using `withCredentials: true` in your requests
   - Check that SameSite=None and Secure=false (for HTTP development) are properly set

## Testing the Setup

1. Start the backend: `php artisan serve --port=44649`
2. Log in via `/api/login` or `/api/register`
3. Verify that the `auth_token` cookie is set in browser dev tools
4. Make subsequent authenticated requests - the cookie should be automatically sent
5. Refresh the page - the cookie should still be available if configured correctly

## Security Notes

- The `auth_token` cookie contains a Laravel Sanctum personal access token
- HttpOnly flag prevents client-side JavaScript access (protection against XSS)
- The backend middleware automatically attaches the cookie value to Authorization header
- Tokens are cleared on logout

For any issues with the integration, please ensure your frontend is configured to handle cookies with `credentials: 'include'` for all API requests and that the backend port matches your actual running instance.
