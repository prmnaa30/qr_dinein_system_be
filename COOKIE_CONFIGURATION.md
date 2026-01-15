# Cookie Configuration for Frontend Integration

This document explains the cookie configuration implemented in the backend and provides guidance for frontend integration.

## Backend Configuration

### Changes Made:

1. **Cookie Security Settings**: Updated the `setCookie()` method in `AuthController.php` to conditionally set the secure flag based on the environment
2. **SameSite Policy**: Changed from 'lax' to 'none' to allow cross-origin requests
3. **Domain Configuration**: Added proper domain configuration in `.env` file

### Key Configuration Values:

- Cookie name: `auth_token`
- Cookie lifetime: 24 hours
- SameSite policy: `none` (allows cross-origin requests)
- HttpOnly: `true` (prevents XSS attacks)
- Secure: `false` in local environment, `true` in production HTTPS

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
        target: 'http://localhost:8000', // Your Laravel backend
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

## Troubleshooting

### Common Issues:

1. **Cookies not being sent after refresh**:
   - Ensure frontend is sending `credentials: 'include'` with requests
   - Check that the domain configuration matches your setup

2. **CORS errors**:
   - Verify that `FRONTEND_URL` in the backend .env matches your frontend URL
   - Ensure the frontend is making requests to the correct backend endpoint

3. **Secure cookies not working in development**:
   - The system automatically disables secure cookies in local environment
   - This allows HTTP requests during development

## Testing the Setup

1. Log in via `/api/login` or `/api/register`
2. Verify that the `auth_token` cookie is set in browser dev tools
3. Make subsequent authenticated requests - the cookie should be automatically sent
4. Refresh the page - the cookie should still be available

## Security Notes

- The `auth_token` cookie contains a Laravel Sanctum personal access token
- HttpOnly flag prevents client-side JavaScript access (protection against XSS)
- The backend middleware automatically attaches the cookie value to Authorization header
- Tokens are cleared on logout

For any issues with the integration, please check that your frontend is configured to handle cookies with `credentials: 'include'` for all API requests.
