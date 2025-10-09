# Authentication API Documentation

This document describes how to use the authentication API endpoints.

## Base URL
All API endpoints are prefixed with `/api`

## Response Format
All API responses follow this consistent JSON structure:

### Success Response
```json
{
    "success": true,
    "message": "Operation successful",
    "data": {
        // Response data here
    }
}
```

### Error Response
```json
{
    "success": false,
    "message": "Error message",
    "errors": {
        // Validation errors or error details here
    }
}
```

## Authentication Endpoints

### 1. Register
**POST** `/api/auth/register`

Register a new user account.

#### Request Body
```json
{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123",
    "password_confirmation": "password123"
}
```

#### Success Response (201)
```json
{
    "success": true,
    "message": "Registration successful",
    "data": {
        "user": {
            "id": 1,
            "name": "John Doe",
            "email": "john@example.com",
            "email_verified_at": null,
            "created_at": "2025-10-07T10:00:00.000000Z",
            "updated_at": "2025-10-07T10:00:00.000000Z"
        },
        "token": "1|abc123def456...",
        "token_type": "Bearer"
    }
}
```

#### Error Response (422)
```json
{
    "success": false,
    "message": "Validation failed",
    "errors": {
        "email": ["This email address is already registered."],
        "password": ["Password confirmation does not match."]
    }
}
```

### 2. Login
**POST** `/api/auth/login`

Authenticate a user and receive an access token.

#### Request Body
```json
{
    "email": "john@example.com",
    "password": "password123"
}
```

#### Success Response (200)
```json
{
    "success": true,
    "message": "Login successful",
    "data": {
        "user": {
            "id": 1,
            "name": "John Doe",
            "email": "john@example.com",
            "email_verified_at": null,
            "created_at": "2025-10-07T10:00:00.000000Z",
            "updated_at": "2025-10-07T10:00:00.000000Z"
        },
        "token": "2|xyz789abc123...",
        "token_type": "Bearer"
    }
}
```

#### Error Response (401)
```json
{
    "success": false,
    "message": "The provided credentials are incorrect.",
    "errors": {
        "email": ["The provided credentials are incorrect."]
    }
}
```

### 3. Forgot Password
**POST** `/api/auth/forgot-password`

Send a password reset link to the user's email.

#### Request Body
```json
{
    "email": "john@example.com"
}
```

#### Success Response (200)
```json
{
    "success": true,
    "message": "Password reset link sent successfully",
    "data": {
        "status": "We have emailed your password reset link!"
    }
}
```

#### Error Response (400)
```json
{
    "success": false,
    "message": "Unable to send password reset link",
    "errors": {
        "email": ["We can't find a user with that email address."]
    }
}
```

### 4. Reset Password
**POST** `/api/auth/reset-password`

Reset the user's password using the token received via email.

#### Request Body
```json
{
    "token": "reset_token_from_email",
    "email": "john@example.com",
    "password": "newpassword123",
    "password_confirmation": "newpassword123"
}
```

#### Success Response (200)
```json
{
    "success": true,
    "message": "Password reset successful",
    "data": {
        "status": "Your password has been reset successfully!"
    }
}
```

#### Error Response (400)
```json
{
    "success": false,
    "message": "Password reset failed",
    "errors": {
        "email": ["This password reset token is invalid."]
    }
}
```

### 5. Logout
**POST** `/api/auth/logout`

Logout the authenticated user and invalidate their token.

#### Headers
```
Authorization: Bearer your_access_token_here
```

#### Success Response (200)
```json
{
    "success": true,
    "message": "Logout successful"
}
```

### 6. Get Current User
**GET** `/api/auth/me`

Get the authenticated user's information.

#### Headers
```
Authorization: Bearer your_access_token_here
```

#### Success Response (200)
```json
{
    "success": true,
    "message": "User data retrieved successfully",
    "data": {
        "user": {
            "id": 1,
            "name": "John Doe",
            "email": "john@example.com",
            "email_verified_at": null,
            "created_at": "2025-10-07T10:00:00.000000Z",
            "updated_at": "2025-10-07T10:00:00.000000Z"
        }
    }
}
```

## Using the API

### 1. Authentication Flow
1. Register a new account or login with existing credentials
2. Store the received token securely
3. Include the token in the `Authorization` header for protected endpoints:
   ```
   Authorization: Bearer your_token_here
   ```

### 2. Example with cURL

#### Register
```bash
curl -X POST http://your-domain.com/api/auth/register \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123",
    "password_confirmation": "password123"
  }'
```

#### Login
```bash
curl -X POST http://your-domain.com/api/auth/login \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "email": "john@example.com",
    "password": "password123"
  }'
```

#### Get User Info
```bash
curl -X GET http://your-domain.com/api/auth/me \
  -H "Authorization: Bearer your_token_here" \
  -H "Accept: application/json"
```

#### Logout
```bash
curl -X POST http://your-domain.com/api/auth/logout \
  -H "Authorization: Bearer your_token_here" \
  -H "Accept: application/json"
```

### 3. Example with JavaScript (Fetch API)

```javascript
// Register
const registerUser = async (userData) => {
  const response = await fetch('/api/auth/register', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
    },
    body: JSON.stringify(userData)
  });
  
  return await response.json();
};

// Login
const loginUser = async (credentials) => {
  const response = await fetch('/api/auth/login', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
    },
    body: JSON.stringify(credentials)
  });
  
  return await response.json();
};

// Get user info
const getUserInfo = async (token) => {
  const response = await fetch('/api/auth/me', {
    method: 'GET',
    headers: {
      'Authorization': `Bearer ${token}`,
      'Accept': 'application/json',
    }
  });
  
  return await response.json();
};

// Logout
const logoutUser = async (token) => {
  const response = await fetch('/api/auth/logout', {
    method: 'POST',
    headers: {
      'Authorization': `Bearer ${token}`,
      'Accept': 'application/json',
    }
  });
  
  return await response.json();
};
```

## Error Codes

- **200**: Success
- **201**: Created (successful registration)
- **400**: Bad Request
- **401**: Unauthorized (invalid credentials)
- **422**: Validation Error
- **500**: Internal Server Error

## Notes

1. All endpoints require `Content-Type: application/json` and `Accept: application/json` headers
2. The access token does not expire automatically - you need to implement token rotation if needed
3. Password reset emails are sent using Laravel's built-in mail system
4. Make sure to configure your `.env` file with proper mail settings for forgot password functionality
5. All timestamps are in UTC format